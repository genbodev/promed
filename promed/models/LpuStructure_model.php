<?php
/**
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Markoff Andrew
* @version      31.08.2009
*/

class LpuStructure_model extends swModel {
	/**
	 * Схема в БД
	 */
	protected $_scheme = "dbo";

    /**
	 * Правила для входящих параметров
	 */
	public $inputRules = array(
		'createLpuUnit' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitType_id', 'label' => 'Тип группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitTypeDop_id', 'label' => 'Дополнительный тип группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Адрес группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_Code', 'label' => 'Код группы отделений', 'rules' => 'required|is_numeric|is_natural_no_zero|max_length[8]', 'type' => 'string'),
			array('field' => 'LpuUnit_Name', 'label' => 'Наименование группы отделений', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuUnit_Descr', 'label' => 'Комментарии по записи на прием', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_Phone', 'label' => 'Телефон записи на прием', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_IsEnabled', 'label' => 'Признак активности', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_isPallCC', 'label' => 'Признак центра паллиативной медицинской помощи (ПМП)', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_IsNotFRMO', 'label' => 'Признак запрета передачи в ФРМО', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_IsDirWithRec', 'label' => 'Признак записи', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_ExtMedCnt', 'label' => 'Признак приема по скорой помощи', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuUnit_Email', 'label' => 'Электронная почта', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnit_IP', 'label' => 'Адрес и способ подключения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnitSet_id', 'label' => 'Идентификатор объединения групп отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_Guid', 'label' => 'ГУИД', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_begDate', 'label' => 'Дата начала действия записи', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_endDate', 'label' => 'Дата окончания действия записи', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_IsOMS', 'label' => 'Признак Работает по ОМС', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'UnitDepartType_fid', 'label' => 'Тип (ФРМО)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitProfile_fid', 'label' => 'Профиль (ФРМО)', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_isStandalone', 'label' => 'Признак "Обособленное подразделение"', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Основное здание', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_isHomeVisit', 'label' => 'Признак "Приём на дому"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_isCMP', 'label' => 'Признак "Приём скорой помощи"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_FRMOUnitID', 'label' => 'Идентификатор ФРМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_FRMOid', 'label' => 'Идентификатор ФРМО для структурного подразделения', 'rules' => '', 'type' => 'id'),
		),
		'updateLpuUnit' => array(
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitType_id', 'label' => 'Тип группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitTypeDop_id', 'label' => 'Дополнительный тип группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Адрес группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_Code', 'label' => 'Код группы отделений', 'rules' => 'required|is_numeric|is_natural_no_zero|max_length[8]', 'type' => 'string'),
			array('field' => 'LpuUnit_Name', 'label' => 'Наименование группы отделений', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuUnit_Descr', 'label' => 'Комментарии по записи на прием', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_Phone', 'label' => 'Телефон записи на прием', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_IsEnabled', 'label' => 'Признак активности', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_isPallCC', 'label' => 'Признак центра паллиативной медицинской помощи (ПМП)', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_IsNotFRMO', 'label' => 'Признак запрета передачи в ФРМО', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_IsDirWithRec', 'label' => 'Признак записи', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_ExtMedCnt', 'label' => 'Признак приема по скорой помощи', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuUnit_Email', 'label' => 'Электронная почта', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnit_IP', 'label' => 'Адрес и способ подключения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnitSet_id', 'label' => 'Идентификатор объединения групп отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_Guid', 'label' => 'ГУИД', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_begDate', 'label' => 'Дата начала действия записи', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_endDate', 'label' => 'Дата окончания действия записи', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_IsOMS', 'label' => 'Признак Работает по ОМС', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'UnitDepartType_fid', 'label' => 'Тип (ФРМО)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitProfile_fid', 'label' => 'Профиль (ФРМО)', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_isStandalone', 'label' => 'Признак "Обособленное подразделение"', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Основное здание', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_isHomeVisit', 'label' => 'Признак "Приём на дому"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_isCMP', 'label' => 'Признак "Приём скорой помощи"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuUnit_FRMOUnitID', 'label' => 'Идентификатор ФРМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_FRMOid', 'label' => 'Идентификатор ФРМО для структурного подразделения', 'rules' => '', 'type' => 'id'),
		),
		'getLpuUnitById' => array(
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_OID', 'label' => 'OID структурного подразделения в справочнике структурных подразделений ФРМО', 'rules' => '', 'type' => 'string')
		),
		'getLpuUnitList' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
		),
		'createLpuSection' => array(
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_setDate', 'label' => 'Дата создания отделения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSection_disDate', 'label' => 'Дата закрытия отделения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSectionProfile_Code', 'label' => 'Код профиля отделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuSection_Code', 'label' => 'Код отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionCode_id', 'label' => 'Код отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_Name', 'label' => 'Наименование отделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MesAgeGroup_id', 'label' => 'Идентификатор фозрастной группы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MESLevel_id', 'label' => 'Идентификатор уровня МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsHTMedicalCare', 'label' => 'Флаг выполненеия ВМП', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuSection_KolAmbul', 'label' => 'Количество бригад скорой помощи', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_KolJob', 'label' => 'Количество рабочих мест', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanAutopShift', 'label' => 'Плановое число вскрытий за смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanResShift', 'label' => 'Плановое число исследований в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanTrip', 'label' => 'Плановое число выездов в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanVisitDay', 'label' => 'Плановое число посещений в сутки', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanVisitShift', 'label' => 'Плановое число посещений в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionAge_id', 'label' => 'Идентификатор типа отделения по возрасту', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Идентификатор профиля коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionOuter_id', 'label' => 'Идентификатор отделения МО в сторонней МИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Код типа отделения (по ФРМР) для формы 30', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания МО', 'rules' => '', 'type' => 'id'),
		),
		'createLpuSectionDopProfile' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionLpuSectionProfile_begDate', 'label' => 'Дата начала действия дополнительного профиля отделения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSectionLpuSectionProfile_endDate', 'label' => 'Дата окончания действия дополнительного профиля отделения', 'rules' => '', 'type' => 'date'),
		),
		'deleteLpuSection' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
		),
		'deleteLpuSectionDopProfile' => array(
			array('field' => 'LpuSectionLpuSectionProfile_id', 'label' => 'Идентификатор дополнительного профиля отделения', 'rules' => 'required', 'type' => 'id'),
		),
		'createLpuSectionBedState' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Идентификатор профиля коек', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfileLink_fedid', 'label' => 'Идентификатор стыковочной таблицы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionBedState_ProfileName', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionBedState_Fact', 'label' => 'Фактическое количество', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_Plan', 'label' => 'Планируемое количество', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_Repair', 'label' => 'Отклонение от планируемого количества', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_CountOms', 'label' => 'Количество коек, оплачиваемых по ОМС', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_MalePlan', 'label' => 'Мужские койки. Плановое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_MaleFact', 'label' => 'Мужские койки Фактическое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_FemalePlan', 'label' => 'Женские койки. Плановое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_FemaleFact', 'label' => 'Женские койки. Фактическое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSectionBedState_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
		),
		'updateLpuSectionBedState' => array(
			array('field' => 'LpuSectionBedState_id', 'label' => 'Идентификатор коек и их профилей в отделении МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Идентификатор профиля коек', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfileLink_fedid', 'label' => 'Идентификатор стыковочной таблицы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionBedState_ProfileName', 'label' => 'Наименование', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionBedState_Fact', 'label' => 'Фактическое количество', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_Plan', 'label' => 'Планируемое количество', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_Repair', 'label' => 'Отклонение от планируемого количества', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_CountOms', 'label' => 'Количество коек, оплачиваемых по ОМС', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_MalePlan', 'label' => 'Мужские койки. Плановое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_MaleFact', 'label' => 'Мужские койки Фактическое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_FemalePlan', 'label' => 'Женские койки. Плановое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_FemaleFact', 'label' => 'Женские койки. Фактическое количество', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionBedState_begDate', 'label' => 'Дата начала', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSectionBedState_endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date'),
		),
		'deleteLpuSectionBedState' => array(
			array('field' => 'LpuSectionBedState_id', 'label' => 'Идентификатор коек и их профилей в отделении МО', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionBedStateListBySection' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения МО', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuBuildingById' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuRegionByID' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuRegionByМО' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuRegionWorkerPlaceByID' => array(
			array('field' => 'MedStaffRegion_id', 'label' => 'Идентификатор периода работы врача на участке', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuRegionWorkerPlaceByRegion' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuRegionWorkerPlaceListByMedStaffFact' => array(
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuRegionWorkerPlaceListByTime' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffRegion_begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'MedStaffRegion_endDate', 'label' => 'Дата окончания периода', 'rules' => '', 'type' => 'date'),
		),
		'getLpuBuildingListByCodeAndName' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_Code', 'label' => 'Код подразделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuBuilding_Name', 'label' => 'Наименование подразделения', 'rules' => '', 'type' => 'string'),
		),
		'getLpuBuildingListByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_OID', 'label' => 'МО в ФРМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'extended', 'label' => 'Признак возврата дополнительных полей', 'rules' => '', 'type' => 'int'),
		),
		'getLpuListByRegion' => array(
			array('field' => 'Region_id', 'label' => 'Код региона', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'offset', 'label' => 'Смещение', 'rules' => '', 'type' => 'int'),
			array('field' => 'allowTest', 'label' => 'Признак возврата тестовых МО', 'rules' => '', 'type' => 'int'),
			array('field' => 'extended', 'label' => 'Признак возврата дополнительных полей', 'rules' => '', 'type' => 'int'),
		),
		'getLpuListBySubRgn' => array(
			array('field' => 'SubRgn_id', 'label' => 'Идентификатор района', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Extended', 'label' => 'Признак возврата дополнительных полей', 'rules' => '', 'type' => 'int'),
		),
		'getLpuSectionById' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionOuter_id', 'label' => 'Идентификатор отделения МО в сторонней МИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_OID', 'label' => 'OID отделения в справочнике отделений и кабинетов ФРМО', 'rules' => '', 'type' => 'string')
		),
		'getLpuSectionDopProfileList' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_OID', 'label' => 'OID отделения в справочнике отделений и кабинетов ФРМО', 'rules' => '', 'type' => 'string')
		),
		'getLpuSectionListByBuilding' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionListByCodeAndName' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_Code', 'label' => 'Код отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionCode_Code', 'label' => 'Код отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_Name', 'label' => 'Наименование отделения', 'rules' => 'required', 'type' => 'string'),
		),
		'getLpuSectionListByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_OID', 'label' => 'МО в ФРМО', 'rules' => '', 'type' => 'string')
		),
		'saveLpuBuilding' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_begDate', 'label' => 'Период действия с', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_endDate', 'label' => 'Период действия по', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_Code', 'label' => 'Код подразделения', 'rules' => 'required|is_numeric|is_natural_no_zero', 'type' => 'int'),
			array('field' => 'LpuBuilding_IsExport', 'label' => 'Признак "Выгружать в ПМУ"', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_Name', 'label' => 'Наименование подразделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuBuilding_Nick', 'label' => 'Краткое наименование подразделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_WorkTime', 'label' => 'Время работы', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_RoutePlan', 'label' => 'Схема проезда', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuildingType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_CmpStationCode', 'label' => 'Код станции', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_CmpSubstationCode', 'label' => 'Код подстанции', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_id', 'label' => 'Идентификатор адреса для выписки рецептов', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'PAddress_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'PKLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_Latitude', 'label' => 'Координаты широты', 'rules' => '', 'type'  => 'float'),
			array('field' => 'LpuBuilding_Longitude', 'label' => 'Координаты долготы', 'rules' => '', 'type'  => 'float'),
			array('field' => 'LpuFilial_id', 'label' => 'Идентификатор филиала', 'rules' => '', 'type'  => 'id'),
			array('field' => 'LpuBuilding_IsAIDSCenter', 'label' => 'СПИД-центр', 'rules' => '', 'type' => 'swcheckbox'),
			array('field' => 'LpuBuildingHealth_Phone', 'label' => 'Телефон кабинета здоровья', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuildingHealth_Email', 'label' => 'Электронная почта кабинета здоровья', 'rules' => '', 'type' => 'string'),
		),
		'createLpuBuilding' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuBuilding_begDate', 'label' => 'Период действия с', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_endDate', 'label' => 'Период действия по', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_Code', 'label' => 'Код подразделения', 'rules' => 'required|is_numeric|is_natural_no_zero', 'type' => 'int'),
			array('field' => 'LpuBuilding_IsExport', 'label' => 'Признак "Выгружать в ПМУ"', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuilding_Name', 'label' => 'Наименование подразделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuBuilding_Nick', 'label' => 'Краткое наименование подразделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_WorkTime', 'label' => 'Время работы', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_RoutePlan', 'label' => 'Схема проезда', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuildingType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_CmpStationCode', 'label' => 'Код станции', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_CmpSubstationCode', 'label' => 'Код подстанции', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_id', 'label' => 'Идентификатор адреса для выписки рецептов', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'PAddress_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'PKLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_Latitude', 'label' => 'Координаты широты', 'rules' => '', 'type'  => 'float'),
			array('field' => 'LpuBuilding_Longitude', 'label' => 'Координаты долготы', 'rules' => '', 'type'  => 'float'),
		),
		'updateLpuBuilding' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuBuilding_begDate', 'label' => 'Период действия с', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_endDate', 'label' => 'Период действия по', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuilding_Code', 'label' => 'Код подразделения', 'rules' => 'is_numeric|is_natural_no_zero', 'type' => 'int'),
			array('field' => 'LpuBuilding_IsExport', 'label' => 'Признак "Выгружать в ПМУ"', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuilding_Name', 'label' => 'Наименование подразделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_Nick', 'label' => 'Краткое наименование подразделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_WorkTime', 'label' => 'Время работы', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_RoutePlan', 'label' => 'Схема проезда', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuildingType_id', 'label' => 'Идентификатор типа подразделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_CmpStationCode', 'label' => 'Код станции', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuilding_CmpSubstationCode', 'label' => 'Код подстанции', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_id', 'label' => 'Идентификатор адреса для выписки рецептов', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'Address_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'PAddress_Address', 'label' => 'Адрес', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_House', 'label' => 'Дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Corpus', 'label' => 'Корпус', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Flat', 'label' => 'Квартира', 'rules' => '', 'type' => 'string'),
			array('field' => 'PAddress_Zip', 'label' => 'Индекс', 'rules' => '', 'type' => 'string'),
			array('field' => 'PKLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLRGN_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLSubRGN_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLTown_id', 'label' => 'Идентификатор населенного пункта', 'rules' => '', 'type' => 'id'),
			array('field' => 'PKLStreet_id', 'label' => 'Идентификатор улицы', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_Latitude', 'label' => 'Координаты широты', 'rules' => '', 'type'  => 'float'),
			array('field' => 'LpuBuilding_Longitude', 'label' => 'Координаты долготы', 'rules' => '', 'type'  => 'float'),
		),
		'saveLpuRegion' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_Name', 'label' => 'Номер участка', 'rules' => 'required|is_numeric|is_natural_no_zero', 'type' => 'int'),
			array('field' => 'LpuRegion_tfoms', 'label' => 'Номер участка в ТФОМС', 'rules' => 'is_numeric|is_natural_no_zero', 'type' => 'int'),
			array('field' => 'LpuRegion_Descr', 'label' => 'Описание участка', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuRegionType_id', 'label' => 'Тип участка', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuRegionType_SysNick', 'label' => 'Системное наименование типа участка', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuRegion_begDate', 'label' => 'Дата создания', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuRegion_endDate', 'label' => 'Дата закрытия', 'rules' => '', 'type' => 'date'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'LpuSection_id', 'label' => 'Отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegionMedPersonalData', 'label' => 'Врачи на участке', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkPost', 'label' => 'Признак проверки должности', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkRegType', 'label' => 'Признак проверки типа отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkMainMPDoubles', 'label' => 'Признак проверки дублей основного врача', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkLpuSection', 'label' => 'Признак проверки отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'checkStavka', 'label' => 'Признак проверки ставки', 'rules' => '', 'type' => 'string'),
		),
		'saveLpuSection' => array(
			array('field' => 'LevelType_id', 'label' => 'Уровень', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuCostType_id', 'label' => 'Признак участия в формировании затрат МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_CountShift', 'label' => 'Ставка', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_Area', 'label' => 'Площадь отделения', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuSectionType_id', 'label' => 'Пункт', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedicalCareKind_id', 'label' => 'Вид МП', 'rules' => '', 'type' => 'id'),
			array('field' => 'lpuSectionProfileData', 'label' => 'Список дополнительных профилей отделения', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'lpuSectionMedProductTypeLinkData', 'label' => 'Список медицинского оборудования', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuSectionServiceData', 'label' => 'Список обслуживаемых отделений', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'AttributeSignValueData', 'label' => 'Список значний атрибутов', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_pid', 'label' => 'Родительский идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_id', 'label' => 'Группа отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Профиль', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_Code', 'label' => 'Код отделения', 'rules' => 'trim|required|is_numeric|is_natural|max_length[9]', 'type' => 'string'),
			array('field' => 'LpuSectionCode_id', 'label' => 'Код отделения', 'rules' => '', 'type' => 'id' ),
			array('field' => 'LpuSection_Name', 'label' => 'Наименование отделения', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuSection_setDate', 'label' => 'Дата начала действия', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_disDate', 'label' => 'Дата окончания действия', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSectionAge_id', 'label' => 'Возрастная группа', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Профиль коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'MESLevel_id', 'label' => 'Уровень МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_Descr', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_Contacts', 'label' => 'Контакты', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionHospType_id', 'label' => 'Вид госпитализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionDopType_id', 'label' => 'Доп. тип отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsDirRec', 'label' => 'Разрешить запись в отделение через направления', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_F14', 'label' => 'Использовать в форме 14-ОМС', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_IsUseReg', 'label' => 'Использовать данные подотделения в реестрах', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_IsQueueOnFree', 'label' => 'Разрешить помещать в очередь при наличии свободных мест', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_PlanAutopShift', 'label' => 'Плановое число вскрытий в смену', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_PlanVisitShift', 'label' => 'Плановое число посещений в смену', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_PlanTrip', 'label' => 'Плановое число выездов в смену', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_KolAmbul', 'label' => 'Количество бригад скорой помощи', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_PlanResShift', 'label' => 'Плановое число исследований в смену', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_KolJob', 'label' => 'Количество рабочих мест', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_PlanVisitDay', 'label' => 'Плановое число посещений в сутки', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_IsCons', 'label' => 'Консультационное отделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsExportLpuRegion', 'label' => 'Выгружать участки', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsHTMedicalCare', 'label' => 'Выполнение высокотехнологичной медицинской помощи', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsNoKSG', 'label' => 'Без КСГ', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionMedicalCareKindData', 'label' => 'Виды оказания МП', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Типо (Форма 30)', 'rules' => '', 'type' => 'string'),
			array('field' => 'FPID', 'label' => 'Функциональное подразделение (СУР)', 'rules' => '', 'type' => 'id'),
			array('field' => 'PalliativeType_id', 'label' => 'Вид отделения ПМП', 'rules' => '', 'type' => 'id'),
			array('field' => 'FRMOUnit_id', 'label' => 'ФРМО Справочник структурных подразделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'FRMOSection_id', 'label' => 'ФРМО Справочник отделений и кабинетов', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_FRMOBuildingOid', 'label' => 'Идентификатор структурного подразделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSection_IsNotFRMO', 'label' => 'Признак запрета отправки в ФРМО', 'rules' => '', 'type' => 'string'),
		),
		'saveLpuUnit' => array(
			array('field' => 'DopNew', 'label' => 'Тип(доп.)', 'rules' => '', 'type'  => 'string'),
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_begDate', 'label' => 'Период действия: от', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_endDate', 'label' => 'Период действия: до', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuUnit_Code', 'label' => 'Код группы отделений', 'rules' => 'required|is_numeric|is_natural_no_zero|max_length[8]', 'type' => 'string'),
			array('field' => 'LpuUnit_Name', 'label' => 'Наименование группы отделений', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuUnitType_id', 'label' => 'Тип группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuUnitTypeDop_id', 'label' => 'Дополнительный тип группы отделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_Phone', 'label' => 'Телефоны', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_Descr', 'label' => 'Примечание', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_Email', 'label' => 'Email', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnit_IP', 'label' => 'IP адрес', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuUnitSet_id', 'label' => 'Справочник', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Подразделение', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuUnit_IsEnabled', 'label' => 'Включить запись операторами', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_isPallCC', 'label' => 'Признак центра паллиативной медицинской помощи (ПМП)', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_IsNotFRMO', 'label' => 'Признак запрета передачи в ФРМО', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_IsOMS', 'label' => 'Флаг работы по ОМС', 'rules' => '', 'type' => 'checkbox'),
			array('field' => 'LpuUnitProfile_fid', 'label' => 'Тип (ФРМО)', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_isStandalone', 'label' => 'Обособленность', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_isCMP', 'label' => 'Прием скорой помощи', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_isHomeVisit', 'label' => 'Прием на дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'UnitDepartType_fid', 'label' => 'Прием на дом', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Основное здание', 'rules' => '', 'type' => 'string'),
			array('field' => 'FRMOUnit_id', 'label' => 'ФРМО Справочник структурных подразделений', 'rules' => '', 'type' => 'id'),
			array('field' => 'ignoreFRMOUnitCheck', 'label' => 'Признак игнорирования проверки', 'rules' => '', 'type' => 'id'),
		),
		'getLpuRegionListByName' => array(
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuRegion_Name', 'label' => 'Номер участка', 'rules' => 'required', 'type' => 'string')
		),
		'getLpuRegionListByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
		),
		'createLpuRegion' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuRegionType_id', 'label' => 'Тип участка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuRegion_Name', 'label' => 'Номер участка', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuRegion_begDate', 'label' => 'Дата создания', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuRegion_endDate', 'label' => 'Дата закрытия', 'rules' => '', 'type' => 'date')
		),
		'updateLpuRegion' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuilding_id', 'label' => 'Идентификатор подразделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegionType_id', 'label' => 'Тип участка', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuRegion_Name', 'label' => 'Номер участка', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuRegion_begDate', 'label' => 'Дата создания', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuRegion_endDate', 'label' => 'Дата закрытия', 'rules' => '', 'type' => 'date')
		),
		'createLpuRegionWorkerPlace' => array(
			array('field' => 'LpuRegion_id', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы сотрудника', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffRegion_begDate', 'label' => 'Дата начала периода', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'MedStaffRegion_endDate', 'label' => 'Дата окончания периода', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffRegion_isMain', 'label' => 'Признак основного врача на участке', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateLpuRegionWorkerPlace' => array(
			array('field' => 'MedStaffRegion_id', 'label' => 'Идентификатор периода работы врача на участке', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedStaffFact_id', 'label' => 'Идентификатор места работы сотрудника', 'rules' => '', 'type' => 'id'),
			array('field' => 'MedStaffRegion_begDate', 'label' => 'Дата начала периода', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffRegion_endDate', 'label' => 'Дата окончания периода', 'rules' => '', 'type' => 'date'),
			array('field' => 'MedStaffRegion_isMain', 'label' => 'Признак основного врача на участке', 'rules' => '', 'type' => 'api_flag')
		),
		'updateLpuSection' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_setDate', 'label' => 'Дата создания отделения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSection_disDate', 'label' => 'Дата закрытия отделения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSectionProfile_Code', 'label' => 'Код профиля отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuUnit_id', 'label' => 'Идентификатор группы отделений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_Code', 'label' => 'Код отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionCode_id', 'label' => 'Код отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_Name', 'label' => 'Наименование отделения', 'rules' => '', 'type' => 'string'),
			array('field' => 'MesAgeGroup_id', 'label' => 'Идентификатор фозрастной группы', 'rules' => '', 'type' => 'id'),
			array('field' => 'MESLevel_id', 'label' => 'Идентификатор уровня МЭС', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsHTMedicalCare', 'label' => 'Флаг выполненеия ВМП', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuSection_KolAmbul', 'label' => 'Количество бригад скорой помощи', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_KolJob', 'label' => 'Количество рабочих мест', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanAutopShift', 'label' => 'Плановое число вскрытий за смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanResShift', 'label' => 'Плановое число исследований в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanTrip', 'label' => 'Плановое число выездов в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanVisitDay', 'label' => 'Плановое число посещений в сутки', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSection_PlanVisitShift', 'label' => 'Плановое число посещений в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionAge_id', 'label' => 'Идентификатор типа отделения по возрасту', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionBedProfile_id', 'label' => 'Идентификатор профиля коек', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionOuter_id', 'label' => 'Идентификатор отделения МО в сторонеей МИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'FRMPSubdivision_id', 'label' => 'Код типа отделения (по ФРМР) для формы 30', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания МО', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSection_IsNotFRMO', 'label' => 'Признак запрета отправки в ФРМО', 'rules' => '', 'type' => 'string'),
		),
		'updateLpuSectionDopProfile' => array(
			array('field' => 'LpuSectionLpuSectionProfile_id', 'label' => 'Идентификатор дополнительного профиля отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionProfile_id', 'label' => 'Идентификатор профиля отделения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionLpuSectionProfile_begDate', 'label' => 'Дата начала действия дополнительного профиля отделения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSectionLpuSectionProfile_endDate', 'label' => 'Дата окончания действия дополнительного профиля отделения', 'rules' => '', 'type' => 'date'),
		),
		'createLpuSectionWard' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWard_Name', 'label' => 'Наименование (номер) палаты', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuSectionWard_Floor', 'label' => 'Этаж', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuWardType_id', 'label' => 'Тип палаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Sex_id', 'label' => 'Вид палаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWard_MainPlace', 'label' => 'Количество основных мест в палате', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_DopPlace', 'label' => 'Количество дополнительных мест в палате', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_BedRepair', 'label' => 'Количество коек на ремонте', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'LpuSectionWard_Square', 'label' => 'Площадь палаты', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_DayCost', 'label' => 'Стоимость нахождения в сутки', 'rules' => '', 'type' => 'float', 'default' => 0),
			array('field' => 'LpuSectionWard_Views', 'label' => 'Вид из окна', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionWard_setDate', 'label' => 'Дата начала периода действия', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuSectionWard_disDate', 'label' => 'Дата окончания периода действия', 'rules' => '', 'type' => 'date'),
		),
		'updateLpuSectionWard' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWard_Name', 'label' => 'Наименование (номер) палаты', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionWard_Floor', 'label' => 'Этаж', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'LpuWardType_id', 'label' => 'Тип палаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'Sex_id', 'label' => 'Вид палаты', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuSectionWard_MainPlace', 'label' => 'Количество основных мест в палате', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_DopPlace', 'label' => 'Количество дополнительных мест в палате', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_BedRepair', 'label' => 'Количество коек на ремонте', 'rules' => '', 'type' => 'int', 'default' => 0),
			array('field' => 'LpuSectionWard_Square', 'label' => 'Площадь палаты', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSectionWard_DayCost', 'label' => 'Стоимость нахождения в сутки', 'rules' => '', 'type' => 'float', 'default' => 0),
			array('field' => 'LpuSectionWard_Views', 'label' => 'Вид из окна', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuSectionWard_setDate', 'label' => 'Дата начала периода действия', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuSectionWard_disDate', 'label' => 'Дата окончания периода действия', 'rules' => '', 'type' => 'date'),
		),
		'deleteLpuSectionWard' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionWardListByName' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWard_Name', 'label' => 'Наименование (номер) палаты', 'rules' => 'required', 'type' => 'string'),
		),
		'getLpuSectionWardListBySection' => array(
			array('field' => 'LpuSection_id', 'label' => 'Идентификатор отделения', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionWardById' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
		),
		'createLpuSectionWardComfortLink' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWardComfortLink_Count', 'label' => 'Количество объектов', 'rules' => 'required', 'type' => 'int'),
			array('field' => 'DChamberComfort_id', 'label' => ' Наименование объекта', 'rules' => 'required', 'type' => 'id'),
		),
		'updateLpuSectionWardComfortLink' => array(
			array('field' => 'LpuSectionWardComfortLink_id', 'label' => 'Идентификатор объекта комфорта в палате', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuSectionWardComfortLink_Count', 'label' => 'Количество объектов', 'rules' => '', 'type' => 'int'),
			array('field' => 'DChamberComfort_id', 'label' => ' Наименование объекта', 'rules' => '', 'type' => 'id'),
		),
		'deleteLpuSectionWardComfortLink' => array(
			array('field' => 'LpuSectionWardComfortLink_id', 'label' => 'Идентификатор объекта комфорта в палате', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionWardComfortLinkByName' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DChamberComfort_id', 'label' => ' Наименование объекта', 'rules' => 'required', 'type' => 'id'),
		),
		'getLpuSectionWardComfotLinkListByWard' => array(
			array('field' => 'LpuSectionWard_id', 'label' => 'Идентификатор палаты', 'rules' => 'required', 'type' => 'id'),
		),
		'getMOById' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
		),
	);

    /**
     * Это Doc-блок
     */
	function __construct()
	{
		parent::__construct();
	}

    /**
     * Это Doc-блок
     */
	function GetMedServiceNodeList($data,$parent_object)
	{
		/*
		if( !$this->config->item('IS_DEBUG') )
		{
			return array();
		}
		*/
		$params = array();
		$filter = '(1=1)';
		//В дереве структуры МО отображать службы только на тех уровнях, на которых они заведены
		switch($parent_object){//
			case 'lpu':
				$params['Lpu_id'] = $data['object_id'];
				$filter = 'ms.Lpu_id = :Lpu_id and ms.LpuBuilding_id is null and ms.LpuUnit_id is null and ms.LpuSection_id is null';
				break;
			case 'lpubuilding':
				$params['LpuBuilding_id'] = $data['object_id'];
				$filter = 'ms.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id is null and ms.LpuUnit_id is null and ms.LpuSection_id is null';
				break;
			case 'lpuunittype':
				$params['LpuBuilding_id'] = $data['object_id'];
				$params['LpuUnitType_id'] = $data['LpuUnitType_id'];
				$filter = 'ms.LpuBuilding_id = :LpuBuilding_id and ms.LpuUnitType_id = :LpuUnitType_id and ms.LpuUnit_id is null and ms.LpuSection_id is null';
				break;
			case 'lpuunit':
				$params['LpuUnit_id'] = $data['object_id'];
				$filter = 'ms.LpuUnit_id = :LpuUnit_id and ms.LpuSection_id is null';
				break;
			case 'lpusection':
				$params['LpuSection_id'] = $data['object_id'];
				$filter = 'ms.LpuSection_id = :LpuSection_id';
				break;
		}
		$leafcount = "0";
		//if ( $this->config->item('IS_DEBUG') == true ) {
			//$leafcount = "(Select count(*) from v_MedService (nolock) ms2 where ms2.MedService_pid = ms.MedService_id)";
		//}
		//$leafcount .= " + (Select count(StorageStructLevel_id) from v_StorageStructLevel with (nolock) where MedService_id = ms.MedService_id)";
		$sql = "
			select
				ms.MedService_id,
				ms.MedService_Name,
				--ms.MedService_Nick,
				--ms.MedServiceType_id,
				--convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
				--convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT,
				case when ms.MedService_endDT is not null and ms.MedService_endDT < dbo.tzGetDate() then 'medservice-closed16' else 'medservice16' end as iconCls,
				ms.LpuBuilding_id,
				ms.LpuSection_id,
				ms.LpuUnit_id,
				ms.Lpu_id,
				mst.MedServiceType_SysNick,
				eq.ElectronicQueueInfo_id,
				{$leafcount} as leafcount
			from
				v_MedService ms with (NOLOCK)
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
				outer apply (
					select top 1 eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq with (NOLOCK)
					where eq.MedService_id = ms.MedService_id
				) as eq
			where
				{$filter}
			order by
				ms.MedService_Name
		";
        //echo getDebugSQL($sql,$params);die;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Получение списка аппаратов
     */
	function GetMedServiceAppNodeList($data)
	{
		/*
		if( !$this->config->item('IS_DEBUG') )
		{
			return array();
		}
		*/
		$params = array();
		$params['MedService_pid'] = $data['object_id'];

		$sql = "
			select
				ms.MedService_id,
				ms.MedService_Name,
				--ms.MedService_Nick,
				--ms.MedServiceType_id,
				--convert(varchar(10),ms.MedService_begDT,104) as MedService_begDT,
				--convert(varchar(10),ms.MedService_endDT,104) as MedService_endDT,
				ms.LpuBuilding_id,
				ms.LpuSection_id,
				ms.LpuUnit_id,
				ms.Lpu_id,
				mst.MedServiceType_SysNick,
				(Select count(*) from v_MedService (nolock) ms2 where ms2.MedService_pid = ms.MedService_id) as leafcount
			from
				v_MedService ms with (NOLOCK)
				left join v_MedServiceType mst (nolock) on mst.MedServiceType_id = ms.MedServiceType_id
			where
				ms.MedService_pid = :MedService_pid
			order by
				ms.MedService_Name
		";
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение списка складов
	 */
	function GetStorageNodeList($data,$parent_object)
	{
		$params = array();
		$filter = '(1=1)';
		$leafcount = "0";

		//В дереве структуры МО отображать службы только на тех уровнях, на которых они заведены
		switch($parent_object){
			case 'title':
				$params['Lpu_id'] = !empty($data['Lpu_id']) ? $data['Lpu_id'] : $data['session']['lpu_id'];
				$filter = 'SSL.Lpu_id = :Lpu_id and S.Storage_pid is null';
				$leafcount = "(select count(CS.Storage_id) from v_Storage CS with (nolock) where CS.Storage_pid = S.Storage_id)";
				break;
			case 'lpu':
				$params['Lpu_id'] = $parent_object == 'lpu' ? $data['object_id'] : $data['Lpu_id'];
				$filter = 'SSL.Lpu_id = :Lpu_id and SSL.LpuBuilding_id is null and SSL.LpuUnit_id is null and SSL.LpuSection_id is null and SSL.MedService_id is null';
				break;
			case 'lpubuilding':
				$params['LpuBuilding_id'] = $data['object_id'];
				$filter = 'SSL.LpuBuilding_id = :LpuBuilding_id and SSL.LpuUnit_id is null and SSL.LpuSection_id is null and SSL.MedService_id is null';
				break;
			case 'lpuunit':
				$params['LpuUnit_id'] = $data['object_id'];
				$filter = 'SSL.LpuUnit_id = :LpuUnit_id and SSL.LpuSection_id is null and SSL.MedService_id is null';
				break;
			case 'lpusection':
				$params['LpuSection_id'] = $data['object_id'];
				$filter = 'SSL.LpuSection_id = :LpuSection_id and SSL.MedService_id is null';
				break;
			case 'medservice':
				$params['MedService_id'] = $data['object_id'];
				$filter = 'SSL.MedService_id = :MedService_id';
				break;
			case 'storage':
				$params['Storage_pid'] = $data['object_id'];
				$filter = 'S.Storage_pid = :Storage_pid';
				$leafcount = "(select count(CS.Storage_id) from v_Storage CS with (nolock) where CS.Storage_pid = S.Storage_id)";
				break;
		}

		if (in_array($parent_object, array('title', 'storage'))) { //В этих режимах нет дополнительных фильтров по элементам структуры, поэтому записи могут двоиться. Чтобы этого избежать, нужна отдельная версия запроса
			$sql = "
				select distinct
					S.Storage_id,
					S.Storage_Name,
					null as LpuBuilding_id,
					null as LpuSection_id,
					null as LpuUnit_id,
					SSL.Lpu_id,
					null as MedService_id,
					case when S.Storage_endDate is not null and S.Storage_endDate < dbo.tzGetDate() then 'product-closed16' else 'product16' end as iconCls,
					merch_ms.MedService_Nick as MerchMedService_Nick,					
					{$leafcount} as leafcount
				from
					v_StorageStructLevel SSL with(nolock)
					inner join v_Storage S with(nolock) on S.Storage_id = SSL.Storage_id
					outer apply (
						select top 1
							i_ms.MedService_Nick
						from 
							v_Storage i_s with (nolock)
							left join v_StorageStructLevel i_ssl with (nolock) on i_ssl.Storage_id = i_s.Storage_id
							left join v_MedService i_ms with (nolock) on i_ms.MedService_id = i_ssl.MedService_id 
							left join v_MedServiceType i_mst with (nolock) on i_mst.MedServiceType_id = i_ms.MedServiceType_id
							outer apply (
								select
									(case
										when i_s.Storage_id = S.Storage_id then 1
										else 2
									end) as val
							) ord
						where
							(
								i_s.Storage_id = S.Storage_id or
								i_s.Storage_id = S.Storage_pid
							) and
							i_mst.MedServiceType_SysNick = 'merch'
						order by
							ord.val
					) merch_ms
				where
					{$filter}
				order by
					S.Storage_Name
			";
		} else {
			$sql = "
				select
					S.Storage_id,
					S.Storage_Name,
					SSL.LpuBuilding_id,
					SSL.LpuSection_id,
					SSL.LpuUnit_id,
					SSL.Lpu_id,
					case when S.Storage_endDate is not null and S.Storage_endDate < dbo.tzGetDate() then 'product-closed16' else 'product16' end as iconCls,
					SSL.MedService_id,
					{$leafcount} as leafcount
				from
					v_StorageStructLevel SSL with(nolock)
					inner join v_Storage S with(nolock) on S.Storage_id = SSL.Storage_id
				where
					{$filter}
				order by
					S.Storage_Name
			";
		}

		//echo getDebugSQL($sql,$params);die;
		$res = $this->db->query($sql,$params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Это Doc-блок
     */
	function GetLpuNodeList($data)
	{
		if ($data['Lpu_id']>0)
		{
			$filter = "Lpu_id=".$data['Lpu_id'];
		}
		else
		{
			$filter = "(1=1)";
		}
		$sql = "select Lpu.Lpu_id, Lpu.Lpu_Nick as Lpu_Name, MALT.MesAgeLpuType_Code
			from v_Lpu Lpu (nolock)
				left join v_MesAgeLpuType MALT (nolock) on MALT.MesAgeLpuType_id = Lpu.MesAgeLpuType_id
			where {$filter}
			--and Lpu_id < 20000000
			order by Lpu.Lpu_Nick";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Метод достает все филиалы для конкретного МО, к которым прикреплено хотя бы одно здание
	 *
	 * @param $data [Lpu_id]
	 * @return array | bool
	 */
	function GetLpuFilialNodeList($data)
	{
		$sql = "
			SELECT
				LF.LpuFilial_id,
				LpuFilial_Name
			FROM
				v_LpuBuilding LB with (nolock)
			JOIN 
				v_LpuFilial LF with (nolock) on LB.LpuFilial_id = LF.LpuFilial_id
			WHERE
				LF.Lpu_id = :Lpu_id AND
				LB.LpuFilial_id IS NOT NULL
			GROUP BY
				LF.LpuFilial_id, LpuFilial_Name
			";

		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;

	}
    /**
     * Это Doc-блок
     */
	function GetLpuBuildingNodeList($data)
	{
		$filter = '';
		$buildpass = '';
		$join = '';
		$case = '';
		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true) {
			$add_filter = '';
			if (isset($data['deniedSectionsList']) && is_array($data['deniedSectionsList']) && !empty($data['deniedSectionsList'][0])) {
				$add_filter = "and LS.LpuSection_id not in (".implode(',',$data['deniedSectionsList']).")";
			}
			if (!empty($data['LpuBuildingPass_id'])) {
				$buildpass = "or LS.LpuBuildingPass_id = ".$data['LpuBuildingPass_id']."";
			}
			$join = "outer apply (
				select
					count(LS.LpuSection_id) as LpuSectionsCount
				from
					LpuSection LS (nolock)
					inner join LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
				where
					LU.LpuBuilding_id = LB.LpuBuilding_id
					and LS.LpuSection_pid is null
					and (LS.LpuBuildingPass_id is null ".$buildpass.")
					and ISNULL(LS.LpuSection_deleted, 1) <> 2
					{$add_filter}
			) av";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as claimed,";
		}

		// Ищутся здания прикрепленные к филиалу или ЛПУ, в зависмости от того, для какого объекта
		$lpuOrFilialFilter = ( $data['object'] === 'LpuFilial' ) ? 'lf.LpuFilial_id = :object_id ' : 'lb.Lpu_id = :object_id AND lf.LpuFilial_id is null ';

		$sql = "select
			lb.Lpu_id,
			lb.LpuBuilding_id,
			lb.LpuBuilding_Name,
			lb.LpuBuildingType_id,
			eq.ElectronicQueueInfo_id,
			rm.RegisterMO_OID,
			case when lb.LpuBuilding_endDate is not null and lb.LpuBuilding_endDate < dbo.tzGetDate() then 'lpu-building-closed16' else 'lpu-building16' end as iconCls,
			{$case}
			(Select count(LpuUnit_id) from v_LpuUnit with (nolock) where LpuBuilding_id = LB.LpuBuilding_id)
			+ (Select count(MedService_id) from v_MedService with (nolock) where LpuBuilding_id = LB.LpuBuilding_id)
			+ (Select count(StorageStructLevel_id) from v_StorageStructLevel with (nolock) where LpuBuilding_id = LB.LpuBuilding_id)
			as leafcount
			from
				v_LpuBuilding LB with (nolock)
				left join v_LpuFilial lf with (nolock) on lf.LpuFilial_id = LB.LpuFilial_id
				left join nsi.v_RegisterMO rm with (nolock) on rm.RegisterMO_id = lf.RegisterMO_id
			{$join}
			outer apply (
					select top 1 eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq with (NOLOCK)
					where eq.LpuBuilding_id = LB.LpuBuilding_id
					and eq.LpuSection_id is null
				) as eq
			where
			{$lpuOrFilialFilter}
			{$filter} 
			order by LpuBuilding_Code";

		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuUnitNodeList($data)
	{
		$filter = '';
		$buildpass = '';
		$join = '';
		$case = '';
		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true) {
			$add_filter = '';
			if (isset($data['deniedSectionsList']) && is_array($data['deniedSectionsList']) && !empty($data['deniedSectionsList'][0])) {
				$add_filter = "and LS.LpuSection_id not in (".implode(',',$data['deniedSectionsList']).")";
			}
			if (!empty($data['LpuBuildingPass_id'])) {
				$buildpass = "or LS.LpuBuildingPass_id = ".$data['LpuBuildingPass_id']."";
			}
			$join = "outer apply (
				select
					count(LS.LpuSection_id) as LpuSectionsCount
				from
					LpuSection LS (nolock)
					inner join LpuUnit LU with (nolock) on LS.LpuUnit_id = LU.LpuUnit_id
				where
					LU.LpuUnit_id = LpuUnit.LpuUnit_id
					and LS.LpuSection_pid is null
					and (LS.LpuBuildingPass_id is null ".$buildpass.")
					and ISNULL(LS.LpuSection_deleted, 1) <> 2
					{$add_filter}
			) av";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as claimed,";
		}

		$sql = "
			select
				LpuBuilding_id,
				LpuUnit_id,
				LpuUnit_Name,
				LpuUnit.UnitDepartType_fid,
				fu.FRMOUnit_OID,
				case when LpuUnit.LpuUnit_endDate is not null and LpuUnit.LpuUnit_endDate < dbo.tzGetDate() then 'lpu-unit-closed16' else 'lpu-unit16' end as iconCls,
				{$case}
				(Select count(*) from v_LpuSection LpuSection with (nolock) where LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id)
				+ (Select count(StorageStructLevel_id) from v_StorageStructLevel with (nolock) where LpuUnit_id = LpuUnit.LpuUnit_id)
				+ (Select count(MedService_id) from v_MedService with (nolock) where LpuUnit_id = LpuUnit.LpuUnit_id)
				as leafcount
			from
				v_LpuUnit LpuUnit with (nolock)
				left join nsi.v_FRMOUnit fu (nolock) on fu.FRMOUnit_id = LpuUnit.FRMOUnit_id
				{$join}
			where
				LpuBuilding_id = ".$data['object_id']." and LpuUnitType_id = ".$data['LpuUnitType_id']." {$filter}
			order by
				LpuUnit_Code";

		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuUnitTypeNodeList($data)
	{
		$params = array(
			'LpuBuilding_id' => $data['object_id'],
		);
		$join = '';
		$case = '';
		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true) {
			$add_filter = '';
			$buildpass = '';
			if (isset($data['deniedSectionsList']) && is_array($data['deniedSectionsList']) && !empty($data['deniedSectionsList'][0])) {
				$add_filter = "and LS.LpuSection_id not in (".implode(',',$data['deniedSectionsList']).")";
			}
			if (!empty($data['LpuBuildingPass_id'])) {
				$buildpass = "or LS.LpuBuildingPass_id = ".$data['LpuBuildingPass_id']."";
			}
			$join = "outer apply (
				select
					count(LS.LpuSection_id) as LpuSectionsCount
				from
					LpuSection LS (nolock)
					inner join LpuUnit with (nolock) on LS.LpuUnit_id = LpuUnit.LpuUnit_id
						and LpuUnit.LpuBuilding_id = :LpuBuilding_id
						and LpuUnit.LpuUnitType_id = LUT.LpuUnitType_id
				where
					LS.LpuSection_pid is null
					and (LS.LpuBuildingPass_id is null ".$buildpass.")
					and ISNULL(LS.LpuSection_deleted, 1) <> 2
					{$add_filter}
			) av";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as claimed,";
		}

		//unitType
		$sql = "
			select
				LU.LpuBuilding_id,
				LUT.LpuUnitType_id,
				RTrim(LUT.LpuUnitType_Name) as LpuUnitType_Name,
				case when LU.LpuUnit_endDate is not null and LU.LpuUnit_endDate < dbo.tzGetDate() then 'lpu-unittype-closed16' else 'lpu-unittype16' end as iconCls,
				{$case}
				RTrim(LUT.LpuUnitType_Nick) as LpuUnitType_Nick
			from v_LpuUnitType LUT with (nolock)
			cross apply (
				select top 1
					v_LpuUnit.LpuBuilding_id,
					v_LpuUnit.LpuUnit_endDate
				from v_LpuUnit with (nolock)
				where v_LpuUnit.LpuBuilding_id = :LpuBuilding_id
					and v_LpuUnit.LpuUnitType_id = LUT.LpuUnitType_id
				order by v_LpuUnit.LpuUnit_endDate
			) LU
			{$join}
		";
		//echo getDebugSQL($sql, $params);die;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuRegionTypeNodeList($data)
	{
		$Lpu_id = (isset($data['Lpu_id']) && (!empty($data['Lpu_id'])))?$data['Lpu_id']:$data['session']['lpu_id'];
		$filter = ($Lpu_id>0)?"LpuRegion.Lpu_id=".$Lpu_id:"(1=1)";

		// Типы участков только имеющиеся
		$sql = "select
			LpuRegionType.LpuRegionType_id as LpuRegionType_id,
			RTrim(LpuRegionType.LpuRegionType_Name) as LpuRegionType_Name
			from v_LpuRegion LpuRegion with (nolock)
			left join v_LpuRegionType LpuRegionType with (nolock) on LpuRegionType.LpuRegionType_id = LpuRegion.LpuRegionType_id
			where {$filter}
			group by LpuRegionType.LpuRegionType_id, LpuRegionType.LpuRegionType_Name
			order by LpuRegionType.LpuRegionType_id";
		// Все типы участков
		/*
		$sql = "select
			LpuRegionType.LpuRegionType_id as LpuRegionType_id,
			RTrim(LpuRegionType.LpuRegionType_Name) as LpuRegionType_Name
			from LpuRegionType
			order by LpuRegionType.LpuRegionType_id";
		*/
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuSectionNodeList($data)
	{
		$filter = '';
		$join = '';
		$case = '';
		$select = "(Select count(LS.LpuSection_id) from v_LpuSection LS with (nolock) where LS.LpuSection_pid = LpuSection.LpuSection_id)
			+ (Select count(MS.MedService_id) from v_MedService MS with (nolock) where MS.LpuSection_id = LpuSection.LpuSection_id)
			+ (Select count(StorageStructLevel_id) from v_StorageStructLevel with (nolock) where LpuSection_id = LpuSection.LpuSection_id)
			as leafcount,
			(
					SELECT
						LU.UnitDepartType_fid
					FROM
						v_LpuUnit LU with (nolock)
					WHERE LpuUnit_id = ".$data['object_id']."
				) as UnitDepartType_fid";

		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true) {
			$add_filter = '';
			$select = "0 as leafcount";

			if (isset($data['deniedSectionsList']) && is_array($data['deniedSectionsList']) && !empty($data['deniedSectionsList'][0])) {
				$add_filter = "and LS.LpuSection_id not in (".implode(',',$data['deniedSectionsList']).")";
			}

			$join = "outer apply (
				select
					count(LS.LpuSection_id) as LpuSectionsCount
				from
					LpuSection LS (nolock)
				where
					LpuSection.LpuSection_id = LS.LpuSection_id
					and LS.LpuBuildingPass_id is null
					and ISNULL(LS.LpuSection_deleted, 1) <> 2
					{$add_filter}
			) av";
			$case = " case when av.LpuSectionsCount > 0 then 2 else 1 end as claimed,";
		}
		
		if(!empty($data['Lpu_id'])){
			$filter .= " AND lpu_id = ".$data['Lpu_id']." ";
		}

		$sql = "select
			LpuUnit_id,
			LpuSectionProfile_id,
			LpuSection_id,
			eq.ElectronicQueueInfo_id,
			case when LpuSection.LpuSection_disDate is not null and LpuSection.LpuSection_disDate < dbo.tzGetDate() then 'lpu-section-closed16' else 'lpu-section16' end as iconCls,
			(rtrim(LpuSection_Code) + '. ' + rtrim(LpuSection_Name)) as LpuSection_Name,
			{$case}
			{$select}
			from v_LpuSection LpuSection with (nolock)
			{$join}
			outer apply (
					select top 1 eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq with (NOLOCK)
					where eq.LpuSection_id = LpuSection.LpuSection_id
				) as eq
			where LpuUnit_id = ".$data['object_id']." and LpuSection_pid is null {$filter}
			order by LpuSection_Code";

		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuSectionPidNodeList($data)
	{
		$filter = '';
		$buildpass = '';
		if (!empty($data['SectionsOnly']) && $data['SectionsOnly'] == true) {

			if (!empty($data['LpuBuildingPass_id'])) {
				$buildpass = "or LpuBuildingPass_id = ".$data['LpuBuildingPass_id']."";
			}
			$filter = " and (LpuBuildingPass_id is null ".$buildpass.")
				and ISNULL(LpuSection_deleted, 1) <> 2";

			if (isset($data['deniedSectionsList']) && is_array($data['deniedSectionsList']) && !empty($data['deniedSectionsList'][0])) {
				$filter .= " and LpuSection_id not in (".implode(',',$data['deniedSectionsList']).") ";
			}
		}

		$sql = "
			select LpuSection_pid,
			LpuSectionProfile_id,
			case when LpuSection.LpuSection_disDate is not null and LpuSection.LpuSection_disDate < dbo.tzGetDate() then 'lpu-subsection-closed16' else 'lpu-subsection16' end as iconCls,
			LpuSection_id,
			eq.ElectronicQueueInfo_id,
			(rtrim(LpuSection_Code) + '. ' + rtrim(LpuSection_Name)) as LpuSection_Name,
			(Select count(StorageStructLevel_id) from v_StorageStructLevel with (nolock) where LpuSection_id = LpuSection.LpuSection_id) as leafcount
			from
				v_LpuSection LpuSection with (nolock)
				outer apply (
					select top 1 eq.ElectronicQueueInfo_id
					from v_ElectronicQueueInfo eq with (NOLOCK)
					where eq.LpuSection_id = LpuSection.LpuSection_id
				) as eq
			where LpuSection_pid = ".$data['object_id']." {$filter}
			order by LpuSection_Code";
		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuRegionNodeList($data)
	{
		if (!empty($data['Lpu_id']))
			{
			$filter = "LR.Lpu_id=".$data['Lpu_id'];
			}
		else
			{
			$filter = "(1=1)";
			}
		$join = '';
		if ( isset($data['uchOnly']) )
		{
			$join = " inner join v_MedStaffRegion with(nolock) on v_MedStaffRegion.LpuRegion_id = LR.LpuRegion_id and v_MedStaffRegion.MedPersonal_id = " . (int)$data['session']['medpersonal_id'] . "  ";
		}
		else
			$filter .= " and LpuRegionType_id = ".$data['object_id'] . " ";
		$sql = "
			SELECT
				LR.LpuRegion_id,
				LR.LpuRegion_Name,
				IsNull(LR.LpuRegion_Descr,'') as LpuRegion_Descr,
				LR.LpuRegionType_id
			FROM v_LpuRegion LR with (nolock)
			" . $join . "
			where {$filter}
			--and isnumeric(LR.LpuRegion_Name)=1 -- это аццкая затычка пока не переведем поле LpuRegion_name под число
			order by
				case when ISNUMERIC(LR.LpuRegion_Name) = 1 then cast(LpuRegion_Name as bigint) else 1488 end
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuAllQuery( $data ){
		$sql = "
			SELECT
				Lpu_id,
				Okopf_Name,
				Org_Name,
				Lpu_Nick,
				Org_Code,
				LpuType_Name,
				UAddress_Address,
				PAddress_Address,
				Lpu_IsLab,

				LpuType_Code
			FROM
				v_Lpu_all with (nolock)
			WHERE
				Lpu_id=:Lpu_id
		";
		$res = $this->db->query( $sql, array(
			'Lpu_id' => $data['Lpu_id']
		));
		if ( is_object( $res ) ) {
			return $res->result( 'array' );
		}

		return false;
	}
    /**
     * Это Doc-блок
     */
	protected function _checkLpuUnitType($data)
	{
		$params = array(
			'LpuUnit_id' => $data['LpuUnit_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id']
		);

		$query = "SELECT LU.LpuUnitType_id, StaffCount
			FROM v_LpuUnit LU with (nolock)
			outer apply (
				select
					COUNT(*) as StaffCount
				from
					persis.v_Staff ST with (nolock)
				where
					ST.LpuUnit_id = LU.LpuUnit_id
			) as StaffTotal
			WHERE LU.LpuUnit_id = :LpuUnit_id";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function ExportErmpStaff($data)
	{
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$and = "";
		$inner = "";
        $and_DT = "";
		if (($data['Lpu_id']) && ($data['Lpu_id'] != '100500')) { // 100500 - значение фильтра МО - "Все"
			$and .= " and s.Lpu_id = '".$data['Lpu_id']."'";
		}
		if ($data['ESESW_date']) {
			//$inner .= " and isnull(ss.BeginDate,'2099-12-31')<='". $data['ESESW_date']."' and isnull(ss.EndDate,'2099-12-31')>='".$data['ESESW_date']." '";
            $and_DT = " and ss.updDT >='".$data['ESESW_date']."'";
		}
        //Пока не удалил старый запрос, а то малоличо
		/*$sql = "
				SELECT
				-- UZ МО
						--l.Lpu_id as 'UZ_ID' ,
						NEWID() as'UZ_ID',
						ltrim(rtrim(Lpu_Nick)) as 'UZ_Name' ,
						isnull(ORG_INN, '') as 'UZ_INN' ,
						isnull(ORG_KPP, '') as 'UZ_KPP' ,
						isnull(Org_OGRN,'') as 'UZ_OGRN',
						ISNULL(pt.PassportToken_tid,'') as 'UZ_OID',
						'' as 'UZ_Type' ,
				-- UZ/LPULevel Уровень
						isnull(ll.LpuSubjectionLevel_id, 0) as 'UZ_LPULevel_ID' ,
						isnull(ll.LpuSubjectionLevel_pid, 0) as 'UZ_LPULevel_Parent' ,
						ltrim(rtrim(ll.LpuSubjectionLevel_name)) as 'UZ_LPULevel_Name' ,
				-- UZ/Nomen Тип учреждения
						l.LpuType_id as 'UZ_Nomen_ID' ,
						isnull(LpuType_pid, 0) as 'UZ_Nomen_Parent' ,
						ltrim(rtrim(l.LpuType_Name)) as 'UZ_Nomen_Name' ,
				--Municipality

						isnull(KL.id, kll.KLAdr_Code) as 'UZ_Municipality_ID' ,
						isnull(KL.name, kll.KLArea_Name) as 'UZ_Municipality_Name' ,
						case when KL.id is not null then isnull(KL.parent, kll2.KLAdr_Code)
														when KL.id is null then kll2.KLAdr_Code
												   end as 'UZ_Municipality_Parent',
						case when KL.id is not null then isnull(kl.prefix, '')
														when KL.id is null then 'Город'
												   end as 'UZ_Municipality_Prefix' ,
						sd.id as 'Branch_ID' ,
						ltrim(rtrim(sd.name)) as 'Branch_Name' ,
						isnull(sd.parent, '') as 'Branch_Parent' ,
						'' as 'Unit' ,

						isnull(fp.id, '') as 'StuffPost_ID' ,
						isnull(fp.parent, '') as 'StuffPost_Parent' ,
						ltrim(rtrim(fp.name)) as 'StuffPost_Name' ,

						cast(sum(s.rate) as varchar(8)) as 'Quantity',
						'' as 'Comment'
				FROM    persis.v_staff s with ( nolock )
				left join persis.Staff ss with (nolock) on ss.id=s.id
				outer apply (
					select top 1 p.FRMPSubdivision_id
					from persis.WorkPlace p with (nolock)
					where s.id = p.Staff_id
						and p.IsDummyWP = 0
					order by p.FRMPSubdivision_id desc
				) p
				left join v_Lpu l with (nolock) on l.Lpu_id=s.Lpu_id
				left join v_Org o with (nolock) on o.Org_id=l.org_id
				left join persis.post post with (nolock) on post.id=s.post_id
				inner join persis.frmppost fp with (nolock) on fp.id=post.frmpentry_id
				left join LpuSubjectionLevel ll with (nolock) on ll.LpuSubjectionLevel_id=l.LpuSubjectionLevel_id
				left join LpuType lt with (nolock) on lt.LpuType_id=l.LpuType_id
				left join v_OrgServiceTerr OST with (nolock) on OST.Org_id = l.Org_id
				left join KLArea kll with (nolock) on kll.KLArea_id=coalesce(OST.klcity_id,OST.kltown_id,ost.KLSubRgn_id,dbo.GetmaincityRegion())
				left join v_KLArea kll2 with (nolock) on kll2.KLArea_id = kll.KLArea_pid
				left join KLArea kl3 with (nolock) on kl3.KLArea_id=dbo.GetmaincityRegion()
				left join persis.FRMPKladr kl with (nolock) on kl.id=ISNULL(kll.KLAdr_Code, kl3.KLAdr_Code)
				left join fed.PassportToken pt on pt.Lpu_id = l.lpu_id
				left join fed.PasportMO PMO on PMO.Lpu_id = l.lpu_id
				outer apply (
							select top 1 sd.id,sd.name,sd.parent from persis.FRMPSubdivision sd with (nolock)
							where sd.id=p.FRMPSubdivision_id
							) sd
				where
						sd.id is not null
						and s.Rate<>0  and ll.LpuSubjectionLevel_name is not null and ORG_KPP is not null
						and l.Lpu_id not in ( select    lpu_id
											  from      v_lpu with ( nolock )
											  where     Lpu_Nick like '%закрыт%'
														or Lpu_endDate is not null )".$and."
						and ISNULL(PMO.PasportMO_IsNoFRMP,'2') <> '1'
						and fp.id is not null
				group by
						l.Lpu_id,Lpu_Nick,ORG_INN,ORG_KPP,Org_OGRN,ll.LpuSubjectionLevel_id,ll.LpuSubjectionLevel_pid,ll.LpuSubjectionLevel_name,l.LpuType_id,LpuType_pid,
						l.LpuType_Name,KL.id,kll.KLAdr_Code,KL.name,kll.KLArea_Name,KL.parent,kll2.KLAdr_Code,kl.prefix,sd.id,sd.name,sd.parent,fp.id ,fp.parent,fp.name,pt.PassportToken_tid
				order by
						ltrim(rtrim(Lpu_Nick)) , ltrim(rtrim(fp.name))
		";*/
        $sql = "
                WITH zzz as (SELECT
                -- UZ МО
                --l.Lpu_id as 'UZ_ID' ,
                --NEWID() as'UZ_ID',
                ltrim(rtrim(Lpu_Nick)) as 'UZ_Name' ,
                isnull(ORG_INN, '') as 'UZ_INN' ,
                isnull(ORG_KPP, '') as 'UZ_KPP' ,
                isnull(Org_OGRN,'') as 'UZ_OGRN',
                ISNULL(pt.PassportToken_tid,'') as 'UZ_OID',
                '' as 'UZ_Type' ,
                -- UZ/LPULevel Уровень
                isnull(ll.LpuSubjectionLevel_id, 0) as 'UZ_LPULevel_ID' ,
                isnull(ll.LpuSubjectionLevel_pid, 0) as 'UZ_LPULevel_Parent' ,
                ltrim(rtrim(ll.LpuSubjectionLevel_name)) as 'UZ_LPULevel_Name' ,
                -- UZ/Nomen Тип учреждения
                l.LpuType_id as 'UZ_Nomen_ID' ,
                isnull(LpuType_pid, 0) as 'UZ_Nomen_Parent' ,
                ltrim(rtrim(l.LpuType_Name)) as 'UZ_Nomen_Name' ,
                --Municipality
                isnull(KL.id, kll.KLAdr_Code) as 'UZ_Municipality_ID' ,
                isnull(KL.name, kll.KLArea_Name) as 'UZ_Municipality_Name' ,
                case when KL.id is not null then isnull(KL.parent, kll2.KLAdr_Code)
                when KL.id is null then kll2.KLAdr_Code
                end as 'UZ_Municipality_Parent',
                case when KL.id is not null then isnull(kl.prefix, '')
                when KL.id is null then 'Город'
                end as 'UZ_Municipality_Prefix' ,
                sd.id as 'Branch_ID' ,
                ltrim(rtrim(sd.name)) as 'Branch_Name' ,
                isnull(sd.parent, '') as 'Branch_Parent' ,
                '' as 'Unit' ,
                s.post_id,s.rate,
                '' as 'Comment'
            FROM persis.v_staff s with ( nolock )
                left join persis.Staff ss with (nolock) on ss.id=s.id
                outer apply (
                    select top 1 p.FRMPSubdivision_id
                    from persis.WorkPlace p with (nolock)
                    where s.id = p.Staff_id
                        and p.IsDummyWP = 0
                    order by p.FRMPSubdivision_id desc
                ) p
                left join v_Lpu l with (nolock) on l.Lpu_id=s.Lpu_id
                left join v_Org o with (nolock) on o.Org_id=l.org_id
                --left join persis.post post with (nolock) on post.id=s.post_id
                --inner join persis.frmppost fp with (nolock) on fp.id=post.frmpentry_id
                left join LpuSubjectionLevel ll with (nolock) on ll.LpuSubjectionLevel_id=l.LpuSubjectionLevel_id
                left join LpuType lt with (nolock) on lt.LpuType_id=l.LpuType_id
                left join v_OrgServiceTerr OST with (nolock) on OST.Org_id = l.Org_id
                left join KLArea kll with (nolock) on kll.KLArea_id=coalesce(OST.klcity_id,OST.kltown_id,ost.KLSubRgn_id,dbo.GetmaincityRegion())
                left join v_KLArea kll2 with (nolock) on kll2.KLArea_id = kll.KLArea_pid
                left join KLArea kl3 with (nolock) on kl3.KLArea_id=dbo.GetmaincityRegion()
                left join persis.FRMPKladr kl with (nolock) on kl.id=ISNULL(kll.KLAdr_Code, kl3.KLAdr_Code)
                left join fed.PassportToken pt with(nolock) on pt.Lpu_id = l.lpu_id
                left join fed.PasportMO PMO with(nolock) on PMO.Lpu_id = l.lpu_id
                outer apply (
                    select top 1 sd.id,sd.name,sd.parent
                    from persis.FRMPSubdivision sd with (nolock)
                    where sd.id=p.FRMPSubdivision_id
                ) sd
            where
                sd.id is not null
                and s.Rate<>0
                and ll.LpuSubjectionLevel_name is not null
                and o.ORG_KPP is not null
                and l.Lpu_Nick not like N'%закрыт%'
                and l.Lpu_endDate is null
                and ISNULL(PMO.PasportMO_IsNoFRMP,'1') <> '2'".$and."
                ".$and_DT."
            ),
            yyy
            AS
            (
                SELECT persis.post.*,persis.frmppost.fullname,persis.frmppost.name AS name1,persis.frmppost.parent AS parent1,persis.frmppost.id AS idd
                FROM persis.post with(nolock)
                INNER JOIN persis.frmppost with(nolock)
                ON persis.Post.frmpEntry_id = persis.FRMPPost.id
            )
            SELECT
                    NEWID() as'UZ_ID',
                    UZ_Name ,
                    UZ_INN ,
                    UZ_KPP ,
                    UZ_OGRN ,
                    UZ_OID ,
                    UZ_Type ,
                    UZ_LPULevel_ID ,
                    UZ_LPULevel_Parent ,
                    UZ_LPULevel_Name ,
                    UZ_Nomen_ID ,
                    UZ_Nomen_Parent ,
                    UZ_Nomen_Name ,
                    UZ_Municipality_ID ,
                    UZ_Municipality_Name ,
                    UZ_Municipality_Parent ,
                    UZ_Municipality_Prefix ,
                    Branch_ID ,
                    Branch_Name ,
                    Branch_Parent ,
                    Unit ,
                    Comment,
                isnull(fp.idd, '') as 'StuffPost_ID' ,
                isnull(fp.parent1, '') as 'StuffPost_Parent' ,
                ltrim(rtrim(fp.name1)) as 'StuffPost_Name' ,
                cast(sum(rate) as varchar(8)) as 'Quantity'
            FROM zzz with(nolock)
                left join yyy fp with (nolock) on fp.id=post_id
            group by
                --UZ_ID,
                UZ_Name,
                UZ_INN,
                UZ_KPP,
                UZ_OGRN,
                UZ_OID,
                UZ_Type,
                UZ_LPULevel_ID,
                UZ_LPULevel_Parent,
                UZ_LPULevel_Name,
                UZ_Nomen_ID,
                UZ_Nomen_Parent,
                UZ_Nomen_Name,
                UZ_Municipality_ID,
                UZ_Municipality_Name,
                UZ_Municipality_Parent,
                UZ_Municipality_Prefix,
                Branch_ID,
                Branch_Name,
                Branch_Parent,
                Unit,
                Comment,
                fp.idd,
                fp.parent1,
                fp.name1
                order BY
            UZ_Name,
            ltrim(rtrim(fp.name1))
        ";
		//echo getDebugSQL($sql,$params);die;
		$result = $this->db->query($sql,$params);
		if (is_object($result)) {
            $res = $result->result('array');
            $temp = array();
            foreach ($res as &$item) {
                if ($item['StuffPost_ID']!=0) {
                    $temp[] = $item;
                }
            }
			return $temp;
		} else {
			return false;
		}
	}

    /**
     * https://redmine.swan.perm.ru/issues/41129
     */
    function getIsNoFRMP($data)
    {
        $params = array(
            'Lpu_id' => $data['Lpu_id']
        );
        $query = "
            select ISNULL(PasportMO_IsNoFRMP,'1') as PasportMO_IsNoFRMP
            from fed.PasportMO with (nolock)
            where Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query,$params);die;
        $result = $this->db->query($query,$params);
        if (is_object($result)) {
            return $result->result('array');
        } else {
            return false;
        }
    }

    /**
     * Это Doc-блок
     */
	function saveLpuUnit($data) {
		if ( !empty($data['LpuUnit_id']) ) {
			$checkResult = $this->_checkOpenChildStruct('saveLpuUnit', $data);
			if (!empty($checkResult) && strlen($checkResult)>0) {
				return array(array('Error_Msg' => $checkResult));
			}

			$checkResult = $this->_checkLpuUnitType($data);
			if ( is_array($checkResult) && (count($checkResult) > 0) && $checkResult[0]['StaffCount'] > 0 && $checkResult[0]['LpuUnitType_id'] != $data['LpuUnitType_id'] ) {
				return array(array('Error_Code' => 100011, 'Error_Msg' => 'Изменение типа группы отделений невозможно, для некоторых отделений существуют строки штатного расписания.'));
			}

			// Проверяем меняется ли тип группы отделений.
			$getLpuUnitList = $this->getLpuUnitList(array('LpuUnit_id' => $data['LpuUnit_id']));
			$oldLpuUnitType = (!empty($getLpuUnitList[0]['LpuUnitType_id'])) ? $getLpuUnitList[0]['LpuUnitType_id'] : '';
			$newLpuUnitType = (!empty($data['LpuUnitType_id'])) ? $data['LpuUnitType_id'] : '';

			if ( $newLpuUnitType != $oldLpuUnitType ) {
				$dbreg = $this->load->database('registry', true);
				$this->load->model('Registry_model', 'Reg_model');
				$checkResult = $this->Reg_model->checkLpuSectionInRegistry($data);
				if ( !empty($checkResult) ) {
					return array(array('Error_Msg' => $checkResult));
				}
			}
		}

		if (!empty($data['FRMOUnit_id']) && !empty($data['LpuUnit_id'])) {
			$resp = $this->queryResult("
				select
					ls.LpuSection_id
				from
					v_LpuSection ls (nolock)
					inner join nsi.v_FRMOSection fs (nolock) on fs.FRMOSection_id = ls.FRMOSection_id
					inner join nsi.v_FRMOUnit fu (nolock) on fu.FRMOUnit_OID = fs.FRMOUnit_OID
				where
					ls.LpuUnit_id = :LpuUnit_id
					and fu.FRMOUnit_id <> :FRMOUnit_id
			", array(
				'LpuUnit_id' => $data['LpuUnit_id'],
				'FRMOUnit_id' => $data['FRMOUnit_id']
			));

			if (!empty($resp[0]['LpuSection_id'])) {
				if (empty($data['ignoreFRMOUnitCheck'])) {
					return array(array('Error_Msg' => 'YesNo', 'Error_Code' => '101', 'Alert_Msg' => 'Значения в полях «ФРМО. Справочник структурных подразделений» и «ФРМО. Справочник отделений и кабинетов» принадлежат разным структурным подразделениям справочника ФРМО. После сохранения у отделений будут удалены значения, несоответствующие структуре ФРМО. Продолжить?'));
				} else {
					foreach($resp as $respone) {
						$resp_upd = $this->queryResult("
							declare
								@Error_Code bigint,
								@Error_Message varchar(4000);
							set nocount on
							begin try
								update
									LpuSection with (rowlock)
								set
									FRMOSection_id = null
								where
									LpuSection_id = :LpuSection_id
							end try
							begin catch
								set @Error_Code = error_number()
								set @Error_Message = error_message()
							end catch
							set nocount off
							Select @Error_Code as Error_Code, @Error_Message as Error_Msg
						", array(
							'LpuSection_id' => $respone['LpuSection_id']
						));

						if (!empty($resp_upd[0]['Error_Msg'])) {
							return array(array('Error_Msg' => $resp_upd[0]['Error_Msg']));
						}
					}
				}
			}
		}

		/*if ( !empty($data['FRMOUnit_id']) ) {
			$checkResult = $this->getFirstResultFromQuery("
				select top 1 LpuUnit_id
				from v_LpuUnit with (nolock)
				where FRMOUnit_id = :FRMOUnit_id
					" . (!empty($data['LpuUnit_id']) ? "and LpuUnit_id != :LpuUnit_id" : "") . "
			", array(
				'FRMOUnit_id' => $data['FRMOUnit_id'],
				'LpuUnit_id' => (!empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null),
			));

			if ( $checkResult !== false && !empty($checkResult) ) {
				return array(array('Error_Msg' => 'Выбранная запись из справочника структурных подразделений ФРМО уже используется'));
			}
		}*/

		if ( !empty($data['LpuUnit_id']) ) {
			$proc = 'p_LpuUnit_upd';

			if ( !empty($data['source']) && $data['source'] == 'API' ) {
				$data['LpuBuilding_id'] = $this->getFirstResultFromQuery("select top 1 LpuBuilding_id from v_LpuUnit with (nolock) where LpuUnit_id = :LpuUnit_id", $data, true);

				if ( $data['LpuBuilding_id'] === false || empty($data['LpuBuilding_id']) ) {
					return array(array('Error_Msg' => 'Не удалось определить идентификатор подразделения'));
				}
			}
		}
		else {
			$proc = 'p_LpuUnit_ins';
		}

		$dopFields = '';
		$params = array(
			'LpuUnit_id' => (!empty($data['LpuUnit_id']) ? $data['LpuUnit_id'] : null),
			'LpuUnit_begDate' => $data['LpuUnit_begDate'],
			'LpuUnit_endDate' => $data['LpuUnit_endDate'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'LpuUnitType_id' => $data['LpuUnitType_id'],
			'LpuUnitTypeDop_id' => $data['LpuUnitTypeDop_id'],
			'LpuUnit_Code' => $data['LpuUnit_Code'],
			'LpuUnit_Name' => $data['LpuUnit_Name'],
			'LpuUnit_Phone' => $data['LpuUnit_Phone'],
			'LpuUnit_Descr' => $data['LpuUnit_Descr'],
			'LpuUnit_Email' => $data['LpuUnit_Email'],
			'LpuUnit_IP' => $data['LpuUnit_IP'],
			'LpuUnit_IsEnabled' => $data['LpuUnit_IsEnabled'],
			'LpuUnit_isPallCC' => ($data['LpuUnit_isPallCC'] == 'on' || $data['LpuUnit_isPallCC'] == '2' ? 2 : 1),
			'LpuUnit_IsNotFRMO' => ($data['LpuUnit_IsNotFRMO'] == 'on' || $data['LpuUnit_IsNotFRMO'] == '2' ? 2 : 1),
			'LpuUnitProfile_fid' => $data['LpuUnitProfile_fid'],
			'LpuUnit_isStandalone' => $data['LpuUnit_isStandalone'],
			'LpuUnit_isCMP' => $data['LpuUnit_isCMP'],
			'LpuUnit_isHomeVisit' => $data['LpuUnit_isHomeVisit'],
			'UnitDepartType_fid' => $data['UnitDepartType_fid'],
			'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
			'LpuUnitSet_id' => (!empty($data['LpuUnitSet_id']) ? $data['LpuUnitSet_id'] : null),
			'LpuUnit_IsOMS' => (!empty($data['LpuUnit_IsOMS']) ? $data['LpuUnit_IsOMS'] : null),
			'FRMOUnit_id' => (!empty($data['FRMOUnit_id']) ? $data['FRMOUnit_id'] : null),
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $data['Server_id'],
		);

		if ( !empty($data['source']) && $data['source'] == 'API' ) {
			$params['Address_id'] = $data['Address_id'];
			$params['LpuUnit_IsDirWithRec'] = $data['LpuUnit_IsDirWithRec'];
			$params['LpuUnit_ExtMedCnt'] = $data['LpuUnit_ExtMedCnt'];
			$params['LpuUnit_Guid'] = $data['LpuUnit_Guid'];
			$params['LpuUnit_FRMOUnitID'] = $data['LpuUnit_FRMOUnitID'];
			$params['LpuUnit_FRMOid'] = $data['LpuUnit_FRMOid'];
		}
		else {
			// все все это требует рефакторинга
			if ( empty($data['LpuUnit_id']) ) {
				if ( !empty($data['LpuUnit_IsEnabled']) &&
					(isSuperadmin() || ($data['session']['region']['nick'] == 'kareliya' && isLpuAdmin($data['Lpu_id'])))
				) {
					$params['LpuUnit_IsEnabled'] = ($data['LpuUnit_IsEnabled'] == 'on' || $data['LpuUnit_IsEnabled'] == '2') ? 2 : 1;
				}
				else {
					$params['LpuUnit_IsEnabled'] = 1;
				}
			}
			else {
				if ( !empty($data['LpuUnit_IsEnabled']) &&
					(isSuperadmin() || ($data['session']['region']['nick'] == 'kareliya' && isLpuAdmin($data['Lpu_id'])))
				) {
					$params['LpuUnit_IsEnabled'] = ($data['LpuUnit_IsEnabled'] == 'on' || $data['LpuUnit_IsEnabled'] == '2') ? 2 : 1;
				}
				else {
					$LpuUnit_IsEnabled = $this->getFirstResultFromQuery("SELECT top 1 LpuUnit_IsEnabled FROM v_LpuUnit with (nolock) WHERE LpuUnit_id = :LpuUnit_id", $data, true);

					if ( $LpuUnit_IsEnabled === false ) {
						return array(array('Error_Msg' => 'Ошибка при определении признака записи на прием'));
					}

					$params['LpuUnit_IsEnabled'] = $LpuUnit_IsEnabled;
				}
			}

			$LpuUnitTypeDop_id = $data['LpuUnitTypeDop_id'];

			// Доп/справочник может быть добавлен / и кстати - почему Like
			if ( !empty($data['DopNew']) ) {
				//$dop_new = toAnsi($data['DopNew']);
				$dop_new = $data['DopNew'];
				$sql = "
					select
						LpuUnitTypeDop_id
					from
						v_LpuUnitTypeDop with (nolock)
					where
						LpuUnitTypeDop_Name = :dop_new and Server_id=:Server_id
					";
				$result = $this->db->query($sql, array(
					'dop_new' => $dop_new,
					'Server_id' => $data['Server_id']
				));
				if (is_object($result))
				{
					if ( isset( $sel[0] ) )
					{
						$sel = $result->result('array');
						if ( $sel[0]['LpuUnitTypeDop_id'] > 0 )
							$LpuUnitTypeDop_id = $sel[0]['LpuUnitTypeDop_id'];
					}
					else
					{
						$sql = "
							declare @LUTD_id bigint
							exec p_LpuUnitTypeDop_ins
								@LpuUnitTypeDop_Name = :LpuUnitTypeDop_Name,
								@pmUser_id = :pmUser_id,
								@Server_id = :Server_id,
								@LpuUnitTypeDop_id=@LUTD_id output
							select @LUTD_id as LpuUnitTypeDop_id;
							";
						$result = $this->db->query($sql, array(
							'LpuUnitTypeDop_Name' => $dop_new,
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id']
						));
						if (is_object($result))
						{
							$sel = $result->result('array');
							if ( $sel[0]['LpuUnitTypeDop_id'] > 0 ) {
								$params['LpuUnitTypeDop_id'] = $sel[0]['LpuUnitTypeDop_id'];
							}
						}
					}
				}
			}

			if ( !empty($data['LpuUnit_id']) ) {
				$dopParams = $this->getFirstRowFromQuery("
					select
						Address_id,
						LpuUnit_IsDirWithRec,
						LpuUnit_ExtMedCnt,
						LpuUnit_Guid,
						LpuUnit_FRMOUnitID,
						LpuUnit_FRMOid
					from v_LpuUnit with (nolock)
					where LpuUnit_id = :LpuUnit_id
				", $data, true);

				if ( $dopParams === false ) {
					return array(array('Error_Msg' => 'Ошибка при получении доп. параметров группы отделений'));
				}

				$params = array_merge($params, $dopParams);
			}
			else {
				$params['Address_id'] = null;
				$params['LpuUnit_IsDirWithRec'] = null;
				$params['LpuUnit_ExtMedCnt'] = null;
				$params['LpuUnit_Guid'] = null;
				$params['LpuUnit_FRMOUnitID'] = null;
				$params['LpuUnit_FRMOid'] = null;
			}

			if ($data['session']['region']['nick'] == 'ufa') {
				$params['LpuUnit_IsOMS'] = $data['LpuUnit_IsOMS']+1;
			}
		}

		$query = "
			declare
				@LpuUnit_id bigint = :LpuUnit_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';

			exec {$proc}
				@LpuUnit_id = @LpuUnit_id output,
				@LpuUnit_begDate = :LpuUnit_begDate,
				@LpuUnit_endDate = :LpuUnit_endDate,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@LpuUnitTypeDop_id = :LpuUnitTypeDop_id,
				@LpuUnit_Code = :LpuUnit_Code,
				@LpuUnit_Name = :LpuUnit_Name,
				@LpuUnit_Phone = :LpuUnit_Phone,
				@LpuUnit_Descr = :LpuUnit_Descr,
				@LpuUnit_Email = :LpuUnit_Email,
				@LpuUnit_IP = :LpuUnit_IP,
				@LpuUnit_IsEnabled = :LpuUnit_IsEnabled,
				@LpuUnitProfile_fid = :LpuUnitProfile_fid,
				@LpuUnit_isStandalone = :LpuUnit_isStandalone,
				@LpuUnit_isCMP = :LpuUnit_isCMP,
				@LpuUnit_isHomeVisit = :LpuUnit_isHomeVisit,
				@UnitDepartType_fid = :UnitDepartType_fid,
				@LpuBuildingPass_id = :LpuBuildingPass_id,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@LpuUnitSet_id = :LpuUnitSet_id,
				@LpuUnit_IsOMS = :LpuUnit_IsOMS,
				@Address_id = :Address_id,
				@LpuUnit_IsDirWithRec = :LpuUnit_IsDirWithRec,
				@LpuUnit_ExtMedCnt = :LpuUnit_ExtMedCnt,
				@LpuUnit_Guid = :LpuUnit_Guid,
				@LpuUnit_FRMOUnitID = :LpuUnit_FRMOUnitID,
				@LpuUnit_FRMOid = :LpuUnit_FRMOid,
				@LpuUnit_isPallCC = :LpuUnit_isPallCC,
				@LpuUnit_IsNotFRMO = :LpuUnit_IsNotFRMO,
				@FRMOUnit_id = :FRMOUnit_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;

			select @LpuUnit_id as LpuUnit_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			// Удаляем данные из кэша
			$this->load->library('swCache');
			$this->swcache->clear("LpuUnitList_".$data['Lpu_id']);

			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function saveLpuBuilding($data)
	{
		if ( !empty($data['LpuBuilding_id']) ) {
			$checkResult = $this->_checkOpenChildStruct('saveLpuBuilding', $data);
			if ( !empty($checkResult) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => $checkResult));
			}
		}

		$Address_id = NULL;
		$PAddress_id = NULL;
		$data['LpuLevel_id'] = NULL;
		$data['LpuLevel_cid'] = NULL;

		// Проверка уникальности кода среди подразделений, выгружаемых в ПМУ, в рамках одной МО
		// @task https://redmine.swan.perm.ru/issues/66399
		// Для Астрахани проверку отключаем
		// @task https://redmine.swan.perm.ru/issues/67816
		if ( !in_array($this->getRegionNick(), array('astra')) && !empty($data['LpuBuilding_Code']) && $data['LpuBuilding_IsExport'] == 2 && empty($data['LpuBuilding_endDate']) ) {
			$query = "
				select top 1 LpuBuilding_id
				from v_LpuBuilding with (nolock)
				where Lpu_id = :Lpu_id
					and LpuBuilding_id != ISNULL(:LpuBuilding_id, 0)
					and LpuBuilding_Code = :LpuBuilding_Code
					and LpuBuilding_endDate is null
					and LpuBuilding_IsExport = 2
			";
			$result = $this->db->query($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'LpuBuilding_Code' => $data['LpuBuilding_Code'],
				'LpuBuilding_endDate' => $data['LpuBuilding_endDate']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['LpuBuilding_id']) ) {
				return array(array('Error_Msg' => 'Код подразделения должен быть уникальным в рамках МО. Указанный код уже используется. Измените введенное значение в поле "Код".'));
			}
		}

		if ( !empty($data['fromAPI']) || in_array($this->regionNick, array('krym')) ) {
			$query = "
				select top 1 LpuBuilding_id
				from v_LpuBuilding with (nolock)
				where Lpu_id = :Lpu_id
					and LpuBuilding_id != ISNULL(:LpuBuilding_id, 0)
					and LpuBuilding_Code = :LpuBuilding_Code
			";
			$result = $this->db->query($query, array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuBuilding_id' => $data['LpuBuilding_id'],
				'LpuBuilding_Code' => $data['LpuBuilding_Code']
			));

			if ( !is_object($result) ) {
				return false;
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['LpuBuilding_id']) ) {
				return array(array('Error_Msg' => 'Код подразделения должен быть уникальным в рамках МО. Указанный код уже используется. Измените введенное значение в поле "Код".'));
			}
		}

		if (!isset($data['LpuBuilding_id']))
		{
			$proc = 'p_LpuBuilding_ins';
		}
		else
		{
			$proc = 'p_LpuBuilding_upd';
			$sql = "select Address_id, PAddress_id, LpuLevel_id, LpuLevel_cid from v_LpuBuilding with (nolock) where LpuBuilding_id = :LpuBuilding_id";
			$res = $this->db->query($sql, array('LpuBuilding_id' => $data['LpuBuilding_id']));
			if ( is_object($res) )
			{
				$sel = $res->result('array');

				if ( is_array($sel) && count($sel) > 0 ) {
					$Address_id = $sel[0]['Address_id'];
					$PAddress_id = $sel[0]['PAddress_id'];
					$data['LpuLevel_id'] = $sel[0]['LpuLevel_id'];
					$data['LpuLevel_cid'] = $sel[0]['LpuLevel_cid'];
				}
			}
		}

		if ( empty($data['fromAPI']) ) {
			$data['Address_id'] = $Address_id;
			$data['PAddress_id'] = $PAddress_id;
			// Сохранение адреса
			// возможные варианты:
			// 1. Удаление адреса   - если Address_id not null and другие поля пустые
			// 2. Добавление адреса - если Address_id null and другие поля заполнены
			// 3. Апдейт адреса - если Address_id not null and другие поля заполнены

			// создаем или редактируем адрес
			// Если строка адреса пустая
			if (!isset($data['Address_Address']))
			{
				$Address_id = NULL;
			}
			else
			{
				// не было адреса
				if ((isset($data['Address_Address'])) && (!isset($Address_id)))
				{
					$sql = "
						declare
							@Address_id bigint = null,
							@Error_Code bigint,
							@Error_Message varchar(4000);
						exec p_Address_ins
							@Server_id = :Server_id,
							@Address_id = @Address_id output,
							@KLAreaType_id = Null, -- опреляется логикой в хранимке
							@KLCountry_id = :KLCountry_id,
							@KLRgn_id = :KLRGN_id,
							@KLSubRgn_id = :KLSubRGN_id,
							@KLCity_id = :KLCity_id,
							@KLTown_id = :KLTown_id,
							@KLStreet_id = :KLStreet_id,
							@Address_Zip = :Address_Zip,
							@Address_House = :Address_House,
							@Address_Corpus = :Address_Corpus,
							@Address_Flat = :Address_Flat,
							@Address_Address = :Address_Address,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output
						select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					";
					$res = $this->db->query($sql, array(
						'Server_id' => $data['Server_id'],
						'KLCountry_id' => $data['KLCountry_id'],
						'KLRGN_id' => $data['KLRGN_id'],
						'KLSubRGN_id' => $data['KLSubRGN_id'],
						'KLCity_id' => $data['KLCity_id'],
						'KLTown_id' => $data['KLTown_id'],
						'KLStreet_id' => $data['KLStreet_id'],
						'Address_Zip' => $data['Address_Zip'],
						'Address_House' => $data['Address_House'],
						'Address_Corpus' => $data['Address_Corpus'],
						'Address_Flat' => $data['Address_Flat'],
						'Address_Address' => $data['Address_Address'],
						'pmUser_id' => $data['pmUser_id'],
					));

					if ( is_object($res) ) {
						$sel = $res->result('array');

						if ( !empty($sel[0]['Error_Msg']) ) {
							return $sel;
						}

						$Address_id = $sel[0]['Address_id'];
					}
					else {
						return false;
					}
				}
				// обновляем адрес
				else {
					if ( is_object($res) )
					{
						$sql = "
							declare
								@Address_id bigint = :Address_id,
								@Error_Code bigint,
								@Error_Message varchar(4000);
							exec p_Address_upd
								@Server_id = :Server_id,
								@Address_id = @Address_id output,
								@KLAreaType_id = NULL, -- опреляется логикой в хранимке
								@KLCountry_id = :KLCountry_id,
								@KLRgn_id = :KLRGN_id,
								@KLSubRgn_id = :KLSubRGN_id,
								@KLCity_id = :KLCity_id,
								@KLTown_id = :KLTown_id,
								@KLStreet_id = :KLStreet_id,
								@Address_Zip = :Address_Zip,
								@Address_House = :Address_House,
								@Address_Corpus = :Address_Corpus,
								@Address_Flat = :Address_Flat,
								@Address_Address = :Address_Address,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output
							select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
						";
						$res = $this->db->query($sql, array(
							'Server_id' => $data['Server_id'],
							'Address_id' => $data['Address_id'],
							'KLCountry_id' => $data['KLCountry_id'],
							'KLRGN_id' => $data['KLRGN_id'],
							'KLSubRGN_id' => $data['KLSubRGN_id'],
							'KLCity_id' => $data['KLCity_id'],
							'KLTown_id' => $data['KLTown_id'],
							'KLStreet_id' => $data['KLStreet_id'],
							'Address_Zip' => $data['Address_Zip'],
							'Address_House' => $data['Address_House'],
							'Address_Corpus' => $data['Address_Corpus'],
							'Address_Flat' => $data['Address_Flat'],
							'Address_Address' => $data['Address_Address'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
					else
						return false;
				}
			}

			// Фактический адрес
			// Если строка адреса пустая
			if (!isset($data['PAddress_Address']))
			{
				$PAddress_id = NULL;
			}
			else
			{
				// не было адреса
				if ((isset($data['PAddress_Address'])) && (!isset($PAddress_id)))
				{
					$sql = "
						declare
							@Address_id bigint = null,
							@Error_Code bigint,
							@Error_Message varchar(4000);
						exec p_Address_ins
							@Server_id = :Server_id,
							@Address_id = @Address_id output,
							@KLAreaType_id = Null, -- опреляется логикой в хранимке
							@KLCountry_id = :KLCountry_id,
							@KLRgn_id = :KLRGN_id,
							@KLSubRgn_id = :KLSubRGN_id,
							@KLCity_id = :KLCity_id,
							@KLTown_id = :KLTown_id,
							@KLStreet_id = :KLStreet_id,
							@Address_Zip = :Address_Zip,
							@Address_House = :Address_House,
							@Address_Corpus = :Address_Corpus,
							@Address_Flat = :Address_Flat,
							@Address_Address = :Address_Address,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output
						select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
					";
					$res = $this->db->query($sql, array(
						'Server_id' => $data['Server_id'],
						'KLCountry_id' => $data['PKLCountry_id'],
						'KLRGN_id' => $data['PKLRGN_id'],
						'KLSubRGN_id' => $data['PKLSubRGN_id'],
						'KLCity_id' => $data['PKLCity_id'],
						'KLTown_id' => $data['PKLTown_id'],
						'KLStreet_id' => $data['PKLStreet_id'],
						'Address_Zip' => $data['PAddress_Zip'],
						'Address_House' => $data['PAddress_House'],
						'Address_Corpus' => $data['PAddress_Corpus'],
						'Address_Flat' => $data['PAddress_Flat'],
						'Address_Address' => $data['PAddress_Address'],
						'pmUser_id' => $data['pmUser_id']
					));

					if ( is_object($res) ) {
						$sel = $res->result('array');

						if ( !empty($sel[0]['Error_Msg']) ) {
							return $sel;
						}

						$PAddress_id = $sel[0]['Address_id'];
					}
					else {
						return false;
					}
				}
				// обновляем адрес
				else
				{
					if ( is_object($res) )
					{
						$sql = "
							declare
								@Address_id bigint = :Address_id,
								@Error_Code bigint,
								@Error_Message varchar(4000);
							exec p_Address_upd
								@Server_id = :Server_id,
								@Address_id = @Address_id output,
								@KLAreaType_id = NULL, -- опреляется логикой в хранимке
								@KLCountry_id = :KLCountry_id,
								@KLRgn_id = :KLRGN_id,
								@KLSubRgn_id = :KLSubRGN_id,
								@KLCity_id = :KLCity_id,
								@KLTown_id = :KLTown_id,
								@KLStreet_id = :KLStreet_id,
								@Address_Zip = :Address_Zip,
								@Address_House = :Address_House,
								@Address_Corpus = :Address_Corpus,
								@Address_Flat = :Address_Flat,
								@Address_Address = :Address_Address,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code output,
								@Error_Message = @Error_Message output
							select @Address_id as Address_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
						";
						$res = $this->db->query($sql, array(
							'Server_id' => $data['Server_id'],
							'Address_id' => $data['PAddress_id'],
							'KLCountry_id' => $data['PKLCountry_id'],
							'KLRGN_id' => $data['PKLRGN_id'],
							'KLSubRGN_id' => $data['PKLSubRGN_id'],
							'KLCity_id' => $data['PKLCity_id'],
							'KLTown_id' => $data['PKLTown_id'],
							'KLStreet_id' => $data['PKLStreet_id'],
							'Address_Zip' => $data['PAddress_Zip'],
							'Address_House' => $data['PAddress_House'],
							'Address_Corpus' => $data['PAddress_Corpus'],
							'Address_Flat' => $data['PAddress_Flat'],
							'Address_Address' => $data['PAddress_Address'],
							'pmUser_id' => $data['pmUser_id']
						));
					}
					else
						return false;
				}
			}
		}
		else {
			$Address_id = (!empty($data['Address_id']) ? $data['Address_id'] : null);
			$PAddress_id = (!empty($data['PAddress_id']) ? $data['PAddress_id'] : null);
		}

		$query = "
		declare
			@LpuBuilding_id bigint = :LpuBuilding_id,
			@Error_Code bigint,
			@Error_Message varchar(4000);
		exec {$proc}
			@LpuBuilding_id = @LpuBuilding_id output,
			@Lpu_id = :Lpu_id,
			@LpuBuildingType_id = :LpuBuildingType_id,
			@LpuBuilding_Code = :LpuBuilding_Code,
			@LpuBuilding_begDate = :LpuBuilding_begDate,
			@LpuBuilding_endDate = :LpuBuilding_endDate,
			@LpuBuilding_Nick = :LpuBuilding_Nick,
			@LpuBuilding_Name = :LpuBuilding_Name,
			@LpuBuilding_WorkTime = :LpuBuilding_WorkTime,
			@LpuBuilding_RoutePlan = :LpuBuilding_RoutePlan,
			@Address_id = :Address_id,
			@PAddress_id = :PAddress_id,
			@LpuLevel_id = :LpuLevel_id,
			@LpuLevel_cid = :LpuLevel_cid,
			@pmUser_id = :pmUser_id,
			@Server_id = :Server_id,
			@LpuBuilding_IsExport = :LpuBuilding_IsExport,
			@LpuBuilding_CmpStationCode = :LpuBuilding_CmpStationCode,
			@LpuBuilding_CmpSubstationCode = :LpuBuilding_CmpSubstationCode,
			@LpuBuilding_Longitude = :LpuBuilding_Longitude,
			@LpuBuilding_Latitude = :LpuBuilding_Latitude,
			@LpuBuilding_IsPrint = :LpuBuilding_IsPrint,
			@LpuFilial_id = :LpuFilial_id,
			@LpuBuilding_IsAIDSCenter = :LpuBuilding_IsAIDSCenter,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuBuilding_id as LpuBuilding_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$params = array(
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuildingType_id' => $data['LpuBuildingType_id'],
			'LpuBuilding_Code' => $data['LpuBuilding_Code'],
			'LpuBuilding_begDate' => $data['LpuBuilding_begDate'],
			'LpuBuilding_endDate' => $data['LpuBuilding_endDate'],
			'LpuBuilding_Nick' => $data['LpuBuilding_Nick'],
			'LpuBuilding_Name' => $data['LpuBuilding_Name'],
			'LpuBuilding_WorkTime' => $data['LpuBuilding_WorkTime'],
			'LpuBuilding_RoutePlan' => $data['LpuBuilding_RoutePlan'],
			'Address_id' => $Address_id,
			'PAddress_id' => $PAddress_id,
			'LpuLevel_id' => $data['LpuLevel_id'],
			'LpuLevel_cid' => $data['LpuLevel_cid'],
			'Server_id' => $data['Server_id'],
			'LpuBuilding_IsExport' => $data['LpuBuilding_IsExport'],
			'LpuBuilding_CmpStationCode' => $data['LpuBuilding_CmpStationCode'],
			'LpuBuilding_CmpSubstationCode' => $data['LpuBuilding_CmpSubstationCode'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuBuilding_Latitude'  => $data['LpuBuilding_Latitude'],
			'LpuBuilding_Longitude' => $data['LpuBuilding_Longitude'],
			'LpuBuilding_IsPrint' => (empty($data['LpuBuilding_id']) && $this->getRegionNick() == 'krym' ) ? 1 : null,
			'LpuFilial_id' => $data['LpuFilial_id'],
			'LpuBuilding_IsAIDSCenter' => $data['LpuBuilding_IsAIDSCenter']
		);
		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result))
		{
			// Блок кабинета здоровья (данные в отдельной таблице LpuBuildingHealth )
			if( is_object($result->first_row()) ) {
				$LpuBuilding_id = $result->first_row()->LpuBuilding_id;
				//узнаем, что имеется на это подразделение в LpuBuildingHealth :
				$query = "
					SELECT
						LpuBuildingHealth_id
					FROM LpuBuildingHealth
					WHERE LpuBuilding_id = :LpuBuilding_id
				";
				$health_id = $this->getFirstResultFromQuery($query, array('LpuBuilding_id'=>$LpuBuilding_id));
				
				$query = "";
				$params = array(
					'LpuBuilding_id'=>$LpuBuilding_id,
					'LpuBuildingHealth_id' => $health_id,
					'pmUser_id'=>$data['pmUser_id']
				);
				
				if(!empty($data['LpuBuildingHealth_Phone']) or !empty($data['LpuBuildingHealth_Email'])) {
					$params['phone'] = $data['LpuBuildingHealth_Phone'] ? $data['LpuBuildingHealth_Phone'] : '';
					$params['email'] = $data['LpuBuildingHealth_Email'] ? $data['LpuBuildingHealth_Email'] : '';
					
					$proc = $health_id ? "p_LpuBuildingHealth_upd" : "p_LpuBuildingHealth_ins";
					
					$query = "
						DECLARE @LpuBuildingHealth_id bigint = :LpuBuildingHealth_id,
								@Error_Code bigint,
								@Error_Message varchar(4000)

						EXEC	{$proc}
								@LpuBuildingHealth_id = @LpuBuildingHealth_id OUTPUT,
								@LpuBuilding_id = :LpuBuilding_id,
								@LpuBuildingHealth_Phone = :phone,
								@LpuBuildingHealth_Email = :email,
								@pmUser_id = :pmUser_id,
								@Error_Code = @Error_Code OUTPUT,
								@Error_Message = @Error_Message OUTPUT

						SELECT	@LpuBuildingHealth_id as LpuBuildingHealth_id,
								@Error_Code as Error_Code,
								@Error_Message as Error_Message
					";
				} else { //все поля блока пусты,
					if($health_id) {//и есть запись в таблице => удалить
						$query = "
							DECLARE @Error_Code int,
									@Error_Message varchar(4000)

							EXEC	p_LpuBuildingHealth_del
									@LpuBuildingHealth_id = :LpuBuildingHealth_id,
									@Error_Code = @Error_Code OUTPUT,
									@Error_Message = @Error_Message OUTPUT

							SELECT	@Error_Code as Error_Code,
									@Error_Message as Error_Message
						";
					}
				}
				if($query) {
					//~ echo getDebugSQL($query, $params);exit;
					$healthresult = $this->db->query($query, $params);
				}
			}
			
			/*
			// Удаляем адрес
			if (!isset($data['Address_Address']))
			{
				if (isset($data['Address_id']))
				{
					$sql = "exec p_Address_del {$data['Address_id']}";
					$res = $this->db->query($sql);
				}
			}

			if (!isset($data['PAddress_Address']))
			{
				if (isset($data['PAddress_id']))
				{
					$sql = "exec p_Address_del {$data['PAddress_id']}";
					$res = $this->db->query($sql);
				}
			}
			*/
			// Если вставка успешно завершилась, прибиваем кэш, если он конечно есть
			$this->load->library('swCache');
			$this->swcache->clear("LpuBuildingList_".$data['Lpu_id']);

			return $result->result('array');
		}
		else
		{
			return false;
		}
	}

    /**
     * Проверка на наличие незакрытых дочерних структур
     */
	protected function _checkOpenChildStruct($method, $data) {
		switch($method){
			case 'saveLpuSection':
				$data['endDate'] = $data['LpuSection_disDate'];
				//•	При закрытии отделений проверять подотделения, службы и склады.
				//•	При закрытии подотделений проверять есть ли не закрытые склады.
				$query = "
					select top 1 LpuSection_id as hasChild
					from v_LpuSection with (nolock)
					where
						LpuSection_pid = :LpuSection_id
						and (LpuSection_disDate is null or LpuSection_disDate > :endDate)

					union all

					select top 1 S.Storage_id as hasChild
					from
						v_Storage S with (nolock)
						inner join StorageStructLevel SSL with (nolock) on S.Storage_id = SSL.Storage_id
					where
						SSL.LpuSection_id = :LpuSection_id
						and (S.Storage_endDate is null or S.Storage_endDate > :endDate)

					union all

					select top 1 MedService_id as hasChild
					from v_MedService with (nolock)
					where
						LpuSection_id = :LpuSection_id
						and (MedService_endDT is null or MedService_endDT > :endDate)
				";
			break;
			case 'saveLpuUnit':
				$data['endDate'] = $data['LpuUnit_endDate'];
				//•	При закрытии групп отделений проверять отделения, службы и склады.
				$query = "
					select top 1 LpuSection_id as hasChild
					from v_LpuSection with (nolock)
					where
						LpuUnit_id = :LpuUnit_id
						and (LpuSection_disDate is null or LpuSection_disDate > :endDate)

					union all

					select top 1 S.Storage_id as hasChild
					from
						v_Storage S with (nolock)
						inner join StorageStructLevel SSL with (nolock) on S.Storage_id = SSL.Storage_id
					where
						SSL.LpuUnit_id = :LpuUnit_id
						and (S.Storage_endDate is null or S.Storage_endDate > :endDate)

					union all

					select top 1 MedService_id as hasChild
					from v_MedService with (nolock)
					where
						LpuUnit_id = :LpuUnit_id
						and (MedService_endDT is null or MedService_endDT > :endDate)
				";
			break;
			case 'saveLpuBuilding':
				$data['endDate'] = $data['LpuBuilding_endDate'];
				//•	При закрытии подразделения проверять группы отделений, службы и склады.
				$query = "
					select top 1 LpuUnit_id as hasChild
					from v_LpuUnit with (nolock)
					where
						LpuBuilding_id = :LpuBuilding_id
						and (LpuUnit_endDate is null or LpuUnit_endDate > :endDate)

					union all

					select top 1 S.Storage_id as hasChild
					from
						v_Storage S with (nolock)
						inner join StorageStructLevel SSL with (nolock) on S.Storage_id = SSL.Storage_id
					where
						SSL.LpuBuilding_id = :LpuBuilding_id
						and (S.Storage_endDate is null or S.Storage_endDate > :endDate)

					union all

					select top 1 MedService_id as hasChild
					from v_MedService with (nolock)
					where
						LpuBuilding_id = :LpuBuilding_id
						and (MedService_endDT is null or MedService_endDT > :endDate)
				";
			break;
		}

		if (!empty($query) && !empty($data['endDate'])) {
			//echo getDebugSQL($query,$data);die;
			$result = $this->db->query($query,$data);

			if (!is_object($result)) {
				return 'Ошибка запроса к БД (проверка ссылок на отделения в документах).';
			}

			$resp = $result->result('array');

			if (is_array($resp) && count($resp) > 0 && !empty($resp[0]['hasChild'])) {
				return 'Закрытие невозможно, есть незакрытые подчинённые элементы структуры.';
			} else {
				return "";
			}
		}
		else {
			return "";
		}
	}

    /**
     * Проверка даты закрытия строк штатного расписания (для задачи http://redmine.swan.perm.ru/issues/17622)
     */
	public function checkStaff($data)
	{
		if (empty($data['LpuSection_id'])) //При удалении отделения передается не "LpuSection_id", а "id"
		{
			$data['LpuSection_id'] = $_POST['id'];
			//При удалении проверяем, есть ли уже дата закрытия у отделения
			$query_section = "select LS.LpuSection_disDate
			 from v_LpuSection LS with (nolock)
			 where LS.LpuSection_id = :LpuSection_id
			";
			$querysection_result = $this->db->query($query_section, array('LpuSection_id'=>$data['LpuSection_id']));
			$section_disDate = $querysection_result->result('array');
			if(!is_null($section_disDate[0]['LpuSection_disDate']));
			//Если у отделения уже стоит дата закрытия, то используем ее в качестве параметра для поиска по штатному расписанию
				$data['LpuSection_disDate'] = $section_disDate[0]['LpuSection_disDate'];
		}

		$params = array(
			'LpuSection_id' =>  $data['LpuSection_id']
		);

		if (empty($data['LpuSection_disDate'])) //Если это поле пустое, то в качестве параметра берем текущую дату
			$filter_date = ' or cast(st.EndDate as date) > dbo.tzGetDate())';
		else
		{
			$filter_date = ' or cast(st.EndDate as date) > :LpuSection_disDate)';
			$params['LpuSection_disDate'] =  $data['LpuSection_disDate'];
		}

		//Проверяем, есть ли позиции штатного расписания с пустой датой закрытия или с большей, чем дата закрытия (удаления) отделения:
		$query = "select st.*
			from persis.v_Staff st with (nolock)
			where st.LpuSection_id = :LpuSection_id
			and (st.EndDate is null".$filter_date;
		$query_result = $this->db->query($query,$params);
		//Если такие позиции есть, то выводим ошибку о невозможности закрытия/удаления отделения:
		$result = $query_result->result('array');
		if (is_array($result) && count($result) > 0) {
			return "Для удаления/закрытия отделения закройте все строки штатного расписания датой меньше или равной дате окончания работы отделения";
		}
		else {
			return "";
		}

	}

	/**
	 * Проверка наличия дочерних объектов
	 */
	public function checkLpuSectionHasChildObjects($ids) {
		$params = array();
		$inid = implode(',', $ids);

		$result = $this->queryResult("
			select top 1 'MedService' as [object_name]
			from v_MedService with (nolock)
			where LpuSection_id in (" . $inid . ")

			union all

			select top 1 'LpuSection' as [object_name]
			from v_LpuSection with (nolock)
			where LpuSection_pid in (" . $inid . ")
		", $params, true);

		if ( $result === false ) {
			return 'Ошибка запроса к БД (проверка наличия дочерних объектов)';
		}

		if ( is_array($result) && count($result) > 0 ) {
			return 'Существуют дочерние объекты в БД, ссылающиеся на ' . (count($ids) == 1 ? 'указанное отделение' : 'указанные отделения') . '.';
		}

		return '';
	}

	/**
	 * Проверка существования ссылок на отделения в документах
	 */
	function CheckLpuSectionLinksExists($ids)
	{
		$params = array();
		$inid = implode(',',$ids);
		$query = "
			Declare @useLpuSection as int = 0;

			-- Стационар
			Set @useLpuSection = (select top 1 1 from v_EvnSection t with(nolock) where t.LpuSection_id in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPS t with(nolock) where t.LpuSection_id in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPS t with(nolock) where t.LpuSection_did in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPS t with(nolock) where t.LpuSection_eid in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPS t with(nolock) where t.LpuSection_pid in (".$inid."))
			-- Поликлиника
			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPL t with(nolock) where t.LpuSection_id in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPL t with(nolock) where t.LpuSection_did in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnPL t with(nolock) where t.LpuSection_oid in (".$inid."))

			if (@useLpuSection is null)
				Set @useLpuSection = (select top 1 1 from v_EvnVizit t with(nolock) where t.LpuSection_id in (".$inid."))

			Select @useLpuSection as useLpuSection
		";

		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return 'Ошибка запроса к БД (проверка ссылок на отделения в документах)';
		}
		$resp = $result->result('array');
		if (is_array($resp) && count($resp) > 0 && !empty($resp[0]['useLpuSection'])) {
			return 'Существуют объекты в БД, ссылающиеся на ' . (count($ids) == 1 ? 'указанное отделение' : 'указанные отделения') . '.';
		}
		return '';
	}

    /**
     * Это Doc-блок
     */
	public function saveLpuSection($data) {
		if ( empty($data['Lpu_id']) ) {
			return array(array('Error_Msg' => 'Не указана МО'));
		}

		$this->beginTransaction();

		if ( !isSuperadmin() && !empty($data['LpuBuilding_id']) ) {
			// МО подразделения должна совпадать с текущей МО пользователя
			$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_LpuBuilding with (nolock) where LpuBuilding_id = :LpuBuilding_id", $data, true);

			if ( $Lpu_id === false || empty($Lpu_id) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при получении данных по подразделению'));
			}
			else if ( $Lpu_id != $data['Lpu_id'] ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'МО подразделения не соответствует Вашей текущей МО. Сохранение запрещено.'));
			}
		}

		if ( !empty($data['source']) && $data['source'] == 'API' ) {
			// Проверяем связку Lpu_id + LpuSectionOuter_id
			if ( !empty($data['LpuSectionOuter_id']) ) {
				$checkResult = $this->getFirstResultFromQuery("
					select top 1 LpuSectionOuter_id
					from v_LpuSection with(nolock)
					where LpuSectionOuter_id = :LpuSectionOuter_id
						and Lpu_id = :Lpu_id
						" . (!empty($data['LpuSection_id']) ? "and LpuSection_id != :LpuSection_id" : "") . "
				", $data);

				if ( $checkResult !== false && !empty($checkResult) ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Дубль по идентификатору отделения МО в сторонней МИС'));
				}
			}

			$data['LpuSectionProfile_id'] = $this->getLpuSectionProfileId($data['LpuSectionProfile_Code']);
			if (empty($data['LpuSectionProfile_id'])) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при получении идентификатора профиля отделения'));
			}

			// Проверяем соответствие LpuUnit_id и Lpu_id
			$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_LpuUnit with (nolock) where LpuUnit_id = :LpuUnit_id", $data, true);

			if ( $Lpu_id === false || empty($Lpu_id) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при получении данных по группе отделений'));
			}
			else if ( $Lpu_id != $data['Lpu_id'] ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'МО не соответствует группе отделений.'));
			}

			// Проверяем наличие записи в persis.FRMPSubdivision
			$FRMPSubdivision_id = $this->getFirstResultFromQuery("select top 1 id from persis.FRMPSubdivision with (nolock) where id = :FRMPSubdivision_id", $data, true);

			if ( $FRMPSubdivision_id === false || empty($FRMPSubdivision_id) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при проверке значения FRMPSubdivision_id'));
			}
		}
		// Тянем значение LpuSectionOuter_id из БД для случаев сохранения отделения не через API
		else if ( !empty($data['LpuSection_id']) ) {
			$dopParams = $this->getFirstRowFromQuery("
				select
					LpuSectionOuter_id,
					LpuBuildingPass_id
				from v_LpuSection with (nolock)
				where LpuSection_id = :LpuSection_id
			", $data, true);

			if ( $dopParams === false ) {
				return array(array('Error_Msg' => 'Ошибка при получении доп. параметров отделения'));
			}

			$data = array_merge($data, $dopParams);
		}

		if ( !empty($data['LpuSection_id']) ) {
			$checkResult = $this->_checkOpenChildStruct('saveLpuSection', $data);
			if ( !empty($checkResult) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => $checkResult));
			}

			// Проверяем штатное расписание, если при сохранении у отделения проставлена дата закрытия (задача http://redmine.swan.perm.ru/issues/17622)
			if ( !empty($data['LpuSection_disDate']) ) {
				$checkResult = $this->checkStaff($data);
				if ( !empty($checkResult) ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => $checkResult));
				}
			}

			// проверяем меняется ли профиль отделения.
			if ( $data['session']['region']['nick'] != 'ufa' || !isSuperadmin() ) {
				$getLpuSectionList = $this->getLpuSectionList(array('LpuSection_id' => $data['LpuSection_id']));
				$oldLpuSectionProfile = (!empty($getLpuSectionList[0]['LpuSectionProfile_id'])) ? $getLpuSectionList[0]['LpuSectionProfile_id'] : '';
				$newLpuSectionProfile = (!empty($data['LpuSectionProfile_id'])) ? $data['LpuSectionProfile_id'] : '';

				// Разобраться с подключением к реестровой БД
				if ( $newLpuSectionProfile != $oldLpuSectionProfile ) {
					$dbreg = $this->load->database('registry', true);
					$this->load->model('Registry_model', 'Reg_model');
					$checkResult = $this->Reg_model->checkLpuSectionInRegistry($data);
					if ( !empty($checkResult) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => $checkResult));
					}
				}
			}
		}

        if ($data['session']['region']['nick']!='ufa') {//Доработки по #17064 не распространяются на Уфу, поскольку у сисадминов и статистов Уфы  - замешательство (#18313)
            //#17064 Указание уровня МЭС при добавлении поликлинических отделений
            if (empty($data['MESLevel_id'])) {
                $LpuUnitType_id = $this->getFirstResultFromQuery('SELECT LpuUnitType_id FROM v_LpuUnit t with (nolock) WHERE t.LpuUnit_id = :LpuUnit_id', array('LpuUnit_id' => $data['LpuUnit_id']));
                if (!empty($LpuUnitType_id)) {
                    if (2 == $LpuUnitType_id) {
                        $data['MESLevel_id'] = $this->getFirstResultFromQuery('SELECT MesLevel_id FROM v_MESLevel with (nolock) WHERE MesLevel_Code = :MesLevel_Code', array('MesLevel_Code' => 1));
						if (empty($data['MESLevel_id'])) {
							$data['MESLevel_id'] = null;
						}
                    }
                } else {
                    //throw new Exception('У группы отделений не установлен атрибут "идентификатор типа подразделения МО"');
                }
            }
        }

		$sqlquery = "
			SELECT top 1 L.Lpu_IsLab, L.Lpu_id, LU.UnitDepartType_fid 
			FROM v_LpuUnit LU with (nolock)
				LEFT JOIN v_Lpu L with (nolock) on L.Lpu_id = LU.Lpu_id
			WHERE LU.LpuUnit_id = :LpuUnit_id
		";
		$UnitLpuInfo = $this->db->query($sqlquery, array('LpuUnit_id' => $data['LpuUnit_id']))->result('array');

		if ( !is_array($UnitLpuInfo) || count($UnitLpuInfo) == 0 ) {
			$this->rollbackTransaction();
			return false;
		}

		if ( !empty($UnitLpuInfo[0]["UnitDepartType_fid"]) && $UnitLpuInfo[0]["UnitDepartType_fid"] == 2 && !empty($data['FRMOSection_id']) ) {
			$checkResult = $this->getFirstResultFromQuery("
				select top 1 ls.LpuSection_id
				from v_LpuSection ls with (nolock)
				where ls.FRMOSection_id = :FRMOSection_id
					and ls.LpuSection_id != ISNULL(:LpuSection_id, 0)
			", array(
				'FRMOSection_id' => $data['FRMOSection_id'],
				'LpuSection_id' => !empty($data['LpuSection_id']) ? $data['LpuSection_id'] : null,
			));

			if ( $checkResult !== false && empty($checkResult) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'ОИД ФРМО отделения/кабинета не уникален. Уникальность этого значения требуется  для передачи на ФРМО данных об отделениях стационаров. Исправьте ОИД ФРМО отделения.'));
			}
		}

		$data["Lpu_IsLab"] = $UnitLpuInfo[0]["Lpu_IsLab"];
		$data["UnitLpuInfo_id"] = $UnitLpuInfo[0]["Lpu_id"];

		$sqlquery = "
			SELECT
		LpuPeriodOMS_begDate,
		LpuPeriodOMS_endDate
			FROM LpuPeriodOMS with (nolock)
			WHERE Lpu_id = :Lpu_Id
				and LpuPeriodOMS_pid is null
		";
		$queryParams = array('Lpu_Id' => $data["UnitLpuInfo_id"]);
		$datesOMS = $this->db->query($sqlquery, $queryParams)->result('array');

		$data["activeOMS"] = false;
		$today = new DateTime(date("d.m.Y"));

		if ($data["Lpu_IsLab"] == 2) {
			foreach ($datesOMS as $dates) {
				if ($dates["LpuPeriodOMS_begDate"] <= $today) {
					if (($dates["LpuPeriodOMS_endDate"] >= $today) || $dates["LpuPeriodOMS_endDate"] == null) {
						$data["activeOMS"] = true;
					}
				}
			}
		}

		if ( !empty($data['LpuSectionCode_id']) ) {
			$data['LpuSection_Code'] = $this->getFirstResultFromQuery("select top 1 LpuSectionCode_Code from v_LpuSectionCode with (nolock) where LpuSectionCode_id = :LpuSectionCode_id", $data, true);
		}

		$params = array(
			'FRMPSubdivision_id' => $data['FRMPSubdivision_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Server_id' => $data['Server_id'],
			'LpuUnit_id' => $data['LpuUnit_id'],
			'LpuSection_pid' => $data['LpuSection_pid'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'LpuSection_Code' => $data['LpuSection_Code'],
			'LpuSectionCode_id' => $data['LpuSectionCode_id'],
			'LpuSection_Name' => $data['LpuSection_Name'],
			'LpuSection_setDate' => $data['LpuSection_setDate'],
			'LpuSection_disDate' => $data['LpuSection_disDate'],
			'LpuSectionAge_id' => $data['LpuSectionAge_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
			'MESLevel_id' => $data['MESLevel_id'],
			'LpuSection_PlanVisitShift' => $data['LpuSection_PlanVisitShift'],
			'LpuSection_PlanTrip' => $data['LpuSection_PlanTrip'],
			'LpuSection_PlanVisitDay' => $data['LpuSection_PlanVisitDay'],
			'LpuSection_PlanAutopShift' => $data['LpuSection_PlanAutopShift'],
			'LpuSection_PlanResShift' => $data['LpuSection_PlanResShift'],
			'LpuSection_KolJob' => $data['LpuSection_KolJob'],
			'LpuSection_KolAmbul' => $data['LpuSection_KolAmbul'],
			'LpuSection_Descr' => $data['LpuSection_Descr'],
			'LpuSection_Contacts' => $data['LpuSection_Contacts'],
			'LpuSectionHospType_id' => $data['LpuSectionHospType_id'],
			'LpuSection_IsCons' => $data['LpuSection_IsCons'],
			'LpuSection_IsExportLpuRegion' => $data['LpuSection_IsExportLpuRegion'],
			'LpuSection_IsHTMedicalCare' => $data['LpuSection_IsHTMedicalCare'],
			'LpuSection_IsNoKSG' => $data['LpuSection_IsNoKSG'],
			'LevelType_id' => $data['LevelType_id'],
			'LpuSectionType_id' => $data['LpuSectionType_id'],
			'LpuSection_Area' => $data['LpuSection_Area'],
			'LpuSection_CountShift' => $data['LpuSection_CountShift'],
			'LpuSectionDopType_id' => $data['LpuSectionDopType_id'],
			'LpuCostType_id' => $data['LpuCostType_id'],
			'LpuSectionOuter_id' => (!empty($data['LpuSectionOuter_id']) ? $data['LpuSectionOuter_id'] : null),
			'LpuBuildingPass_id' => (!empty($data['LpuBuildingPass_id']) ? $data['LpuBuildingPass_id'] : null),
			'PalliativeType_id' => (!empty($data['PalliativeType_id']) ? $data['PalliativeType_id'] : null),
			'LpuSection_IsNotFRMO' => ($data['LpuSection_IsNotFRMO'] == 'on' || $data['LpuSection_IsNotFRMO'] == '2' ? 2 : 1),
			'FRMOUnit_id' => (!empty($data['FRMOUnit_id']) ? $data['FRMOUnit_id'] : null),
			'FRMOSection_id' => (!empty($data['FRMOSection_id']) ? $data['FRMOSection_id'] : null),
			'LpuSection_FRMOBuildingOid' => (!empty($data['LpuSection_FRMOBuildingOid']) ? $data['LpuSection_FRMOBuildingOid'] : null),
			'pmUser_id' => $data['pmUser_id']
		);

		if ( empty($data['LpuSection_id']) ) {
			$proc = 'p_LpuSection_ins';
		}
		else {
			$proc = 'p_LpuSection_upd';
		}

		// Подотделения и отделения
		if ( !empty($data['LpuSection_pid']) ) {
			$params['LpuUnit_id'] = null;
		}

		// On в YesNo
		if ( !empty($data['LpuSection_F14']) ) {
			$params['LpuSection_IsF14'] = ($data['LpuSection_F14']=='on' || $data['LpuSection_F14']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsF14'] = 1;
		}

		if ( !empty($data['LpuSection_IsDirRec']) ) {
			$params['LpuSection_IsDirRec'] = ($data['LpuSection_IsDirRec']=='on' || $data['LpuSection_IsDirRec']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsDirRec'] = 1;
		}

		if ( !empty($data['LpuSection_IsQueueOnFree']) ) {
			$params['LpuSection_IsQueueOnFree'] = ($data['LpuSection_IsQueueOnFree']=='on' || $data['LpuSection_IsQueueOnFree']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsQueueOnFree'] = 1;
		}

		if ( !empty($data['LpuSection_IsUseReg']) ) {
			$params['LpuSection_IsUseReg'] = ($data['LpuSection_IsUseReg']=='on' || $data['LpuSection_IsUseReg']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsUseReg'] = 1;
		}

		if ( !empty($data['LpuSection_IsHTMedicalCare']) ) {
			$params['LpuSection_IsHTMedicalCare'] = ($data['LpuSection_IsHTMedicalCare']=='on' || $data['LpuSection_IsHTMedicalCare']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsHTMedicalCare'] = 1;
		}

		if ( !empty($data['LpuSection_IsNoKSG']) ) {
			$params['LpuSection_IsNoKSG'] = ($data['LpuSection_IsNoKSG']=='on' || $data['LpuSection_IsNoKSG']=='2')?2:1;
		}
		else {
			$params['LpuSection_IsNoKSG'] = 1;
		}

		$query = "
			declare
			@LpuSection_id bigint = :LpuSection_id,
			@Error_Code int = 0,
			@Error_Message varchar(4000) = '';

			exec {$proc}
				@LpuSection_id = @LpuSection_id output,
				@Server_id = :Server_id,
				@MesAgeGroup_id = Null,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSection_pid = :LpuSection_pid,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSection_Code = :LpuSection_Code,
				@LpuSectionCode_id = :LpuSectionCode_id,
				@LpuSection_Name = :LpuSection_Name,
				@LpuSection_setDate = :LpuSection_setDate,
				@LpuSection_disDate = :LpuSection_disDate,
				@LpuSectionAge_id = :LpuSectionAge_id,
				@LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
				@MESLevel_id = :MESLevel_id,
				@LpuSection_IsF14 = :LpuSection_IsF14,
				@LpuSection_Descr = :LpuSection_Descr,
				@LpuSection_Contacts = :LpuSection_Contacts,
				@LpuSectionHospType_id = :LpuSectionHospType_id,
				@LpuSection_IsDirRec = :LpuSection_IsDirRec,
				@LpuSection_IsQueueOnFree = :LpuSection_IsQueueOnFree,
				@LpuSection_IsUseReg = :LpuSection_IsUseReg,
				@FRMPSubdivision_id = :FRMPSubdivision_id,
				@LpuSection_PlanVisitShift = :LpuSection_PlanVisitShift,
				@LpuSection_PlanTrip = :LpuSection_PlanTrip,
				@LpuSection_PlanVisitDay = :LpuSection_PlanVisitDay,
				@LpuSection_PlanAutopShift = :LpuSection_PlanAutopShift,
				@LpuSection_PlanResShift = :LpuSection_PlanResShift,
				@LpuSection_KolJob = :LpuSection_KolJob,
				@LpuSection_KolAmbul = :LpuSection_KolAmbul,
				@LpuSection_IsCons = :LpuSection_IsCons,
				@LpuSection_IsExportLpuRegion = :LpuSection_IsExportLpuRegion,
				@LpuSection_IsHTMedicalCare = :LpuSection_IsHTMedicalCare,
				@LpuSection_IsNoKSG = :LpuSection_IsNoKSG,
				@LevelType_id = :LevelType_id,
				@LpuSectionType_id = :LpuSectionType_id,
				@LpuSection_Area = :LpuSection_Area,
				@LpuSection_CountShift = :LpuSection_CountShift,
				@LpuSectionDopType_id = :LpuSectionDopType_id,
				@LpuCostType_id = :LpuCostType_id,
				@LpuSectionOuter_id = :LpuSectionOuter_id,
				@LpuBuildingPass_id = :LpuBuildingPass_id,
				@PalliativeType_id = :PalliativeType_id,
				@FRMOUnit_id = :FRMOUnit_id,
				@FRMOSection_id = :FRMOSection_id,
				@LpuSection_FRMOBuildingOid = :LpuSection_FRMOBuildingOid,
				@LpuSection_IsNotFRMO = :LpuSection_IsNotFRMO,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuSection_id as LpuSection_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		//echo getDebugSql($query,$params);exit;
		$result = $this->db->query($query, $params );

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			return false;
		}

		$mainResponse = $result->result('array');

		if ( !is_array($mainResponse) || count($mainResponse) == 0 ) {
			$this->rollbackTransaction();
			return false;
		}

		$data['LpuSection_id'] = $mainResponse[0]['LpuSection_id'];

		if ( !empty($data['lpuSectionProfileData']) ) {
			$lpuSectionProfileData = json_decode($data['lpuSectionProfileData'], true);

			if ( is_array($lpuSectionProfileData) ) {
				for ( $i = 0; $i < count($lpuSectionProfileData); $i++ ) {
					$lpuSectionProfile = array(
						 'pmUser_id' => $data['pmUser_id']
						,'Server_id' => $data['Server_id']
						,'LpuSection_id' => $mainResponse[0]['LpuSection_id']
					);

					if ( empty($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_id']) || !is_numeric($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_id']) ) {
						continue;
					}

					if ( empty($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_begDate']) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указана дата начала действия дополнительного профиля отделения'));
					}
					else if ( CheckDateFormat($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_begDate']) != 0 ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты начала действия дополнительного профиля отделения'));
					}

					if ( !empty($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_endDate']) && CheckDateFormat($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_endDate']) != 0 ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты окончания действия дополнительного профиля отделения'));
					}

					if ( !isset($lpuSectionProfileData[$i]['RecordStatus_Code']) || !is_numeric($lpuSectionProfileData[$i]['RecordStatus_Code']) || !in_array($lpuSectionProfileData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					if ( empty($lpuSectionProfileData[$i]['LpuSectionProfile_id']) || !is_numeric($lpuSectionProfileData[$i]['LpuSectionProfile_id']) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указан профиль отделения в записи из списка дополнительных профилей отделения'));
					}

					$lpuSectionProfile['LpuSectionLpuSectionProfile_id'] = $lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_id'];
					$lpuSectionProfile['LpuSectionProfile_id'] = $lpuSectionProfileData[$i]['LpuSectionProfile_id'];
					$lpuSectionProfile['LpuSectionLpuSectionProfile_begDate'] = ConvertDateFormat($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_begDate']);

					if ( !empty($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_endDate']) ) {
						$lpuSectionProfile['LpuSectionLpuSectionProfile_endDate'] = ConvertDateFormat($lpuSectionProfileData[$i]['LpuSectionLpuSectionProfile_endDate']);
					}
					else {
						$lpuSectionProfile['LpuSectionLpuSectionProfile_endDate'] = NULL;
					}

					switch ( $lpuSectionProfileData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveLpuSectionLpuSectionProfile($lpuSectionProfile);
						break;

						case 3:
							$queryResponse = $this->deleteLpuSectionLpuSectionProfile($lpuSectionProfile);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при ' . ($lpuSectionProfileData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' дополнительного профиля отделения'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}
		
		if ( !empty($data['lpuSectionMedProductTypeLinkData']) ) {
			$lpuSectionMedProductTypeLinkData = json_decode($data['lpuSectionMedProductTypeLinkData'], true);

			if ( is_array($lpuSectionMedProductTypeLinkData) ) {
				for ( $i = 0; $i < count($lpuSectionMedProductTypeLinkData); $i++ ) {
					$lpuSectionProfile = array(
						 'pmUser_id' => $data['pmUser_id']
						,'Server_id' => $data['Server_id']
						,'LpuSection_id' => $mainResponse[0]['LpuSection_id']
					);

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id']) ) {
						continue;
					}

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указана дата начала действия мед. оборудования'));
					}
					else if ( CheckDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']) != 0 ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты начала действия мед. оборудования'));
					}

					if ( !empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) && CheckDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) != 0 ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Неверный формат даты окончания действия мед. оборудования'));
					}

					if ( !isset($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code']) || !in_array($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'], array(0, 2, 3)) ) {
						continue;
					}

					if ( empty($lpuSectionMedProductTypeLinkData[$i]['MedProductType_id']) || !is_numeric($lpuSectionMedProductTypeLinkData[$i]['MedProductType_id']) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Не указан тип оборудования в записи из списка мед. оборудования'));
					}

					$lpuSectionProfile['LpuSectionMedProductTypeLink_id'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_id'];
					$lpuSectionProfile['MedProductType_id'] = $lpuSectionMedProductTypeLinkData[$i]['MedProductType_id'];
					$lpuSectionProfile['LpuSectionMedProductTypeLink_TotalAmount'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_TotalAmount'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_IncludePatientKVI'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_IncludePatientKVI'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_IncludeReanimation'] = $lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_IncludeReanimation'] ?? 0;
					$lpuSectionProfile['LpuSectionMedProductTypeLink_begDT'] = ConvertDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_begDT']);

					if ( !empty($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']) ) {
						$lpuSectionProfile['LpuSectionMedProductTypeLink_endDT'] = ConvertDateFormat($lpuSectionMedProductTypeLinkData[$i]['LpuSectionMedProductTypeLink_endDT']);
					}
					else {
						$lpuSectionProfile['LpuSectionMedProductTypeLink_endDT'] = NULL;
					}

					switch ( $lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveLpuSectionMedProductTypeLink($lpuSectionProfile);
						break;

						case 3:
							$queryResponse = $this->deleteLpuSectionMedProductTypeLink($lpuSectionProfile);
						break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при ' . ($lpuSectionMedProductTypeLinkData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' мед. оборудования'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		if (!empty($data['LpuSectionServiceData'])) {
			$LpuSectionServiceData = json_decode($data['LpuSectionServiceData'], true);

			if ( is_array($LpuSectionServiceData) ) {
				foreach($LpuSectionServiceData as $LpuSectionService) {
					$LpuSectionService['pmUser_id'] = $data['pmUser_id'];
					$LpuSectionService['LpuSection_id'] = $mainResponse[0]['LpuSection_id'];

					if ( empty($LpuSectionService['LpuSection_did']) ) {
						if ( !empty($LpuSectionService['LpuSectionService_id']) && $LpuSectionService['LpuSectionService_id'] > 0 ) {
							$LpuSectionService['RecordStatus_Code'] = 3;
						}
						else {
							continue;
						}
					}

					switch ( $LpuSectionService['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveLpuSectionService($LpuSectionService);
							break;

						case 3:
							$queryResponse = $this->deleteLpuSectionService($LpuSectionService);
							break;
					}

					if ( isset($queryResponse) && !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при ' . ($LpuSectionService['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' обслуживаемого отделения'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		if ( !empty($data['LpuSectionMedicalCareKindData']) ) {
			$LpuSectionMedicalCareKindData = json_decode($data['LpuSectionMedicalCareKindData'], true);

			if ( is_array($LpuSectionMedicalCareKindData) ) {
				for ( $i = 0; $i < count($LpuSectionMedicalCareKindData); $i++ ) {
					$LpuSectionMedicalCareKind = array(
						'pmUser_id' => $data['pmUser_id'],
						'Server_id' => $data['Server_id'],
						'LpuSection_id' => $mainResponse[0]['LpuSection_id']
					);

					if (
						empty($LpuSectionMedicalCareKindData[$i]['LpuSectionMedicalCareKind_id']) ||
						!is_numeric($LpuSectionMedicalCareKindData[$i]['LpuSectionMedicalCareKind_id']) ||
						!isset($LpuSectionMedicalCareKindData[$i]['RecordStatus_Code']) ||
						!is_numeric($LpuSectionMedicalCareKindData[$i]['RecordStatus_Code']) ||
						!in_array($LpuSectionMedicalCareKindData[$i]['RecordStatus_Code'], array(0, 1, 2, 3))
					) {
						continue;
					}

					$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_id'] = $LpuSectionMedicalCareKindData[$i]['LpuSectionMedicalCareKind_id'];
					$LpuSectionMedicalCareKind['MedicalCareKind_id'] = $LpuSectionMedicalCareKindData[$i]['MedicalCareKind_id'];
					$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_begDate'] = $LpuSectionMedicalCareKindData[$i]['LpuSectionMedicalCareKind_begDate'];
					$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate'] = $LpuSectionMedicalCareKindData[$i]['LpuSectionMedicalCareKind_endDate'];

					if (
						!empty($data['LpuSection_disDate']) && (
							empty($LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate']) ||
							$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate'] > ConvertDateFormat($data['LpuSection_disDate'])
						)
					) {
						$LpuSectionMedicalCareKindData[$i] = 2;
						$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate'] = $data['LpuSection_disDate'];
					}

					if (
						!empty($LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate']) &&
						$LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_begDate'] > $LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate']
					) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Вид МП: Дата начала периода не может быть меньше даты окончания периода '));
					}

					if (
						($data['LpuSection_setDate'] > $LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_begDate']) ||
						(!empty($data['LpuSection_disDate']) && $data['LpuSection_disDate'] < $LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_begDate'])
					) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Вид МП: Дата начала периода должна попадать в интервал дат работы отделения'));
					}

					if (
						!empty($LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate']) &&
						($data['LpuSection_setDate'] > $LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate']) ||
						(!empty($data['LpuSection_disDate']) && $data['LpuSection_disDate'] < $LpuSectionMedicalCareKind['LpuSectionMedicalCareKind_endDate'])
					) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Вид МП: Дата окончания периода должна попадать в интервал дат работы отделения '));
					}

					$queryResponse = array();
					switch ( $LpuSectionMedicalCareKindData[$i]['RecordStatus_Code'] ) {
						case 0:
						case 2:
							$queryResponse = $this->saveLpuSectionMedicalCareKind($LpuSectionMedicalCareKind);
							break;

						case 3:
							$queryResponse = $this->deleteLpuSectionMedicalCareKind($LpuSectionMedicalCareKind);
							break;
					}

					if ( !is_array($queryResponse) ) {
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при ' . ($LpuSectionMedicalCareKindData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' вида оказания  МП'));
					}
					else if ( !empty($queryResponse[0]['Error_Msg']) ) {
						$this->rollbackTransaction();
						return $queryResponse;
					}
				}
			}
		}

		if (empty($data['LpuSection_pid'])) {
			if (!empty($data['AttributeSignValueData'])) {
				$this->load->model('Attribute_model');
				$AttributeSignValueData = json_decode($data['AttributeSignValueData'], true);

				if ( is_array($AttributeSignValueData) ) {
					foreach($AttributeSignValueData as $AttributeSignValue) {
						$AttributeSignValue['pmUser_id'] = $data['pmUser_id'];
						$AttributeSignValue['AttributeSignValue_TablePKey'] = $mainResponse[0]['LpuSection_id'];
						$AttributeSignValue['AttributeSignValue_begDate'] = !empty($AttributeSignValue['AttributeSignValue_begDate'])?ConvertDateFormat($AttributeSignValue['AttributeSignValue_begDate']):null;
						$AttributeSignValue['AttributeSignValue_endDate'] = !empty($AttributeSignValue['AttributeSignValue_endDate'])?ConvertDateFormat($AttributeSignValue['AttributeSignValue_endDate']):null;

						$this->Attribute_model->isAllowTransaction = false;
						switch ( $AttributeSignValue['RecordStatus_Code'] ) {
							case 0:
							case 2:
								$AttributeSignValue_begDate = DateTime::createFromFormat('Y-m-d H:i', $AttributeSignValue['AttributeSignValue_begDate'].' 00:00');
								$LpuSection_setDate = DateTime::createFromFormat('Y-m-d H:i', $data['LpuSection_setDate'].' 00:00');
								if ($AttributeSignValue_begDate < $LpuSection_setDate) {
									$this->rollbackTransaction();
									return array(array('Error_Msg' => 'Начало действия значений атрибутов не может быть раньше даты создания отделения'));
								}
								if (!empty($AttributeSignValue['AttributeSignValue_endDate']) && !empty($data['LpuSection_disDate'])) {
									$AttributeSignValue_endDate = DateTime::createFromFormat('Y-m-d H:i', $AttributeSignValue['AttributeSignValue_endDate'].' 00:00');
									$LpuSection_disDate = DateTime::createFromFormat('Y-m-d H:i', $data['LpuSection_disDate'].' 00:00');
									if ($AttributeSignValue_endDate > $LpuSection_disDate) {
										$this->rollbackTransaction();
										return array(array('Error_Msg' => 'Окончание действия значений атрибутов не может быть позже даты закрытия отделения'));
									}
								}

								$queryResponse = $this->Attribute_model->saveAttributeSignValue($AttributeSignValue);
								break;

							case 3:
								$queryResponse = $this->Attribute_model->deleteAttributeSignValue($AttributeSignValue);
								break;
						}
						$this->Attribute_model->isAllowTransaction = true;

						if ( isset($queryResponse) && !is_array($queryResponse) ) {
							$this->rollbackTransaction();
							return array(array('Error_Msg' => 'Ошибка при ' . ($AttributeSignValue['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' обслуживаемого отделения'));
						}
						else if ( !empty($queryResponse[0]['Error_Msg']) ) {
							$this->rollbackTransaction();
							return $queryResponse;
						}
					}
				}
			}

			if ( empty($data['ignoreLpuSectionAttributes']) ) {
				$error_msg = '';
				//условие refs #85379
				if(($data["Lpu_IsLab"] != 2) || ($data["Lpu_IsLab"] == 2 && $data["activeOMS"])){
					//Проверка сохраненных атрибутов
					$error_msg = $this->_checkLpuSectionAttributeSignValue(array(
						'LpuSection_id' => $mainResponse[0]['LpuSection_id'],
						'LpuSection_setDate' => $data['LpuSection_setDate'],
						'LpuSection_disDate' => $data['LpuSection_disDate']
					));
				}
				if (strlen($error_msg) > 0) {
					$this->rollbackTransaction();
					return $this->createError('', $error_msg);
				}
			}

			### Обновляем дату у всех атрибутов отделения, у которых дата закрытия больше чем дата закрытия отделения, либо дата закрытия не установлена ###

			if(!empty($data['LpuSection_disDate'])) {
				$LpuSection_disDate = DateTime::createFromFormat('Y-m-d H:i', $data['LpuSection_disDate'].' 00:00');
				$lpuSectionId = $data['LpuSection_id'];

				$sqlEndDate = "
					declare
						@Error_Code bigint,
						@Error_Message varchar(4000);
					set nocount on
					begin try
						update AttributeSignValue with (rowlock)
						set AttributeSignValue_endDate =:endDate
						from AttributeSignValue ASV
							inner join AttributeSign [AS] on [AS].AttributeSign_id = ASV.AttributeSign_id
						where
							AttributeSignValue_TablePKey =:tableKey 
							AND [AS].AttributeSign_TableName = 'dbo.LpuSection'
							AND ( AttributeSignValue_endDate > :endDate OR AttributeSignValue_endDate is null )
					end try
					begin catch
						set @Error_Code = error_number()
						set @Error_Message = error_message()
					end catch
					set nocount off
					Select @Error_Code as Error_Code, @Error_Message as Error_Msg
				";

				$queryParams = array(
					'endDate'  => $LpuSection_disDate,
					'tableKey' => $lpuSectionId
				);
				
				$result = $this->db->query($sqlEndDate, $queryParams)->result('array');

				if(!empty($result[0]['Error_Code'])) {

					$this->rollbackTransaction();
					return $this->createError('', 'Ошибка базы данных '. $result[0]['Error_Msg']);
				}
			}
		}
		
		if ($this->getRegionNick() == 'kz') {
			$LpuSectionFPIDLink_id = $this->getFirstResultFromQuery("select LpuSectionFPIDLink_id from r101.LpuSectionFPIDLink (nolock) where LpuSection_id = :LpuSection_id", array(
				'LpuSection_id' => $data['LpuSection_id']
			));
			$proc = $LpuSectionFPIDLink_id ? 'p_LpuSectionFPIDLink_upd' : 'p_LpuSectionFPIDLink_ins';
			$query = "
			declare
				@LpuSectionFPIDLink_id bigint = :LpuSectionFPIDLink_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec r101.{$proc}
				@LpuSectionFPIDLink_id = @LpuSectionFPIDLink_id output,
				@LpuSection_id = :LpuSection_id,
				@FPID = :FPID,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuSectionFPIDLink_id as LpuSectionFPIDLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
			$result = $this->queryResult($query, array(
				'LpuSectionFPIDLink_id' => $LpuSectionFPIDLink_id ? $LpuSectionFPIDLink_id : null,
				'LpuSection_id' => $data['LpuSection_id'],
				'FPID' => $data['FPID'],
				'pmUser_id' => $data['pmUser_id']
			));
		}

		$response = $this->saveOtherLpuSectionParams($data);

		if ( !is_array($response) || count($response) == 0 ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при сохранении дополнительных параметров отделения'));
		}
		else if ( !empty($response[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $response;
		}

		$this->commitTransaction();

		// Удаляем данные из кэша
		$this->load->library('swCache');
		$this->swcache->clear("LpuSectionList_".$data['Lpu_id']);

		return $mainResponse;
	}

	/**
	 * Сохранение доп. параметров
	 * Заглушка для регионов, в которых нет необходимости отдельно сохранять дополнительные параметры отделения
	 */
	function saveOtherLpuSectionParams($data) {
		return array(array('Error_Msg' => ''));
	}

	/**
     * Это Doc-блок
     */
	function SaveLpuRegion($data)
	{
		$this->beginTransaction();

		if (!empty($data['LpuRegion_id']) && !empty($data['LpuRegion_endDate']) && empty($data['ignorePersonCardCheck'])) {
			$RegionPersonCard = $this->getFirstRowFromQuery("
				SELECT top 1
					LpuAttachType_Name,
					convert(varchar(10), cast(Person_BirthDay as datetime), 104) as Person_BirthDay,
					convert(varchar(10), cast(PersonCard_begDate as datetime), 104) as PersonCard_begDate,
					ISNULL(Person_SurName, '') + ' ' + ISNULL(Person_FirName, '') + ' ' + ISNULL(Person_SecName, '') as Person_FIO
				FROM
					v_PersonCard with (nolock)
				WHERE
					LpuRegion_id = :LpuRegion_id
					and (PersonCard_endDate is null or PersonCard_endDate > :LpuRegion_endDate)
				",
				array('LpuRegion_id' => $data['LpuRegion_id'], 'LpuRegion_endDate' => $data['LpuRegion_endDate'])
			);

			if ( $RegionPersonCard ) {
            	$this->rollbackTransaction();
				return array(array('Error_Code' => '997','Error_Msg' => 'Закрывать можно участки, не имеющие прикреплённого населения. Участок содержит открытые записи о прикреплении : '.$RegionPersonCard['Person_FIO'].', '.$RegionPersonCard['Person_BirthDay'].' г.р., тип прикрепления - '.$RegionPersonCard['LpuAttachType_Name'].' , дата прикрепления - '.$RegionPersonCard['PersonCard_begDate']));
			}
		}

		//Проверка соответствия типа участка профилю отделения на участке
		if ( isset($data['checkRegType']) && $data['checkRegType'] == 'true' ) {
			switch($this->getRegionNick()){
				case 'perm':
					$reg_query = "
						select
							case when
								(LRT.LpuRegionType_SysNick = 'ter' and LSP.LpuSectionProfile_Code in ('1000', '1001', '1003', '1007', '1010', '1011', '97', '57'))
								or (LRT.LpuRegionType_SysNick = 'ped' and LSP.LpuSectionProfile_Code in ('0900', '0902', '0905', '0907', '1011', '68', '57'))
								or (LRT.LpuRegionType_SysNick = 'stom' and LSP.LpuSectionProfile_Code in ('1800', '1801', '1802', '1803', '1810', '1811', '1830', '85', '89', '86', '87', '171'))
								or (LRT.LpuRegionType_SysNick = 'gin' and LSP.LpuSectionProfile_Code in ('2500', '2509', '2510', '2514', '2517', '2518', '2519', '3', '136'))
								or (LRT.LpuRegionType_SysNick = 'vop' and LSP.LpuSectionProfile_Code in ('1000', '1001', '1003', '1007', '1010', '1011', '0900', '0902', '0905', '0907', '97', '68', '57')
								or LRT.LpuRegionType_SysNick not in ('ter', 'ped', 'stom', 'gin', 'vop'))
								then 1 else 2
							end as LpuSectionCheck
						from
							v_LpuSection LS (nolock)
							left join v_LpuSectionProfile LSP with (nolock) on LS.LpuSectionProfile_id = LSP.LpuSectionProfile_id
							left join v_LpuRegionType LRT with (nolock) on LRT.LpuRegionType_id = :LpuRegionType_id
						where
							LpuSection_id = :LpuSection_id
					";
					break;
			}

			if (!empty($reg_query)){
				$result = $this->queryResult($reg_query, array('LpuSection_id' => $data['LpuSection_id'], 'LpuRegionType_id' => $data['LpuRegionType_id']));

				if ( count($result)>0 && $result[0]['LpuSectionCheck'] == 2 ) {
					$this->rollbackTransaction();
					return array(array('Error_Code' => '994', 'Error_Msg' => 'Профиль указанного отделения участка не соответствует типу участка.'));
				}
			}
		}

		// Проверяем номер на уникальность
		// @task https://redmine.swan.perm.ru/issues/66328
		// Исключение для Астрахани и Казахстана
		// @task https://redmine.swan.perm.ru/issues/77134
		// @task https://redmine.swan.perm.ru/issues/81469
		if(!in_array($this->getRegionNick(), array('astra','kz'))){
			$query = "
				select top 1 LpuRegion_id
				from v_LpuRegion with (nolock)
				where Lpu_id = :Lpu_id
					and LpuRegion_id != ISNULL(:LpuRegion_id, 0)
					and LpuRegionType_id = :LpuRegionType_id
					and LpuRegion_Name = :LpuRegion_Name
					and LpuRegion_begDate <= ISNULL(cast(:LpuRegion_endDate as date), LpuRegion_begDate)
					and ISNULL(LpuRegion_endDate, cast(:LpuRegion_begDate as date)) >= :LpuRegion_begDate
			";
			$result = $this->db->query($query, array(
				'LpuRegion_id' => $data['LpuRegion_id'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuRegionType_id' => $data['LpuRegionType_id'],
				'LpuRegion_Name' => $data['LpuRegion_Name'],
				'LpuRegion_begDate' => $data['LpuRegion_begDate'],
				'LpuRegion_endDate' => (!empty($data['LpuRegion_endDate']) ? $data['LpuRegion_endDate'] : NULL)
			));

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return false;
			}

			$resp = $result->result('array');

			if ( is_array($resp) && count($resp) > 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Code' => '995', 'Error_Msg' => 'Участок с таким номером, типом и периодом действия уже существует в системе.'));
			}
		}

		$allowedRegion = false;
		$data['allowEmptyMedPersonalData'] = !empty($data['allowEmptyMedPersonalData'])?$data['allowEmptyMedPersonalData']:false;

		//проверка наличия врачей на участке
		if (!$data['allowEmptyMedPersonalData'] && empty($data['LpuRegionMedPersonalData']) && empty($data['LpuRegion_endDate'])){
			if (empty($data['LpuRegionType_SysNick'])) {
				$data['LpuRegionType_SysNick'] = $this->getFirstResultFromQuery("
					select top 1 LpuRegionType_SysNick from v_LpuRegionType with(nolock) where LpuRegionType_id = :LpuRegionType_id
				", $data);
			}
			switch($this->getRegionNick()){
				case 'perm':
					if (in_array($data['LpuRegionType_SysNick'], array('ter','ped','vop','comp','prip','feld','gin','stom'))){
						$allowedRegion = true;
					}
					break;
				case 'khak':
					if (in_array($data['LpuRegionType_SysNick'], array('ter','ped','gin','stom','vop'))){
						$allowedRegion = true;
						return false;
					}
					break;
				case 'buryatiya':
					if (in_array($data['LpuRegionType_SysNick'], array('ter','ped','gin','vop'))){
						$allowedRegion = true;
					}
					break;
				case 'ufa':
					if (in_array($data['LpuRegionType_SysNick'], array('ter','ped','gin','vop','feld'/*,'stom'*/))){
						$allowedRegion = true;
					}
					break;
				default:
					if (in_array($data['LpuRegionType_SysNick'], array('ter','ped','gin','vop'/*,'stom'*/))){
						$allowedRegion = true;
					}
					break;
			}

			//Код 995 для сообщений без возможности дальнейшего сохранения
			if ($allowedRegion) {
				$this->rollbackTransaction();
				return array(array('Error_Code' => '995', 'Error_Msg' => 'На участке должен быть хотя бы один врач.'));
			}
		}

		if (!isset($data['LpuRegion_id'])) {
			$proc = 'p_LpuRegion_ins';
		} else {
			$proc = 'p_LpuRegion_upd';
		}

		$query = "
		declare
			@LpuRegion_id bigint = :LpuRegion_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@LpuRegion_id = @LpuRegion_id output,
			@Lpu_id = :Lpu_id,
			@LpuRegionType_id = :LpuRegionType_id,
			@LpuSection_id = :LpuSection_id,
			@LpuRegion_Name = :LpuRegion_Name,
			@LpuRegion_tfoms = :LpuRegion_tfoms,
			@LpuRegion_Descr = :LpuRegion_Descr,
			@LpuRegion_begDate = :LpuRegion_begDate,
			@LpuRegion_endDate = :LpuRegion_endDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuRegion_id as LpuRegion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'LpuRegion_id' => $data['LpuRegion_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuRegionType_id' => $data['LpuRegionType_id'],
			'LpuRegion_Name' => $data['LpuRegion_Name'],
			'LpuRegion_tfoms' => !empty($data['LpuRegion_tfoms'])?$data['LpuRegion_tfoms']:null,
			'LpuRegion_Descr' => $data['LpuRegion_Descr'],
			'LpuRegion_begDate' => $data['LpuRegion_begDate'],
			'LpuRegion_endDate' => $data['LpuRegion_endDate'],
			'pmUser_id' => $data['pmUser_id']
		));

		if (!is_object($result)) {
			$this->rollbackTransaction();
			return false;
		}

		$resp = $result->result('array');

		// Обрабатываем список врачей на участке
		if ( !empty($data['LpuRegionMedPersonalData']) ) {
			$LpuRegionMedPersonalData = json_decode($data['LpuRegionMedPersonalData'], true);
			if ( is_array($LpuRegionMedPersonalData) ) {
				$countMainRec = 0;

				foreach ($LpuRegionMedPersonalData as $key => $value) {
					if (
						!empty($value['MedStaffRegion_isMain'])
						&& ($value['MedStaffRegion_isMain'] === true || $value['MedStaffRegion_isMain'] === 'true' || $value['MedStaffRegion_isMain'] === '2')
						&& (empty($value['status']) || $value['status'] != '3')
					) {
						$LpuRegionMedPersonalData[$key]['MedStaffRegion_isMain'] = 2;
						$countMainRec +=1;
					}
				}

				if ($countMainRec > 1 ){
					$this->rollbackTransaction();
					return array(array('Error_Code' => '995', 'Error_Msg' => 'На участке не может быть больше одного основного врача.'));
				}

				$checkDel = $this->checkMedStaffRegionDelAvailable($LpuRegionMedPersonalData, $data['Lpu_id'], $resp[0]['LpuRegion_id'], $data['LpuSection_id']);
				if (is_array($checkDel) && !empty($checkDel[0]['Error_Msg'])){
					$this->rollbackTransaction();
					return $checkDel;
				}

				// Проверяем наличие записей главного врача в на других участках
				// https://redmine.swan.perm.ru/issues/74912
				if ( $countMainRec > 0 && (empty($data['checkMainMPDoubles']) || $data['checkMainMPDoubles'] === true || $data['checkMainMPDoubles'] == "true") ) {
					$mainMedPersonalCheckData = $this->checkMainMedPersonal($LpuRegionMedPersonalData, $data['Lpu_id']);

					if ( !is_array($mainMedPersonalCheckData) || $mainMedPersonalCheckData['count'] == -1 ) {
						$this->rollbackTransaction();
						return array(array('Error_Code' => '998', 'Error_Msg' => 'Ошибка при проверке дублей главного врача участка.'));
					}
					else if ( $mainMedPersonalCheckData['count'] > 2 ) {
						$this->rollbackTransaction();
						return array(array('Error_Code' => '999', 'Error_Msg' => 'Сотрудник ' . $mainMedPersonalCheckData['fio'] . ' отмечен как основной врач еще на ' . $mainMedPersonalCheckData['count'] . ' участк' . (substr($mainMedPersonalCheckData['count'], -1) == '1' ? 'е' : 'ах') . '.'));
					}
				}

				for ( $j = 0; $j < count($LpuRegionMedPersonalData); $j++ ) {
					if ( !empty($LpuRegionMedPersonalData[$j]['status']) && $LpuRegionMedPersonalData[$j]['status'] == 1 ) {
						continue;
					} 

					if (
						($this->regionNick == 'perm' && !empty($LpuRegionMedPersonalData[$j]['MedStaffFact_id']))
						|| ($this->regionNick != 'perm' && !empty($LpuRegionMedPersonalData[$j]['MedPersonal_id']))
					) {
						$LpuRegionMedPersonal = array(
							'Lpu_id' => $data['Lpu_id'],
							'Server_id' => $data['Server_id'],
							'pmUser_id' => $data['pmUser_id'],
							'LpuSection_id' => $data['LpuSection_id'],
							'checkPost' => !empty($data['checkPost'])?$data['checkPost']:null,
							'checkStavka' => !empty($data['checkStavka'])?$data['checkStavka']:null,
							'checkLpuSection' => !empty($data['checkLpuSection'])?$data['checkLpuSection']:null,
							'LpuRegion_id' => $resp[0]['LpuRegion_id']
						);

						$LpuRegionMedPersonal['MedStaffFact_id'] = (!empty($LpuRegionMedPersonalData[$j]['MedStaffFact_id']) ? $LpuRegionMedPersonalData[$j]['MedStaffFact_id'] : null);
						$LpuRegionMedPersonal['MedPersonal_id'] = $LpuRegionMedPersonalData[$j]['MedPersonal_id'];
						$LpuRegionMedPersonal['MedStaffRegion_id'] = $LpuRegionMedPersonalData[$j]['MedStaffRegion_id'];
						$LpuRegionMedPersonal['MedStaffRegion_isMain'] = (!empty($LpuRegionMedPersonalData[$j]['MedStaffRegion_isMain']) ? $LpuRegionMedPersonalData[$j]['MedStaffRegion_isMain'] : null);
						$LpuRegionMedPersonal['MedStaffRegion_begDate'] = $LpuRegionMedPersonalData[$j]['MedStaffRegion_begDate'];
						$LpuRegionMedPersonal['MedStaffRegion_endDate'] = $LpuRegionMedPersonalData[$j]['MedStaffRegion_endDate'];
						$LpuRegionMedPersonal['status'] = !empty($LpuRegionMedPersonalData[$j]['status'])?$LpuRegionMedPersonalData[$j]['status']:0;

						switch ($LpuRegionMedPersonal['status']){
							case 0:
							case 2:
								$response = $this->saveMedStaffRegion($LpuRegionMedPersonal);
								break;
							case 3:
								$response = $this->deleteMedStaffRegion($LpuRegionMedPersonal);
								break;
						}

						if ( !$response && !is_array($response)) {
							$this->rollbackTransaction();
							$response['Error_Code'] = '2';
							$response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение палаты)';
							return $response;
						} else if (!empty($response[0]['Error_Msg'])){
							return $response;
						}
					}
				}
			}
		}

		// Проверка-сообщение на наличие открытого периода по фондодержанию (период фондодержания искать по типу участка, период работы участка должен попадать в период фондодержания).
		$query = "
			Select top 1 1
			from v_LpuPeriodFondHolder LpuPeriodFondHolder (nolock)
			where
				Lpu_id = :Lpu_id and
				LpuRegionType_id = :LpuRegionType_id and
				(LpuPeriodFondHolder_begDate <= :LpuRegion_endDate or :LpuRegion_endDate is null) and
				(LpuPeriodFondHolder_endDate >= :LpuRegion_begDate or LpuPeriodFondHolder_endDate is null)
		";
		$result = $this->queryResult($query, $data);
		if ( count($result)==0) {
			$resp[0]['Alert_Msg'] = 'Для данного типа участка нет открытого периода по фондодержанию.';
		}

		$this->commitTransaction();

		// Для Казахстана делаем сохранение в сервисе РПН
		/*if ($this->regionNick == 'kz' && (!isset($data['noTransferService']))) {
			$this->load->model("ServiceRPN_model");
			$this->ServiceRPN_model->setLpuRegion($data, $resp);
		}*/
		return $resp;
	}

	/**
	 * Проверяет возможность изменения (удаления) врачей на участке
	 */
	function checkMedStaffRegionDelAvailable($data, $Lpu_id, $LpuRegion_id, $LpuSection_id) {

		if (!is_array($data) || count($data) == 0){
			return array(array('Error_Code' => '996', 'Error_Msg' => 'На участке должен быть хотя бы один врач.'));
		}

		/**
		 * Проверяет отсутствие даты окончания работы, т.е. равенство искусственной максимальной величине
		 */
		function isEmptyDate($date){
			return $date === '2038-01-01';
		}

		/**
		 * Возвращает пересечение переданных периодов
		 */
		function dateLineIntersect($date){
			$count = 1;
			$ids = 1;
			$tmp_date = &$date;
			$res_array = array();
			$BEGDATE = 'MedStaffRegion_begDate';
			$ENDDATE = 'MedStaffRegion_endDate';

			foreach($date as &$kv){
				$kv['id'] = $ids;
				$ids +=1;
			}
			unset($kv);

			foreach ($date as $r){
				foreach ($date as $r2){

					//если это два разных пересекающихся участка - добавляем элемент в массив пересечений
					if ($r['id'] != $r2['id'] && !empty($r[$BEGDATE]) && !empty($r2[$BEGDATE]) &&  $r[$BEGDATE] <= $r2[$ENDDATE] && $r[$ENDDATE] >= $r2[$BEGDATE]){
						if ($r[$BEGDATE] <= $r2[$BEGDATE] && $r[$ENDDATE] <= $r2[$ENDDATE]){
							array_push($res_array, array('id' => '',$BEGDATE => $r2[$BEGDATE], $ENDDATE =>$r[$ENDDATE]));
						} else if ($r[$BEGDATE] >= $r2[$BEGDATE] && $r[$ENDDATE] >= $r2[$ENDDATE]){
							array_push($res_array, array('id' => '',$BEGDATE => $r[$BEGDATE], $ENDDATE =>$r2[$ENDDATE]));
						} else if ($r[$BEGDATE] != $r2[$BEGDATE] && $r[$ENDDATE] != $r2[$ENDDATE]){
							array_push($res_array, array('id' => '',$BEGDATE => $r2[$BEGDATE], $ENDDATE =>$r2[$ENDDATE]));
						}
						$count +=1;
					}
				}
			}

			if ($count > 1){
				//удаляем дубли и сново ищем пересечения
				$res_array = array_map("unserialize", array_unique( array_map("serialize", $res_array) ));
				return dateLineIntersect($res_array);
			} else {
				return $tmp_date;
			}
		}

		$existDelRecs = false;
		$BEGDATE = 'MedStaffRegion_begDate';
		$ENDDATE = 'MedStaffRegion_endDate';
		$ID = 'MedStaffRegion_id';

		//массив с записями на удаление
		$del_array = array();

		//массив с записями на обновление/добавление
		$upd_array = array();

		foreach ($data as &$row){

			//если период открыт то проставляем максимальную дату для удобства
			if (empty($row[$ENDDATE])){
				$row[$ENDDATE] = '2038-01-01';
			}
			$row[$BEGDATE] = date("Y-m-d", strtotime($row[$BEGDATE]));
			$row[$ENDDATE] = date("Y-m-d", strtotime($row[$ENDDATE]));
		}

		unset($row);

		foreach ($data as $row){
			if (!empty($row['status']) && $row['status'] === 3){
				$existDelRecs = true;
				array_push($del_array, $row);
			} else {
				array_push($upd_array, $row);
			}
		}

		//Если в изменяемых врачах есть удаляемые - то проверяем возможность их удаления
		if ($existDelRecs){
			if ( !empty($LpuSection_id) ) {
				$medStaffFactList = array();

				foreach($del_array as $drow){
					if ( !empty($drow['MedStaffFact_id']) && !in_array($drow['MedStaffFact_id'], array_keys($medStaffFactList)) ) {
						$medStaffFactList[$drow['MedStaffFact_id']] = 0;
					}
				}

				if ( count($medStaffFactList) > 0 ) {
					$query = "
						select MedStaffFact_id
						from v_MedStaffFact with (nolock)
						where MedStaffFact_id in (" . implode(",", array_keys($medStaffFactList)) . ")
							and LpuSection_id = :LpuSection_id
					";
					$result = $this->db->query($query, array(
						'LpuSection_id' => $LpuSection_id
					));

					if ( !is_object($result) ) {
						return array(array('Error_Code' => '990', 'Error_Msg' => 'Ошибка при проверке актуальности мест работы.'));
					}

					$resp = $result->result('array');

					if ( is_array($resp) && count($resp) > 0 ) {
						foreach ( $resp as $row ) {
							$medStaffFactList[$row['MedStaffFact_id']] = 1;
						}
					}
				}

				foreach($del_array as $key => $drow){
					if ( !empty($drow['MedStaffFact_id']) && in_array($drow['MedStaffFact_id'], array_keys($medStaffFactList)) && $medStaffFactList[$drow['MedStaffFact_id']] === 0 ) {
						unset($del_array[$key]);
					}
				}
			}

			//Если в удаляемых врачах есть врач с открытым концом периода, а в добавляемых/обновляемых - нет, то выводим ошибку
			//Дублируем проверку на клиенте, здесь эта ошибка всплыть не должна
			foreach($del_array as $drow){
				$existEndDate = false;
				if (empty($drow[$ENDDATE])){
					$existEndDate = true;
					foreach ($upd_array as $urow){
						if (empty($urow[$ENDDATE])){
							$existEndDate = false;
							break;
						}
					}
				}

				if ($existEndDate){
					return array(array('Error_Code' => '995', 'Error_Msg' => 'Нельзя удалять врача без даты окончания работы, если при этом на участке нет другого врача без даты окончания работы.'));
				}
			}

			//не должно быть прикреплённого населения на участке, в период когда на нём не работало ни одного врача
			//проверяем наличие участков на добавление/обновление, пересекающихся с удаляемым
			foreach($del_array as $dr){
				//обновляемые участки, пересекающиеся с удаляемым
				$susp_array = array();
				foreach ($upd_array as $ur){
					if ((isEmptyDate($dr[$ENDDATE]) && isEmptyDate($ur[$ENDDATE])) ||
						(!isEmptyDate($dr[$ENDDATE]) && isEmptyDate($ur[$ENDDATE]) && $ur[$BEGDATE] <= $dr[$ENDDATE] ) ||
						(!isEmptyDate($ur[$ENDDATE]) && isEmptyDate($ur[$ENDDATE]) && $dr[$BEGDATE] <= $ur[$ENDDATE]) ||
						($dr[$BEGDATE] <= $ur[$ENDDATE] && $dr[$ENDDATE] >= $ur[$BEGDATE])
					){
						array_push($susp_array, $ur);
					}
				}

				if (count($susp_array) > 0){

					//Для всех участков в $susp_array если даты начала/окончания выходят за границы удаляемого участка - приравниваем их к датам удаляемого участка
					foreach ($susp_array as &$r){
						if ($r[$BEGDATE] < $dr[$BEGDATE]){
							$r[$BEGDATE] = $dr[$BEGDATE];
						}

						if ($r[$ENDDATE] > $dr[$ENDDATE]){
							$r[$ENDDATE] = $dr[$ENDDATE];
						}
					}

					unset($r);

					//удаляем вложенные периоды, т.е. те, которые целиком попадают в другой
					$counter = 0;
					foreach ($susp_array as &$rec){
						foreach ($susp_array as $r_vloj){

							//Если период целиком лежит в другом - удаляем его
							if ($rec[$ID] != $r_vloj[$ID] && $r_vloj[$BEGDATE] <= $rec[$BEGDATE] && $r_vloj[$ENDDATE] >= $rec[$ENDDATE]){
								unset($susp_array[$counter]);
							}
						}
						$counter +=1;
					}
					unset($rec);

					foreach ($susp_array as &$rec){
						foreach ($susp_array as $r_vloj){
							//Если период пересекается с другим - корректируем даты так что бы они не пересекались, а соприкасались
							if ($rec[$ID] != $r_vloj[$ID] && $rec[$BEGDATE] <= $r_vloj[$ENDDATE] && $rec[$ENDDATE] >= $r_vloj[$BEGDATE]){
								if ($rec[$BEGDATE] > $r_vloj[$BEGDATE]){
									$rec[$BEGDATE] = $r_vloj[$ENDDATE];
								} else {
									$rec[$ENDDATE] = $r_vloj[$BEGDATE];
								}
							}
						}
					}
					unset($rec);

					$empty_dates_array = array();

					//идентификатор, что бы в дальнейшем отличать записи друг от друга
					$empt_id = 1;

					//создаём массив с датами каждого периода, на которые небыло врачей на участке
					foreach ($susp_array as $rec){
						if ($dr[$BEGDATE] == $rec[$BEGDATE]){
							array_push($empty_dates_array, array('id'=>$empt_id, $BEGDATE =>$rec[$ENDDATE], $ENDDATE =>$dr[$ENDDATE]));
						} else if ($dr[$ENDDATE] == $rec[$ENDDATE]){
							array_push($empty_dates_array, array('id'=>$empt_id, $BEGDATE =>$dr[$BEGDATE], $ENDDATE =>$rec[$BEGDATE]));
						} else {
							//период целиком лежит в периоде удаляемого, добавляем два участка, получающиеся от их пересечения
							array_push($empty_dates_array, array('id'=>$empt_id, $BEGDATE =>$dr[$BEGDATE], $ENDDATE =>$rec[$BEGDATE]));
							$empt_id += 1;
							array_push($empty_dates_array, array('id'=>$empt_id, $BEGDATE =>$rec[$ENDDATE], $ENDDATE =>$dr[$ENDDATE]));
						}
						$empt_id += 1;
					}

					//Получаем массив с периодами, когда на участке не работал ни один врач
					$intersect_empty_dates_array = dateLineIntersect($empty_dates_array);

					//если есть даты, на которые на участке небыло врачей - проверяем есть ли на этот период прикрепления
					if (is_array($intersect_empty_dates_array) && count($intersect_empty_dates_array) > 0){

						$checkAttach = $this->checkAttachOnDates(array('dates' => $intersect_empty_dates_array, 'Lpu_id' => $Lpu_id, 'LpuRegion_id' => $LpuRegion_id));
						if (is_array($checkAttach)){
							return $checkAttach;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Сохраняет операцию с лицензией МО
	 */
	function checkAttachOnDates($data) {
		if (empty($data) || !is_array($data) || count($data) == 0 || empty($data['dates']) || !is_array($data['dates']) || empty($data['Lpu_id']) || empty($data['LpuRegion_id']) ){
			return array(array('Error_Code' => '995', 'Error_Msg' => 'Переданы неправильные параметры для проверки.'));
		}

		$queryParams = array(
			'LpuRegion_id' => $data['LpuRegion_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$dateFilter = '';

		foreach ($data['dates'] as $row){
			if (!empty($dateFilter)){
				$dateFilter .= " or ";
			}
			$dateFilter .= " (PersonCard_begDate >= '".$row['MedStaffRegion_begDate']."' and (PersonCard_endDate is null or PersonCard_endDate <= '".$row['MedStaffRegion_endDate']."')) ";
		}

		$query = "
			select top 1
				Person_id
			from
				v_PersonCard with (nolock)
			where
				LpuRegion_id = :LpuRegion_id
				and Lpu_id = :Lpu_id
				and ({$dateFilter})
		";

		//echo getDebugSQL($query, $queryParams);die;
		$resp = $this->queryResult($query, $queryParams);
		if (!empty($resp[0]['Person_id'])){
			return array(array('Error_Code' => '995', 'Error_Msg' => 'На период работы удаляемого врача найдены прикрепленные к участку пациенты. Удаление невозможно.'));
		}
		return true;
	}
	/**
	 * Сохраняет  операцию с лицензией МО
	 */
	function saveMedStaffRegion($data) {

		if (isset($data['MedStaffRegion_id']) && $data['MedStaffRegion_id'] > 0) {
			$procedure_action = "p_MedStaffRegion_upd";
		} else {
			$data['MedStaffRegion_id'] = 0;
			$procedure_action = "p_MedStaffRegion_ins";
		}

		$queryParams = array(
			'MedStaffRegion_id' => $data['MedStaffRegion_id'],
			'MedStaffRegion_isMain' => (!empty($data['MedStaffRegion_isMain']) && in_array($data['MedStaffRegion_isMain'], array('true', 2)))?2:1,
			'MedStaffFact_id' => $data['MedStaffFact_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'MedStaffRegion_begDate' => !empty($data['MedStaffRegion_begDate']) ? date("Y-m-d", strtotime($data['MedStaffRegion_begDate'])) : null,
			'MedStaffRegion_endDate' => !empty($data['MedStaffRegion_endDate']) ? date("Y-m-d", strtotime($data['MedStaffRegion_endDate'])) : null,
			'Lpu_id' => $data['Lpu_id'],
			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id'],
			'LpuRegion_id' => $data['LpuRegion_id']
		);

		/*if ($this->getRegionNick() == 'perm') {

			$select = "";
			if (!empty($data['checkStavka']) && $data['checkStavka'] == 'true'){

				$select .= "
					case
						when ISNULL(MSF.MedStaffFact_Stavka, 0) <> 0 then 1 else 2
					end as checkStavka,
				";
			}

			if (!empty($data['checkLpuSection']) && $data['checkLpuSection'] == 'true'){
				$select .= "
					case
						when MSF.LpuSection_id = :LpuSection_id then 1 else 2
					end as checkLpuSection,
				";
			}

			if (!empty($data['checkPost']) && $data['checkPost'] == 'true'){
				$select .= "
					case
						when
							(LRT.LpuRegionType_SysNick = 'ter' and P.code in (73, 74, 76, 111, 117))
							or (LRT.LpuRegionType_SysNick = 'ped' and P.code in (46, 47, 111, 117))
							or (LRT.LpuRegionType_SysNick = 'gin' and P.code in (12, 13))
							or (LRT.LpuRegionType_SysNick = 'vop' and P.code in (40))
							or (LRT.LpuRegionType_SysNick = 'stom' and P.code in (191, 192, 194)) then 1 else 2
					end as checkPost,
				";
			}

			// Проверки врача
			// Надо: оставить проверку только по врачам у кого не указана дата закрытия в форме "Участок: Редактирование"
			// @task https://redmine.swan.perm.ru/issues/66024
			if ( empty($queryParams['MedStaffRegion_endDate']) ) {
				$query = "select
						{$select}
						MSF.Person_Fio
					from
						v_MedStaffFact MSF (nolock)
						left join persis.Post P with (nolock) on P.id = MSF.Post_id
						left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = :LpuRegion_id
						left join v_LpuRegionType LRT with (nolock) on LR.LpuRegionType_id = LRT.LpuRegionType_id
					where
						MSF.MedPersonal_id = :MedPersonal_id
				";

				//echo getDebugSQL($query, $queryParams);die;
				$result = $this->queryResult($query, $queryParams);

				$noErrors = false;

				foreach($result as $key => $value){
					if (
						(empty($value['checkStavka']) || $value['checkStavka'] == 1)
						&& (empty($value['checkLpuSection']) || $value['checkLpuSection'] == 1)
						&& (empty($value['checkPost']) || $value['checkPost'] == 1)
					) {
						$noErrors = true;
					}
					if (!empty($value['checkStavka']) && $value['checkStavka'] == 2){
						$answer = array('Error_Code' => '991', 'Error_Msg' => 'Место работы врача имеет нулевую ставку.');
					}

					if (!empty($value['checkLpuSection']) && $value['checkLpuSection'] == 2){
						$answer = array('Error_Code' => '992', 'Error_Msg' => 'Отделение места работы врача не соответствует отделению, указанному на  участке.');
					}

					if (!empty($value['checkPost']) && $value['checkPost'] == 2){
						$answer = array('Error_Code' => '993', 'Error_Msg' => 'Должность места работы врача не соответствует типу участка.');
					}
				}

				if (!$noErrors){
					return array($answer);
				}
			}
		}*/

		$query = "
			declare
				@MedStaffRegion_id bigint = :MedStaffRegion_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec {$procedure_action}
				@MedStaffRegion_id = @MedStaffRegion_id output,
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@LpuRegion_id = :LpuRegion_id,				
				@MedStaffRegion_isMain = :MedStaffRegion_isMain,
				@MedStaffFact_id = :MedStaffFact_id,
				@MedPersonal_id = :MedPersonal_id,
				@MedStaffRegion_begDate = :MedStaffRegion_begDate,
				@MedStaffRegion_endDate = :MedStaffRegion_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @MedStaffRegion_id as MedStaffRegion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		//echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Это Doc-блок
     */
	function SaveUslugaSection($data)
	{
		// предварительно надо проверить, может такая услуга на этом отделении уже есть
		if (!$this->checkUslugaSection($data))
		{
			return array(array('UslugaSection_id'=>null, 'Error_Code'=>1,'Error_Msg' => 'Сохранение невозможно, поскольку выбранная услуга уже заведена на этом отделении!<br/>Для ввода или редактирования тарифа найдите ранее сохраненную услугу и измените ее.'));
		}

		if (!isset($data['UslugaSection_id']))
		{
			$proc = 'p_UslugaSection_ins';
		}
		else
		{
			$proc = 'p_UslugaSection_upd';
		}

		$query = "
		declare
			@UslugaSection_id bigint = :UslugaSection_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@UslugaSection_id = @UslugaSection_id output,
			@LpuSection_id = :LpuSection_id,
			@Usluga_id = :Usluga_id,
			@UslugaSection_Code = :UslugaSection_Code,
			@UslugaPrice_ue = :UslugaPrice_ue,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @UslugaSection_id as UslugaSection_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'UslugaSection_id' => $data['UslugaSection_id'],
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Usluga_id' => $data['Usluga_id'],
			'UslugaSection_Code' => $data['Usluga_Code'],
			'UslugaPrice_ue' => $data['UslugaPrice_ue'],
			'pmUser_id' => $data['pmUser_id']
		));
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
     * Это Doc-блок
     */
	function SaveUslugaSectionTariff($data)
	{
		$this->load->helper('Date');
		if (!isset($data['UslugaSectionTariff_id']))
		{
			$proc = 'p_UslugaSectionTariff_ins';
		}
		else
		{
			$proc = 'p_UslugaSectionTariff_upd';
		}

		$query = "
		declare
			@UslugaSectionTariff_id bigint = :UslugaSectionTariff_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@UslugaSectionTariff_id = @UslugaSectionTariff_id output,
			@UslugaSection_id = :UslugaSection_id,
			@UslugaSectionTariff_Tariff = :UslugaSectionTariff_Tariff,
			@UslugaSectionTariff_begDate = :UslugaSectionTariff_begDate,
			@UslugaSectionTariff_endDate = :UslugaSectionTariff_endDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @UslugaSectionTariff_id as UslugaSectionTariff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'UslugaSectionTariff_id' => $data['UslugaSectionTariff_id'],
			'Server_id' => $data['Server_id'],
			'UslugaSection_id' => $data['UslugaSection_id'],
			'UslugaSectionTariff_Tariff' => $data['UslugaSectionTariff_Tariff'],
			'UslugaSectionTariff_begDate' => $data['UslugaSectionTariff_begDate'],
			'UslugaSectionTariff_endDate' => $data['UslugaSectionTariff_endDate'],
			'pmUser_id' => $data['pmUser_id']
		));
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
     * Это Doc-блок
     */
	function SaveUslugaComplexTariff($data)
	{
		$this->load->helper('Date');
		if (!isset($data['UslugaComplexTariff_id']))
		{
			$proc = 'p_UslugaComplexTariff_ins';
		}
		else
		{
			$proc = 'p_UslugaComplexTariff_upd';
		}

		$query = "
		declare
			@UslugaComplexTariff_id bigint = :UslugaComplexTariff_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@UslugaComplexTariff_id = @UslugaComplexTariff_id output,
			@UslugaComplex_id = :UslugaComplex_id,
			@UslugaComplexTariff_Tariff = :UslugaComplexTariff_Tariff,
			@UslugaComplexTariff_begDate = :UslugaComplexTariff_begDate,
			@UslugaComplexTariff_endDate = :UslugaComplexTariff_endDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @UslugaComplexTariff_id as UslugaComplexTariff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'UslugaComplexTariff_id' => $data['UslugaComplexTariff_id'],
			'Server_id' => $data['Server_id'],
			'UslugaComplex_id' => $data['UslugaComplex_id'],
			'UslugaComplexTariff_Tariff' => $data['UslugaComplexTariff_Tariff'],
			'UslugaComplexTariff_begDate' => $data['UslugaComplexTariff_begDate'],
			'UslugaComplexTariff_endDate' => $data['UslugaComplexTariff_endDate'],
			'pmUser_id' => $data['pmUser_id']
		));
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
     * Это Doc-блок
     */
	/*function SaveMedStaffRegion($data)
	{

		if (!isset($data['MedStaffRegion_id']))
		{
			$proc = 'p_MedStaffRegion_ins';
		}
		else
		{
			$proc = 'p_MedStaffRegion_upd';
		}
		$query = "
		declare
			@MedStaffRegion_id bigint = :MedStaffRegion_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@MedStaffRegion_id = @MedStaffRegion_id output,
			@Lpu_id = :Lpu_id,
			@MedPersonal_id = :MedPersonal_id,
			@LpuRegion_id = :LpuRegion_id,
			@MedStaffRegion_begDate = :MedStaffRegion_begDate,
			@MedStaffRegion_endDate = :MedStaffRegion_endDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @MedStaffRegion_id as MedStaffRegion_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($query, array(
			'MedStaffRegion_id' => $data['MedStaffRegion_id'],
			'Server_id' => $data['Server_id'],
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['MedPersonal_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'MedStaffRegion_begDate' => $data['MedStaffRegion_begDate'],
			'MedStaffRegion_endDate' => $data['MedStaffRegion_endDate'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}*/
    /**
     * Это Doc-блок
     */
	function SaveLpuSectionTariff($data)
	{
		$this->load->helper('Date');
		if (empty($data['LpuSectionTariff_id']))
		{
			$proc = 'p_LpuSectionTariff_ins';
		}
		else
		{
			$proc = 'p_LpuSectionTariff_upd';
		}

		$query = "
		declare
			@LpuSectionTariff_id bigint = :LpuSectionTariff_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@LpuSectionTariff_id = @LpuSectionTariff_id output,
			@LpuSection_id = :LpuSection_id,
			@LpuSectionTariff_Code = Null,
			@TariffClass_id = :TariffClass_id,
			@LpuSectionTariff_Tariff = :LpuSectionTariff_Tariff,
			@LpuSectionTariff_TotalFactor = :LpuSectionTariff_TotalFactor,
			@LpuSectionTariff_setDate = :LpuSectionTariff_setDate,
			@LpuSectionTariff_disDate = :LpuSectionTariff_disDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionTariff_id as LpuSectionTariff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'LpuSectionTariff_id' => $data['LpuSectionTariff_id'],
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'TariffClass_id' => $data['TariffClass_id'],
			'LpuSectionTariff_Tariff' => $data['LpuSectionTariff_Tariff'],
			'LpuSectionTariff_TotalFactor' => $data['LpuSectionTariff_TotalFactor'],
			'LpuSectionTariff_setDate' => $data['LpuSectionTariff_setDate'],
			'LpuSectionTariff_disDate' => $data['LpuSectionTariff_disDate'],
			'pmUser_id' => $data['pmUser_id']
		));

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
     * Это Doc-блок
     */
	function SaveLpuSectionShift($data)
	{
		$checkLpuSectionShiftFailure = $this->checkLpuSectionShift($data);

		if (!$checkLpuSectionShiftFailure) {

			$this->load->helper('Date');

			if (!isset($data['LpuSectionShift_id'])) {
				$proc = 'p_LpuSectionShift_ins';
			} else {
				$proc = 'p_LpuSectionShift_upd';
			}

			$query = "
		declare
			@LpuSectionShift_id bigint = :LpuSectionShift_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@LpuSectionShift_id = @LpuSectionShift_id output,
			@LpuSection_id = :LpuSection_id,
			@LpuSectionShift_Code = Null,
			@LpuSectionShift_Count = :LpuSectionShift_Count,
			@LpuSectionShift_setDate = :LpuSectionShift_setDate,
			@LpuSectionShift_disDate = :LpuSectionShift_disDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionShift_id as LpuSectionShift_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

			$result = $this->db->query($query, array(
				'LpuSectionShift_id' => $data['LpuSectionShift_id'],
				'Server_id' => $data['Server_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSectionShift_Count' => $data['LpuSectionShift_Count'],
				'LpuSectionShift_setDate' => $data['LpuSectionShift_setDate'],
				'LpuSectionShift_disDate' => $data['LpuSectionShift_disDate'],
				'pmUser_id' => $data['pmUser_id']
			));

			if (is_object($result))
				return $result->result('array');
			else {
				$response['Error_Msg'] = toUTF('Ошибка запроса к БД');
				return array($response);
			}

		} else {
			$response['Error_Msg'] = toUTF($checkLpuSectionShiftFailure['Error_Msg']);
			return array($response);
		}
	}
    /**
     * Сохранение палаты в структуре МО
     */
	function SaveLpuSectionBedState($data)
	{
        $this->beginTransaction();

		if ( !empty($data['source']) && $data['source'] == 'API' ) {
			// проверяем наличие переданных значений во внешних таблицах
			$checkResult = $this->checkForeignKeyValues('LpuSectionBedState', 'dbo', $data);

			if ( $checkResult !== true ) {
				return array(array('Error_Msg' => $checkResult));
			}
		}

		$this->load->helper('Date');

		$filter = "";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0)) {
			$filter .= " and Server_id=".$data['Server_id'];
		} elseif ($data['session']['server_id']>0) {
			$filter .= " and Server_id = ".$data['session']['server_id'];
		}
		// Проверка на пересечение
		if(!empty($data['LpuSectionBedProfileLink_id'])){
			$query = "
				Select top 1
					LSBS.LpuSectionBedState_id
					,LSBP.LpuSectionBedProfile_Name
					,convert(varchar,cast(LSBS.LpuSectionBedState_begDate as datetime),104) as LpuSectionBedState_begDate
					,convert(varchar,cast(LSBS.LpuSectionBedState_endDate as datetime),104) as LpuSectionBedState_endDate
				from
					LpuSectionBedState LSBS with (nolock)
					left join v_LpuSectionBedProfile LSBP with(nolock) on LSBS.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
				where
					(ISNULL(LpuSectionProfile_id,0) = ISNULL(:LpuSectionProfile_id,0))
					and LpuSectionBedProfileLink_fedid = :LpuSectionBedProfileLink_fedid
					and ((LpuSectionBedState_begDate <= :LpuSectionBedState_endDate or :LpuSectionBedState_endDate is null) and (LpuSectionBedState_endDate >= :LpuSectionBedState_begDate or LpuSectionBedState_endDate is null))
					and LpuSection_id = :LpuSection_id
					and LpuSectionBedState_id != IsNull(:LpuSectionBedState_id, 0)
					and (LpuSectionBedState_deleted is null or LpuSectionBedState_deleted != 2)
			";
			/*
			// Проверка на пересечение
			$query = "
				Select
					COUNT(*) as rec
				from
					LpuSectionBedState with (nolock)
				where
					(ISNULL(LpuSectionProfile_id,0) = ISNULL(:LpuSectionProfile_id,0))
					and (ISNULL(LpuSectionBedProfile_id,0) = ISNULL(:LpuSectionBedProfile_id,0))
					and ((LpuSectionBedState_begDate <= :LpuSectionBedState_endDate or :LpuSectionBedState_endDate is null) and (LpuSectionBedState_endDate >= :LpuSectionBedState_begDate or LpuSectionBedState_endDate is null))
					and LpuSection_id = :LpuSection_id
					and LpuSectionBedState_id != IsNull(:LpuSectionBedState_id, 0)
			";
			 */
			/*
			echo getDebugSql($query, array(
				'LpuSectionBedState_id' => $data['LpuSectionBedState_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSectionBedState_begDate' => $data['LpuSectionBedState_begDate'],
				'LpuSectionBedState_endDate' => $data['LpuSectionBedState_endDate']
			));
			exit;
			*/
			$result = $this->db->query($query, array(
				'LpuSectionBedState_id' => (!empty($data['LpuSectionBedState_id']) ? $data['LpuSectionBedState_id'] : null),
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSectionBedState_begDate' => $data['LpuSectionBedState_begDate'],
				'LpuSectionBedState_endDate' => $data['LpuSectionBedState_endDate'],
				'LpuSectionBedProfileLink_fedid' => $data['LpuSectionBedProfileLink_id']
			));

			$response = $result->result('array');

			if ( !is_array($response) ) {
				$this->rollbackTransaction();
				return  array(0 => array('Error_Msg' => 'Произошла ошибка при выполнении проверки на пересечение профилей коек.'));
			}else if (count($response) > 0) {
				$this->rollbackTransaction();
				$endDate = ($response[0]['LpuSectionBedState_endDate']) ? " по ".$response[0]['LpuSectionBedState_endDate'] : "";

				if ( !empty($data['source']) && $data['source'] == 'API' ) {
					$errorMsg = "В структуре отделения уже внесена информация по выбранному профилю койки: " . $response[0]['LpuSectionBedProfile_Name'] . ", период действия: с " . $response[0]['LpuSectionBedState_begDate'] . $endDate . ". Необходимо изменить профиль койки или период действия.";
				}
				else {
					$errorMsg = "В структуре отделения уже внесена информация по выбранному профилю койки: <b>".$response[0]['LpuSectionBedProfile_Name']."</b>, период действия: с ".$response[0]['LpuSectionBedState_begDate'].$endDate." .<br>".mb_strtoupper("Необходимо изменить профиль койки или период действия").".";
				}

				return array(0 => array('Error_Msg' => $errorMsg));
			}
		}

		if (!isset($data['LpuSectionBedState_id'])) {
			$proc = 'p_LpuSectionBedState_ins';
		}
		else {
			$proc = 'p_LpuSectionBedState_upd';

			if ( !empty($data['source']) && $data['source'] == 'API' ) {
				$data['LpuSection_id'] = $this->getFirstResultFromQuery("select top 1 LpuSection_id from v_LpuSectionBedState with (nolock) where LpuSectionBedState_id = :LpuSectionBedState_id", $data, true);

				if ( $data['LpuSection_id'] === false || empty($data['LpuSection_id']) ) {
					return array(array('Error_Msg' => 'Не удалось определить идентификатор отделения'));
				}
			}
		}

		$query = "
			declare
				@LpuSectionBedState_id bigint = :LpuSectionBedState_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec {$proc}
				@Server_id = :Server_id,
				@LpuSectionBedState_id = @LpuSectionBedState_id output,
				@LpuSection_id = :LpuSection_id,
				@LpuSectionBedState_Plan = :LpuSectionBedState_Plan,
				@LpuSectionBedState_Repair = :LpuSectionBedState_Repair,
				@LpuSectionBedState_Fact = :LpuSectionBedState_Fact,
				@LpuSectionBedState_ProfileName = :LpuSectionBedState_ProfileName,
				@LpuSectionBedState_begDate = :LpuSectionBedState_begDate,
				@LpuSectionBedState_endDate = :LpuSectionBedState_endDate,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSectionBedProfile_id = :LpuSectionBedProfile_id,
				@LpuSectionBedState_CountOms = :LpuSectionBedState_CountOms,
				@LpuSectionBedState_MalePlan = :LpuSectionBedState_MalePlan,
				@LpuSectionBedState_MaleFact = :LpuSectionBedState_MaleFact,
				@LpuSectionBedState_FemalePlan = :LpuSectionBedState_FemalePlan,
				@LpuSectionBedState_FemaleFact = :LpuSectionBedState_FemaleFact,
				@LpuSectionBedProfileLink_fedid = :LpuSectionBedProfileLink_fedid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuSectionBedState_id as LpuSectionBedState_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$params = array(
			'LpuSectionBedState_id' => (!empty($data['LpuSectionBedState_id']) ? $data['LpuSectionBedState_id'] : null),
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			//'LpuSectionBedState_Name' => $data['LpuSectionBedState_Name'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'LpuSectionBedProfile_id' => $data['LpuSectionBedProfile_id'],
			'LpuSectionBedState_Plan' => $data['LpuSectionBedState_Plan'],
			'LpuSectionBedState_Repair' => $data['LpuSectionBedState_Repair'],
			'LpuSectionBedState_ProfileName' => $data['LpuSectionBedState_ProfileName'],
			'LpuSectionBedState_Fact' => $data['LpuSectionBedState_Fact'],
			'LpuSectionBedState_CountOms' => $data['LpuSectionBedState_CountOms'],
			'LpuSectionBedState_begDate' => $data['LpuSectionBedState_begDate'],
			'LpuSectionBedState_endDate' => $data['LpuSectionBedState_endDate'],
			'LpuSectionBedState_MalePlan' => $data['LpuSectionBedState_MalePlan'],
			'LpuSectionBedState_MaleFact' => $data['LpuSectionBedState_MaleFact'],
			'LpuSectionBedState_FemalePlan' => $data['LpuSectionBedState_FemalePlan'],
			'LpuSectionBedState_FemaleFact' => $data['LpuSectionBedState_FemaleFact'],
			'LpuSectionBedProfileLink_fedid' => (!empty($data['LpuSectionBedProfileLink_id']) ? $data['LpuSectionBedProfileLink_id'] : null),
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $params);die();
		$result = $this->db->query($query, $params);

		/*if (is_object($result))
		{
			return $result->result('array');
		}
		else
		{
			return false;
		}*/

        //$result = $this->db->query($query, $data);

        if ( !is_object($result) ) {
            $this->rollbackTransaction();
            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение койки по профилю)';
            return $response;
        }

        $queryResponse = $result->result('array');

        if ( !is_array($queryResponse) ) {
            $this->rollbackTransaction();
            $response['Error_Msg'] = 'Ошибка при сохранение койки по профилю';
            return $response;
        }
        else if ( !empty($queryResponse[0]['Error_Msg']) ) {
            $this->rollbackTransaction();
            return $queryResponse;
        }

        $response = $queryResponse[0];

        // Обрабатываем список операций над койками
        if ( !empty($data['DBedOperationData']) ) {
            //var_dump($data['DBedOperationData']);
            $DBedOperationData = json_decode($data['DBedOperationData'], true);
            //var_dump($DBedOperationData);
            if ( is_array($DBedOperationData) ) {
                //var_dump($DBedOperationData);
                $isDBedOperationRepeat[] = array();
                for ( $i = 0; $i < count($DBedOperationData); $i++ ) {
                    $DBedOperation = array(
                        'pmUser_id' => $data['pmUser_id']
                        ,'LpuSectionBedState_id' => $response['LpuSectionBedState_id']
                    );

                    if ( empty($DBedOperationData[$i]['LpuSectionBedStateOper_id']) || !is_numeric($DBedOperationData[$i]['LpuSectionBedStateOper_id']) ) {
                        continue;
                    }
                    //var_dump('1');

                    if ( empty($DBedOperationData[$i]['DBedOperation_id']) || !is_numeric($DBedOperationData[$i]['DBedOperation_id']) ) {
                        continue;
                            /*$this->rollbackTransaction();
                            $response['Error_Msg'] = 'Не указано наименование операции.';
                            return array($response);*/
                    }
                    //var_dump('2');

                    if ( empty($DBedOperationData[$i]['LpuSectionBedStateOper_OperDT']) ) {
                        continue;
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Не указана дата операции.';
                            return array($response);
                    }
                    //var_dump('3');

                    $DBedOperation['LpuSectionBedStateOper_id'] = $DBedOperationData[$i]['LpuSectionBedStateOper_id'];
                    $DBedOperation['DBedOperation_id'] = $DBedOperationData[$i]['DBedOperation_id'];
                    $DBedOperation['LpuSectionBedStateOper_OperDT'] = $DBedOperationData[$i]['LpuSectionBedStateOper_OperDT'];
                    //var_dump($DBedOperation);
                    //var_dump('4');
                    $queryResponse = $this->saveDBedOperation($DBedOperation);
                    //var_dump($queryResponse);
                    //var_dump('5');

                    if ( !is_array($queryResponse) ) {
                        $this->rollbackTransaction();
                        $response['Error_Msg'] = 'Ошибка при ' . ($DBedOperationData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' объекта комфортности';
                        return array($response);
                    } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                        $this->rollbackTransaction();
                        return $queryResponse[0];
                    }

                    //У одной койки не могут быть однотипные операции с одинаковыми датами
                    /*                  for ( $j = 0; $j < count($isDBedOperationRepeat); $j++ ) {
                        if ($DBedOperationData[$i]['DBedOperation_id'] == $isDBedOperationRepeat[$i][$j][$k] && $DBedOperationData[$i]['LpuSectionBedStateOper_OperDT'] == $isDBedOperationRepeat[$i]['LpuSectionBedStateOper_OperDT']) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Нельзя сохранить объекты комфортности с одинаковым наименованием.';
                            return array($response);
                        }
                    }

                    array_push($isDBedOperationRepeat[$i]['DBedOperation_id'], $DBedOperationData[$i]['DBedOperation_id']);
                    array_push($isDBedOperationRepeat[$i]['LpuSectionBedStateOper_OperDT'], $DBedOperationData[$i]['LpuSectionBedStateOper_OperDT']);*/

                    //var_dump($isDBedOperationRepeat);
                }
                //var_dump($isDBedOperationRepeat);
            }
        }

        $this->commitTransaction();

        return array($response);

    }

    /**
     * Удаление в отделении МО коек по профилю
     */
	public function deleteLpuSectionBedState($data) {
		$sp = $this->getLpuSectionBedState($data);

		if ( $sp === false || !is_array($sp) || count($sp) == 0 ) {
			return array(array(
				'Error_Code' => 6,
				'Error_Msg' => 'Ошибка при получении данных'
			));
		}
		if ( !empty($sp[0]['Lpu_id']) && $data['Lpu_id'] != $sp[0]['Lpu_id'] ) {
			return array(array(
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		return $this->queryResult("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec dbo.p_LpuSectionBedState_del
				@LpuSectionBedState_id = :LpuSectionBedState_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Message as Error_Msg;
        ", $data);
	}

	/**
	 * Получение списка коек коечного фонда отделения МО
	 */
	public function getLpuSectionBedStateListBySectionForAPI($data) {
		return $this->queryResult("
			select
				LpuSectionBedState_id,
				LpuSectionBedProfile_id,
				LpuSectionBedState_ProfileName,
				LpuSectionBedState_Fact,
				LpuSectionBedState_Plan,
				LpuSectionBedState_Repair,
				LpuSectionBedState_CountOms,
				LpuSectionBedState_MalePlan,
				LpuSectionBedState_MaleFact,
				LpuSectionBedState_FemalePlan,
				LpuSectionBedState_FemaleFact,
				convert(varchar(10), LpuSectionBedState_begDate, 120) as LpuSectionBedState_begDate,
				convert(varchar(10), LpuSectionBedState_endDate, 120) as LpuSectionBedState_endDate
			from dbo.v_LpuSectionBedState with (nolock)
			where LpuSection_id = :LpuSection_id
		", $data);
	}

    /**
     * Это Doc-блок
     */
	function SaveLpuSectionFinans($data) {
		if ( empty($data['LpuSectionFinans_id']) ) {
			$proc = 'p_LpuSectionFinans_ins';
		}
		else {
			$proc = 'p_LpuSectionFinans_upd';
		}

		$query = "
			declare
				@Res bigint = :LpuSectionFinans_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';

			exec {$proc}
				@Server_id = :Server_id,
				@LpuSectionFinans_id = @Res output,
				@LpuSection_id = :LpuSection_id,
				@PayType_id = :PayType_id,
				@LpuSectionFinans_IsMRC = :LpuSectionFinans_IsMRC,
				@LpuSectionFinans_IsQuoteOff = :LpuSectionFinans_IsQuoteOff,
				@LpuSectionFinans_begDate = :LpuSectionFinans_begDate,
				@LpuSectionFinans_endDate = :LpuSectionFinans_endDate,
				@LpuSectionFinans_Plan = :LpuSectionFinans_Plan,
				@LpuSectionFinans_PlanHosp = :LpuSectionFinans_PlanHosp,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Res as LpuSectionFinans_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'LpuSectionFinans_id' => $data['LpuSectionFinans_id'],
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'PayType_id' => $data['PayType_id'],
			'LpuSectionFinans_IsMRC' => $data['LpuSectionFinans_IsMRC'],
			'LpuSectionFinans_IsQuoteOff' => $data['LpuSectionFinans_IsQuoteOff'],
			'LpuSectionFinans_begDate' => $data['LpuSectionFinans_begDate'],
			'LpuSectionFinans_endDate' => $data['LpuSectionFinans_endDate'],
			'LpuSectionFinans_Plan' => $data['LpuSectionFinans_Plan'],
			'LpuSectionFinans_PlanHosp' => $data['LpuSectionFinans_PlanHosp'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object($result)) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

    /**
     * Это Doc-блок
     */
	function SaveLpuSectionLicence($data)
	{
		$this->load->helper('Date');
		if (!isset($data['LpuSectionLicence_id']))
		{
			$proc = 'p_LpuSectionLicence_ins';
		}
		else
		{
			$proc = 'p_LpuSectionLicence_upd';
		}

		$query = "
		declare
			@LpuSectionLicence_id bigint = :LpuSectionLicence_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@LpuSectionLicence_id = @LpuSectionLicence_id output,
			@LpuSection_id = :LpuSection_id,
			@LpuSectionLicence_Num = :LpuSectionLicence_Num,
			@LpuSectionLicence_begDate = :LpuSectionLicence_begDate,
			@LpuSectionLicence_endDate = :LpuSectionLicence_endDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionLicence_id as LpuSectionLicence_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'LpuSectionLicence_id' => $data['LpuSectionLicence_id'],
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionLicence_Num' => $data['LpuSectionLicence_Num'],
			'LpuSectionLicence_begDate' => $data['LpuSectionLicence_begDate'],
			'LpuSectionLicence_endDate' => $data['LpuSectionLicence_endDate'],
			'pmUser_id' => $data['pmUser_id']
		));

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
     * Это Doc-блок
     */
	function SaveLpuSectionTariffMes($data)
	{
		$this->load->helper('Date');
		if (!isset($data['LpuSectionTariffMes_id'])) {
			$proc = 'p_LpuSectionTariffMes_ins';
		} else {
			$proc = 'p_LpuSectionTariffMes_upd';
		}
		$query = "
		declare
			@LpuSectionTariffMes_id bigint = :LpuSectionTariffMes_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@LpuSectionTariffMes_id = @LpuSectionTariffMes_id output,
			@LpuSection_id = :LpuSection_id,

			@Mes_id = :Mes_id,
			@TariffMesType_id = :TariffMesType_id,
			@LpuSectionTariffMes_Tariff = :LpuSectionTariffMes_Tariff,
			@LpuSectionTariffMes_setDate = :LpuSectionTariffMes_setDate,
			@LpuSectionTariffMes_disDate = :LpuSectionTariffMes_disDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionTariffMes_id as LpuSectionTariffMes_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($query, array(
			'LpuSectionTariffMes_id' => $data['LpuSectionTariffMes_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'Mes_id' => $data['Mes_id'],
			'TariffMesType_id' => $data['TariffMesType_id'],
			'LpuSectionTariffMes_Tariff' => $data['LpuSectionTariffMes_Tariff'],
			'LpuSectionTariffMes_setDate' => $data['LpuSectionTariffMes_setDate'],
			'LpuSectionTariffMes_disDate' => $data['LpuSectionTariffMes_disDate'],
			'pmUser_id' => $data['pmUser_id']
		));
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function SaveLpuSectionPlan($data)
	{
		$this->load->helper('Date');
		if (!isset($data['LpuSectionPlan_id'])) {
			$proc = 'p_LpuSectionPlan_ins';
		} else {
			$proc = 'p_LpuSectionPlan_upd';
		}
		$query = "
		declare
			@LpuSectionPlan_id bigint = :LpuSectionPlan_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@LpuSectionPlan_id = @LpuSectionPlan_id output,
			@LpuSection_id = :LpuSection_id,
			@LpuSectionPlanType_id = :LpuSectionPlanType_id,
			@LpuSectionPlan_setDate = :LpuSectionPlan_setDate,
			@LpuSectionPlan_disDate = :LpuSectionPlan_disDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionPlan_id as LpuSectionPlan_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($query, array(
			'LpuSectionPlan_id' => $data['LpuSectionPlan_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionPlanType_id' => $data['LpuSectionPlanType_id'],
			'LpuSectionPlan_setDate' => $data['LpuSectionPlan_setDate'],
			'LpuSectionPlan_disDate' => $data['LpuSectionPlan_disDate'],
			'pmUser_id' => $data['pmUser_id']
		));
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
	 * Метод сохраняет данные формы PersonDopDispPlan в структуре МО
	 */
	function SavePersonDopDispPlan($data)
	{
		if ( !isSuperadmin() && empty($data['LpuRegion_id']) ) {
			return array(array('Error_Msg' => 'Поле "Участок" обязательно для заполнения'));
		}

		// Проверяем уникальность записи с учетом всех параметров
		$query = "
			select count(PersonDopDispPlan_id) as cnt
			from v_PersonDopDispPlan with (nolock)
			where PersonDopDispPlan_id != ISNULL(:PersonDopDispPlan_id, 0)
				and Lpu_id = :Lpu_id
				and ISNULL(LpuRegion_id, 0) = ISNULL(:LpuRegion_id, 0)
				and ISNULL(PersonDopDispPlan_Year, 0) = ISNULL(:PersonDopDispPlan_Year, 0)
				and ISNULL(PersonDopDispPlan_Month, 0) = ISNULL(:PersonDopDispPlan_Month, 0)
				and DispDopClass_id = :DispDopClass_id
		";
        if (in_array($data['DispDopClass_id'], array(4, 5))){
            $query .= "and EducationInstitutionType_id = :EducationInstitutionType_id";
        }
		$result = $this->db->query($query, array(
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'PersonDopDispPlan_Year' => $data['PersonDopDispPlan_Year'],
			'PersonDopDispPlan_Month' => $data['PersonDopDispPlan_Month'],
			'DispDopClass_id' => $data['DispDopClass_id'],
			'EducationInstitutionType_id' => $data['EducationInstitutionType_id']
		));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (проверка уникальности записи) (строка ' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при проверке уникальности записи (строка ' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['cnt']) ) {
			return array(array('Error_Msg' => 'План диспансеризации взрослого населения с указанными годом, месяцем и номером участка уже существует в базе данных'));
		}

		if (!isset($data['PersonDopDispPlan_id'])) {
			$proc = 'p_PersonDopDispPlan_ins';
		} else {
			$proc = 'p_PersonDopDispPlan_upd';
		}

		$trans_result = array();

		$query = "
			declare
				@PersonDopDispPlan_id bigint = :PersonDopDispPlan_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec {$proc}
				@PersonDopDispPlan_id = @PersonDopDispPlan_id output,
				@Lpu_id = :Lpu_id,
				@LpuRegion_id = :LpuRegion_id,
				@PersonDopDispPlan_Year = :PersonDopDispPlan_Year,
				@PersonDopDispPlan_Month = :PersonDopDispPlan_Month,
				@PersonDopDispPlan_Plan = :PersonDopDispPlan_Plan,
				@DispDopClass_id = :DispDopClass_id,
				@EducationInstitutionType_id = :EducationInstitutionType_id,
				@QuoteUnitType_id = :QuoteUnitType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @PersonDopDispPlan_id as PersonDopDispPlan_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, array(
			'PersonDopDispPlan_id' => $data['PersonDopDispPlan_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'PersonDopDispPlan_Year' => $data['PersonDopDispPlan_Year'],
			'PersonDopDispPlan_Month' => $data['PersonDopDispPlan_Month'],
			'PersonDopDispPlan_Plan' => $data['PersonDopDispPlan_Plan'],
			'DispDopClass_id' => $data['DispDopClass_id'],
			'EducationInstitutionType_id' => $data['EducationInstitutionType_id'],
			'QuoteUnitType_id' => $data['QuoteUnitType_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( !is_object($result) ) {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (сохранение плана диспансеризации) (строка ' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			return array(array('Error_Msg' => 'Ошибка при сохранении плана диспансеризации'));
		}

		return $response;
	}

	/**
	 * Метод сохраняет данные формы LpuSectionQuote в структуре МО
	 */
	function SaveLpuSectionQuote($data)
	{
		$data['LpuSectionQuote_Fact'] = null;
		$data['OrgSMO_id'] = null;

		if ( empty($data['LpuSectionQuote_id']) ) {
			$proc = 'p_LpuSectionQuote_ins';
		}
		else {
			$proc = 'p_LpuSectionQuote_upd';

			// https://redmine.swan.perm.ru/issues/48193
			$query = "
				select top 1
					LpuSectionQuote_Fact,
					OrgSMO_id
				from
					v_LpuSectionQuote LSQ with (nolock)
				where
					LpuSectionQuote_id = :LpuSectionQuote_id
			";
			$result = $this->db->query($query, array('LpuSectionQuote_id' => $data['LpuSectionQuote_id']));

			if ( !is_object($result) ) {
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (получение данных)'));
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 ) {
				$data['LpuSectionQuote_Fact'] = $response[0]['LpuSectionQuote_Fact'];
				$data['OrgSMO_id'] = $response[0]['OrgSMO_id'];
			}
		}

		$trans_good = true;
		$trans_result = array();
		$this->db->trans_begin();

		// предварительно надо проверить на уникальность данных
		$query = "
			select
				count(*) as rec
			from v_LpuSectionQuote LSQ with (nolock)
			where (1 = 1)
				and LSQ.Lpu_id = :Lpu_id
				and LSQ.LpuSectionQuote_id <> ISNULL(:LpuSectionQuote_id, 0)
				and LSQ.LpuSectionQuote_Year = :LpuSectionQuote_Year
				and LSQ.LpuSectionQuote_begDate = :LpuSectionQuote_begDate
				and LSQ.LpuUnitType_id = :LpuUnitType_id
				and LSQ.LpuSectionProfile_id = :LpuSectionProfile_id
				and LSQ.QuoteUnitType_id = :QuoteUnitType_id
				and LSQ.PayType_id = :PayType_id
		";
		$result = $this->db->query($query, array(
			'LpuSectionQuote_id' => $data['LpuSectionQuote_id'],
			'Lpu_id' => $data['Lpu_id'],
			'LpuSectionQuote_Year' => $data['LpuSectionQuote_Year'],
			'LpuSectionQuote_begDate' => $data['LpuSectionQuote_begDate'],
			'LpuUnitType_id' => $data['LpuUnitType_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'QuoteUnitType_id'=>$data['QuoteUnitType_id'],
			'PayType_id' => $data['PayType_id']
		));
		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (контроль двойных записей при сохранении планирования МО)'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Ошибка при контроле двойных записей планирования'));
		}
		else if ( $response[0]['rec'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Невозможно сохранить планирование на указанный год, с указанным профилем <br/>и видом медицинской помощи, поскольку запись планирования с этими данными уже существует.'));
		}
		if ($trans_good === true) {
			$query = "
			declare
				@LpuSectionQuote_id bigint = :LpuSectionQuote_id,
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec {$proc}
				@LpuSectionQuote_id = @LpuSectionQuote_id output,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuUnitType_id = :LpuUnitType_id,
				@LpuSectionQuote_Count = :LpuSectionQuote_Count,
				@LpuSectionQuote_Year = :LpuSectionQuote_Year,
				@Lpu_id = :Lpu_id,
				@LpuSectionQuote_begDate = :LpuSectionQuote_begDate,
				@LpuSectionQuote_Fact = :LpuSectionQuote_Fact,
				@PayType_id = :PayType_id,
				@OrgSMO_id = :OrgSMO_id,
				@QuoteUnitType_id=:QuoteUnitType_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuSectionQuote_id as LpuSectionQuote_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

			$result = $this->db->query($query, array(
				'LpuSectionQuote_id' => $data['LpuSectionQuote_id'],
				'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
				'LpuUnitType_id' => $data['LpuUnitType_id'],
				'LpuSectionQuote_Count' => $data['LpuSectionQuote_Count'],
				'LpuSectionQuote_Year' => $data['LpuSectionQuote_Year'],
				'Lpu_id' => $data['Lpu_id'],
				'LpuSectionQuote_begDate' => $data['LpuSectionQuote_begDate'],
				'LpuSectionQuote_Fact' => $data['LpuSectionQuote_Fact'],
				'PayType_id' => $data['PayType_id'],
				'OrgSMO_id' => $data['OrgSMO_id'],
				'QuoteUnitType_id'=>$data['QuoteUnitType_id'],
				'pmUser_id' => $data['pmUser_id']
			));
			if ( is_object($result) ) {
				$response = $result->result('array');
				if ( !is_array($response) || count($response) == 0 ) {
					$trans_good = false;
					$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении планирования МО!'));
				}
				else
				{
					$trans_result = $response;
				}
			}
			else {
				$trans_good = false;
				$trans_result = array(0 => array('Error_Msg' => 'Ошибка при сохранении планирования МО!'));
			}
		}
		if ( $trans_good === true ) {
			$this->db->trans_commit();
		}
		else {
			$this->db->trans_rollback();
		}
		return $trans_result;
	}

	/**
     * Проверка на уникальность врача на участке
	*/
	public function checkSaveMedStaffRegion($data)
	{
		if ((!isset($data['MedPersonal_id'])) || ($data['MedPersonal_id']==0))
			return false;
		if ((!isset($data['LpuRegion_id'])) || ($data['LpuRegion_id']==0))
			return false;
		$params = array('MedPersonal_id' => $data['MedPersonal_id'], 'LpuRegion_id' => $data['LpuRegion_id']);
		if ((isset($data['MedStaffRegion_id'])) && ($data['MedStaffRegion_id']>0))
		{
			$medstaffregion = "MSR.MedStaffRegion_id != :MedStaffRegion_id";
			$params['MedStaffRegion_id'] = $data['MedStaffRegion_id'];
		}
		else
		{
			$medstaffregion = "(1=1)";
		}

		if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id']>0))
		{
			$lpu = "MSR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}
		else
		{
			$lpu = "(1<>1)";
		}
		$sql = "
			select
				count(*) as record_count
				from v_MedStaffRegion MSR with (nolock)
			where
				MSR.MedPersonal_id = :MedPersonal_id and
				MSR.LpuRegion_id = :LpuRegion_id and
				{$medstaffregion} and
				{$lpu}
		";
		$res = $this->db->query($sql, $params);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

    /**
     * Это Doc-блок
     */
	function getStreetHouses($data, $town_id, $street_id, $lpuregion_id, $lpuregionstreet_id)
	{
		// Получаем тип участка
		$lpuregiontype_id = 0;
		if (isset($lpuregion_id))
		{
			$query = "
				select
					LpuRegionType_id
				from v_LpuRegion with (nolock)
				where LpuRegion_id = ?";
			$result = $this->db->query($query, array($lpuregion_id));
			if (is_object($result))
			{
				$res = $result->result('array');
				if (count($res)>0)
				{
					$lpuregiontype_id = $res[0]['LpuRegionType_id'];
				}
				else
				{
					return false;
				}
			}
			else
			{
				return false;
			}
			$queryParams = array();
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$queryParams['KLStreet_id'] = ($street_id == 0)?NULL:$street_id;
			$queryParams['KLTown_id'] = ($town_id == 0)?NULL:$town_id;
			$queryParams['LpuRegionType_id'] = $lpuregiontype_id;
			$queryParams['LpuRegionStreet_id'] = ($lpuregionstreet_id == 0)?NULL:$lpuregionstreet_id;

			$query = "
				select
					LpuRegion.LpuRegion_id,
					LpuRegionStreet_HouseSet
				from LpuRegionStreet with (nolock)
				inner join v_LpuRegion LpuRegion with(nolock) on LpuRegion.LpuRegion_id = LpuRegionStreet.LpuRegion_id
				where
					Lpu_id = :Lpu_id and
					(KLStreet_id = :KLStreet_id or :KLStreet_id is null) and
					(KLTown_id = :KLTown_id or :KLTown_id is null) and
					(LpuRegion.LpuRegionType_id = :LpuRegionType_id) and
					(LpuRegionStreet.LpuRegionStreet_id <> :LpuRegionStreet_id or :LpuRegionStreet_id is null)";

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
		else
		{
			return false;
		}
	}

	/**
	 * Возвращает улицу по указанному идентификатору
	 *
	 * @param int $KLStreet_id ID улицы
	 * @return array
	 */
	public function getKLStreetById($KLStreet_id){
		$sql = "SELECT TOP 1 * FROM KLStreet with(nolock) WHERE KLStreet_id = :KLStreet_id";
		return $this->db->query($sql, array('KLStreet_id' => $KLStreet_id))->row_array();
	}

    /**
     * Сохранение территории обслуживамой подразделением
     */
	public function SaveLpuBuildingStreet($data){
		if (!isset($data['LpuBuildingStreet_id'])) {
			$street = $this->getKLStreetById($data['KLStreet_id']);
			if (!empty($street)) {
				$sql = "
					declare
						@KLHouse_id bigint = :KLHouse_id,
						@Error_Code int,
						@Error_Message varchar(4000);
					exec p_KLHouse_ins
						@KLHouse_id = @KLHouse_id output,
						@KLStreet_id = :KLStreet_id,
						@KLSocr_id = :KLSocr_id,
						@KLHouse_Name = :KLHouse_Name,
						@KLHouse_Corpus = :KLHouse_Corpus,
						@KLAdr_Code = :KLAdr_Code,
						@KLAdr_Index = :KLAdr_Index,
						@KLAdr_Gninmb = :KLAdr_Gninmb,
						@KLAdr_Uno = :KLAdr_Uno,
						@KLAdr_Ocatd = :KLAdr_Ocatd,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @KLHouse_id as KLHouse_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";
				$result = $this->db->query($sql, array(
					'KLHouse_id' => null,
					'KLStreet_id' => $data['KLStreet_id'],
					'KLHouse_Name' => $data['LpuBuildingStreet_HouseSet'],
					'KLSocr_id' => '78',
					'KLHouse_Corpus' => null,
					'KLAdr_Code' => $street['KLAdr_Code'],
					'KLAdr_Index' => $street['KLAdr_Index'],
					'KLAdr_Gninmb' => $street['KLAdr_Gninmb'],
					'KLAdr_Uno' => $street['KLAdr_Uno'],
					'KLAdr_Ocatd' => $street['KLAdr_Ocatd'],
					'pmUser_id' => $data['pmUser_id']
				));
				if (is_object($result)) {
					$resHouse = $result->result('array');
					$KLHouse_id = $resHouse[0]['KLHouse_id'];
					$KLArea_id = ($data['KLCity_id'] > 0)?$data['KLCity_id']:(($data['KLTown_id'] > 0)?$data['KLTown_id']:null);

					$query = "
					declare
						@KLHouseCoords_id bigint = :KLHouseCoords_id,
						@Error_Code int,
						@Error_Message varchar(4000);
					exec p_KLHouseCoords_ins
						@KLHouseCoords_id = @KLHouseCoords_id output,
						@KLHouse_id = :KLHouse_id,
						@KLStreet_id = :KLStreet_id,
						@KLArea_id = :KLArea_id,
						@KLHouseCoords_Name = :KLHouseCoords_Name,
						@KLHouseCoords_LatLng = :KLHouseCoords_LatLng,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @KLHouseCoords_id as KLHouseCoords_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

					$result = $this->db->query($query, array(
						'KLHouseCoords_id' => null,
						'KLHouse_id' => $KLHouse_id,
						'KLStreet_id' => $data['KLStreet_id'],
						'KLArea_id' => $KLArea_id,
						'KLHouseCoords_Name' => $data['LpuBuildingStreet_HouseSet'],
						'KLHouseCoords_LatLng' => '0',
						'pmUser_id' => $data['pmUser_id']
					));
					if (is_object($result)) {
						$resHouseCoords = $result->result('array');
						$KLHouseCoords_id = $resHouseCoords[0]['KLHouseCoords_id'];

						$query = "
						declare
							@LpuBuildingKLHouseCoordsRel_id bigint = :LpuBuildingKLHouseCoordsRel_id,
							@Error_Code int,
							@Error_Message varchar(4000);
						exec p_LpuBuildingKLHouseCoordsRel_ins
							@LpuBuildingKLHouseCoordsRel_id = @LpuBuildingKLHouseCoordsRel_id output,
							@LpuBuilding_id = :LpuBuilding_id,
							@KLHouseCoords_id = :KLHouseCoords_id,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;
						select @LpuBuildingKLHouseCoordsRel_id as LpuBuildingStreet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

						$result = $this->db->query($query, array(
							'LpuBuildingKLHouseCoordsRel_id' => null,
							'LpuBuilding_id' => $data['LpuBuilding_id'],
							'KLHouseCoords_id' => $KLHouseCoords_id,
							'pmUser_id' => $data['pmUser_id']
						));
						return $result->result('array');
					}
				}
			}

		} else {
			//$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$queryParams['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$queryParams['Rel_id'] = $data['LpuBuildingStreet_id'];

			$query = "
				select
					KHC.KLHouse_id,
					REL.LpuBuilding_id
				from LpuBuildingKLHouseCoordsRel REL (nolock)

				inner join KLHouseCoords KHC with(nolock) on KHC.KLHouseCoords_id = REL.KLHouseCoords_id

				where
					REL.LpuBuilding_id = :LpuBuilding_id and
					REL.LpuBuildingKLHouseCoordsRel_id = :Rel_id
			";

			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$house_id = $result->result('array');
				$house_id = $house_id[0]['KLHouse_id'];
				if ($house_id > 0) {
					// Если по старому
					$query = "
					declare
						@Res bigint,
						@Error_Code int,
						@Error_Message varchar(4000);
					set @Res = :KLHouse_id;
					exec p_KLHouseName_upd
						@KLHouse_id = @Res,
						@KLHouse_Name = :KLHouse_Name,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select '".$data['LpuBuildingStreet_id']."' as LpuBuildingStreet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

					$result = $this->db->query($query, array(
						'KLHouse_id' => $house_id,
						'KLHouse_Name' => $data['LpuBuildingStreet_HouseSet'],
						'pmUser_id' => $data['pmUser_id']
					));
					return $result->result('array');
				} else {
					//Если по новому работаем по спаршенному Виалон-у
					$query = "
						select
							KLHouseCoords_id
						from LpuBuildingKLHouseCoordsRel (nolock)
						where
							LpuBuildingKLHouseCoordsRel_id = ".$queryParams['Rel_id'];
					$result = $this->db->query($query, $queryParams);
					if (is_object($result)) {
						$housecoords_id = $result->result('array');
						$housecoords_id = $housecoords_id[0]['KLHouseCoords_id'];
						$query = "
						update
							KLHouseCoords
						set
							KLHouseCoords_Name = :KLHouseCoords_Name,
							pmUser_updID = :pmUser_id,
							KLHouseCoords_updDT = getdate()
						where
							KLHouseCoords_id = :KLHouseCoords_id;
						";


						$result = $this->db->query($query, array(
							'KLHouseCoords_id' => $housecoords_id,
							'KLHouseCoords_Name' => $data['LpuBuildingStreet_HouseSet'],
							'pmUser_id' => $data['pmUser_id']
						));
						$result = $this->db->query("select '".$data['LpuBuildingStreet_id']."' as LpuBuildingStreet_id, null as Error_Code, null as Error_Msg;");
						return $result->result('array');
					}
				}
			}
			else
			{
				return false;
			}
		}
	}

	/**
     * Это Doc-блок
     */
	function SaveMedServiceStreet($data)
	{
		// if (!isset($data['MedServiceStreet_id'])) {
		if (isset($data['MedServiceStreet_id'])) {
			$this->load->database();
			$utils =& get_instance();
			$utils->load->model("Utils_model", "umodel", true);
			$umodel =& $utils->umodel;
			$response = $umodel->ObjectRecordDelete($data, 'MedServiceKLHouseCoordsRel', true, $data['MedServiceStreet_id']);
		}
		$res[] = array();
		$data['MedServiceStreet_isAll'] = ( !empty($data['MedServiceStreet_isAll']) && ($data['MedServiceStreet_isAll'] == "on" || $data['MedServiceStreet_isAll'] === 'true') )? 2: 1;
		$data['MedServiceStreet_HouseSet'] = !empty($data['MedServiceStreet_HouseSet'])?$data['MedServiceStreet_HouseSet']:'';
		//$data['KLTown_id'] = !empty($data['KLTown_id'])? $data['KLTown_id']: '4119';

		if (!empty($data['KLStreet_id'])) {
			$query = "
			select
				ST.KLAdr_Code,
				ST.KLAdr_Index,
				ST.KLAdr_Gninmb,
				ST.KLAdr_Uno,
				ST.KLAdr_Ocatd
			from KLStreet ST (nolock)
			where
				ST.KLStreet_id = :KLStreet_id
		";
			$queryParams['KLStreet_id'] = $data['KLStreet_id'];
			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$res = $result->result('array');
			}
		}

		$query = "
			declare
				@KLHouse_id bigint = :KLHouse_id,
				@Error_Code int,
				@Error_Message varchar(4000);
			exec p_KLHouse_ins
				@KLHouse_id = @KLHouse_id output,
				@KLStreet_id = :KLStreet_id,
				@KLSocr_id = :KLSocr_id,
				@KLHouse_Name = :KLHouse_Name,
				@KLHouse_Corpus = :KLHouse_Corpus,
				@KLAdr_Code = :KLAdr_Code,
				@KLAdr_Index = :KLAdr_Index,
				@KLAdr_Gninmb = :KLAdr_Gninmb,
				@KLAdr_Uno = :KLAdr_Uno,
				@KLAdr_Ocatd = :KLAdr_Ocatd,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @KLHouse_id as KLHouse_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		$result = $this->db->query($query, array(
			'KLHouse_id' => null,
			'KLStreet_id' => !empty($data['KLStreet_id']) ? $data['KLStreet_id'] : null,
			'KLHouse_Name' => $data['MedServiceStreet_HouseSet'],
			'KLSocr_id' => '78',
			'KLHouse_Corpus' => null,
			'KLAdr_Code' => !empty($res[0]['KLAdr_Code']) ? $res[0]['KLAdr_Code'] : null,
			'KLAdr_Index' => !empty($res[0]['KLAdr_Index']) ? $res[0]['KLAdr_Index'] : null,
			'KLAdr_Gninmb' => !empty($res[0]['KLAdr_Gninmb']) ? $res[0]['KLAdr_Gninmb'] : null,
			'KLAdr_Uno' => !empty($res[0]['KLAdr_Uno']) ? $res[0]['KLAdr_Uno'] : null,
			'KLAdr_Ocatd' => !empty($res[0]['KLAdr_Ocatd']) ? $res[0]['KLAdr_Ocatd'] : null,
			'pmUser_id' => $data['pmUser_id']
		));

		if (is_object($result)) {
			$resHouse = $result->result('array');
			$KLHouse_id = $resHouse[0]['KLHouse_id'];
			$KLArea_id = ($data['KLTown_id'] > 0) ? $data['KLTown_id'] : (($data['KLCity_id'] > 0) ? $data['KLCity_id'] : null);
			if ($KLArea_id == null) {
				$KLArea_id = $data['KLSubRGN_id'];
			}

			$query = "
				declare
					@KLHouseCoords_id bigint = :KLHouseCoords_id,
					@Error_Code int,
					@Error_Message varchar(4000);
				exec p_KLHouseCoords_ins
					@KLHouseCoords_id = @KLHouseCoords_id output,
					@KLHouse_id = :KLHouse_id,
					@KLStreet_id = :KLStreet_id,
					@KLArea_id = :KLArea_id,
					@KLHouseCoords_Name = :KLHouseCoords_Name,
					@KLHouseCoords_LatLng = :KLHouseCoords_LatLng,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @KLHouseCoords_id as KLHouseCoords_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

			$result = $this->db->query($query, array(
				'KLHouseCoords_id' => null,
				'KLHouse_id' => $KLHouse_id,
				'KLStreet_id' => !empty($data['KLStreet_id']) ? $data['KLStreet_id'] : null,
				'KLArea_id' => $KLArea_id,
				'KLHouseCoords_Name' => $data['MedServiceStreet_HouseSet'],
				'KLHouseCoords_LatLng' => '0',
				'pmUser_id' => $data['pmUser_id']
			));

			if (is_object($result)) {
				$resHouseCoords = $result->result('array');
				$KLHouseCoords_id = $resHouseCoords[0]['KLHouseCoords_id'];

				$query = "
					declare
						@MedServiceKLHouseCoordsRel_id bigint = :MedServiceKLHouseCoordsRel_id,
						@Error_Code int,
						@Error_Message varchar(4000);
					exec p_MedServiceKLHouseCoordsRel_ins
						@MedServiceKLHouseCoordsRel_id = @MedServiceKLHouseCoordsRel_id output,
						@MedService_id = :MedService_id,
						@KLHouseCoords_id = :KLHouseCoords_id,
						@MedServiceStreet_isAll = :MedServiceStreet_isAll,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select @MedServiceKLHouseCoordsRel_id as MedServiceStreet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

				$result = $this->db->query($query, array(
					'MedServiceKLHouseCoordsRel_id' => null,
					'MedService_id' => $data['MedService_id'],
					'KLHouseCoords_id' => $KLHouseCoords_id,
					'MedServiceStreet_isAll' => $data['MedServiceStreet_isAll'],
					'pmUser_id' => $data['pmUser_id']
				));
				return $result->result('array');
			}
		}

		//}
		/*
		else {
			//$queryParams['Lpu_id'] = $data['session']['lpu_id'];
			$queryParams['MedService_id'] = $data['MedService_id'];
			$queryParams['Rel_id'] = $data['MedServiceStreet_id'];

			$query = "
				select
					KHC.KLHouse_id,
					REL.MedService_id
				from MedServiceKLHouseCoordsRel REL (nolock)

				inner join KLHouseCoords KHC on KHC.KLHouseCoords_id = REL.KLHouseCoords_id

				where
					REL.MedService_id = :MedService_id and
					REL.MedServiceKLHouseCoordsRel_id = :Rel_id
			";

			$result = $this->db->query($query, $queryParams);
			if (is_object($result)) {
				$house_id = $result->result('array');
				$house_id = $house_id[0]['KLHouse_id'];
				if ($house_id > 0) {
					$query = "
					declare
						@Res bigint,
						@Error_Code int,
						@Error_Message varchar(4000);
					set @Res = :KLHouse_id;
					exec p_KLHouseName_upd
						@KLHouse_id = @Res,
						@KLHouse_Name = :KLHouse_Name,
						@pmUser_id = :pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
					select '".$data['MedServiceStreet_id']."' as MedServiceStreet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

					$result = $this->db->query($query, array(
						'KLHouse_id' => $house_id,
						'KLHouse_Name' => $data['MedServiceStreet_HouseSet'],
						'pmUser_id' => $data['pmUser_id']
					));
					return $result->result('array');
				} else {
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		*/
	}

    /**
     * Это Doc-блок
     */
	function SaveLpuRegionStreet($data)
	{

		$is_all = !empty($data['LpuRegionStreet_IsAll']) && ($data['LpuRegionStreet_IsAll'] == self::YES_ID || $data['LpuRegionStreet_IsAll'] == self::CHECKBOX_VAL) ? true : false;

		if (!isset($data['LpuRegionStreet_id']))
		{
			$proc = 'p_LpuRegionStreet_ins';
		}
		else
		{
			$proc = 'p_LpuRegionStreet_upd';
		}

		$query = "
		declare
			@LpuRegionStreet_id bigint = :LpuRegionStreet_id,
			@Error_Code bigint = 0,
			@Error_Message varchar(4000) = '';
		exec {$proc}
			@Server_id = :Server_id,
			@LpuRegionStreet_id = @LpuRegionStreet_id output,
			@LpuRegion_id = :LpuRegion_id,
			@KLCountry_id = :KLCountry_id,
			@KLRGN_id = :KLRGN_id,
			@KLSubRGN_id = :KLSubRGN_id,
			@KLCity_id = :KLCity_id,
			@KLTown_id = :KLTown_id,
			@LpuRegionStreet_IsAll = :LpuRegionStreet_IsAll,
			@KLStreet_id = :KLStreet_id,
			@LpuRegionStreet_HouseSet = :LpuRegionStreet_HouseSet,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuRegionStreet_id as LpuRegionStreet_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";
		$result = $this->db->query($query, array(
			'LpuRegionStreet_id' => $data['LpuRegionStreet_id'],
			'Server_id' => $data['Server_id'],
			'LpuRegion_id' => $data['LpuRegion_id'],
			'KLCountry_id' => $data['KLCountry_id'],
			'KLRGN_id' => $data['KLRGN_id'],
			'KLSubRGN_id' => $data['KLSubRGN_id'],
			'KLCity_id' => $data['KLCity_id'],
			'KLTown_id' => $data['KLTown_id'],
			'LpuRegionStreet_IsAll'=>  $is_all ? self::YES_ID : self::NO_ID,
			'KLStreet_id' => $data['KLStreet_id'],
			'LpuRegionStreet_HouseSet' => $data['LpuRegionStreet_HouseSet'],
			'pmUser_id' => $data['pmUser_id']
		));
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
     * Это Doc-блок
     */
	function getLpuUnitList($data) {
		$additionalFields = array();
		$additionalWith = "";
		$filterList = array("(1 = 1)");
		$params = array();
		/*
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			$filter .= "and Lpu_id=".$data['Lpu_id'];
		*/
		if ( !empty($data['LpuBuilding_id']) ) {
			$filterList[] = "lu.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuUnit_Code'])) {
			$filterList[] = "lu.LpuUnit_Code = :LpuUnit_Code";
			$params['LpuUnit_Code'] = $data['LpuUnit_Code'];
		}
		if (!empty($data['LpuUnit_Name'])) {
			$filterList[] = "lu.LpuUnit_Name like :LpuUnit_Name";
			$params['LpuUnit_Name'] = $data['LpuUnit_Name'];
		}
		if (!empty($data['LpuUnitType_Code'])) {
			$filterList[] = "lut.LpuUnitType_Code = :LpuUnitType_Code";
			$params['LpuUnitType_Code'] = $data['LpuUnitType_Code'];
		}

		if ( !empty($data['LpuUnit_id']) ) {
			$filterList[] = "lu.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];

			$additionalFields[] = "
				case when exists (select top 1 id from LpuUnitChilds with(nolock)) then 1 else 0 end as ChildsCount
			";
			$additionalWith = "
				with LpuUnitChilds as (
					select top 1 MedService_id as id
					from v_MedService with (nolock)
					where LpuUnit_id = :LpuUnit_id

					union all

					select top 1 UslugaComplexPlace_id as id
					from v_UslugaComplexPlace with (nolock)
					where LpuUnit_id = :LpuUnit_id
				)
			";
		}

		$sql = "
			" . $additionalWith . "

			SELECT
				lu.LpuUnit_id,
				lu.Lpu_id,
				convert(varchar(10), lu.LpuUnit_begDate, 104) as LpuUnit_begDate,
				convert(varchar(10), lu.LpuUnit_endDate, 104) as LpuUnit_endDate,
				lu.LpuBuilding_id,
				lu.LpuUnitSet_id,
				lu.LpuUnit_Code,
				lu.LpuUnitType_id,
				lu.LpuUnitTypeDop_id,
				lu.LpuUnitProfile_fid,
				lu.LpuUnit_isStandalone,
				lu.LpuUnit_isCMP,
				lu.LpuUnit_isHomeVisit,
				lu.LpuUnit_FRMOUnitID,
				lu.FRMOUnit_id,
				lu.UnitDepartType_fid,
				lu.LpuBuildingPass_id,
				RTrim(lu.LpuUnit_Name) as LpuUnit_Name,
				RTrim(lu.LpuUnit_Phone) as LpuUnit_Phone,
				RTrim(lu.LpuUnit_Descr) as LpuUnit_Descr,
				RTrim(lu.LpuUnit_Email) as LpuUnit_Email,
				RTrim(lu.LpuUnit_IP) as LpuUnit_IP,
				case when lu.LpuUnit_IsEnabled = 2 then 'on' else 'off' end as LpuUnit_IsEnabled,
				case when lu.LpuUnit_isPallCC = 2 then 'on' else 'off' end as LpuUnit_isPallCC,
				case when lu.LpuUnit_IsNotFRMO = 2 then 'on' else 'off' end as LpuUnit_IsNotFRMO,
				case when lu.LpuUnit_IsOMS = 2 or lu.LpuUnit_IsOMS is null then 'on' else 'off' end as LpuUnit_IsOMS
				" . (count($additionalFields) > 0 ? "," . implode(",", $additionalFields) : "") . "
			FROM 
				v_LpuUnit lu with (nolock)
				inner join v_LpuUnitType lut with(nolock) on lut.LpuUnitType_id = lu.LpuUnitType_id
			WHERE " . implode(" and ", $filterList) . "
		";
		$res = $this->db->query($sql, $params);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}
    /**
     * Получение списка групп отделений
	 */
	function getLpuUnitCombo($data) {
		$where = "";
		$filters = array();
		$params = array();

		if ((isset($data['Lpu_id'])) && ($data['Lpu_id'] > 0)) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else if ((isset($data['session']['lpu_id'])) && ($data['session']['lpu_id'] > 0)) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['session']['lpu_id'];
		}

		if ((isset($data['LpuBuilding_id'])) && ($data['LpuBuilding_id'] > 0)) {
			$filters[] = "LU.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}

		if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id'] > 0)) {
			$filters[] = "LU.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] =  $data['LpuUnit_id'];
		}

		if (count($filters) > 0) {
			$where = "WHERE ".join(" AND ", $filters);
		} else {
			return array(array());
		}

		$sql = "
			SELECT top 500
				LU.LpuUnit_id,
				LU.LpuUnit_Code,
				LU.LpuUnit_Name,
				ISNULL(LU.LpuUnit_IsEnabled, 1) as LpuUnit_IsEnabled,
				LUT.LpuUnitType_SysNick,
				LB.LpuBuilding_id,
				LB.LpuBuilding_Name,
				ISNULL(LU.LpuUnit_IsNotFRMO, 1) as LpuUnit_IsNotFRMO,
				ISNULL(LU.LpuUnit_FRMOUnitID, fu.FRMOUnit_OID) as LpuUnit_FRMOUnitID 
			FROM
				v_LpuUnit LU with (nolock)
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
				left join v_LpuUnitType LUT with (nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join nsi.v_FRMOUnit fu (nolock) on fu.FRMOUnit_id = LU.FRMOUnit_id
			{$where}
		";

		$res = $this->db->query($sql,  $params);
		if (is_object($res)) {
			return $res->result('array');
		} else {
			return false;
		}
	}

    /**
     * Это Doc-блок
     */
	function getLpuUnitSetCombo($data)
	{
		$filterList = array();
		$queryParams = array();

		$filterList[] = "(Lpu_id = " . $data['session']['lpu_id'] . " or Lpu_id is null)";
		// Закомментировал по задаче https://redmine.swan.perm.ru/issues/32214
		/*$filter .="and not exists(select LpuUnitSet_Code from LpuUnitSet t1 (nolock)
					where t1.Lpu_id!=".$data['session']['lpu_id']." and t1.LpuUnitSet_Code=lsc.LpuUnitSet_Code)";*/

		if ( !empty($data['LpuUnitSet_IsCmp']) ) {
			$filterList[] = "LpuUnitSet_IsCmp = :LpuUnitSet_IsCmp";
			$queryParams['LpuUnitSet_IsCmp'] = $data['LpuUnitSet_IsCmp'];
		}

		$sql = "
			SELECT
				 LpuUnitSet_id
				,LpuUnitSet_Code
				,convert(varchar(10), LpuUnitSet_begDate, 104) as LpuUnitSet_begDate
				,convert(varchar(10), LpuUnitSet_endDate, 104) as LpuUnitSet_endDate
				,LpuUnitSet_IsCmp
			FROM v_LpuUnitSet lsc with (nolock)
			WHERE " . implode(' and ', $filterList) . "
		";

		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) ) {

			$result = $res->result('array');

			// добавляем даты начала и конца текущего месяца
			foreach($result as $key => $value) {
				$result[$key]['curBegDateMonth'] = date("01.m.Y");
				$result[$key]['curEndDateMonth'] = date("t.m.Y");
			}
			
			return $result;
		} else {
			return false;
		}
	}
    /**
     * Это Doc-блок
     */
	function getLpuBuildingList($data)
	{
		$filter = "(1=1) ";
		$params = array();
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$filter .= "and LpuBuilding.Lpu_id=:Lpu_id ";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if ((isset($data['LpuFilial_id'])) && ($data['LpuFilial_id']>0)) {
			$filter .= "and LpuBuilding.LpuFilial_id=:LpuFilial_id ";
			$params['LpuFilial_id'] = $data['LpuFilial_id'];
		}
		if ((isset($data['LpuBuilding_id'])) && ($data['LpuBuilding_id']>0)) {
			$filter .= "and LpuBuilding.LpuBuilding_id=:LpuBuilding_id ";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LpuBuilding_endDate is null or LpuBuilding_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LpuBuilding_endDate <= dbo.tzGetDate()";
		}

		$sql = "
			SELECT
				LpuBuilding.Lpu_id,
				LpuBuilding.LpuBuilding_id,
				rtrim(LpuBuilding_Nick) as LpuBuilding_Nick,
				rtrim(LpuBuilding_Name) as LpuBuilding_Name,
				LpuBuilding_Code as LpuBuilding_Code,
				convert(varchar(10), LpuBuilding_begDate, 104) as LpuBuilding_begDate,
				convert(varchar(10), LpuBuilding_endDate, 104) as LpuBuilding_endDate,
				LpuBuilding.LpuBuildingType_id as LpuBuildingType_id,
				rtrim(LpuBuildingType_Name) as LpuBuildingType_Name,
				rtrim(LpuBuilding_WorkTime) as LpuBuilding_WorkTime,
				rtrim(LpuBuilding_RoutePlan) as LpuBuilding_RoutePlan,
				LpuBuilding.LpuBuilding_IsExport,
				LpuBuilding.LpuBuilding_CmpStationCode,
				LpuBuilding.LpuBuilding_CmpSubstationCode,
				LpuBuilding.Address_id,
			    LpuBuilding.LpuBuilding_Longitude,
				LpuBuilding.LpuBuilding_Latitude,
				Address.KLAreaType_id,
				Address.Address_Zip as Address_Zip,
				Address.KLCountry_id as KLCountry_id,
				Address.KLRGN_id as KLRGN_id,
				Address.KLSubRGN_id as KLSubRGN_id,
				Address.KLCity_id as KLCity_id,
				Address.KLTown_id as KLTown_id,
				Address.KLStreet_id as KLStreet_id,
				Address.Address_House as Address_House,
				Address.Address_Corpus as Address_Corpus,
				Address.Address_Flat as Address_Flat,
				Address.Address_Address as Address_Address,
				Address.Address_Address as Address_AddressText,
				LpuBuilding.PAddress_id,
				PAddress.Address_Zip as PAddress_Zip,
				PAddress.KLCountry_id as PKLCountry_id,
				PAddress.KLRGN_id as PKLRGN_id,
				PAddress.KLSubRGN_id as PKLSubRGN_id,
				PAddress.KLCity_id as PKLCity_id,
				PAddress.KLTown_id as PKLTown_id,
				PAddress.KLStreet_id as PKLStreet_id,
				PAddress.Address_House as PAddress_House,
				PAddress.Address_Corpus as PAddress_Corpus,
				PAddress.Address_Flat as PAddress_Flat,
				PAddress.Address_Address as PAddress_Address,
				PAddress.Address_Address as PAddress_AddressText,
				LpuBuilding.LpuFilial_id as LpuFilial_id,
				LFilial.LpuFilial_Name as LpuFilial_Name,
				LFilial.LpuFilial_Code as LpuFilial_Code,
				isnull(LpuBuilding.LpuBuilding_IsAIDSCenter, 1) as LpuBuilding_IsAIDSCenter,
				Lhealth.LpuBuildingHealth_Phone,
				Lhealth.LpuBuildingHealth_Email
			FROM v_LpuBuilding LpuBuilding with (nolock)
				left join LpuBuildingType with(nolock) on LpuBuildingType.LpuBuildingType_id = LpuBuilding.LpuBuildingType_id
				left join v_Address Address with(nolock) on LpuBuilding.Address_id = Address.Address_id
				left join v_Address PAddress with(nolock) on LpuBuilding.PAddress_id = PAddress.Address_id
				left join v_LpuFilial LFilial with (nolock) on LpuBuilding.LpuFilial_id = LFilial.LpuFilial_id
				left join v_Lpu Lpu with(nolock) on Lpu.Lpu_id = LpuBuilding.Lpu_id
				left join v_LpuBuildingHealth Lhealth with(nolock) on Lhealth.LpuBuilding_id = LpuBuilding.LpuBuilding_id
			WHERE {$filter}
		";
		//var_dump(getDebugSQL($sql, $params)); exit;
		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Это Doc-блок
     */
	function getLpuRegionList($data)
	{
		$filter = "(1=1) ";
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			$filter .= "and Lpu_id=".$data['Lpu_id'];
		if ((isset($data['LpuRegion_id'])) && ($data['LpuRegion_id']>0))
			$filter .= "and LpuRegion_id=".$data['LpuRegion_id'];

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LpuRegion_endDate is null or LpuRegion_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LpuRegion_endDate <= dbo.tzGetDate()";
		}

		if (!empty($data['LpuRegionType_id'])) {
			$filter .= " and LpuRegionType_id = ".$data['LpuRegionType_id'];
		}

		$sql = "
			select
				LpuRegion_id,
				LpuRegion_Name,
				LpuRegion_Descr,
				LpuRegionType_id,
				convert(varchar(10), cast(LpuRegion_begDate as datetime), 104) as LpuRegion_begDate,
				convert(varchar(10), cast(LpuRegion_endDate as datetime), 104) as LpuRegion_endDate,
				Lpu_id
			from
				v_LpuRegion (nolock)
			WHERE {$filter}
		";
		//print $sql;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Это Doc-блок
     */
	function getMedStaffRegion($data)
	{
		$filter = "(1=1) ";
		$queryParams = array();
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$filter .= " and msr.Lpu_id=:Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		} elseif (isset($data['session']['lpu_id'])) {
			$filter .= " and msr.Lpu_id=:Lpu_id";
			$queryParams['Lpu_id'] = $data['session']['lpu_id'];
		}

		/*if ((isset($data['MedStaffFact_id'])) && ($data['MedStaffFact_id']>0)) {
			$filter .= " and msr.MedStaffFact_id=:MedStaffFact_id";
			$queryParams['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}*/

		if ((isset($data['MedStaffRegion_id'])) && ($data['MedStaffRegion_id']>0)) {
			$filter .= " and msr.MedStaffRegion_id = :MedStaffRegion_id";
			$queryParams['MedStaffRegion_id'] = $data['MedStaffRegion_id'];
		}

		if (!empty($data['LpuRegion_id'])) {
			$filter .= " and msr.LpuRegion_id = :LpuRegion_id";
			$queryParams['LpuRegion_id'] = $data['LpuRegion_id'];
		}

		if(!empty($data['showClosed']) && $data['showClosed'] == 1)
		{
			$filter .= " and (msr.MedStaffRegion_endDate is null or CAST(msr.MedStaffRegion_endDate as DATE) >= dbo.tzGetDate()) ";
		}

		$queryParams['LpuRegion_id'] = !empty($data['LpuRegion_id'])?$data['LpuRegion_id']:null;

		$sql = "
			SELECT
				msr.MedStaffRegion_id,
				msr.MedStaffFact_id,
				ISNULL(msf.MedPersonal_id, msr.MedPersonal_id) as MedPersonal_id,
				msr.MedStaffRegion_isMain,
				case when msr.MedStaffRegion_isMain = 2 then '(Основной врач)' else '' end as msr_descr,
				ISNULL(msf.Person_Fio, mp.Person_Fio) as MedPersonal_FIO,
				msr.LpuRegion_id,
				convert(varchar,msr.MedStaffRegion_begDate,104) as MedStaffRegion_begDate,
				convert(varchar,msr.MedStaffRegion_endDate,104) as MedStaffRegion_endDate,
				lr.LpuRegionType_id,
				lr.LpuRegion_Name,
				p.name as PostMed_Name,
				msr.Lpu_id,
				1 as [status]
			FROM v_MedStaffRegion msr with (nolock)
				left join v_MedStaffFact msf on msf.MedStaffFact_id = msr.MedStaffFact_id and msr.Lpu_id = msf.Lpu_id
				outer apply (
					select top 1 Person_Fio
					from v_MedPersonal with (nolock)
					where MedPersonal_id = msr.MedPersonal_id
				) mp
				inner join v_LpuRegion lr with(nolock) on lr.LpuRegion_id = msr.LpuRegion_id
				LEFT JOIN persis.Post p with (nolock) on p.id = msf.Post_id
			WHERE
				{$filter}
		";
		//print $sql;
		//echo getDebugSQL($sql, $queryParams);die;
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function getUslugaSectionTariff($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['UslugaSectionTariff_id'])) && (is_numeric($data['UslugaSectionTariff_id'])))
			$filter .= " and UslugaSectionTariff_id=".$data['UslugaSectionTariff_id'];

		if ((isset($data['UslugaSection_id'])) && (is_numeric($data['UslugaSection_id'])))
			$filter .= " and UslugaSection_id=".$data['UslugaSection_id'];

		$sql = "
			Select
				Server_id,
				UslugaSectionTariff_id,
				UslugaSection_id,
				UslugaSectionTariff_Tariff,
				convert(varchar,cast(UslugaSectionTariff_begDate as datetime),104) as UslugaSectionTariff_begDate,
				convert(varchar,cast(UslugaSectionTariff_endDate as datetime),104) as UslugaSectionTariff_endDate
			from UslugaSectionTariff with (nolock)
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetUslugaComplexTariff($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['UslugaComplexTariff_id'])) && (is_numeric($data['UslugaComplexTariff_id'])))
			$filter .= " and UslugaComplexTariff_id=".$data['UslugaComplexTariff_id'];

		if ((isset($data['UslugaComplex_id'])) && (is_numeric($data['UslugaComplex_id'])))
			$filter .= " and UslugaComplex_id=".$data['UslugaComplex_id'];

		$sql = "
			Select
				Server_id,
				UslugaComplexTariff_id,
				UslugaComplex_id,
				UslugaComplexTariff_Tariff,
				convert(varchar,cast(UslugaComplexTariff_begDate as datetime),104) as UslugaComplexTariff_begDate,
				convert(varchar,cast(UslugaComplexTariff_endDate as datetime),104) as UslugaComplexTariff_endDate
			from UslugaComplexTariff with (nolock)
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение списка улиц или улицы для вывода в стуктуре МО на участок
	*/
	function getLpuRegionStreet($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and LpuRegionStreet.Server_id=".$data['Server_id'];
		}
		/*elseif ($data['session']['server_id']>0)
		{
			$filter .= " and LpuRegionStreet.Server_id in(0,1,2,3,".$data['session']['server_id'].")";
		}*/

		if ((isset($data['LpuRegionStreet_id'])) && (is_numeric($data['LpuRegionStreet_id'])))
			$filter .= " and LpuRegionStreet_id=".$data['LpuRegionStreet_id'];

		if ((isset($data['LpuRegion_id'])) && (is_numeric($data['LpuRegion_id'])))
			$filter .= " and LpuRegion_id=".$data['LpuRegion_id'];

		if (!empty($data['LpuRegion_list'])) {
			$filter .= " and LpuRegion_id in ({$data['LpuRegion_list']}) ";
		}

		$sql = "
			Select
				LpuRegionStreet.Server_id,
				LpuRegionStreet_id,
				LpuRegion_id,
				LpuRegionStreet.KLCountry_id,
				LpuRegionStreet.KLRGN_id,
				rgn.KLRgn_FullName,
				KLSubRGN_id,
				KLCity_id,
			  	LpuRegionStreet_IsAll,
				t.KLArea_id as tKLArea_id,
				c.KLArea_id as cKLArea_id,
				LpuRegionStreet.KLTown_id,
				case IsNULL(LpuRegionStreet.KLTown_id,'')
				when '' then RTrim(c.KLArea_Name)+' '+ISNULL(cs.KLSocr_Nick,'')
				else RTrim(t.KLArea_Name)+' '+ISNULL(ts.KLSocr_Nick,'')
				end as KLTown_Name,
				LpuRegionStreet.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				LpuRegionStreet_HouseSet
			from LpuRegionStreet with (nolock)
			left join KLArea t with (nolock) on t.KLArea_id = LpuRegionStreet.KLTown_id
			left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = LpuRegionStreet.KLStreet_id
			left join KLArea c with (nolock) on c.Klarea_id = LpuRegionStreet.KLCity_id
			left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
			left join v_KLRgn rgn (nolock) on rgn.KLRGN_id = LpuRegionStreet.KLRGN_id
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}



	/**
	* Получение списка улиц или улицы для вывода в стуктуре МО на службу
	*/
	function getMedServiceStreet($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['MedServiceStreet_id'])) && (is_numeric($data['MedServiceStreet_id'])))
			$filter .= " and MedServiceKLHouseCoordsRel_id=".$data['MedServiceStreet_id'];

		if ((isset($data['MedService_id'])) && (is_numeric($data['MedService_id'])))
			$filter .= " and MedService_id=".$data['MedService_id'];

		$sql = "
			Select
				'1' as Server_id,
				MAX(HCR.MedServiceKLHouseCoordsRel_id) as MedServiceStreet_id,
				MedService_id,
				t.KLCountry_id,

				case when t.KLAreaLevel_id = 1 then t.KLArea_id else
				 case when cstatpid.KLAreaLevel_id = 1 then cstatpid.KLArea_id else
				  case when rstatpid.KLAreaLevel_id = 1 then rstatpid.KLArea_id else
				   case when rtatpid.KLAreaLevel_id = 1 then rtatpid.KLArea_id else null end end end end as KLRGN_id,
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else
				 case when cstatpid.KLAreaLevel_id = 2 then cstatpid.KLArea_id else
				  case when rstatpid.KLAreaLevel_id = 2 then rstatpid.KLArea_id else null end end end as KLSubRGN_id,
				case when t.KLAreaLevel_id = 2 then t.KLArea_Name else
				 case when cstatpid.KLAreaLevel_id = 2 then cstatpid.KLArea_Name else
				  case when rstatpid.KLAreaLevel_id = 2 then rstatpid.KLArea_Name else null end end end as KLSubRGN_Name,
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else
				 case when cstatpid.KLAreaLevel_id = 3 then cstatpid.KLArea_id else null end end as KLCity_id,
				case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as KLTown_id,

				t.KLArea_Name as KLTown_Name,
				H.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				H.KLHouse_Name as MedServiceStreet_HouseSet,
				--HCR.MedServiceStreet_isAll
				CASE WHEN ISNULL(HCR.MedServiceStreet_isAll, 1) = 1 THEN 'false' else 'true' END as MedServiceStreet_isAll
			from MedServiceKLHouseCoordsRel HCR with (nolock)
			left join KLHouseCoords HC with (nolock) on HC.KLHouseCoords_id = HCR.KLHouseCoords_id
			left join KLArea t with (nolock) on t.KLArea_id = HC.KLArea_id
			left join KLAreaStat rstat with (nolock) on rstat.KLSubRGN_id = HC.KLArea_id
			left join KLAreaStat cstat with (nolock) on cstat.KLCity_id = HC.KLArea_id
			left join KLAreaStat tstat with (nolock) on tstat.KLTown_id = HC.KLArea_id

			left join KLArea cstatpid with (nolock) on cstatpid.KLArea_id = t.KLArea_pid
			left join KLArea rstatpid with (nolock) on rstatpid.KLArea_id = cstatpid.KLArea_pid
			left join KLArea rtatpid with (nolock) on rtatpid.KLArea_id = rstatpid.KLArea_pid


			left join KLHouse H with (nolock) on H.KLHouse_id = HC.KLHouse_id
			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = H.KLStreet_id
			left join KLSocr ts with (nolock) on ts.KLSocr_id = H.KLSocr_id


			WHERE {$filter}

			Group BY

            HC.KLHouse_id,
            H.KLHouse_Name,
            MedService_id,
            t.KLCountry_id,
			t.KLAreaLevel_id,
            t.KLArea_id,
            t.KLArea_Name,
            cstatpid.KLArea_Name,
            rstatpid.KLArea_Name,
			cstatpid.KLArea_id,
			cstatpid.KLAreaLevel_id,
			rstatpid.KLArea_id,
			rstatpid.KLAreaLevel_id,
			rtatpid.KLArea_id,
			rtatpid.KLAreaLevel_id,
            t.KLArea_Name,
            H.KLStreet_id,
            KLStreet_FullName,
			HCR.MedServiceStreet_isAll
		";

		$res = $this->db->query($sql);
		if ( is_object($res) ) {
			$resArr = $res->result('array');
			for ($i = 0; $i < sizeof($resArr); $i++) {
				if ($resArr[$i]['KLSubRGN_Name'] == $resArr[$i]['KLTown_Name']) {
					$resArr[$i]['KLTown_Name'] = '';
				}
			}
			return $resArr;
		}
		else
			return false;
	}

	/**
	* Получение списка улиц или улицы для вывода в стуктуре МО на участок
	*/
	function getLpuBuildingStreet($data)
	{
		$filter = "(1=1) ";
		/*
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and LpuBuildingStreet.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and LpuBuildingStreet.Server_id in(0,1,2,3,".$data['session']['server_id'].")";
		}*/

		if ((isset($data['LpuBuildingStreet_id'])) && (is_numeric($data['LpuBuildingStreet_id'])))
			$filter .= " and LpuBuildingKLHouseCoordsRel_id=".$data['LpuBuildingStreet_id'];

		if ((isset($data['LpuBuilding_id'])) && (is_numeric($data['LpuBuilding_id'])))
			$filter .= " and LpuBuilding_id=".$data['LpuBuilding_id'];

		//		$sql = "
		//			Select
		//				LpuBuildingStreet.Server_id,
		//				LpuBuildingStreet_id,
		//				LpuBuilding_id,
		//				LpuBuildingStreet.KLCountry_id,
		//				KLRGN_id,
		//				KLSubRGN_id,
		//				KLCity_id,
		//				LpuBuildingStreet.KLTown_id,
		//				case IsNULL(LpuBuildingStreet.KLTown_id,'')
		//				when '' then RTrim(c.KLArea_Name)+' '+cs.KLSocr_Nick
		//				else RTrim(t.KLArea_Name)+' '+ts.KLSocr_Nick
		//				end as KLTown_Name,
		//				LpuBuildingStreet.KLStreet_id,
		//				RTrim(KLStreet_FullName) as KLStreet_Name,
		//				LpuBuildingStreet_HouseSet
		//			from LpuBuildingStreet with (nolock)
		//			left join KLArea t with (nolock) on t.KLArea_id = LpuBuildingStreet.KLTown_id
		//			left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
		//			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = LpuBuildingStreet.KLStreet_id
		//			left join KLArea c with (nolock) on c.Klarea_id = LpuBuildingStreet.KLCity_id
		//			left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
		//			WHERE {$filter}
		//		";

		$sql = "
			Select
				'1' as Server_id,

				MAX(HCR.LpuBuildingKLHouseCoordsRel_id) as LpuBuildingStreet_id,
				LpuBuilding_id,
				t.KLCountry_id,

				case when t.KLAreaLevel_id = 1 then t.KLArea_id else null end as KLRGN_id,
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else null end as KLSubRGN_id,
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else null end as KLCity_id,
				case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as KLTown_id,

				case when ISNULL(rstat.KLRGN_id, 0) > 0 then rstat.KLRGN_id else
					case when ISNULL(cstat.KLRGN_id, 0) > 0 then cstat.KLRGN_id else
						case when ISNULL(tstat.KLRGN_id, 0) > 0 then tstat.KLRGN_id else null end end end as KLRGN_id,

				t.KLArea_Name as KLTown_Name,
				H.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				H.KLHouse_Name as LpuBuildingStreet_HouseSet
			from LpuBuildingKLHouseCoordsRel HCR with (nolock)
			left join KLHouseCoords HC with (nolock) on (HC.KLHouseCoords_id = HCR.KLHouseCoords_id)
			left join KLArea t with (nolock) on t.KLArea_id = HC.KLArea_id
			left join KLAreaStat rstat with (nolock) on rstat.KLSubRGN_id = HC.KLArea_id
			left join KLAreaStat cstat with (nolock) on cstat.KLCity_id = HC.KLArea_id
			left join KLAreaStat tstat with (nolock) on tstat.KLTown_id = HC.KLArea_id
			left join KLHouse H with (nolock) on H.KLHouse_id = HC.KLHouse_id
			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = H.KLStreet_id
			left join KLSocr ts with (nolock) on ts.KLSocr_id = H.KLSocr_id


			WHERE {$filter} and HC.KLHouse_id is not null

			Group BY

			HC.KLHouse_id,
			H.KLHouse_Name,
			LpuBuilding_id,
			t.KLCountry_id,
			t.KLAreaLevel_id,
			t.KLArea_id,
			rstat.KLRGN_id,
			cstat.KLRGN_id,
			tstat.KLRGN_id,
			t.KLArea_Name,
			H.KLStreet_id,
			KLStreet_FullName

			UNION

			Select
				'1' as Server_id,

				HCR.LpuBuildingKLHouseCoordsRel_id as LpuBuildingStreet_id,
				LpuBuilding_id,
				t.KLCountry_id,

				case when t.KLAreaLevel_id = 1 then t.KLArea_id else null end as KLRGN_id,
				case when t.KLAreaLevel_id = 2 then t.KLArea_id else null end as KLSubRGN_id,
				case when t.KLAreaLevel_id = 3 then t.KLArea_id else null end as KLCity_id,
				case when t.KLAreaLevel_id = 4 then t.KLArea_id else null end as KLTown_id,

				case when ISNULL(rstat.KLRGN_id, 0) > 0 then rstat.KLRGN_id else
					case when ISNULL(cstat.KLRGN_id, 0) > 0 then cstat.KLRGN_id else
						case when ISNULL(tstat.KLRGN_id, 0) > 0 then tstat.KLRGN_id else null end end end as KLRGN_id,

				t.KLArea_Name as KLTown_Name,
				HC.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				HC.KLHouseCoords_Name as LpuBuildingStreet_HouseSet
			from LpuBuildingKLHouseCoordsRel HCR with (nolock)
			left join KLHouseCoords HC with (nolock) on (HC.KLHouseCoords_id = HCR.KLHouseCoords_id)
			left join KLArea t with (nolock) on t.KLArea_id = HC.KLArea_id

			left join KLAreaStat rstat with (nolock) on rstat.KLSubRGN_id = HC.KLArea_id
			left join KLAreaStat cstat with (nolock) on cstat.KLCity_id = HC.KLArea_id
			left join KLAreaStat tstat with (nolock) on tstat.KLTown_id = HC.KLArea_id

			left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = HC.KLStreet_id



			WHERE {$filter} and HC.KLHouse_id is null

			ORDER BY KLStreet_Name

		";
			//echo getDebugSql($sql, array());
		//exit;

		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение тарифов на отделение
	*/
	function getLpuSectionTariff($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['LpuSectionTariff_id'])) && (is_numeric($data['LpuSectionTariff_id'])))
			$filter .= " and LpuSectionTariff_id=".$data['LpuSectionTariff_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LpuSectionTariff_disDate is null or LpuSectionTariff_disDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LpuSectionTariff_disDate <= dbo.tzGetDate()";
		}

		if ( strlen($filter) == 6) {
			if ((isset($data['Server_id'])) && ($data['Server_id']>0))
			{
				$filter .= " and Server_id=".$data['Server_id'];
			}
			elseif ($data['session']['server_id']>0)
			{
				$filter .= " and Server_id=".$data['session']['server_id'];
			}
		}

		$sql = "
			Select
				Server_id,
				LpuSectionTariff_id,
				LpuSection_id,
				LpuSectionTariff.TariffClass_id,
				RTrim(TariffClass_Name) as TariffClass_Name,
				LpuSectionTariff_Tariff,
				LpuSectionTariff_TotalFactor,
				convert(varchar,cast(LpuSectionTariff_setDate as datetime),104) as LpuSectionTariff_setDate,
				convert(varchar,cast(LpuSectionTariff_disDate as datetime),104) as LpuSectionTariff_disDate
			from LpuSectionTariff with (nolock)
			left join TariffClass with (nolock) on TariffClass.TariffClass_id = LpuSectionTariff.TariffClass_id
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Функция чтения справочника профилей, по которым заведены отделения в структуре МО
	*/
	function getLpuSectionProfile($data)
	{
		$filter = "(1=1) ";
		$join = '';

		switch ($data['LpuUnitType_id']) {
			case 13:
				$filter .= " and lsp.LpuSectionProfile_Code in ('84')"; // Профили для СМП
			break;
			case 2: // https://redmine.swan.perm.ru/issues/20265
				$join = " left join v_LpuSection ls with (nolock) on ls.LpuSectionProfile_id = lsp.LpuSectionProfile_id and ls.Lpu_id = :Lpu_id ";
				$filter .= " and (ls.LpuSection_id is not null or lsp.LpuSectionProfile_Code in ('917', '1017', '1019')) "; // Профили для ДД
			break;
			default:
				$join .= " inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSectionProfile_id = lsp.LpuSectionProfile_id ";
			break;
		}

		if (!empty($data['isProfileSpecCombo'])) {
			$filter .= "
				and lsp.ProfileSpec_Name is not null
				and lsp.LpuSectionProfile_InetDontShow is null
				and isnull(msf.RecType_id, 6) not in (2,5,6,8)
				and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
			";

			$join .= "
				inner join v_MedStaffFact msf with (nolock) on msf.LpuSection_id = ls.LpuSection_id
			";
		}

		$sql = "
			Select distinct
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name,
				lsp.ProfileSpec_Name
			from v_LpuSectionProfile lsp with (nolock)
			{$join}
			where {$filter}
			order by LpuSectionProfile_Code
		";
		//echo getDebugSQL($sql, $data);die();
		$res = $this->db->query($sql, $data);
		
		if (is_object($res)) return $res->result('array');
		else return false;

		/*
		 select
                rtrim(ProfileSpec_Name) as ProfileSpec_Name,
                LpuSectionProfile_id,
				LpuSectionProfile_Code,
				LpuSectionProfile_IsArea
            from v_LpuSectionProfile lsp with (nolock)
            where
                nullif(ProfileSpec_Name, '') is not null
				and LpuSectionProfile_InetDontShow is null
                and LpuSectionProfile_mainid is null
				and LpuSectionProfile_id in (
					select distinct isnull(lsp.lpusectionprofile_mainid, ls.LpuSectionProfile_id) as LpuSectionProfile_id
					from v_MedStaffFact msf with (nolock)
						left join v_LpuUnit_ER lu with (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
						left join v_Lpu l with (nolock) on lu.Lpu_id = l.Lpu_id
						left join v_Address a with (nolock) on lu.Address_id = a.Address_id
						left join v_LpuSection ls with (nolock) on msf.LpuSection_id = ls.LpuSection_id
						inner join v_LpuSectionProfile lsp on lsp.lpusectionprofile_id = ls.lpusectionprofile_id
					where
						lu.Lpu_id = :lpu_id
						and isnull(msf.RecType_id, 6) not in (2,5,6,8)
						and (isnull(msf.WorkData_endDate, '2030-01-01') > dbo.tzGetDate())
						and ISNULL(l.Lpu_IsTest, 1) = 1
						and lu.LpuUnit_Enabled = 1
						and isnull(l.Lpu_endDate, '2030-01-01') >= getdate()
						{$patient_age_filter}
						{$gyn_filter}
						{$lpu_unit_filter}
				)
            order by ProfileSpec_Name";
		 * */
	}

	/**
	* Получение смен на отделение
	*/
	function getLpuSectionShift($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['LpuSectionShift_id'])) && (is_numeric($data['LpuSectionShift_id'])))
			$filter .= " and LpuSectionShift_id=".$data['LpuSectionShift_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];

		$sql = "
			Select
				Server_id,
				LpuSectionShift_id,
				LpuSection_id,
				LpuSectionShift.LpuSectionShift_Count,
				convert(varchar,cast(LpuSectionShift_setDate as datetime),104) as LpuSectionShift_setDate,
				convert(varchar,cast(LpuSectionShift_disDate as datetime),104) as LpuSectionShift_disDate
			from LpuSectionShift with (nolock)
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение коек на отделение
	*/
	function getLpuSectionBedState($data)
	{
		$filter = "(1=1) ";

		if (!empty($data['LpuSectionBedState_id'])){
			$filter .= " and LSBS.LpuSectionBedState_id = :LpuSectionBedState_id";
		}

		if (!empty($data['LpuSection_id'])){
			$filter .= " and LSBS.LpuSection_id = :LpuSection_id";
		}

		if (isset($data['is_Act'])) {
			$filter .= " and LSBS.LpuSectionBedState_begDate <= dbo.tzGetDate()
						and (LSBS.LpuSectionBedState_endDate >= dbo.tzGetDate() or LSBS.LpuSectionBedState_endDate is null)
			";
		}

		$sql = "
			Select
				LSBS.Server_id,
				LSBS.LpuSectionBedState_id,
				LSBS.LpuSection_id,
				LSBS.LpuSectionBedState_ProfileName,
				LSBS.LpuSectionBedState_Plan,
				LSBS.LpuSectionBedState_Fact,
				LSBS.LpuSectionBedState_Repair,
				LSP.LpuSectionProfile_id,
				LSBP.LpuSectionBedProfile_id,
				LSBS.LpuSectionBedState_CountOms,
				ISNULL(LSP.LpuSectionProfile_Name, LSBP.LpuSectionBedProfile_Name) as LpuSectionProfile_Name, -- для Самары профили коек. (refs #16934)
				convert(varchar,cast(LSBS.LpuSectionBedState_begDate as datetime),104) as LpuSectionBedState_begDate,
				convert(varchar,cast(LSBS.LpuSectionBedState_endDate as datetime),104) as LpuSectionBedState_endDate,
				LSBS.LpuSectionBedState_MalePlan,
				LSBS.LpuSectionBedState_MaleFact,
				LSBS.LpuSectionBedState_FemalePlan,
				LSBS.LpuSectionBedState_FemaleFact,
				LSBS.LpuSectionBedProfileLink_fedid as LpuSectionBedProfileLink_id
			from v_LpuSectionBedState LSBS with (nolock)
				left join v_LpuSectionProfile LSP with (nolock) on LSP.LpuSectionProfile_id = LSBS.LpuSectionProfile_id
				left join v_LpuSectionBedProfile LSBP with (nolock) on LSBP.LpuSectionBedProfile_id = LSBS.LpuSectionBedProfile_id
			WHERE {$filter}
		";

		//echo getDebugSQL($sql, $data);die;
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение финансирования на отделение
	*/
	function getLpuSectionFinans($data)
	{
		$filter = "(1=1) ";

		/*if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and lsf.Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and lsf.Server_id = ".$data['session']['server_id'];
		}*/

		if ((isset($data['LpuSectionFinans_id'])) && (is_numeric($data['LpuSectionFinans_id'])))
			$filter .= " and lsf.LpuSectionFinans_id = ".$data['LpuSectionFinans_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and lsf.LpuSection_id = ".$data['LpuSection_id'];

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (lsf.LpuSectionFinans_endDate is null or lsf.LpuSectionFinans_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and lsf.LpuSectionFinans_endDate <= dbo.tzGetDate()";
		}

		$sql = "
			Select
				lsf.Server_id,
				lsf.LpuSectionFinans_id,
				lsf.LpuSection_id,
				lsf.PayType_id,
				RTrim(pt.PayType_Name) as PayType_Name,
				lsf.LpuSectionFinans_Plan,
				lsf.LpuSectionFinans_PlanHosp,
				lsf.LpuSectionFinans_IsMRC,
				IsNull(mrc.YesNo_Name,'') as IsMRC_Name,
				IsNull(qoff.YesNo_Name,'') as IsQuoteOff_Name,
				lsf.LpuSectionFinans_IsQuoteOff,
				convert(varchar,cast(lsf.LpuSectionFinans_begDate as datetime),104) as LpuSectionFinans_begDate,
				convert(varchar,cast(lsf.LpuSectionFinans_endDate as datetime),104) as LpuSectionFinans_endDate
			from LpuSectionFinans lsf with (nolock)
				left join v_PayType pt with (nolock) on pt.PayType_id = lsf.PayType_id
				left join YesNo mrc with (nolock) on mrc.YesNo_id = lsf.LpuSectionFinans_IsMRC
				left join YesNo qoff with (nolock) on qoff.YesNo_id = lsf.LpuSectionFinans_IsQuoteOff
			WHERE {$filter}
		";
		//print $sql;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение лицензий на отделение
	*/
	function getLpuSectionLicence($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['LpuSectionLicence_id'])) && (is_numeric($data['LpuSectionLicence_id'])))
			$filter .= " and LpuSectionLicence_id=".$data['LpuSectionLicence_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LpuSectionLicence_endDate is null or LpuSectionLicence_endDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LpuSectionLicence_endDate <= dbo.tzGetDate()";
		}

		$sql = "
			Select
				Server_id,
				LpuSectionLicence_id,
				LpuSection_id,
				RTrim(LpuSectionLicence_Num) as LpuSectionLicence_Num,
				convert(varchar,cast(LpuSectionLicence_begDate as datetime),104) as LpuSectionLicence_begDate,
				convert(varchar,cast(LpuSectionLicence_endDate as datetime),104) as LpuSectionLicence_endDate
			from LpuSectionLicence with (nolock)
			WHERE {$filter}
		";
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение тарифов МЭС
	*/
	function getLpuSectionTariffMes($data)
	{
		$filter = "(1=1) ";

		if ((isset($data['LpuSectionTariffMes_id'])) && (is_numeric($data['LpuSectionTariffMes_id'])))
			$filter .= " and LpuSectionTariffMes_id = ".$data['LpuSectionTariffMes_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id = ".$data['LpuSection_id'];

		$sql = "
			Select
				tm.LpuSectionTariffMes_id,
				tm.LpuSection_id,
				tm.Mes_id,
				m.Mes_Code,
				d.Diag_Name,
				tm.TariffMesType_id,
				tmt.TariffMesType_Name,
				tm.LpuSectionTariffMes_Tariff,
				convert(varchar,cast(tm.LpuSectionTariffMes_setDate as datetime),104) as LpuSectionTariffMes_setDate,
				convert(varchar,cast(tm.LpuSectionTariffMes_disDate as datetime),104) as LpuSectionTariffMes_disDate
			from LpuSectionTariffMes tm with (nolock)
				left join MesOld m with(nolock) on tm.Mes_id = m.Mes_id
				left join Diag d with(nolock) on m.Diag_id = d.Diag_id
				left join TariffMesType tmt with(nolock) on tm.TariffMesType_id = tmt.TariffMesType_id
			WHERE {$filter}
		"; //print $sql;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	* Получение информации о палнировании
	*/
	function getLpuSectionPlan($data)
	{
		$filter = "(1=1) and p.LpuSectionPlan_PlanHosp is null "; //следовательно запись не относится к плану госпитализаций

		if ((isset($data['LpuSectionPlan_id'])) && (is_numeric($data['LpuSectionPlan_id'])))
			$filter .= " and LpuSectionPlan_id = ".$data['LpuSectionPlan_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id = ".$data['LpuSection_id'];

		$sql = "
			Select
				p.LpuSectionPlan_id,
				p.LpuSection_id,
				p.LpuSectionPlanType_id,
				pt.LpuSectionPlanType_Name,
				convert(varchar,cast(p.LpuSectionPlan_setDate as datetime),104) as LpuSectionPlan_setDate,
				convert(varchar,cast(p.LpuSectionPlan_disDate as datetime),104) as LpuSectionPlan_disDate
			from LpuSectionPlan p with (nolock)
				left join LpuSectionPlanType pt with(nolock) on p.LpuSectionPlanType_id = pt.LpuSectionPlanType_id
			WHERE {$filter}
		"; //print $sql;
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получает сумму фактически выполненного муниципального заказа из реестров (поэтому запрос должен выполняться на реестровой базе).
	 * Возвращает массив вида array('LpuSectionQuote_Fact'=>'1')
	 *
	 * @access public
	 * @param array $data Массив входящих данных для фильтрации запроса
	 *
	 * @return array Возвращает ассоциативный массив c индексом 'LpuSectionQuote_Fact'.
	 */

	function getLpuSectionQuoteFact($data)
	{
		$filter = "(1=1) ";
		if ($data['LpuUnitType_id']==2)
			$filter .= " and LpuUnit.LpuUnitType_id in (2, 10) ";
		else
			$filter .= " and LpuUnit.LpuUnitType_id = :LpuUnitType_id ";

		$sql = "
			select
				isnull(sum(registrydata_kdfact), 0) as LpuSectionQuote_Fact
			from v_Registry Registry with (NOLOCK)
			 inner join v_RegistryData RegistryData with (NOLOCK) on Registry.Registry_id = RegistryData.Registry_id and isnull(RegistryData.RegistryData_IsPrev, 1) = 1
			 inner join LpuSection with (NOLOCK) on LpuSection.LpuSection_id = RegistryData.LpuSection_id
			 and LpuSection.LpuSectionProfile_id = :LpuSectionProfile_id
			 inner join LpuUnit with (NOLOCK) on LpuUnit.LpuUnit_id = LpuSection.LpuUnit_id and {$filter}
			 where Registry.Lpu_id = :Lpu_id
			 and datepart(year, Registry.Registry_endDate) = :LpuSectionQuote_Year
			 and Registry.KatNasel_id = 1
			 and Registry.RegistryType_id = :RegistryType_id
			 and Registry.RegistryStatus_id = 4
		";
		/*
		echo getDebugSql($sql, $data);
		exit;
		*/
		$res = $this->db->query($sql, $data);
		if (is_object($res))
		{
			return $res->result('array');
		}
		else
			return false;
	}

    /**
     * Это Doc-блок
     */
	function getLpuSectionQuote($data)
	{
		$filter = "(1=1) ";
		if ($data['LpuSectionQuote_id']>0)
			$filter .= " and Quote.LpuSectionQuote_id = :LpuSectionQuote_id ";

		if ($data['Lpu_id']>0)
			$filter .= " and Quote.Lpu_id = :Lpu_id";

		if (!empty($data['LpuSectionQuote_Year'])) {
			$filter .= " and Quote.LpuSectionQuote_Year = :LpuSectionQuote_Year";
		}

		if (!empty($data['LpuUnitType_id'])) {
			$filter .= " and Quote.LpuUnitType_id = :LpuUnitType_id";
		}

		if (!empty($data['LpuSectionProfile_id'])) {
			$filter .= " and Quote.LpuSectionProfile_id = :LpuSectionProfile_id";
		}

		if (!empty($data['PayType_id'])) {
			$filter .= " and Quote.PayType_id = :PayType_id";
		}

		$sql = "
			Select
				Quote.LpuSectionQuote_id,
				Quote.Lpu_id,
				Quote.LpuSectionQuote_Year,
				Quote.LpuSectionQuote_Count,
				convert(varchar,cast(Quote.LpuSectionQuote_begDate as datetime),104) as LpuSectionQuote_begDate,
				Quote.LpuUnitType_id,
				Quote.LpuSectionProfile_id,
				RTrim(LpuUnitType.LpuUnitType_Name) as LpuUnitType_Name,
				RTrim(LpuSectionProfile.LpuSectionProfile_Name) as LpuSectionProfile_Name,
				case
					when Quote.LpuUnitType_id in (2,10) then 2
					when Quote.LpuUnitType_id in (1,6,7,9) then 1
				end as RegistryType_id,
				Quote.PayType_id,
				PayType.PayType_Name,
				Quote.QuoteUnitType_id,
				QuoteUnitType.QuoteUnitType_Name
			from v_LpuSectionQuote Quote with (nolock)
				left join LpuSectionProfile with (nolock) on LpuSectionProfile.LpuSectionProfile_id = Quote.LpuSectionProfile_id
				left join LpuUnitType with (nolock) on LpuUnitType.LpuUnitType_id = Quote.LpuUnitType_id
				left join PayType with (nolock) on PayType.PayType_id = Quote.PayType_id
				left join QuoteUnitType with (nolock) on QuoteUnitType.QuoteUnitType_id = Quote.QuoteUnitType_id
			WHERE {$filter}
		";
		$res = $this->db->query($sql, $data);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function GetPersonDopDispPlan($data)
	{
		$filter = "(1=1) ";
		if (!empty($data['PersonDopDispPlan_id'])) {
			$filter .= " and PDDP.PersonDopDispPlan_id = :PersonDopDispPlan_id ";
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and PDDP.Lpu_id = :Lpu_id";
		}

		if (!empty($data['PersonDopDispPlan_Year'])) {
			$filter .= " and PDDP.PersonDopDispPlan_Year = :PersonDopDispPlan_Year";
		}

		if (!empty($data['DispDopClass_id'])) {
			$filter .= " and PDDP.DispDopClass_id = :DispDopClass_id";
		}

		$sql = "
			Select
				PDDP.PersonDopDispPlan_id,
				PDDP.Lpu_id,
				PDDP.LpuRegion_id,
				LR.LpuRegion_Name,
				PDDP.DispDopClass_id,
				PDDP.PersonDopDispPlan_Year,
				PDDP.PersonDopDispPlan_Month,
				PDDP.PersonDopDispPlan_Month as PersonDopDispPlan_MonthName,
				PDDP.EducationInstitutionType_id,
				PDDP.QuoteUnitType_id,
				EIT.EducationInstitutionType_Name,
				QUT.QuoteUnitType_Name,
				PDDP.PersonDopDispPlan_Plan,
				puc.pmUser_groups as groups
			from v_PersonDopDispPlan PDDP (nolock)
				left join v_LpuRegion LR (nolock) on LR.LpuRegion_id = PDDP.LpuRegion_id
				left join pmUserCache puc (nolock) on puc.pmUser_id = PDDP.pmUser_updID
				left join v_EducationInstitutionType EIT (nolock) on eit.EducationInstitutionType_id = PDDP.EducationInstitutionType_id
				left join v_QuoteUnitType QUT (nolock) on QUT.QuoteUnitType_id = PDDP.QuoteUnitType_id
			WHERE {$filter}
		";

		$arMonthOf = array(
			1 => "Январь",
			2 => "Февраль",
			3 => "Март",
			4 => "Апрель",
			5 => "Май",
			6 => "Июнь",
			7 => "Июль",
			8 => "Август",
			9 => "Сентябрь",
			10 => "Октябрь",
			11 => "Ноябрь",
			12 => "Декабрь"
		);

		$res = $this->db->query($sql, $data);
		if (is_object($res)) {
			$resp = $res->result('array');
			foreach ($resp as &$item) {
				if (isset($arMonthOf[$item['PersonDopDispPlan_Month']])) {
					$item['PersonDopDispPlan_MonthName'] = $arMonthOf[$item['PersonDopDispPlan_Month']];
				} else {
					$item['PersonDopDispPlan_MonthName'] = '';
				}
			}
			return $resp;
		} else {
			return false;
		}
	}

	/**
	 * Получение значения флага работы по ОМС для отделения или группы отделений
	 */
	function getLpuUnitIsOMS($data) {
		$join = "";
		$where = "(1=1)";
		$params = array();
		if (!empty($data['LpuSection_id'])) {
			$join .= " inner join LpuSection LS with(nolock) on LS.LpuUnit_id = LU.LpuUnit_id";
			$where .= " and LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['LpuUnit_id'])) {
			$where .= " and LU.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}

		$query = "
			select
				case
					when LU.LpuUnit_IsOMS is null then 1 else YN.YesNo_Code
				end as LpuUnit_IsOMS
			from v_LpuUnit LU with(nolock)
			left join v_YesNo YN with(nolock) on YN.YesNo_id = LU.LpuUnit_IsOMS
				{$join}
			where
				{$where}
		";

		$result = $this->db->query($query, $params);
		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных'));
		}
	}

	/*
	function checkLpuSectionBedState($data)
	{
		$filter = '
				or
				(
				(LpuSectionBedState_endDate is null OR cast(LpuSectionBedState_endDate as date) >= :LpuSectionBedState_endDate)
				and cast(LpuSectionBedState_begDate as date) <= :LpuSectionBedState_endDate
				)';
		if (!empty($data['LpuSectionBedState_endDate']))
		{
			$filter = '	and LpuSectionBedState_id != :LpuSectionBedState_id';
		}
		$sql = "
		select top 1 LpuSectionBedState_id
		from v_LpuSectionBedState with (nolock)
		where
			Server_id = :Server_id
			and LpuSection_id = :LpuSection_id
			and (
				(
				cast(LpuSectionBedState_begDate as date) <= :LpuSectionBedState_begDate
				and (LpuSectionBedState_endDate is null OR cast(LpuSectionBedState_endDate as date) >= :LpuSectionBedState_begDate)
				)
				{$filter}
			)
		";
		if (isset($data['LpuSectionBedState_id']))
		{
			$sql .= '	and LpuSectionBedState_id != :LpuSectionBedState_id';
		}
		$res = $this->db->query($sql,$data);
		//echo getDebugSQL($sql, $data);exit;
		if (is_object($res) )
	 		return $res->result('array');
	 	else
	 	 	return false;
	}
	*/
	// получаем данные по плановым койкам отделения-родителя и его подотделений
    /**
     * Это Doc-блок
     */
	function getLpuSectionBedStatePlan($data)
	{
		if ($data['LpuSection_isParent'])
		{
			$queryParams = array('LpuSection_id' => $data['LpuSection_id']);
		}
		else
		{
			$queryParams = array('LpuSection_id' => $data['LpuSection_pid']);
		}
		$sql = "
			SELECT
				LpuSection.LpuSection_id,
				LpuSection.LpuSection_pid,
				LSBS.LpuSectionBedState_Plan
			FROM
				v_LpuSection LpuSection with (nolock)
				left join v_LpuSectionBedState LSBS with (nolock) on LpuSection.LpuSection_id = LSBS.LpuSection_id AND LSBS.LpuSectionBedState_isAct = 2
			WHERE
				LpuSection.LpuSection_id = :LpuSection_id
				OR
				LpuSection.LpuSection_pid = :LpuSection_id
		";
		$res = $this->db->query($sql,$queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка атрибутов отделения
	 */
	protected function _checkLpuSectionAttributeSignValue($data) {
		$this->load->model('Attribute_model');

		//Проверка соответсвия дат атрибутов и отделения
		$resp = $this->queryResult("
			select
				convert(varchar(10), ASV.AttributeSignValue_begDate, 120) as AttributeSignValue_begDate
			from
				v_AttributeSignValue ASV with(nolock)
				inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where
				[AS].AttributeSign_TableName = 'dbo.LpuSection'
				and ASV.AttributeSignValue_TablePKey = :LpuSection_id
		", array('LpuSection_id' => $data['LpuSection_id']));
		if (!is_array($resp)) {
			return "Ошибка при запросе сохраненных признаков атрибутов";
		}
		$LpuSection_setDate = DateTime::createFromFormat('Y-m-d H:i', $data['LpuSection_setDate'].' 00:00');
		$LpuSection_disDate = !empty($data['LpuSection_disDate'])?DateTime::createFromFormat('Y-m-d H:i', $data['LpuSection_disDate'].' 00:00'):null;
		foreach($resp as $item) {
			$AttributeSignValue_begDate = DateTime::createFromFormat('Y-m-d H:i', $item['AttributeSignValue_begDate'].' 00:00');
			if ($AttributeSignValue_begDate < $LpuSection_setDate) {
				return "Начало действия значений атрибутов не может быть раньше даты создания отделения";
			}
			if (!empty($LpuSection_disDate) && !empty($item['AttributeSignValue_endDate'])) {
				$AttributeSignValue_endDate = DateTime::createFromFormat('Y-m-d H:i', $item['AttributeSignValue_endDate'].' 00:00');
				if ($AttributeSignValue_endDate > $LpuSection_disDate) {
					return "Окончание действия значений атрибутов не может быть позже даты закрытия отделения";
				}
			}
		}

		//Проверка наличия обязательных атрибутов отделения
		$requiredAttributeSysNickList = array();
		if ($this->regionNick == 'perm') {
			$requiredAttributeSysNickList = array('Section_Code','Building_Code','Section_Name','Building_Name','StructureUnitNomen');
		}
		if (count($requiredAttributeSysNickList) > 0) {
			$requiredAttributeSysNickList_str = "'".implode("','", $requiredAttributeSysNickList)."'";
			$resp = $this->queryResult("
				select
					Attribute_id,
					Attribute_Code,
					Attribute_Name
				from v_Attribute with(nolock)
				where Attribute_SysNick in ({$requiredAttributeSysNickList_str})
			");
			if (!is_array($resp)) {
				return "Ошибка при запросе данных обязательных атрибутов";
			}

			$missedAttributes = array();
			foreach($resp as $item) {
				$key = $item['Attribute_id'];
				$missedAttributes[$key] = $item;
			}

			$query = "
				declare @date date = dbo.tzGetDate();
				set @date = case
					when :LpuSection_disDate is not null then :LpuSection_disDate
					when @date <= :LpuSection_setDate then :LpuSection_setDate
					else @date
				end;
				select
					AV.Attribute_id
				from
					v_AttributeValue AV with(nolock)
					inner join v_AttributeSignValue ASV with(nolock) on ASV.AttributeSignValue_id = AV.AttributeSignValue_id
					inner join v_AttributeSign [AS] with(nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
				where
					[AS].AttributeSign_TableName = 'dbo.LpuSection'
					and ASV.AttributeSignValue_TablePKey = :LpuSection_id
					and ASV.AttributeSignValue_begDate <= @date
					and (ASV.AttributeSignValue_endDate is null or ASV.AttributeSignValue_endDate >= @date)
			";
			$resp = $this->queryResult($query, array(
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSection_setDate' => $data['LpuSection_setDate'],
				'LpuSection_disDate' => !empty($data['LpuSection_disDate'])?$data['LpuSection_disDate']:null
			));
			if (!is_array($resp)) {
				return "Ошибка при запросе сохраненных атрибутов";
			}
			foreach($resp as $item) {
				$key = $item['Attribute_id'];
				if (isset($missedAttributes[$key])) {
					unset($missedAttributes[$key]);
				}
			}

			if (count($missedAttributes) > 0) {
				$attributeNameList = array();
				foreach($missedAttributes as $item) {
					$attributeNameList[] = $item['Attribute_Name'];
				}
				$attributeNameList_str = implode(", ", $attributeNameList);

				return "На отделении отсутствуют обязательные атрибуты – {$attributeNameList_str}.";
			}
		}
		return "";
	}

    /**
     * Это Doc-блок
     */
	function checkLpuSectionFinans($data) {
		$filterList = array();
		$queryParams = array();

		if ( isset($data['Server_id']) && $data['Server_id'] > 0 ) {
			$filterList[] = "Server_id = :Server_id";
			$queryParams['Server_id'] = $data['Server_id'];
		}
		else if ( $data['session']['server_id'] > 0 ) {
			$filterList[] = "Server_id = :Server_id";
			$queryParams['Server_id'] = $data['session']['server_id'];
		}

		if ( !empty($data['LpuSectionFinans_id']) && is_numeric($data['LpuSectionFinans_id']) ) {
			$filterList[] = "LpuSectionFinans_id != :LpuSectionFinans_id";
			$queryParams['LpuSectionFinans_id'] = $data['LpuSectionFinans_id'];
		}

		if ( !empty($data['LpuSection_id']) && is_numeric($data['LpuSection_id']) ) {
			$filterList[] = "LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if ( !empty($data['LpuSectionFinans_begDate']) ) {
			$filterList[] = "LpuSectionFinans_begDate = :LpuSectionFinans_begDate";
			$queryParams['LpuSectionFinans_begDate'] = $data['LpuSectionFinans_begDate'];
		}

		$query = "
			select top 1 LpuSectionFinans_id
			from LpuSectionFinans with (nolock)
			where " . implode(' and ', $filterList) . "
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
	 		return $res->result('array');
		}
	 	else {
	 		return true;
		}
	}

    /**
     * Это Doc-блок
     */
	function checkLpuSectionTariff($data)
	{
		$this->load->helper('Date');
		$filter = "(1=1) ";
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['LpuSectionTariff_id'])) && (is_numeric($data['LpuSectionTariff_id'])))
			$filter .= " and LpuSectionTariff_id!=".$data['LpuSectionTariff_id'];

		if ((isset($data['TariffClass_id'])) && (is_numeric($data['TariffClass_id'])))
			$filter .= " and TariffClass_id=".$data['TariffClass_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];
		else
			return false;
		if (empty($data['LpuSectionTariff_setDate']))
			return false;
		else
			$filter .= " and LpuSectionTariff_setDate="."'".ConvertDateFormat(trim($data['LpuSectionTariff_setDate']))."'";;

		$sql = "
		select 1
		from LpuSectionTariff with (nolock)
		where
			{$filter}
		";

		$res = $this->db->query($sql);
		if (is_object($res) )
	 		return $res->result('array');
	 	else
	 	 	return true;
	}

    /**
     * Это Doc-блок
     */
	function checkLpuSectionLicence($data)
	{
		$this->load->helper('Date');
		$filter = "(1=1) ";
		if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= " and Server_id=".$data['Server_id'];
		}
		elseif ($data['session']['server_id']>0)
		{
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if ((isset($data['LpuSectionLicence_id'])) && (is_numeric($data['LpuSectionLicence_id'])))
			$filter .= " and LpuSectionLicence_id!=".$data['LpuSectionLicence_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];
		else
			return false;
		if (empty($data['LpuSectionLicence_begDate']))
			return false;
		else
			$filter .= " and LpuSectionLicence_begDate="."'".ConvertDateFormat(trim($data['LpuSectionLicence_begDate']))."'";;

		$sql = "
		select 1
		from LpuSectionLicence with (nolock)
		where
			{$filter}
		";

		$res = $this->db->query($sql);
		if (is_object($res) )
	 		return $res->result('array');
	 	else
	 	 	return true;
	}
    /**
     * Это Doc-блок
     */
	function checkLpuSectionShift($data) {

		$this->load->helper('Date');
		$filter = "(1=1) ";

		if ((isset($data['Server_id'])) && ($data['Server_id'] > 0)) {
			$filter .= " and Server_id=".$data['Server_id'];
		} elseif ($data['session']['server_id'] > 0) {
			$filter .= " and Server_id=".$data['session']['server_id'];
		}

		if (
			(isset($data['LpuSectionShift_id']))
			&& (is_numeric($data['LpuSectionShift_id']))
		) {
			$filter .= " and LpuSectionShift_id!=" . $data['LpuSectionShift_id'];
		}

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];
		else
			return array('Error_Msg' => 'Не указано отделение');

		if (empty($data['LpuSectionShift_setDate']))
			return array('Error_Msg' => 'Не указана дата начала смены коек');
		else
			$filter .= " and LpuSectionShift_setDate="."'".ConvertDateFormat(trim($data['LpuSectionShift_setDate']))."'";;

		$sql = "
		select 1
		from
			LpuSectionShift as LSS with (nolock)
		where
			{$filter}
		";

		$res = $this->db->query($sql);

		if (is_object($res)) {

			$result = $res->result('array');

			if (!empty($result[0]) && count($result[0]) > 0)
				return array('Error_Msg' => 'Существуют ранее введенное количество смен по этому отделению с той же датой начала');
			else
				return false;
		} else
			return array('Error_Msg' => 'Ошибка запроса к БД');
	}
    /**
     * Это Doc-блок
     */
	function checkLpuSectionPlan($data)
	{
		$this->load->helper('Date');
		$filter = "(1=1) and LpuSectionPlan_PlanHosp is null "; //следовательно запись не относится к плану госпитализаций

		if ((isset($data['LpuSectionPlan_id'])) && (is_numeric($data['LpuSectionPlan_id'])))
			$filter .= " and LpuSectionPlan_id!=".$data['LpuSectionPlan_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];
		else
			return false;
		if (empty($data['LpuSectionPlan_setDate']))
			return false;
		else
			$filter .= " and LpuSectionPlan_setDate="."'".ConvertDateFormat(trim($data['LpuSectionPlan_setDate']))."'";;

		$sql = "
		select 1
		from LpuSectionPlan with (nolock)
		where
			{$filter}
		";

		$res = $this->db->query($sql);
		if (is_object($res) )
	 		return $res->result('array');
	 	else
	 	 	return true;
	}
    /**
     * Это Doc-блок
     */
	function checkUslugaSection($data)
	{
		$this->load->helper('Date');
		$filter = "(1=1) "; //следовательно запись не относится к плану госпитализаций

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=:LpuSection_id";
		else
			return false;

		if ((isset($data['Usluga_id'])) && (is_numeric($data['Usluga_id'])))
			$filter .= " and Usluga_id=:Usluga_id";
		else
			return false;

		if ((isset($data['UslugaSection_id'])) && (is_numeric($data['UslugaSection_id'])))
			$filter .= " and UslugaSection_id!=:UslugaSection_id";
		if ((isset($data['UslugaSection_Code'])) && (is_numeric($data['UslugaSection_Code'])))
			$filter .= " and UslugaSection_Code=:UslugaSection_Code";
		/*

		if ((isset($data['UslugaPrice_ue'])) && (is_numeric($data['UslugaPrice_ue'])))
			$filter .= " and UslugaPrice_ue=:UslugaPrice_ue";
		*/
		$sql = "
		select count(*) as rec
		from UslugaSection with (nolock)
		where
			{$filter}
		";

		$r = $this->db->query($sql, $data)->result_array();
		if (count($r)>0)
		{
	 		return ($r[0]['rec']==0);
		}
	 	else
	 	 	return true;
	}
    /**
     * Это Doc-блок
     */
	function checkLpuSectionTariffMes($data)
	{
		$this->load->helper('Date');
		$filter = "(1=1) ";

		if ((isset($data['LpuSectionTariffMes_id'])) && (is_numeric($data['LpuSectionTariffMes_id'])))
			$filter .= " and LpuSectionTariffMes_id!=".$data['LpuSectionTariffMes_id'];

		if ((isset($data['LpuSection_id'])) && (is_numeric($data['LpuSection_id'])))
			$filter .= " and LpuSection_id=".$data['LpuSection_id'];
		else
			return false;
		if (empty($data['LpuSectionTariffMes_setDate']))
			return false;
		else
			$filter .= " and LpuSectionTariffMes_setDate="."'".ConvertDateFormat(trim($data['LpuSectionTariffMes_setDate']))."'";
		if (empty($data['Mes_id']))
			return false;
		else
			$filter .= " and Mes_id="."'".ConvertDateFormat(trim($data['Mes_id']))."'";;

		$sql = "
		select 1
		from LpuSectionTariffMes with (nolock)
		where
			{$filter}
		";

		$res = $this->db->query($sql);
		if (is_object($res) )
	 		return $res->result('array');
	 	else
	 	 	return true;
	}
    /**
     * Это Doc-блок
     */
	function GetLpuUnitTypeList($data)
	{
		$sql = "
			SELECT
			*
			FROM v_LpuUnitType with (nolock)
			ORDER BY LpuUnitType_Code";
		$res = $this->db->query($sql);
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function getLpuSectionGrid($data)
	{
		$filter = '';
		$UDType_fid = '';
		$addselect = '';
		$addjoin = '';
		$queryParams = array();
		if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id']>0))
		{
			$filter .= 'and LpuUnit_id = :LpuUnit_id ';
			$queryParams['LpuUnit_id'] = $data['LpuUnit_id'];
			$UDType_fid = ",
				(
					SELECT
						LU.UnitDepartType_fid
					FROM
						v_LpuUnit LU with (nolock)
					WHERE LpuUnit_id = :LpuUnit_id
				) as UnitDepartType_fid ";
		}
		if ((isset($data['LpuSection_pid'])) && ($data['LpuSection_pid']>0))
		{
			$filter .= 'and LpuSection_pid = :LpuSection_pid ';
			$queryParams['LpuSection_pid'] = $data['LpuSection_pid'];
		} else {
			$filter .= 'and LpuSection_pid is null';
		}
		
		if($this->getRegionNick() == 'kz') {
			$addselect .= ", FP.NameRU + ' (' + FP.CodeRu + ')' as FPID";
			$addjoin .= '
				left join r101.LpuSectionFPIDLink lsfl (nolock) on lsfl.LpuSection_id = LpuSection.LpuSection_id
				left join r101.GetFP FP (nolock) on FP.FPID = lsfl.FPID
			';
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LpuSection.LpuSection_disDate is null or LpuSection.LpuSection_disDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LpuSection.LpuSection_disDate <= dbo.tzGetDate()";
		}
		$sql = "
			SELECT
				LpuSection.LpuSection_id,
				LpuSection.LpuSection_pid,
				LpuSection.LpuSection_Code,
				LpuSection.LpuSection_Name,
				LSP.LpuSectionProfile_id,
				LSP.LpuSectionProfile_Name,
				LSBS.LpuSectionBedState_id,
				LSBS.LpuSectionBedState_Plan,
				LSBS.LpuSectionBedState_Fact,
				LSBS.LpuSectionBedState_Repair
				{$UDType_fid}
				{$addselect}
			FROM
				v_LpuSection LpuSection with (nolock)
				left join v_LpuSectionProfile LSP with (nolock) on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
				left join v_LpuSectionBedState LSBS with (nolock) on LpuSection.LpuSection_id = LSBS.LpuSection_id AND LSBS.LpuSectionBedState_isAct = 2
				{$addjoin}
			WHERE (1=1) {$filter}
			ORDER BY LpuSection.LpuSection_Code ASC
		";
		$res = $this->db->query($sql,$queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Это Doc-блок
     */
	function getLpuSectionList($data)
	{
		$filter = "(1=1) ";
		$UDType_fid = '';
		$params = array();
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0)) {
			$filter .= "and Lpu_id=:Lpu_id ";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id']>0)) {
			$filter .= "and LpuUnit_id=:LpuUnit_id ";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
			$UDType_fid = ",
				(
					SELECT
						LU.UnitDepartType_fid
					FROM
						v_LpuUnit LU with (nolock)
					WHERE LpuUnit_id = :LpuUnit_id
				) as UnitDepartType_fid ";
		}
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0)) {
			$filter .= "and LpuSection_id=:LpuSection_id ";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if ((isset($data['LpuSection_pid'])) && ($data['LpuSection_pid']>0)) {
			$filter .= "and LpuSection_pid=:LpuSection_pid ";
			$params['LpuSection_pid'] = $data['LpuSection_pid'];
		}
		$sql = "
			SELECT
				LpuSection_id,
				LpuSection_pid,
				LpuUnit_id,
				Lpu_id,
				LpuSectionProfile_id,
				LpuSection_Code,
				LpuSectionCode_id,
				LpuSection_Name,
				PalliativeType_id,
				convert(varchar,LpuSection_setDate,104) as LpuSection_setDate,
				convert(varchar,LpuSection_disDate,104) as LpuSection_disDate,
				LpuSectionAge_id,
				LpuSectionBedProfile_id,
				MESLevel_id,
				case when LpuSection_IsF14 = 2 then 'on' else 'off' end as LpuSection_F14,
				LpuSection_Descr,
				LpuSection_Contacts,
				LpuSectionHospType_id,
				LpuSection_PlanVisitShift,
				LpuSection_PlanTrip,
				LpuSection_PlanVisitDay,
				LpuSection_PlanAutopShift,
				LpuSection_PlanResShift,
				LpuSection_KolJob,
				LpuSection_KolAmbul,
				LpuSection_IsCons,
				LpuSection_IsExportLpuRegion,
				LevelType_id,
				LpuSectionDopType_id,
				LpuSectionType_id,
				LpuSection_Area,
				LpuSection_CountShift,
				LpuCostType_id,
				FRMPSubdivision_id,
				FRMOUnit_id,
				FRMOSection_id,
				LpuSection_FRMOBuildingOid,
				case when LpuSection_IsNotFRMO = 2 then 'on' else 'off' end as LpuSection_IsNotFRMO,
				case when LpuSection_IsDirRec = 2 then 'on' else 'off' end as LpuSection_IsDirRec,
				case when LpuSection_IsQueueOnFree = 2 then 'on' else 'off' end as LpuSection_IsQueueOnFree,
				case when LpuSection_IsUseReg = 2 then 'on' else 'off' end as LpuSection_IsUseReg,
				ISNULL(LpuSection_IsHTMedicalCare, 1) as LpuSection_IsHTMedicalCare,
				case when LpuSection_IsNoKSG = 2 then 'on' else 'off' end as LpuSection_IsNoKSG,
				(Select count(LpuSection_id) from LpuSection LS with (nolock) where LS.LpuSection_pid = LpuSection.LpuSection_id and IsNull(LpuSection_deleted,1)=1) as pidcount
				{$UDType_fid}
				" . $this->getLpuSectionListAdditionalFields() ."
			FROM v_LpuSection LpuSection with (nolock)
				" . $this->getLpuSectionListAdditionalJoin() ."
			WHERE {$filter}
			";


		$res = $this->db->query($sql, $params);
		if ( is_object($res) )
			return $res->result('array');
		else
		return false;
	}

	/**
	 * Дополнительные поля для выборки списка отделений и данных для формы редактирования отделения
	 */
	function getLpuSectionListAdditionalFields() {
		if ($this->getRegionNick() == 'kz') {
			return ' , lsfl.FPID ';
		}
		return '';
	}

	/**
	 * Дополнительные джойны для выборки списка отделений и данных для формы редактирования отделения
	 */
	function getLpuSectionListAdditionalJoin() {
		if ($this->getRegionNick() == 'kz') {
			return 'outer apply (
				select top 1 FPID
				from r101.LpuSectionFPIDLink (nolock)
				where LpuSection_id = LpuSection.LpuSection_id
			) lsfl ';
		}
		return '';
	}

	/**
     * Это Doc-блок
     */
	function getLpuSectionPid($data)
	{
		$filter = "(1=1) ";
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
			$filter .= "and Lpu_id=:Lpu_id ";
		if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id']>0))
			$filter .= "and LpuUnit_id=:LpuUnit_id ";
		if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0))
			$filter .= "and LpuSection_id!=:LpuSection_id ";
		if ((isset($data['LpuSection_pid'])) && ($data['LpuSection_pid']>0))
			$filter .= "and LpuSection_pid=:LpuSection_pid ";
		$sql = "
			SELECT
				LpuSection_id,
				RTrim(LpuSection_Code) as LpuSection_Code,
				RTrim(LpuSection_Name) as LpuSection_Name
			FROM v_LpuSection LpuSection with (nolock)
			WHERE {$filter} and LpuSection_pid is null
			";
		$res = $this->db->query($sql, $data);
		if ( is_object($res) )
			return $res->result('array');
		else
		return false;
	}

	// Получение списка услуг или одного элемента на структуре МО
    /**
     * Это Doc-блок
     */
	function GetLpuUsluga($data)
	{
		$filter = "(1=1) ";
		// Фильтры для первого уровня структуры МО ( на МО )
		if ((isset($data['level'])) && ($data['level'] == 1))
		{
			if ((isset($data['Lpu_id'])) && ($data['Lpu_id']>0))
				$filter .= "and Lpu_id=".$data['Lpu_id'];
			else
				$filter .= "and Lpu_id=".$data['session']['lpu_id'];
			if ((isset($data['UslugaSection_id'])) && ($data['UslugaSection_id']>0))
				$filter .= "and Usluga_id=".$data['Usluga_id'];
		}
		else
		{
			if ((isset($data['LpuSection_id'])) && ($data['LpuSection_id']>0))
				$filter .= "and LpuSection_id=".$data['LpuSection_id'];
			else if ((isset($data['LpuUnit_id'])) && ($data['LpuUnit_id']>0))
				$filter .= "and LpuUnit_id=".$data['LpuUnit_id'];
			if ((isset($data['UslugaSection_id'])) && ($data['UslugaSection_id']>0))
				$filter .= "and UslugaSection_id=".$data['UslugaSection_id'];
			if ((isset($data['Usluga_id'])) && ($data['Usluga_id']>0))
				$filter .= "and Usluga_id=".$data['Usluga_id'];
		}
		if ((isset($data['level'])) && ($data['level'] == 1))
		{
            $sql = "
                SELECT
                    Usluga_id,
                    Lpu_id,
                    Usluga_Code,
                    Usluga_Name
                FROM v_Usluga with (nolock)
                WHERE {$filter} and UslugaType_id = 2
                ";
		}
		else
		{
            $sql = "
                SELECT
                    us.Usluga_id,
                    us.UslugaSection_id,
                    --LpuUnit_id,
                    Usluga_Code,
                    Usluga_Name,
                    us.LpuSection_id,
                    us.UslugaPrice_ue
                FROM v_UslugaSection us with (nolock)
                left join Usluga with(nolock) on Usluga.Usluga_id = us.Usluga_id
                WHERE {$filter}
                ";
		}
		$res = $this->db->query($sql);
		if ( is_object($res) )
			return $res->result('array');
		else
		return false;
	}

    /**
     * Это Doc-блок
     */
	function copyUslugaFromSection($data, $UslugaComplex_pid = NULL, $UslugaComplex_id = NULL) {
		if ( empty($UslugaComplex_pid) ) {
			$andwhere = " and UslugaComplex_pid is null";
		}
		else {
			$andwhere = " and UslugaComplex_pid = :UslugaComplex_pid";
		}

		if ( isset($data['LpuSection_id']) ) {
			$queryParams = array(
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSection_pid' => $data['LpuSection_pid'],
				'pmUser_id' => $data['pmUser_id'],
				'Server_id' => $data['Server_id'],
				'UslugaComplex_pid' => $UslugaComplex_pid
			);

			$query = "select * from UslugaComplex  with (nolock)
				where LpuSection_id = :LpuSection_pid ".$andwhere;

			$res = $this->db->query($query, $queryParams);

			$result = false;

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( is_array($response) && count($response) > 0 ) {
					$query = "
						declare
							@Res bigint,
							@Error_Code bigint,
							@Error_Message varchar(4000);

						exec p_UslugaComplex_ins
							@Server_id = :Server_id,
							@UslugaComplex_id = @Res,
							@UslugaComplex_pid = :UslugaComplex_pid,
							@Lpu_id = :Lpu_id,
							@LpuSection_id = :LpuSection_id,
							@UslugaComplex_ACode = :UslugaComplex_ACode,
							@UslugaComplex_Code = :UslugaComplex_Code,
							@UslugaComplex_Name = :UslugaComplex_Name,
							@Usluga_id = :Usluga_id,
							@RefValues_id = :RefValues_id,
							@XmlTemplate_id = :XmlTemplate_id,
							@UslugaGost_id = :UslugaGost_id,
							@UslugaComplex_BeamLoad = :UslugaComplex_BeamLoad,
							@UslugaComplex_UET = :UslugaComplex_UET,
							@UslugaComplex_Cost = :UslugaComplex_Cost,
							@UslugaComplex_DailyLimit = :UslugaComplex_DailyLimit,
							@XmlTemplateSeparator_id = :XmlTemplateSeparator_id,
							@UslugaComplex_isGenXml = :UslugaComplex_isGenXml,
							@UslugaComplex_isAutoSum = :UslugaComplex_isAutoSum,
							@LpuSectionProfile_id = :LpuSectionProfile_id,
							@UslugaComplex_begDT = :UslugaComplex_begDT,
							@UslugaComplex_endDT = :UslugaComplex_endDT,
							@pmUser_id = :pmUser_id,
							@Error_Code = @Error_Code output,
							@Error_Message = @Error_Message output;

						select @Res as UslugaComplex_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";

					foreach ( $response as $usluga ) {
						$queryParamsNew = array_merge($usluga, $queryParams);
						$queryParamsNew['UslugaComplex_pid'] = $UslugaComplex_id;

						$res = $this->db->query($query, $queryParamsNew);

						$result = $res;

						if ( is_object($res) ) {
							$responseNew = $res->result('array');

							if ( is_array($responseNew[0]) && count($responseNew[0]) > 0 ) {
								if ( empty($usluga['UslugaComplex_pid']) ) {
									$this->copyUslugaFromSection($data, $usluga['UslugaComplex_id'], $responseNew[0]['UslugaComplex_id']);
								}
							}
						}
					}

				}
			}

			return $result;
		}

		if(isset($data['MedService_id']))
		{
			$query = '
				select
					uc.UslugaComplex_id,
					cast(uc.UslugaComplex_begDT as date) as UslugaComplex_begDT,
					cast(uc.UslugaComplex_endDT as date) as UslugaComplex_endDT
				from
					v_UslugaComplex uc with (NOLOCK)
				where
					uc.LpuSection_id = :LpuSection_pid
					and uc.UslugaComplex_pid is null
			';
			$result = $this->db->query($query, array('LpuSection_pid' => $data['LpuSection_pid']));
			$error = null;
			if ( is_object($result) )
			{
				$this->load->model('MedService_model', 'MedService_model');
				$response = $result->result('array');
				foreach($response as $row) {
					$row['UslugaComplexMedService_id'] = 0;
					$row['MedService_id'] = $data['MedService_id'];
					$row['pmUser_id'] = $data['pmUser_id'];
					$res = $this->MedService_model->saveUslugaComplexMedService($row);
					if(empty($res))
					{
						$error = 'Ошибка запроса БД при копировании услуг отделения';
						break;
					}
					if(!empty($res[0]['Error_Msg']))
					{
						$error = $res[0]['Error_Msg'];
						break;
					}
				}
			}
			if(empty($error))
			{
				return true;
			}
		}
		return false;
	}
    /**
     * Это Doc-блок
     */
	function copyUslugaSectionList($data) {

		if ( isset($data['LpuSection_pid']) ) {
			return $this->copyUslugaFromSection($data);
		}
		else {
			$result = null;
			if(isset($data['LpuSection_id']))
			{
				$query = "
					declare
						@Error_Code int,
						@Error_Message varchar(4000);

					set nocount on;

					begin try
						insert into UslugaComplex (Server_id, Lpu_id, LpuSection_id, UslugaComplex_Name, UslugaComplex_Code, UslugaComplex_UET, UslugaComplex_begDT, Usluga_id, pmUser_insID, pmUser_updID, UslugaComplex_insDT, UslugaComplex_updDT)
						select
							:Server_id as Server_id,
							UPL.Lpu_id,
							:LpuSection_id as LpuSection_id,
							U.Usluga_Name as UslugaComplex_Name,
							U.Usluga_Code as UslugaComplex_Code,
							UPL.UslugaPriceList_Ue as UslugaComplex_UET,
							ls.LpuSection_setDate as UslugaComplex_begDT,
							UPL.Usluga_id,
							:pmUser_id as pmUser_insID,
							:pmUser_id as pmUser_updID,
							dbo.tzGetDate() as UslugaComplex_insDT,
							dbo.tzGetDate() as UslugaComplex_updDT
						from v_UslugaPriceList UPL with (nolock)
						inner join v_LpuSection ls with (nolock) on ls.LpuSection_id = :LpuSection_id
						join v_Usluga U with (nolock) on U.Usluga_id = UPL.Usluga_id
						where UPL.Lpu_id = :Lpu_id
					end try
					begin catch
						set @Error_Code = error_number();
						set @Error_Message = error_message();
					end catch

					select @Error_Code as Error_Code, @Error_Message as Error_Msg;

					set nocount off;
				";

				$queryParams = array(
					'LpuSection_id' => $data['LpuSection_id'],
					'Lpu_id' => $data['Lpu_id'],
					'pmUser_id' => $data['pmUser_id'],
					// 'Region_id' => getRegionNumber(),
					'Server_id' => $data['Server_id']
				);
				$result = $this->db->query($query, $queryParams);
			}

			if ( is_object($result) ) {
				return true;
			}
			else {
				return false;
			}
		}
	}

	/**
	* Получение палат отделения
	*/
	function GetLpuSectionWard($data)
	{
		$filter = '';
		$queryParams = array(
			'LpuSection_id' => $data['LpuSection_id']
		);

		/*if ((isset($data['Server_id'])) && ($data['Server_id']>0))
		{
			$filter .= ' and LpuSectionWard.Server_id = :Server_id';
			$queryParams['Server_id'] = $data['Server_id'];
		}*/

		if ( isset($data['LpuSectionWard_id']) )
		{
			$filter .= ' and LpuSectionWard_id = :LpuSectionWard_id';
			$queryParams['LpuSectionWard_id'] = $data['LpuSectionWard_id'];
		}

		/*if ( isset($data['LpuSectionWard_isAct']) AND $data['LpuSectionWard_isAct'] == 2)
		{
			$filter .= ' and LpuSectionWard_isAct = :LpuSectionWard_isAct';
			$queryParams['LpuSectionWard_isAct'] = $data['LpuSectionWard_isAct'];
		}*/

		$sql = "
			SELECT
				LpuSectionWard.Server_id
				,LpuSectionWard_id
				,LpuSectionWard_isAct
				,LpuSectionWard.LpuSection_id
				,LpuSectionWard_Name
				,LpuSectionWard_Floor
				,LpuWardType.LpuWardType_id
				,LpuSectionWard.Sex_id
				,case
					when LpuSectionWard.Sex_id = 1	then 'мужская'
					when LpuSectionWard.Sex_id = 2	then 'женская'
					else 'общая'
				end as Sex_Name
				,LpuWardType.LpuWardType_Code
				,LpuWardType.LpuWardType_Name
				,ISNULL(LpuSectionWard_BedCount,0) as LpuSectionWard_BedCount
				,ISNULL(LpuSectionWard_MainPlace,0) as LpuSectionWard_MainPlace
				,ISNULL(LpuSectionWard_BedRepair,0) as LpuSectionWard_BedRepair
				,ISNULL(LpuSectionWard_CountRoom,0) as LpuSectionWard_CountRoom
				,ISNULL(LpuSectionWard_DopPlace,0) as LpuSectionWard_DopPlace
				,LpuSectionWard_Views
				,LpuSectionWard_Square
				,LpuSectionWard_DayCost
				,convert(varchar,LpuSectionWard_setDate,104) as LpuSectionWard_setDate
				,convert(varchar,LpuSectionWard_disDate,104) as LpuSectionWard_disDate
				,LpuSectionWard.pmUser_insID
				,LpuSectionWard.pmUser_updID
				--,LpuSectionBedState.LpuSectionBedState_Plan
				--,LpuSectionBedState.LpuSectionBedState_Fact
				--,LpuSectionBedState.LpuSectionBedState_Repair
			FROM
				v_LpuSectionWard LpuSectionWard with (nolock)
				left join v_LpuWardType LpuWardType with (nolock) on (LpuSectionWard.LpuWardType_id = LpuWardType.LpuWardType_id)
				--left join v_LpuSectionBedState LpuSectionBedState with(nolock) on (LpuSectionBedState.LpuSection_id = LpuSectionWard.LpuSection_id)
			WHERE LpuSectionWard.LpuSection_id = :LpuSection_id {$filter}
			ORDER BY LpuSectionWard_Name ASC
		";
		//echo getDebugSQL($sql, $queryParams);exit;
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
    /**
     * Сохранение палатной структуры
     */
	function SaveLpuSectionWard($data)
	{
        $this->beginTransaction();
        $data['LpuSectionWard_BedCount'] = $data['LpuSectionWard_MainPlace'] + (empty($data['LpuSectionWard_DopPlace'])?0:$data['LpuSectionWard_DopPlace']);
		$query = "
		declare
			@LpuSectionWard_id bigint = :LpuSectionWard_id,
			@Error_Code bigint,
			@Error_Message varchar(4000);
		exec p_LpuSectionWard_" . (!empty($data['LpuSectionWard_id']) ? "upd" : "ins") . "
			@Server_id = :Server_id,
			@LpuSectionWard_id = @LpuSectionWard_id output,
			@LpuSection_id = :LpuSection_id,
			@LpuSectionWard_Name = :LpuSectionWard_Name,
			@LpuSectionWard_Floor = :LpuSectionWard_Floor,
			@LpuWardType_id = :LpuWardType_id,
			@Sex_id = :Sex_id,
			@LpuSectionWard_Views = :LpuSectionWard_Views,
			@LpuSectionWard_Square = :LpuSectionWard_Square,
			@LpuSectionWard_CountRoom = :LpuSectionWard_CountRoom,
			@LpuSectionWard_DopPlace = :LpuSectionWard_DopPlace,
			@LpuSectionWard_BedCount = :LpuSectionWard_BedCount,
			@LpuSectionWard_MainPlace = :LpuSectionWard_MainPlace,
			@LpuSectionWard_BedRepair = :LpuSectionWard_BedRepair,
			@LpuSectionWard_DayCost = :LpuSectionWard_DayCost,
			@LpuSectionWard_setDate = :LpuSectionWard_setDate,
			@LpuSectionWard_disDate = :LpuSectionWard_disDate,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionWard_id as LpuSectionWard_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

        $result = $this->db->query($query, $data);

        if ( !is_object($result) ) {
            $this->rollbackTransaction();
            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение палаты)';
            return $response;
        }

        $queryResponse = $result->result('array');

        if ( !is_array($queryResponse) ) {
            $this->rollbackTransaction();
            $response['Error_Msg'] = 'Ошибка при сохранение палаты';
            return $response;
        }
        else if ( !empty($queryResponse[0]['Error_Msg']) ) {
            $this->rollbackTransaction();
            return $queryResponse;
        }

        $response = $queryResponse[0];

        // Обрабатываем список бъектов комфортности
        if ( !empty($data['DChamberComfortData']) ) {
            $DChamberComfortData = json_decode($data['DChamberComfortData'], true);

            if ( is_array($DChamberComfortData) ) {
                $isDChamberRepeat = array();
                for ( $i = 0; $i < count($DChamberComfortData); $i++ ) {
                    $DChamberComfort = array(
                    'pmUser_id' => $data['pmUser_id']
                    ,'LpuSectionWard_id' => $response['LpuSectionWard_id']
                    );

                    if ( empty($DChamberComfortData[$i]['LpuSectionWardComfortLink_id']) || !is_numeric($DChamberComfortData[$i]['LpuSectionWardComfortLink_id']) ) {
                        continue;
                    }

                    if ( empty($DChamberComfortData[$i]['DChamberComfort_id']) || !is_numeric($DChamberComfortData[$i]['DChamberComfort_id']) ) {
                        continue;
                        /*
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Не указано наименование объекта';
                            return array($response);
                        */
                    }

                    if ( empty($DChamberComfortData[$i]['LpuSectionWardComfortLink_Count']) || !is_numeric($DChamberComfortData[$i]['LpuSectionWardComfortLink_Count']) ) {
                        continue;
                        /*
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Не указано количество объектов';
                            return array($response);
                        */
                    }


                    $DChamberComfort['LpuSectionWardComfortLink_id'] = $DChamberComfortData[$i]['LpuSectionWardComfortLink_id'];
                    $DChamberComfort['DChamberComfort_id'] = $DChamberComfortData[$i]['DChamberComfort_id'];
                    $DChamberComfort['LpuSectionWardComfortLink_Count'] = $DChamberComfortData[$i]['LpuSectionWardComfortLink_Count'];

                    $queryResponse = $this->saveLpuSectionWardComfortLink($DChamberComfort);

                    if ( !is_array($queryResponse) ) {
                        $this->rollbackTransaction();
                        $response['Error_Msg'] = 'Ошибка при ' . ($DChamberComfortData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' объекта комфортности';
                        return array($response);
                    }
                    else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                        $this->rollbackTransaction();
                        return $queryResponse[0];
                    }

                    //В одной палате не может быть несколько объектов комфортности с одинаковыми наименованиями
                    if (in_array($DChamberComfortData[$i]['DChamberComfort_id'], $isDChamberRepeat)) {
                        $this->rollbackTransaction();
                        $response['Error_Msg'] = 'Нельзя сохранить объекты комфортности с одинаковым наименованием.';
                        return array($response);
                    } else {
                        array_push($isDChamberRepeat, $DChamberComfortData[$i]['DChamberComfort_id']);
                    }
                }
            }
        }

        $this->commitTransaction();

		// Удаляем данные из кэша
		$this->load->library('swCache');
		$this->swcache->clear("LpuSectionWardList_".$data['Lpu_id']);

        return array($response);

	}

	/**
	* Получение объекта комфортности
	*/
	function loadLpuSectionWardComfortLink($data)
	{
		$filter = '';
        //var_dump($data['LpuSectionWard_id']);
		$queryParams = array(
			'LpuSectionWard_id' => $data['LpuSectionWard_id']
		);
        //var_dump($data);
		if ( isset($data['LpuSectionWardComfortLink_id']) )
		{
			$filter .= ' and LpuSectionWardComfortLink_id = :LpuSectionWardComfortLink_id';
			$queryParams['LpuSectionWardComfortLink_id'] = $data['LpuSectionWardComfortLink_id'];
		}

		/*if ( isset($data['LpuSectionWard_isAct']) AND $data['LpuSectionWard_isAct'] == 2)
		{
			$filter .= ' and LpuSectionWard_isAct = :LpuSectionWard_isAct';
			$queryParams['LpuSectionWard_isAct'] = $data['LpuSectionWard_isAct'];
		}*/

		$sql = "
			SELECT
			    LSWCL.DChamberComfort_id,
			    DCC.DChamberComfort_Name,
                LSWCL.LpuSectionWard_id ,
                LSWCL.LpuSectionWardComfortLink_Count,
                LSWCL.LpuSectionWardComfortLink_id
			FROM
				fed.v_LpuSectionWardComfortLink LSWCL with (nolock)
				left join fed.DChamberComfort DCC with (nolock) on DCC.DChamberComfort_id = LSWCL.DChamberComfort_id
			WHERE LSWCL.LpuSectionWard_id = :LpuSectionWard_id {$filter}
		";
        //echo getDebugSQL($sql, $queryParams);
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Сохранение объекта комфортности
     */

	function saveLpuSectionWardComfortLink($data)
    {

        if (!isset($data['LpuSectionWard_id'])) {
            return array(array('LpuSectionWard_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Для добавления операции необходим идентификатор палаты.'));
            return false;
        }

		$sp = $this->getLpuSectionWardByIdData($data);
		if ( $sp && isset($sp[0]['Lpu_id']) && isset($data['Lpu_id']) && $data['Lpu_id'] != $sp[0]['Lpu_id'] ) {
			return array(array(
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
        if (!isset($data['LpuSectionWardComfortLink_Code'])) {
            $data['LpuSectionWardComfortLink_Code'] = 1;
        }

		if (isset($data['LpuSectionWardComfortLink_id']) && $data['LpuSectionWardComfortLink_id'] > 0 ) {
			$proc = 'fed.p_LpuSectionWardComfortLink_upd';
		} else {
			$proc = 'fed.p_LpuSectionWardComfortLink_ins';
		}

		$query = "
		declare
			@LpuSectionWardComfortLink_id bigint,
			@Error_Code bigint,
			@Error_Message varchar(4000);
        set @LpuSectionWardComfortLink_id = " . (($data['LpuSectionWardComfortLink_id'] > 0) ? ":LpuSectionWardComfortLink_id" : "null") . " ;
		exec {$proc}
		    @LpuSectionWardComfortLink_id = @LpuSectionWardComfortLink_id output,
			@DChamberComfort_id = :DChamberComfort_id,
			@LpuSectionWard_id = :LpuSectionWard_id,
			@LpuSectionWardComfortLink_Count = :LpuSectionWardComfortLink_Count,
			@LpuSectionWardComfortLink_Code = :LpuSectionWardComfortLink_Code,
			@pmUser_id = :pmUser_id,
			@Error_Code = @Error_Code output,
			@Error_Message = @Error_Message output;
		select @LpuSectionWardComfortLink_id as LpuSectionWardComfortLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;";

		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $data);
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
     * Удаление объекта комфортности
     */
	function deleteSectionWardComfortLink($data)
    {

        if (!isset($data['LpuSectionWardComfortLink_id'])) {
            return array(array('LpuSectionWardComfortLink_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.'));
            return false;
        }
		
		$sp = $this->getLpuSectionWardComfortLinkForAPI($data);
		if ( isset($sp[0]['Lpu_id']) && $data['Lpu_id'] != $sp[0]['Lpu_id'] ) {
			return array(array(
				'Error_Code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			));
		}
		
		$query = "
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec fed.p_LpuSectionWardComfortLink_del
				@LpuSectionWardComfortLink_id = :LpuSectionWardComfortLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Message as Error_Msg;
        ";

		$result = $this->db->query($query, $data);
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
     * Удаление палаты
     */
	function deleteLpuSectionWard($data)
    {

        if (!isset($data['LpuSectionWard_id'])) {
            return array(array('LpuSectionWard_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.'));
            return false;
        }
		$sp = $this->getLpuSectionWardByIdData($data);
		if ( $sp && isset($sp[0]['Lpu_id']) && $data['Lpu_id'] != $sp[0]['Lpu_id'] ) {
			 return array(array(
				'error_code' => 6,
				'Error_Msg' => 'Данный метод доступен только для своей МО'
			));
			return false;
		}
		
		$query = "
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec p_LpuSectionWard_del
				@LpuSectionWard_id = :LpuSectionWard_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Message as Error_Msg;
        ";

		$result = $this->db->query($query, $data);
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
     * Удаление врача с участка
     */

	function deleteMedStaffRegion($data) {

        if (isset($data['MedStaffRegion_id']) && $data['MedStaffRegion_id'] > 0) {

			$query = "
				declare
					@Error_Code bigint = 0,
					@Error_Message varchar(4000) = '';
				exec p_MedStaffRegion_del
					@MedStaffRegion_id = :MedStaffRegion_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @Error_Code output,
					@Error_Message = @Error_Message output;
				select @Error_Message as Error_Msg;
			";

			$result = $this->db->query($query, $data);

			if (is_object($result)) {
				return $result->result('array');
			} else {
				return false;
			}

        } else {
			return true;
		}
	}

    /**
     * Удаление операции над койкой
     */

	function deleteSectionBedStateOper($data)
    {

        if (!isset($data['LpuSectionBedStateOper_id'])) {
            return array(array('LpuSectionBedStateOper_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Нельзя удалить запись без идентификатора.'));
            return false;
        }

		$query = "
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec fed.p_LpuSectionBedStateOper_del
				@LpuSectionBedStateOper_id = :LpuSectionBedStateOper_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Message as Error_Msg;
        ";

		$result = $this->db->query($query, $data);
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
	* Получение операции над профилем койки
	*/
	function loadDBedOperation($data)
	{
		$filter = '';
        //var_dump($data['LpuSectionWard_id']);

		$queryParams = array(
			'LpuSectionBedState_id' => $data['LpuSectionBedState_id']
			//'LpuSectionBedState_id' => $data['LpuSectionBedState_id'],
		);
        //var_dump($data);
		if ( isset($data['LpuSectionBedStateOper_id']) )
		{
			$filter .= ' and LpuSectionBedStateOper_id = :LpuSectionBedStateOper_id';
			$queryParams['LpuSectionBedStateOper_id'] = $data['LpuSectionBedStateOper_id'];
		}


		$sql = "
			SELECT
			    LSBS.LpuSectionBedStateOper_id,
			    LSBS.LpuSectionBedState_id,
                LSBS.DBedOperation_id,
                DBO.DBedOperation_Name,
                convert(varchar,LSBS.LpuSectionBedStateOper_OperDT,104) as LpuSectionBedStateOper_OperDT
			FROM
				fed.v_LpuSectionBedStateOper LSBS with (nolock)
				left join fed.DBedOperation DBO with (nolock) on DBO.DBedOperation_id = LSBS.DBedOperation_id
			WHERE LSBS.LpuSectionBedState_id = :LpuSectionBedState_id {$filter}
		";
        //echo getDebugSQL($sql, $queryParams);
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

    /**
     * Сохранение операции над профилем койки
     */

	function saveDBedOperation($data)
    {

        if (!isset($data['LpuSectionBedState_id'])) {
            return array(array('LpuSectionBedState_id' => null, 'Error_Code' => 1,'Error_Msg' => 'Для добавления операции необходим идентификатор койки.'));
            return false;
        }

        if (!isset($data['LpuSectionBedStateOper_Code'])) {
            $data['LpuSectionBedStateOper_Code'] = 1;
        }

        $data['LpuSectionBedStateOper_OperDT'] = date_create($data['LpuSectionBedStateOper_OperDT']);

        $queryParams = array(
            'LpuSectionBedStateOper_id' => $data['LpuSectionBedStateOper_id'],
            'LpuSectionBedState_id' => $data['LpuSectionBedState_id'],
            'DBedOperation_id' => $data['DBedOperation_id'],
            'LpuSectionBedStateOper_Code' => $data['LpuSectionBedStateOper_Code'],
            'LpuSectionBedStateOper_OperDT' => $data['LpuSectionBedStateOper_OperDT'],
            'pmUser_id' => $data['pmUser_id']
        );

		if (isset($data['LpuSectionBedStateOper_id']) && $data['LpuSectionBedStateOper_id'] > 0) {
			$proc = 'fed.p_LpuSectionBedStateOper_upd';
		} else {
			$proc = 'fed.p_LpuSectionBedStateOper_ins';
		}

		$query = "
            declare
                @LpuSectionBedStateOper_id bigint,
                @Error_Code bigint,
                @Error_Message varchar(4000);
            set @LpuSectionBedStateOper_id = " . (($data['LpuSectionBedStateOper_id'] > 0) ? ":LpuSectionBedStateOper_id" : "null") . " ;
            exec {$proc}
                @LpuSectionBedStateOper_id = @LpuSectionBedStateOper_id output,
                @LpuSectionBedState_id = :LpuSectionBedState_id,
                @DBedOperation_id = :DBedOperation_id,
                @LpuSectionBedStateOper_Code = :LpuSectionBedStateOper_Code,
                @LpuSectionBedStateOper_OperDT = :LpuSectionBedStateOper_OperDT,
                @pmUser_id = :pmUser_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
            select @LpuSectionBedStateOper_id as LpuSectionBedStateOper_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
        ";
		//echo getDebugSQL($query, $data);exit;
		$result = $this->db->query($query, $queryParams);
		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Получение операции над профилем койки
	*/
	function getStaffOSMGridDetail($data)
	{
		$filter = '(1=1)';
        //var_dump($data['LpuSectionWard_id']);

		/*$queryParams = array(
			'Staff_id' => $data['Staff_id']
			//'LpuSectionBedState_id' => $data['LpuSectionBedState_id'],
		);*/
        //var_dump($data);

        $queryParams = array();

		if ( isset($data['Staff_id']) )
		{
			$filter .= ' and Staff_id = :Staff_id';
			$queryParams['Staff_id'] = $data['Staff_id'];
		}
		else
		{
			$filter .= ' and Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$sql = "
			SELECT
			    Staff_id,
			    Staff_Num,
			    Staff_OrgName,
			    Staff_OrgBasis,
				Lpu_id,
			    convert(varchar(10), cast(Staff_OrgDT as datetime), 104) as Staff_OrgDT
			FROM
				fed.v_Staff with (nolock)
			WHERE {$filter}
		";
        //echo getDebugSQL($sql, $queryParams);
		$res = $this->db->query($sql, $queryParams);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Проверка может ли выполнять отделение ВМП
	 */
	function checkLpuSectionIsVMP($data)
	{
		$resp = $this->queryResult("
			select top 1
				LS.LpuSection_IsHTMedicalCare,
				LS.LpuSection_Code,
				LS.LpuSection_Name,
				L.Lpu_Nick
			from
				v_LpuSection LS (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = ls.Lpu_id
			where
				LS.LpuSection_id = :LpuSection_id
		", array(
			'LpuSection_id' => $data['LpuSection_id']
		));

		if (!empty($resp[0]) && $resp[0]['LpuSection_IsHTMedicalCare'] != 2) {
			return array('Error_Msg' => 'В отделении '.$resp[0]['Lpu_Nick'].' '.$resp[0]['LpuSection_Code'].' '.$resp[0]['LpuSection_Name'].' не предусмотрено выполнение высокотехнологичной помощи');
		}

		return array('Error_Msg' => '');
	}

    /**
     * Сохранение операции над профилем койки
     */

	function saveStaffOSMGridDetail($data)
    {
        if (!isset($data['Staff_Code'])) { //Неепонятный ненужный параметр
            $data['Staff_Code'] = 1;
        }

        if (!isset($data['Staff_Name'])) { //Неепонятный ненужный параметр
            $data['Staff_Name'] = 1;
        }

        /*if (!isset($data['LpuSectionBedState_cid'])) {
            var_dump($data['LpuSectionBedState_cid']);
            return array(array('LpuSectionBedState_cid' => null, 'Error_Code' => 1,'Error_Msg' => 'Для добавления операции сохраните койку.'));
            return false;
        }*/

        $queryParams = array(
            'Staff_id' => $data['Staff_id'],
            'Staff_Num' => $data['Staff_Num'],
            'Staff_OrgName' => $data['Staff_OrgName'],
            'Staff_OrgDT' => $data['Staff_OrgDT'],
            'Staff_OrgBasis' => $data['Staff_OrgBasis'],
            'Staff_Code' => $data['Staff_Code'],
            'Staff_Name' => $data['Staff_Name'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );
        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_Staff with (nolock)
            where
                (Staff_Num = :Staff_Num) and
                (Staff_OrgName = :Staff_OrgName) and
                (Staff_OrgDT = :Staff_OrgDT) and
                (Staff_id != isnull(:Staff_id, 0)) and
				Lpu_id = :Lpu_id
        ";
        // echo getDebugSQL($query, $queryParams); die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

		if (isset($data['Staff_id']))
		{
			$proc = 'fed.p_Staff_upd';
		}
		else
		{
			$proc = 'fed.p_Staff_ins';
		}

		$query = "
            declare
                @Staff_id bigint,
                @Error_Code bigint,
                @Error_Message varchar(4000);
            set @Staff_id = :Staff_id;
            exec {$proc}
                @Staff_id = @Staff_id output,
                @Staff_Num = :Staff_Num,
                @Staff_Code = :Staff_Code,
                @Staff_Name = :Staff_Name,
                @Lpu_id = :Lpu_id,
                @Staff_OrgName = :Staff_OrgName,
                @Staff_OrgDT = :Staff_OrgDT,
                @Staff_OrgBasis = :Staff_OrgBasis,
                @pmUser_id = :pmUser_id,
                @Error_Code = @Error_Code output,
                @Error_Message = @Error_Message output;
            select @Staff_id as Staff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
        ";
		// echo getDebugSQL($query, $data);exit;
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
     * Это Doc-блок
     */
	function getLpuSectionBedAllQuery($data)
	{
		$query = "
			Select
			-- Общее количество коек в отделении, план
			sum(LpuSectionBedState_PlanCount) as LpuSection_CommonCount,
			-- Из них по основному профилю
			sum(LpuSectionBedState_ProfileCount) as LpuSection_ProfileCount,
			-- Из них узких коек
			sum(LpuSectionBedState_UzCount) as LpuSection_UzCount,
			-- Общее количество коек в отделении, факт
			sum(LpuSectionBedState_Fact) as LpuSection_Fact,
			-- Общее количество коек по палатам
			sum(LpuSectionWard_BedCount) as LpuSection_BedCount,
			-- Из них на ремонте
			sum(LpuSectionWard_BedRepair) as LpuSection_BedRepair,
			-- Плановый резерв коек для экстренных госпитализаций, не более
			isnull(LS.LpuSection_MaxEmergencyBed, 0) as LpuSection_MaxEmergencyBed

			from v_LpuSection LS with (nolock)
			outer apply (
				Select
					sum(LpuSectionWard_BedCount) as LpuSectionWard_BedCount,
					sum(LpuSectionWard_BedRepair) as LpuSectionWard_BedRepair
				from v_LpuSectionWard LSW with (nolock)
				where
					LSW.LpuSectionWard_setDate <=cast(dbo.tzGetDate() as date) and (LSW.LpuSectionWard_disDate >= cast(dbo.tzGetDate() as date) or LSW.LpuSectionWard_disDate is null) and
					LSW.LpuSection_id=LS.LpuSection_id
			) LSW
			outer apply (
				Select
					sum(LpuSectionBedState_Plan) as LpuSectionBedState_PlanCount,
					sum(case when LSBS.LpuSectionProfile_id = LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_ProfileCount,
					sum(case when LSBS.LpuSectionProfile_id != LSS.LpuSectionProfile_id then LpuSectionBedState_Plan else 0 end) as LpuSectionBedState_UzCount,
					sum(LpuSectionBedState_Fact) as LpuSectionBedState_Fact

				from v_LpuSectionBedState LSBS with (nolock)
				left join v_LpuSection LSS with(nolock) on LSS.LpuSection_id = LSBS.LpuSection_id
				where
					LSBS.LpuSectionBedState_begDate <=cast(dbo.tzGetDate() as date) and (LSBS.LpuSectionBedState_endDate >= cast(dbo.tzGetDate() as date) or LSBS.LpuSectionBedState_endDate is null) and
					LSBS.LpuSection_id=LS.LpuSection_id
			) LSBS
			where
			LpuSection_id=:LpuSection_id
			group by LpuSection_MaxEmergencyBed
		";
		/*
		echo getDebugSql($query, $data);
		exit;
		*/
		$result = $this->db->query($query, $data);
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
     * Это Doc-блок
     */
	function getLpuSectionProfileforCombo($data)
	{
		$query = "
			declare @currentDate datetime = dbo.tzGetDate();
			select
				LSP.LpuSectionProfile_id,
				LSP.LpuSectionProfile_Code,
				LSP.LpuSectionProfile_Name
			from
				v_LpuSectionProfile LSP with (nolock)
				inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			where
				( LpuSection.LpuSection_id = :LpuSection_id or LpuSection.LpuSection_pid = :LpuSection_id )
				and ( LSP.LpuSectionProfile_endDT is null or LSP.LpuSectionProfile_endDT > @currentDate )
			union

			select
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name
			from dbo.v_LpuSectionLpuSectionProfile lslsp with (nolock)
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where
				lslsp.LpuSection_id = :LpuSection_id
				and ( LSP.LpuSectionProfile_endDT is null or LSP.LpuSectionProfile_endDT > @currentDate )
			order by
				LpuSectionProfile_Code
		";
		$result = $this->db->query($query, $data);

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
     * Это Doc-блок
     */
	function getLpuSectionBedProfileforCombo($data)
	{
		$query = "
			select
				LSBP.LpuSectionBedProfile_id,
				LSBP.LpuSectionBedProfile_Code,
				LSBP.LpuSectionBedProfile_Name
			from
				v_LpuSectionBedProfile LSBP with (nolock)
				inner join v_LpuSection LpuSection with (nolock) on LpuSection.LpuSectionBedProfile_id = LSBP.LpuSectionBedProfile_id
			where
				LpuSection.LpuSection_pid = :LpuSection_id -- только подотделений
			order by
				LpuSectionBedProfile_Code
		";
		$result = $this->db->query($query, $data);
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
	 * Это Doc-блок
	 */
	function getLpuSectionWardByIdData($data)
	{
		if( !isset($data['LpuSectionWard_id']) ) return false;
		$query = "
			select top 1
				LSW.LpuSectionWard_id
				,LSW.LpuSectionWard_isAct
				,LSW.LpuSection_id
				,LSW.LpuSectionWard_Name
				,LSW.LpuSectionWard_Floor
				,LSW.LpuWardType_id
				,LSW.LpuSectionWard_BedCount
				,LSW.LpuSectionWard_BedRepair
				,LSW.LpuSectionWard_DayCost
				,LSW.LpuSectionWard_setDate
				,LSW.LpuSectionWard_disDate
				,LS.Lpu_id
			from v_LpuSectionWard LSW (nolock)
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LSW.LpuSection_id
			where LSW.LpuSectionWard_id = :LpuSectionWard_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			return $result->result('array');
		}
		else
		{
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	function getLpuSectionData($data)
	{
		$query = "
			select top 1
				LS.Lpu_id,
				LU.LpuBuilding_id,
				LS.LpuUnit_id,
				LS.LpuSection_id
			from v_LpuSection LS with(nolock)
				join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			where LS.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $data);
		if ( is_object($result) ) {
			$response = $result->result('array');
			return array('data'=>$response[0]);
		}
		else
		{
			return false;
		}
	}
	/**
	 * Это Doc-блок
	 */
	function updMaxEmergencyBed($data)
	{
		$query = "
			update LpuSection
				set LpuSection_MaxEmergencyBed = :LpuSection_MaxEmergencyBed
			where
				LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $data);
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
     * Это Doc-блок
     */
	function getExp2DbfData($query2export)
	{
		$queries = array(
			'REG_FOND' => array("
		SELECT distinct
				l.Lpu_OKATO AS TF_OKATO,
				l.Lpu_OGRN AS C_OGRN,
				l.Lpu_Ouzold AS LCOD,
				NULL AS TYPE,
				--ISNULL(qc.Category_id, 0) AS PCOD,
				right('000000000000' + cast(wp.id as varchar(16)), 12) AS PCOD,
				p.Person_Surname AS FAM_V,
				p.Person_Firname AS IM_V,
				p.Person_Secname AS OT_V,
				( SELECT    SUBSTRING(Sex_Name, 1, 1)
				  FROM      v_sex WITH ( NOLOCK )
				  WHERE     Sex_id = personState.Sex_id
				) AS W,
				p.Person_BirthDay AS DR,
				SUBSTRING(p.Person_Snils, 1, 3) + '-' + SUBSTRING(p.Person_Snils, 4, 3)
				+ '-' + SUBSTRING(p.Person_Snils, 7, 3) + ' '
				+ SUBSTRING(p.Person_Snils, 10, 2) AS SS,
				--right('000000' + cast(p.Dolgnost_Code as varchar(6)), 6) AS PRVD,
				wp.Dolgnost_Code AS PRVD,
				p.WorkData_begDate AS D_PR,
				qic.DocumentRecieveDate AS D_SER,
				--right('00000' + cast(s.code as varchar(5)), 5) AS PRVS,
				s.spec_code AS PRVS,
				ISNULL(qc.Category_id, 0) AS KV_KAT,
				Year(qc.AssigmentDate) AS YEAR_KAT,
				--qc.AssigmentYear AS YEAR_KAT,
				wp.DLOBeginDate AS DATE_B,
				NULL AS DATE_E,
				CAST(wp.rate AS VARCHAR(6)) AS STAVKA,
				'' AS MSG_TEXT,
				p.WorkData_begDate AS DATE_P,
				wp.Population AS PRIKREP,
				( SELECT    sl.LpuSubjectionLevel_Name
				  FROM      v_LpuSubjectionLevel sl WITH ( NOLOCK )
				  WHERE     sl.LpuSubjectionLevel_id = l.LpuSubjectionLevel_id
				) AS VEDOM_P
				FROM    v_MedPersonal p WITH ( NOLOCK )
				LEFT JOIN v_Lpu l WITH ( NOLOCK ) ON L.Lpu_id = p.Lpu_id
				LEFT JOIN v_PersonState personState WITH ( NOLOCK ) ON p.Person_id = personState.Person_id
				outer apply (
					Select top 1 Speciality_id from persis.Certificate c WITH ( NOLOCK ) where c.MedWorker_id = p.MedPersonal_id order by CertificateReceipDate desc
				) c
				outer apply (
					Select top 1 spec.spec_code from persis.SpecialityDiploma s WITH ( NOLOCK )
					inner join persis.DiplomaSpeciality d WITH ( NOLOCK ) ON s.DiplomaSpeciality_id = d.id
					inner join tmp.spec spec with(nolock) on spec.spec_id = d.id
					where s.MedWorker_id = p.MedPersonal_id
				) s
				OUTER APPLY ( SELECT TOP 1
										qic.DocumentRecieveDate
							  FROM      persis.QualificationImprovementCourse qic WITH ( NOLOCK )
							  WHERE     qic.MedWorker_id = p.MedPersonal_id
							  ORDER BY  qic.DocumentRecieveDate desc
							) qic
				outer apply (
					Select top 1
						qc.Category_id,
						qc.AssigmentDate
					from persis.QualificationCategory qc WITH ( NOLOCK )
					Where qc.MedWorker_id = p.MedPersonal_id
				) qc
				CROSS APPLY ( SELECT TOP 1
										wp.dlobegindate,
										wp.rate,
										wp.population,
										wp.id,
										post.Dolgnost_Code
							  FROM      persis.WorkPlace wp WITH ( NOLOCK )
							  inner join persis.v_staff s with(nolock) on s.id = wp.Staff_id and s.Lpu_id = p.Lpu_id
							  left join persis.post pp with(nolock) on pp.id = s.Post_id
							  left join tmp.postnew post with(nolock) on post.ID_NEW = pp.id
							  WHERE     wp.MedWorker_id = p.MedPersonal_id
										AND wp.enddate IS NULL
										AND wp.rate>0
										and PrimaryHealthCare = 1
							  ORDER BY  wp.PostOccupationType_id,
										wp.BeginDate,
										wp.id
							) wp
				ORDER BY FAM_V,
				IM_V,
				OT_V"),
			'REG_FOND_NEW' => array("
		SELECT distinct
				l.Lpu_OKATO AS TF_OKATO,
				l.Lpu_OGRN AS C_OGRN,
				l.Lpu_f003mcod AS MCOD,
				NULL AS TYPE,
				--ISNULL(qc.Category_id, 0) AS PCOD,
				right('000000000000' + cast(wp.id as varchar(16)), 12) AS PCOD,
				p.Person_Surname AS FAM_V,
				p.Person_Firname AS IM_V,
				p.Person_Secname AS OT_V,
				( SELECT    SUBSTRING(Sex_Name, 1, 1)
				  FROM      v_sex WITH ( NOLOCK )
				  WHERE     Sex_id = personState.Sex_id
				) AS W,
				p.Person_BirthDay AS DR,
				SUBSTRING(p.Person_Snils, 1, 3) + '-' + SUBSTRING(p.Person_Snils, 4, 3)
				+ '-' + SUBSTRING(p.Person_Snils, 7, 3) + ' '
				+ SUBSTRING(p.Person_Snils, 10, 2) AS SS,
				--right('000000' + cast(p.Dolgnost_Code as varchar(6)), 6) AS PRVD,
				wp.frmpEntry_id AS PRVD,
				p.WorkData_begDate AS D_PR,
				qic.DocumentRecieveDate AS D_SER,
				--right('00000' + cast(s.code as varchar(5)), 5) AS PRVS,
				s.code AS PRVS,
				ISNULL(qc.Category_id, 0) AS KV_KAT,
				Year(qc.AssigmentDate) AS YEAR_KAT,
				--qc.AssigmentYear AS YEAR_KAT,
				wp.DLOBeginDate AS DATE_B,
				NULL AS DATE_E,
				'НЕТ ПРИМЕЧАНИЙ' as MSG_TEXT,
				p.WorkData_begDate AS DATE_P,
				wp.Population AS PRIKREP,
				( SELECT    sl.LpuSubjectionLevel_Name
				  FROM      v_LpuSubjectionLevel sl WITH ( NOLOCK )
				  WHERE     sl.LpuSubjectionLevel_id = l.LpuSubjectionLevel_id
				) AS VEDOM_P
				FROM    v_MedPersonal p WITH ( NOLOCK )
				LEFT JOIN v_Lpu l WITH ( NOLOCK ) ON L.Lpu_id = p.Lpu_id
				LEFT JOIN v_PersonState personState WITH ( NOLOCK ) ON p.Person_id = personState.Person_id
				outer apply (
					Select top 1 Speciality_id from persis.Certificate c WITH ( NOLOCK ) where c.MedWorker_id = p.MedPersonal_id order by CertificateReceipDate desc
				) c
				outer apply (
					Select top 1 d.code from persis.SpecialityDiploma s WITH ( NOLOCK )
					inner join persis.DiplomaSpeciality d WITH ( NOLOCK ) ON s.DiplomaSpeciality_id = d.id
					where s.MedWorker_id = p.MedPersonal_id
				) s
				OUTER APPLY ( SELECT TOP 1
										qic.DocumentRecieveDate
							  FROM      persis.QualificationImprovementCourse qic WITH ( NOLOCK )
							  WHERE     qic.MedWorker_id = p.MedPersonal_id
							  ORDER BY  qic.DocumentRecieveDate desc
							) qic
				outer apply (
					Select top 1
						qc.Category_id,
						qc.AssigmentDate
					from persis.QualificationCategory qc WITH ( NOLOCK )
					Where qc.MedWorker_id = p.MedPersonal_id
				) qc
				CROSS APPLY ( SELECT TOP 1
										wp.dlobegindate,
										wp.rate,
										wp.population,
										wp.id,
										pp.frmpEntry_id AS frmpEntry_id
							  FROM      persis.WorkPlace wp WITH ( NOLOCK )
							  inner join persis.v_staff s with(nolock) on s.id = wp.Staff_id and s.Lpu_id = p.Lpu_id
							  left join persis.post pp with(nolock) on pp.id = s.Post_id
							  WHERE     wp.MedWorker_id = p.MedPersonal_id
										AND wp.enddate IS NULL
										AND wp.rate>0
										and PrimaryHealthCare = 1
							  ORDER BY  wp.PostOccupationType_id,
										wp.BeginDate,
										wp.id
							) wp
				ORDER BY FAM_V,
				IM_V,
				OT_V"),
			'LPU_Q' => array("SELECT  l.Lpu_Ouz AS LPU_OUZ ,
                            IsNull(l.Lpu_OuzOld,l.Lpu_Ouz) AS MCOD ,
                            l.Lpu_OKATO AS TF_OKATO ,
                            l.Lpu_OGRN AS C_OGRN ,
                            l.Lpu_Nick AS M_NAMES ,
                            l.Lpu_Name AS M_NAMEF ,
                            ( SELECT    address_zip
                              FROM      dbo.v_Address a with (nolock)
                              WHERE     a.Address_id = l.UAddress_id
                            ) AS POST_ID ,
                            l.UAddress_Address AS ADRES ,
                            gv.Person_SurName AS FAM_GV ,
                            gv.Person_FirName AS IM_GV ,
                            gv.Person_SecName AS OT_GV ,
                            gb.Person_SurName AS FAM_BUX ,
                            gb.Person_FirName AS IM_BUX ,
                            gb.Person_SecName AS OT_BUX ,
                            l.Org_Phone AS TEL ,
                            gv.OrgHead_Fax AS FAX ,
                            l.Org_Email AS E_MAIL ,
                            LPD.LpuPeriodDLO_begDate AS DATE_B ,
                            LPD.LpuPeriodDLO_endDate AS DATE_E ,
                            SUBSTRING(CAST (l.Lpu_Ouz AS CHAR(10)), 3, 2) AS KOD_TER ,
                            RIGHT(l.Lpu_Ouz, 3) AS KOD_LPU ,
                            l.Lpu_Ouz AS S_LR_LPU
                    FROM    ( SELECT   l1.* ,
                                        o.Org_Phone ,
                                        o.Org_Email
                              FROM      v_lpu l1 with (nolock)
                                        LEFT OUTER JOIN v_org o with(nolock) ON o.Org_id = l1.org_id
                            ) AS l
                            outer apply (
								select
									top 1
										ps.Person_SurName,
										ps.Person_FirName,
										ps.Person_SecName,
                                        oh.Lpu_id,
                                        oh.OrgHead_Fax
								from OrgHead OH with (nolock)
									inner join v_PersonState PS with (nolock) on PS.Person_id = OH.Person_id
								where
									OH.Lpu_id = l.Lpu_id
									and OH.LpuUnit_id is null
									and OH.OrgHeadPost_id = 1
							) as gv
							outer apply (
								select
									top 1
										ps.Person_SurName,
										ps.Person_FirName,
										ps.Person_SecName,
										OH.Lpu_id
								from OrgHead OH with (nolock)
									inner join v_PersonState PS with (nolock) on PS.Person_id = OH.Person_id
								where
									OH.Lpu_id = l.Lpu_id
									and OH.LpuUnit_id is null
									and OH.OrgHeadPost_id = 2
							) as gb
							inner join LpuPeriodDLO LPD with(nolock) on LPD.Lpu_id = l.Lpu_id
                    WHERE   /*l.Lpu_dloBegDate IS NOT NULL
                            AND l.Lpu_dloBegDate <= dbo.tzGetDate()*/
							--exists(Select top 1 LpuPeriodDLO_id from LpuPeriodDLO LPD with (nolock) where LPD.Lpu_id = l.Lpu_id and LPD.LpuPeriodDLO_begDate <= dbo.tzGetDate() and (LPD.LpuPeriodDLO_endDate>=dbo.tzGetDate() or LPD.LpuPeriodDLO_endDate is null))
							l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО"),
	    'SVF_Q' => array("SELECT distinct l.Lpu_OKATO AS TF_OKATO,
        l.Lpu_Ouz AS MCOD,
        l.Lpu_OGRN + ' ' + RIGHT(REPLICATE('0', 10)
                                 + CAST(p.MedPersonal_Code AS VARCHAR(10)), 6) AS PCOD,
        ISNULL(RTRIM(p.Person_SurName), '') AS FAM_V,
        ISNULL(RTRIM(p.Person_FirName), '') AS IM_V,
        ISNULL(RTRIM(p.Person_SecName), '') AS OT_V,
        l.Lpu_OGRN AS C_OGRN,
        ISNULL(RTRIM(wp.Dolgnost_Code), '') AS PRVD,
        ISNULL(RTRIM(wp.Dolgnost_Name), '') AS D_JOB,
        p.WorkData_begDate AS D_PRIK,
        qic.DocumentRecieveDate AS D_SER,
        c.Speciality_id AS PRVS,
        ISNULL(qc.Category_id, 0) AS KV_KAT,
        wp.DLOBeginDate AS DATE_B,
        wp.DLOEndDate AS DATE_E,
        'НЕТ ПРИМЕЧАНИЙ' AS MSG_TEXT,
        SUBSTRING(CAST(L.Lpu_Ouz AS VARCHAR(10)), 3, 2) AS KOD_TER,
        RIGHT(CAST(L.Lpu_Ouz AS VARCHAR(10)), 3) AS KOD_LPU
FROM    dbo.v_MedPersonal p WITH ( NOLOCK )
  LEFT JOIN ( SELECT  *
                    FROM    v_lpu l1 WITH ( NOLOCK )
                    WHERE   exists(Select top 1 LpuPeriodDLO_id from LpuPeriodDLO LPD with (nolock) where LPD.Lpu_id = l1.Lpu_id and LPD.LpuPeriodDLO_begDate <= dbo.tzGetDate() and (LPD.LpuPeriodDLO_endDate>=dbo.tzGetDate() or LPD.LpuPeriodDLO_endDate is null))
                  ) l ON l.Lpu_id = p.lpu_id
			cross apply (
				SELECT top 1
					 t1.DLOBeginDate
					,t1.dloEndDate
					,t1.Descr
					,t2.Code as Dolgnost_Code
					,t2.name as Dolgnost_Name
				FROM persis.v_WorkPlace t1 WITH (NOLOCK)
					-- https://redmine.swan.perm.ru/issues/8681
					-- Добавил получение кода и наименования должности по конкретному месту работы
					inner join persis.Post t2 with (nolock) on t2.id = t1.Post_id
				WHERE
					t1.MedWorker_id = p.MedPersonal_id
					-- https://redmine.swan.perm.ru/issues/8681
					-- Добавил проверку на ставку
					-- ... и убрал, ибо задача была сформулирована некорректно
					-- and ISNULL(t1.Rate, 0) > 0
					and t1.Lpu_id = p.Lpu_id
					and t1.DLOBeginDate is not null
					and t1.DLOBeginDate <= dbo.tzGetDate()
					-- врачей, которые уволились из МО передавать в Кверти в течение 90 дней, после даты увольнения. // https://redmine.swan.perm.ru/issues/7186
					and (DATEADD(day, 90, IsNull(t1.EndDate, dbo.tzGetDate())) > dbo.tzGetDate())
				order by
					-- Добавил сортировку по типу занимаемой должности
					-- https://redmine.swan.perm.ru/issues/8681
					 t1.PostOccupationType_id
					,t1.EndDate
			) wp
        OUTER APPLY ( SELECT TOP 1
                                qic.DocumentRecieveDate
                      FROM      persis.QualificationImprovementCourse qic WITH ( NOLOCK )
                      WHERE     qic.MedWorker_id = p.MedPersonal_id
                      ORDER BY  qic.DocumentRecieveDate desc
                    ) qic
        outer apply (
			Select top 1 Speciality_id from persis.Certificate c WITH ( NOLOCK ) where c.MedWorker_id = p.MedPersonal_id order by CertificateReceipDate desc
		) c
        outer apply (
				select top 1 Category_id
				from persis.[QualificationCategory] qc WITH (NOLOCK)
				where qc.MedWorker_id = p.MedPersonal_id
				order by AssigmentDate desc
			) qc
        where
            p.MedPersonal_Code IS NOT NULL
            AND p.MedPersonal_Code is not null
            AND p.MedPersonal_Code != '0'
            and l.Lpu_id is not null
            and l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО
        ORDER BY FAM_V,
                IM_V,
                OT_V ;
        "),
	    'SVF_Q_2' => array("
		SELECT distinct
			 l.Lpu_OKATO AS TF_OKATO
			,RIGHT(REPLICATE('0', 6) + CAST(p.MedPersonal_Code AS VARCHAR(6)), 6) AS SCOD
			,l.Lpu_Ouz AS MCOD
			,l.Lpu_OGRN + ' ' + RIGHT(REPLICATE('0', 10) + CAST(p.MedPersonal_Code AS VARCHAR(10)), 6) AS PCOD
			,ISNULL(RTRIM(p.Person_SurName), '') AS FAM_V
			,ISNULL(RTRIM(p.Person_FirName), '') AS IM_V
			,ISNULL(RTRIM(p.Person_SecName), '') AS OT_V
			,l.Lpu_OGRN AS C_OGRN
			,ISNULL(RTRIM(wp.Dolgnost_Code), '') AS PRVD
			,ISNULL(RTRIM(wp.Dolgnost_Name), '') AS D_JOB
			,p.WorkData_begDate AS D_PRIK
			,qic.DocumentRecieveDate AS D_SER
			,c.Speciality_id AS PRVS
			,ISNULL(qc.Category_id, 0) AS KV_KAT
			,wp.DLOBeginDate AS DATE_B
			,wp.DLOEndDate AS DATE_E
			,'НЕТ ПРИМЕЧАНИЙ' AS MSG_TEXT
			,SUBSTRING(CAST(L.Lpu_Ouz AS VARCHAR(10)), 3, 2) AS KOD_TER
			,RIGHT(CAST(L.Lpu_Ouz AS VARCHAR(10)), 3) AS KOD_LPU
		FROM
			dbo.v_MedPersonal p WITH (NOLOCK)
			inner join (
				SELECT *
				FROM v_lpu l1 WITH (NOLOCK)
				WHERE exists(select top 1 LpuPeriodDLO_id from LpuPeriodDLO LPD with (nolock) where LPD.Lpu_id = l1.Lpu_id and LPD.LpuPeriodDLO_begDate <= dbo.tzGetDate() and (LPD.LpuPeriodDLO_endDate >= dbo.tzGetDate() or LPD.LpuPeriodDLO_endDate is null))
			) l ON l.Lpu_id = p.lpu_id
			cross apply (
				SELECT top 1
					 t1.DLOBeginDate
					,t1.dloEndDate
					,t1.Descr
					,t2.Code as Dolgnost_Code
					,t2.name as Dolgnost_Name
				FROM persis.v_WorkPlace t1 WITH (NOLOCK)
					-- https://redmine.swan.perm.ru/issues/8681
					-- Добавил получение кода и наименования должности по конкретному месту работы
					inner join persis.Post t2 with (nolock) on t2.id = t1.Post_id
				WHERE
					t1.MedWorker_id = p.MedPersonal_id
					-- https://redmine.swan.perm.ru/issues/8681
					-- Добавил проверку на ставку
					and ISNULL(t1.Rate, 0) > 0
					and t1.Lpu_id = p.Lpu_id
					and t1.DLOBeginDate is not null
					and t1.DLOBeginDate <= dbo.tzGetDate()
					-- врачей, которые уволились из МО передавать в Кверти в течение 90 дней, после даты увольнения. // https://redmine.swan.perm.ru/issues/7186
					and (DATEADD(day, 90, IsNull(t1.EndDate, dbo.tzGetDate())) > dbo.tzGetDate())
				order by
					t1.EndDate
			) wp
			outer apply (
				SELECT TOP 1
					qic.DocumentRecieveDate
				FROM
					persis.QualificationImprovementCourse qic WITH (NOLOCK)
				WHERE
					qic.MedWorker_id = p.MedPersonal_id
				ORDER BY
					qic.DocumentRecieveDate desc
			) qic
			outer apply (
				select top 1 Speciality_id
				from persis.[Certificate] c WITH (NOLOCK)
				where c.MedWorker_id = p.MedPersonal_id
				order by CertificateReceipDate desc
			) c
			outer apply (
				select top 1 Category_id
				from persis.[QualificationCategory] qc WITH (NOLOCK)
				where qc.MedWorker_id = p.MedPersonal_id
				order by AssigmentDate desc
			) qc
		where
			p.MedPersonal_Code is not null
			AND p.MedPersonal_Code != '0'
			and l.Lpu_id is not null
			and l.Lpu_id not in (13002457, 101) -- Минздрав исключаем и тестовую МО
		order by
			FAM_V,
			IM_V,
			OT_V
	")
		);
		$query = $queries[$query2export][0];
		//echo $query;exit;
		$result = $this->db->query($query);
		if (is_object($result)) {
			return $result->result('array');
		}
		else
		{
			return false;
		}

	}
    /**
     * Это Doc-блок
     */
	function getAllLpuNotFRMP($data)
	{
		$query = "
			select
				l.Lpu_Name AS M_NAMEF,
				l.Lpu_Nick AS M_NAMES,
				l.Org_INN as LPU_INN,
				l.Org_KPP as LPU_KPP,
				l.Lpu_OGRN AS C_OGRN,
				convert(varchar(10), cast(l.Lpu_begDate as datetime), 104) as B_DATE
			from
				(select
					l1.Lpu_id,
					l1.Lpu_Nick,
					l1.Lpu_OGRN,
					l1.Lpu_Name,
					l1.Lpu_begDate,
					o.Org_INN,
					o.Org_KPP
				from
					v_lpu l1 with (nolock)
					left outer join v_org o with (nolock) on o.Org_id = l1.org_id
				where
					l1.Lpu_endDate is null
					and l1.Lpu_Nick not like '%закрыт%'
					and l1.Lpu_Nick not like '%тест%'
				) AS l
			where
				l.Lpu_id in	(
					select
						lpu_id
					from
						persis.v_staff s with (nolock)
						inner join persis.post p with (nolock) on p.id=s.Post_id
						inner join persis.postkind pk with (nolock) on p.postkind_id=pk.id and pk.id in (1, 3, 4, 6, 8)
					where
						s.Rate > 0 and s.BeginDate <= dbo.tzGetDate() and (s.EndDate >= dbo.tzGetDate() or s.EndDate is null)
				)

				and l.Lpu_id in (
					select
						lpu_id
					from
						persis.v_staff s with (nolock)
						inner join persis.WorkPlace w with (nolock) on w.Staff_id = s.id
					where
						w.Rate >= 0.2 and w.Rate <= 3 and w.BeginDate <= dbo.tzGetDate() and (w.EndDate >= dbo.tzGetDate() or w.EndDate is null)
				)
			order by
				l.Org_KPP
		";
		$result = $this->db->query($query);
		if ( !is_object($result)) {
			return false;
		}
		$result = $result->result('array');

		foreach($result as $k=>$r) {
			foreach($data as $d) {
				if(
					//$d[0] == $r['M_NAMES'] && // МО ник
					$d[1] == $r['LPU_INN'] && // ИНН
					$d[2] == $r['LPU_KPP'] && // КПП
					$d[3] == $r['C_OGRN'] // ОГРН
				)
				unset($result[$k]);
			}
		}
		return $result;
	}

	/**
	 * Получение данных по отделению для регистратуры
	 */
	function getLpuSectionInfoForReg($data)
	{
		$sql = "
			SELECT
				rtrim(ls.LpuSection_Descr) as LpuSection_Descr,
				ls.LpuSection_id,
				ls.LpuSection_Name,
				ls.LpuSection_updDT,
				rtrim(u.pmUser_Name) as pmUser_Name,
				lp.Org_id,
				lu.LpuUnit_id,
				lu.LpuUnit_Name,
				LS.LpuSectionProfile_id,
				LS.LpuSectionProfile_Name,
				lp.Lpu_id,
				lp.Lpu_Nick
			from v_LpuSection LS with (nolock)
			left join v_LpuUnit lu with (nolock) on LS.LpuUnit_id = lu.LpuUnit_id
			left join v_pmUser u with (nolock) on u.pmUser_id = ls.pmUser_updID
			left join v_lpu_all lp with (nolock) on LS.Lpu_id = lp.Lpu_id
			where LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query(
			$sql,
			array(
				'LpuSection_id' => $data['LpuSection_id']
			)
		);
		if (is_object($result))
		{
			$res = $result->result('array');
			return $res[0];
		}
		else
		{
			return false;
		}
	}


	/**
	 * Получение примечания для отделения
	 */
	function getLpuSectionComment($data)
	{
		$sql = "
			SELECT
				rtrim(ls.LpuSection_Descr) as LpuSection_Descr,
				ls.LpuSection_updDT,
				rtrim(u.pmUser_Name) as pmUser_Name
			from v_LpuSection LS with (nolock)
			left join v_pmUser u with (nolock) on u.pmUser_id = ls.pmUser_updID
			left join v_lpu_all lp with (nolock) on LS.Lpu_id = lp.Lpu_id
			where LpuSection_id = :LpuSection_id
		";
		$res = $this->db->query(
			$sql,
			array(
				'LpuSection_id' => $data['LpuSection_id']
			)
		);
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}


	/**
	 * Сохранение комментария для отделения
	 */
	function saveLpuSectionComment($data) {
		$sql = "
			update LpuSection with (updlock)
			set
				LpuSection_Descr = :LpuSection_Descr,
				LpuSection_updDT = dbo.tzGetDate(),
				pmUser_updID = :pmUser_id
			where
				LpuSection_id = :LpuSection_id";
		$res = $this->db->query(
			$sql,
			array(
				'LpuSection_id' => $data['LpuSection_id'],
				'LpuSection_Descr' => $data['LpuSection_Descr'],
				'pmUser_id' => $data['pmUser_id']
			)
		);
		return array(
			0 => array( 'Error_Msg' => '')
		);
	} //end saveMedStaffFactComment()

	/**
	 * Получение списка средних длительностей лечения для отделения
	 */
	function loadSectionAverageDurationGrid($data)
	{
		$query = "
			SELECT
				sad.SectionAverageDuration_id,
				sad.LpuSection_id,
				sad.SectionAverageDuration_Duration,
				convert(varchar(10),sad.SectionAverageDuration_begDate,104) as SectionAverageDuration_begDate,
				convert(varchar(10),sad.SectionAverageDuration_endDate,104) as SectionAverageDuration_endDate
			from r10.v_SectionAverageDuration SAD with (nolock)
			where sad.LpuSection_id = :LpuSection_id
		";

		$res = $this->db->query($query, $data);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Получение типа здания
	 */
	function loadLpuBuildingType($data)
	{
		$query = "
			SELECT
				LpuBuildingType_id
			from v_LpuBuilding with (nolock)
			where LpuBuilding_id = :LpuBuilding_id
		";

		$res = $this->db->query($query, $data);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}

	/**
	 * Сохранение средней длительности лечения для отделения
	 */
	function saveSectionAverageDuration($data) {
		if (!empty($data['SectionAverageDuration_id']))
		{
			$proc = 'p_SectionAverageDuration_upd';
		}
		else
		{
			$proc = 'p_SectionAverageDuration_ins';
		}

		$query = '
			declare
				@Error_Code bigint,
				@Error_Message varchar(4000),
				@SectionAverageDuration_id bigint = :SectionAverageDuration_id;

			exec r10.' .$proc.'
				@SectionAverageDuration_id = @SectionAverageDuration_id output,
				@SectionAverageDuration_Duration = :SectionAverageDuration_Duration,
				@LpuSection_id = :LpuSection_id,
				@SectionAverageDuration_begDate = :SectionAverageDuration_begDate,
				@SectionAverageDuration_endDate = :SectionAverageDuration_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @SectionAverageDuration_id as SectionAverageDuration_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		';

		//echo getDebugSql($query, $data); die();

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка доступных типов служб в зависимости от уровеня структурного элемента МО
	 */
	function getAllowedMedServiceTypes($data) {

		$queryLpuType = '
			SELECT
				L.LpuType_id
			FROM
				v_Lpu_all L with (nolock)
			WHERE
				L.Lpu_id = :Lpu_id
			';
		$resultLpuType = $this->db->query($queryLpuType, array(
			'Lpu_id'=>$data['Lpu_id']
		));

		if ( !is_object($resultLpuType) || sizeof($resultLpuType->result('array'))<1) {
			return false;
		}
		$resultLpuType = $resultLpuType->result('array');
		$additionalLpuTypeWhereClause = ($resultLpuType[0]['LpuType_id']==111)?'OR ISNULL(LpuType_id,0) = :LpuType_id':'';

		$query = "
			select MedServiceType_id
			from v_MedServiceLevel with (nolock)
			where
				MedServiceLevelType_id = :MedServiceLevelType_id
				AND (ISNULL(LpuType_id,0) = 0 $additionalLpuTypeWhereClause)
		";
		$result = $this->db->query($query, array(
			'MedServiceLevelType_id'=>$data['MedServiceLevelType_id'],
			'LpuType_id'=>$resultLpuType[0]['LpuType_id']
		));

		if ( !is_object($result) ) {
			return false;
		}

		$resultArray = $result->result('array');
		$response = array();

		foreach ( $resultArray as $array ) {
			$response[] = $array['MedServiceType_id'];
		}

		return $response;
	}

	/**
	 * Получение списка структурных элементов МО
	 */
	function getLpuStructureElementList($data) {
		$level = '';
		$where = '';
		$params = array();
		$union_arr = array();
		$joinForLpuSection = '';

		if (!empty($data['LpuSection_id'])) {
			$where .= ' and t.LpuSection_pid = :LpuSection_id';
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$level = 'LpuSection';
		} else
		if (!empty($data['LpuUnit_id'])) {
			$where .= ' and t.LpuUnit_id = :LpuUnit_id';
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
			$level = 'LpuUnit';
		} else
		if (!empty($data['LpuBuilding_id'])) {
			$where .= ' and t.LpuBuilding_id = :LpuBuilding_id';
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
			$level = 'LpuBuilding';
		} else
		if (!empty($data['Lpu_id'])) {
			$where .= ' and t.Lpu_id = :Lpu_id';
			$params['Lpu_id'] = $data['Lpu_id'];
			$level = 'Lpu';
		} else {
			return false;
		}

		$queryLpuBuilding = "
			select
				LpuBuilding_id as LpuStructureElement_id,
				LpuBuilding_Code as LpuStructureElement_Code,
				LpuBuilding_Name as LpuStructureElement_Name,
				'LpuBuilding' as LpuStructure_Nick,
				'1' as LpuStructure_Order,
				'LpuBuilding_'+cast(LpuBuilding_id as varchar) as LpuStructure_id,
				Lpu_id,
				LpuBuilding_id,
				null as LpuUnit_id,
				null as LpuSection_id
			from
				v_LpuBuilding t with(nolock)
			where
				LpuBuilding_endDate is null
				{$where}
		";

		$queryLpuUnit = "
			select
				LpuUnit_id as LpuStructureElement_id,
				LpuUnit_Code as LpuStructureElement_Code,
				LpuUnit_Name as LpuStructureElement_Name,
				'LpuUnit' as LpuStructure_Nick,
				'2' as LpuStructure_Order,
				'LpuUnit_'+cast(LpuUnit_id as varchar) as LpuStructure_id,
				Lpu_id,
				LpuBuilding_id,
				LpuUnit_id,
				null as LpuSection_id
			from
				v_LpuUnit t with(nolock)
			where
				LpuUnit_endDate is null
				{$where}
		";

		$queryLpuSection = "
			select
				t.LpuSection_id as LpuStructureElement_id,
				t.LpuSection_Code as LpuStructureElement_Code,
				t.LpuSection_Name as LpuStructureElement_Name,
				'LpuSection' as LpuStructure_Nick,
				(case when t.LpuSection_pid is null then '3' else 4 end) as LpuStructure_Order,
				'LpuSection_'+cast(t.LpuSection_id as varchar) as LpuStructure_id,
				lu.Lpu_id,
				lu.LpuBuilding_id,
				lu.LpuUnit_id,
				t.LpuSection_id
			from
				v_LpuSection t with(nolock)
				inner join v_LpuUnit lu with(nolock) on lu.LpuUnit_id = t.LpuUnit_id
			where
				(1=1)
				{$where}
		";

		switch($level) {
			case 'Lpu':
				$union_arr = array($queryLpuBuilding, $queryLpuUnit, $queryLpuSection);
				break;
			case 'LpuBuilding':
				$union_arr = array($queryLpuUnit, $queryLpuSection);
				break;
			case 'LpuUnit':
				$union_arr = array($queryLpuSection);
				break;
			case 'LpuSection':
				$union_arr = array($queryLpuSection);
				break;
		}

		$union = implode(" union ", $union_arr);

		$query = "
			select
				LSE.LpuStructure_id,
				LSE.LpuStructure_Nick,
				LSE.LpuStructureElement_id,
				LSE.LpuStructureElement_Code,
				LSE.LpuStructureElement_Name,
				LSE.Lpu_id,
				LSE.LpuBuilding_id,
				LSE.LpuUnit_id,
				LSE.LpuSection_id
			from
				({$union}) LSE
			order by
				LSE.LpuStructure_Order
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение списка МО, обладающих службами ФД, не обслуживаемых ни одним консультационным центром
	 */
	function getLpuWithUnservedDiagMedService($data) {


		$queryParams = array();

		//3 - код службы функциональной диагностики
		$queryParams['MedServiceType_Code'] = 3;
		//3 - тип связи служб
		$queryParams['MedServiceLinkType_Code'] = 3;

		$query = "
			SELECT DISTINCT
				MS.Lpu_id,
                COALESCE(L.Lpu_Nick,L.Lpu_Name,L.Org_Nick,L.Org_Name,'Наименование МО не определено') as Lpu_Nick
			FROM v_MedService MS with (nolock)
				left join v_MedServiceType MST with (nolock) ON MST.MedServiceType_id = MS.MedServiceType_id
                left join v_Lpu_all L with (nolock) ON L.Lpu_id = MS.Lpu_id
			WHERE
				MST.MedServiceType_Code = :MedServiceType_Code AND
				MS.MedService_id NOT IN
				(
					SELECT MSL.MedService_lid
					FROM v_MedServiceLink MSL with (nolock)
						left join v_MedServiceLinkType MSLT with (nolock) on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
					WHERE MSLT.MedServiceLinkType_id = :MedServiceLinkType_Code
				)
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}


	/**
	 * Получение списка служб ФД выбранного МО, не обслуживающихся ни одним консультационным центром
	 */
	function getUnservedDiagMedService($data) {

		$queryParams = array();
		//3 - код службы функциональной диагностики
		$queryParams['MedServiceType_Code'] = 3;
		//3 - тип связи служб
		$queryParams['MedServiceLinkType_Code'] = 3;

		if (!isset($data['Lpu_id'])) {
			return array(array('success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор МО.'));
		} else {
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}


		$query = "
			SELECT
				MS.MedService_id
				,LTRIM(ISNULL(LU.LpuUnit_Name,'') + ' ' + ISNULL(LS.LpuSection_Name,'') + ' ' + ISNULL(LB.LpuBuilding_Name,'') + ' ' + ISNULL(MS.MedService_Name,'')) as MedService_FullName
			FROM
				v_MedService MS with (nolock)
				left join v_MedServiceType MST with (nolock) ON MST.MedServiceType_id = MS.MedServiceType_id
				left join v_LpuUnit LU with (nolock) ON LU.LpuUnit_id = MS.LpuUnit_id
				left join v_LpuSection_all LS with (nolock) ON LS.LpuSection_id = MS.LpuSection_id
				left join v_LpuBuilding LB with (nolock) ON LB.LpuBuilding_id = MS.LpuBuilding_id
			WHERE
				MS.Lpu_id = :Lpu_id AND
				MST.MedServiceType_Code = :MedServiceType_Code
				AND ISNULL(MS.LpuUnit_id,0 ) !=0 AND
				MS.MedService_id NOT IN
				(
					SELECT
						MSL.MedService_lid
					FROM
						v_MedServiceLink MSL with (nolock)
						left join v_MedServiceLinkType MSLT with (nolock) on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
					WHERE
						MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
				)

		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;

	}
	/**
	 * Привязка службы ФД к службе центра удаленной консультации
	 */
	function saveLinkFDServiceToRCCService($data) {

		$queryParams = array();

		if (!isset($data['MedService_FDid'])) {
			return array(array('success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор службы ФД.'));
		} else {
			$queryParams['MedService_FDid'] = $data['MedService_FDid'];
		}

		if (!isset($data['MedService_RCCid'])) {
			return array(array('success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор службы ЦУК.'));
		} else {
			$queryParams['MedService_RCCid'] = $data['MedService_RCCid'];
		}

		//3 - тип связи служб
		$queryParams['MedServiceLinkType_Code'] = 3;
		$queryParams['pmUser_id'] = $data['pmUser_id'];
		$queryParams['MedServiceLink_id']=(isset($data['MedServiceLink_id']))?$data['MedServiceLink_id']:null;

		$procedure = is_null($queryParams['MedServiceLink_id'])?'p_MedServiceLink_ins':'p_MedServiceLink_upd';


		$query = "
			DECLARE
			@Res bigint,
			@Error_Code bigint,
			@Error_Message varchar(4000),
			@MedServiceLinkType_id bigint,
			@SQLstring nvarchar(500),
			@ParamDefinition nvarchar(500);

			SET @SQLString =
				N'SELECT DISTINCT @MedServiceLinkType_id = MSLT.MedServiceLinkType_id
				FROM v_MedServiceLinkType MSLT with (nolock)
				WHERE MSLT.MedServiceLinkType_Code = @MedServiceLinkType_Code ';

			SET @ParamDefinition = N'@MedServiceLinkType_id bigint OUTPUT, @MedServiceLinkType_Code bigint';

			exec sp_executesql
				@SQLString,
				@ParamDefinition,
				@MedServiceLinkType_Code = :MedServiceLinkType_Code,
				@MedServiceLinkType_id = @MedServiceLinkType_id OUTPUT;

			SET @Res = :MedServiceLink_id;

			EXEC  {$procedure}
				@MedServiceLink_id = @Res output,
				@MedServiceLinkType_id = @MedServiceLinkType_id,
				@MedService_id = :MedService_RCCid,
				@MedService_lid = :MedService_FDid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT	@Res as MedServiceLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;

		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			//var_dump_exit($result->result('array'));
			return $result->result('array');
		}
		return false;

	}

	/**
	 * Привязка службы ФД к службе центра удаленной консультации
	 */
	function deleteLinkFDServiceToRCCService($data) {

		$queryParams = array();

		if (!isset($data['MedServiceLink_id'])) {
			return array(array('success' => false, 'Error_Code' => '','Error_Msg' => 'Не указан идентификатор связи служб.'));
		} else {
			$queryParams['MedServiceLink_id'] = $data['MedServiceLink_id'];
		}

		$query = "
			DECLARE
				@Error_Code bigint,
				@Error_Message varchar(4000);
			EXEC p_MedServiceLink_del
				@MedServiceLink_id = :MedServiceLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			SELECT	@Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение списка служб ФД привязанных к службе ЦУК
	 */
	function getFDServicesConnectedToRCCService($data) {

		$queryParams = array();

		if (!isset($data['MedService_id'])) {
			return array(array('success'=>true,'Error_Msg'=>'Не указан идентификатор службы ЦУК'));
		} else {
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		//3 - тип связи служб
		$queryParams['MedServiceLinkType_Code'] = 3;

		$query = '
			SELECT
				MSL.MedServiceLink_id,
				MSL.MedService_lid,
				MS.MedService_Name,
				MS.Lpu_id,
				L.Lpu_Nick,
				ISNULL(LU.LpuUnit_Name,\'\') as LpuUnit_Name,
				ISNULL(LS.LpuSection_Name,\'\') as LpuSection_Name,
				ISNULL(LB.LpuBuilding_Name,\'\') as LpuBuilding_Name
			FROM
				v_MedServiceLink MSL with (nolock)
				left join v_MedService MS with (nolock) on MSL.MedService_lid = MS.MedService_id
				left join v_MedServiceLinkType MSLT with (nolock) on MSLT.MedServiceLinkType_id = MSL.MedServiceLinkType_id
				left join v_Lpu L with (nolock) on L.Lpu_id = MS.Lpu_id
				left join v_LpuSection LS with (nolock) on LS.LpuSection_id = MS.LpuSection_id
				left join v_LpuUnit LU  with (nolock) on LU.LpuUnit_id = MS.LpuUnit_id
				left join v_LpuBuilding LB  with (nolock) on LB.LpuBuilding_id = MS.LpuBuilding_id
			WHERE
				MSLT.MedServiceLinkType_Code = :MedServiceLinkType_Code
				AND MSL.MedService_id = :MedService_id
			';

		$result = $this->db->query($query, $queryParams);

		if (is_object($result)) {
			return $result->result('array');
		}
		return false;

	}

	/**
	 * Получение спсика МО по адресу
	 */
	function getLpuListByAddress($data) {
		$where = "(1=1)";
		$params = array();

		if (!empty($data['KLCity_id'])) {
			$where .= " and KLCity_id = :KLCity_id";
			$params['KLCity_id'] = $data['KLCity_id'];
		}
		if (!empty($data['KLTown_id'])) {
			$where .= " and KLTown_id = :KLTown_id";
			$params['KLTown_id'] = $data['KLTown_id'];
		}

		$query = "
			select
				L.Lpu_id,
				L.Lpu_Nick,
				L.Lpu_Name,
				L.Lpu_BegDate,
				L.Lpu_EndDate
			from v_Lpu L with(nolock)
				inner join v_Address PA with(nolock) on PA.Address_id = L.PAddress_id
			where
				{$where}
		";
		$result = $this->db->query($query, $params);

		if (is_object($result)) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка дополнительных профилей отделения
	 */
	function loadLpuSectionLpuSectionProfileGrid($data) {
		$query = "
			select
				 lslsp.LpuSectionLpuSectionProfile_id
				,lsp.LpuSectionProfile_id
				,lsp.LpuSectionProfile_Code
				,lsp.LpuSectionProfile_Name
				,convert(varchar(10), lslsp.LpuSectionLpuSectionProfile_begDate, 104) as LpuSectionLpuSectionProfile_begDate
				,convert(varchar(10), lslsp.LpuSectionLpuSectionProfile_endDate, 104) as LpuSectionLpuSectionProfile_endDate
				,1 as RecordStatus_Code
			from dbo.v_LpuSectionLpuSectionProfile lslsp with (nolock)
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where
				lslsp.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Получение списка мед. оборудования
	 */
	function loadLpuSectionMedProductTypeLinkGrid($data) {
		$query = "
			select
				 lslsp.LpuSectionMedProductTypeLink_id
				,mpt.MedProductType_id
				,mpt.MedProductType_Name
				,lslsp.LpuSectionMedProductTypeLink_TotalAmount
				,lslsp.LpuSectionMedProductTypeLink_IncludePatientKVI
				,lslsp.LpuSectionMedProductTypeLink_IncludeReanimation
				,convert(varchar(10), lslsp.LpuSectionMedProductTypeLink_begDT, 104) as LpuSectionMedProductTypeLink_begDT
				,convert(varchar(10), lslsp.LpuSectionMedProductTypeLink_endDT, 104) as LpuSectionMedProductTypeLink_endDT
				,1 as RecordStatus_Code
			from dbo.v_LpuSectionMedProductTypeLink lslsp with (nolock)
				inner join passport.v_MedProductType mpt with (nolock) on mpt.MedProductType_id = lslsp.MedProductType_id
			where
				lslsp.LpuSection_id = :LpuSection_id
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}

	/**
	 * Сохранение дополнительного профиля отделения
	 */
	function saveLpuSectionLpuSectionProfile($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :LpuSectionLpuSectionProfile_id;

			exec dbo.p_LpuSectionLpuSectionProfile_" . (!empty($data['LpuSectionLpuSectionProfile_id']) && $data['LpuSectionLpuSectionProfile_id'] > 0 ? "upd" : "ins") . "
				@LpuSectionLpuSectionProfile_id = @Res output,
				@Server_id = :Server_id,
				@LpuSection_id = :LpuSection_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSectionLpuSectionProfile_begDate = :LpuSectionLpuSectionProfile_begDate,
				@LpuSectionLpuSectionProfile_endDate = :LpuSectionLpuSectionProfile_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as LpuSectionLpuSectionProfile_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionLpuSectionProfile_id' => (!empty($data['LpuSectionLpuSectionProfile_id']) && $data['LpuSectionLpuSectionProfile_id'] > 0 ? $data['LpuSectionLpuSectionProfile_id'] : NULL),
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'LpuSectionLpuSectionProfile_begDate' => $data['LpuSectionLpuSectionProfile_begDate'],
			'LpuSectionLpuSectionProfile_endDate' => (!empty($data['LpuSectionLpuSectionProfile_endDate']) ? $data['LpuSectionLpuSectionProfile_endDate'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление дополнительного профиля отделения
	 */
	function deleteLpuSectionLpuSectionProfile($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec dbo.p_LpuSectionLpuSectionProfile_del
				@LpuSectionLpuSectionProfile_id = :LpuSectionLpuSectionProfile_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionLpuSectionProfile_id' => $data['LpuSectionLpuSectionProfile_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение мед. оборудования
	 */
	function saveLpuSectionMedProductTypeLink($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :LpuSectionMedProductTypeLink_id;

			exec dbo.p_LpuSectionMedProductTypeLink_" . (!empty($data['LpuSectionMedProductTypeLink_id']) && $data['LpuSectionMedProductTypeLink_id'] > 0 ? "upd" : "ins") . "
				@LpuSectionMedProductTypeLink_id = @Res output,
				@LpuSection_id = :LpuSection_id,
				@MedProductType_id = :MedProductType_id,
				@LpuSectionMedProductTypeLink_TotalAmount = :LpuSectionMedProductTypeLink_TotalAmount,
				@LpuSectionMedProductTypeLink_IncludePatientKVI = :LpuSectionMedProductTypeLink_IncludePatientKVI,
				@LpuSectionMedProductTypeLink_IncludeReanimation = :LpuSectionMedProductTypeLink_IncludeReanimation,
				@LpuSectionMedProductTypeLink_begDT = :LpuSectionMedProductTypeLink_begDT,
				@LpuSectionMedProductTypeLink_endDT = :LpuSectionMedProductTypeLink_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as LpuSectionMedProductTypeLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionMedProductTypeLink_id' => (!empty($data['LpuSectionMedProductTypeLink_id']) && $data['LpuSectionMedProductTypeLink_id'] > 0 ? $data['LpuSectionMedProductTypeLink_id'] : NULL),
			'LpuSection_id' => $data['LpuSection_id'],
			'MedProductType_id' => $data['MedProductType_id'],
			'LpuSectionMedProductTypeLink_TotalAmount' => $data['LpuSectionMedProductTypeLink_TotalAmount'],
			'LpuSectionMedProductTypeLink_IncludePatientKVI' => $data['LpuSectionMedProductTypeLink_IncludePatientKVI'],
			'LpuSectionMedProductTypeLink_IncludeReanimation' => $data['LpuSectionMedProductTypeLink_IncludeReanimation'],
			'LpuSectionMedProductTypeLink_begDT' => $data['LpuSectionMedProductTypeLink_begDT'],
			'LpuSectionMedProductTypeLink_endDT' => (!empty($data['LpuSectionMedProductTypeLink_endDT']) ? $data['LpuSectionMedProductTypeLink_endDT'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление мед. оборудования
	 */
	function deleteLpuSectionMedProductTypeLink($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec dbo.p_LpuSectionMedProductTypeLink_del
				@LpuSectionMedProductTypeLink_id = :LpuSectionMedProductTypeLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionMedProductTypeLink_id' => $data['LpuSectionMedProductTypeLink_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Сохранение вида оказания  МП
	 */
	function saveLpuSectionMedicalCareKind($data) {
		$query = "
			declare
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);

			set @Res = :LpuSectionMedicalCareKind_id;

			exec dbo.p_LpuSectionMedicalCareKind_" . (!empty($data['LpuSectionMedicalCareKind_id']) && $data['LpuSectionMedicalCareKind_id'] > 0 ? "upd" : "ins") . "
				@LpuSectionMedicalCareKind_id = @Res output,
				@Server_id = :Server_id,
				@LpuSection_id = :LpuSection_id,
				@MedicalCareKind_id = :MedicalCareKind_id,
				@LpuSectionMedicalCareKind_begDate = :LpuSectionMedicalCareKind_begDate,
				@LpuSectionMedicalCareKind_endDate = :LpuSectionMedicalCareKind_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @Res as LpuSectionMedicalCareKind_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionMedicalCareKind_id' => (!empty($data['LpuSectionMedicalCareKind_id']) && $data['LpuSectionMedicalCareKind_id'] > 0 ? $data['LpuSectionMedicalCareKind_id'] : NULL),
			'Server_id' => $data['Server_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'MedicalCareKind_id' => $data['MedicalCareKind_id'],
			'LpuSectionMedicalCareKind_begDate' => $data['LpuSectionMedicalCareKind_begDate'],
			'LpuSectionMedicalCareKind_endDate' => (!empty($data['LpuSectionMedicalCareKind_endDate']) ? $data['LpuSectionMedicalCareKind_endDate'] : NULL),
			'pmUser_id' => $data['pmUser_id']
		);


		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Удаление вида оказания  МП
	 */
	function deleteLpuSectionMedicalCareKind($data) {
		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);

			exec dbo.p_LpuSectionMedicalCareKind_del
				@LpuSectionMedicalCareKind_id = :LpuSectionMedicalCareKind_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;

			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$queryParams = array(
			'LpuSectionMedicalCareKind_id' => $data['LpuSectionMedicalCareKind_id']
		);

		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение списка профилей отделений
	 */
	function loadLpuSectionProfileList($data) {
		$params = array();
		$response = array();
		$list = array();

		if (!empty($data['LpuSection_ids'])) {
			$list = json_decode($data['LpuSection_ids']);
		}
		if (!empty($data['LpuSection_id'])) {
			$list = array($data['LpuSection_id']);
		}

		if (count($list) == 0) {
			return $response;
		}
		$list_str = implode(',', $list);

		$query = "
			select
				lsp.LpuSectionProfile_id,
				lsp.LpuSectionProfile_Code,
				lsp.LpuSectionProfile_Name,
				lsp.LpuSectionProfile_SysNick
			from
				v_LpuSection ls with(nolock)
				inner join v_LpuSectionProfile lsp with(nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			where
				ls.LpuSection_id in ({$list_str})
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		} else {
			$response = $result->result('array');
			return $response;
		}
	}

	/**
	 * Получение списка кодов отделений
	 */
	function loadLpuSectionCodeList($data) {
		$filterList = array();
		$queryParams = array();

		if ( !empty($data['LpuUnitType_id']) || !empty($data['LpuSectionProfile_id']) ) {
			switch ( $data['session']['region']['nick'] ) {
				case 'pskov': $scheme = 'r' . $data['session']['region']['number']; break;
				default: $scheme = $this->_scheme; break;
			}

			$filter = "exists (
				select top 1 LpuSectionCodeLink_id
				from {$scheme}.LpuSectionCodeLink with (nolock)
				where LpuSectionCode_id = lsc.LpuSectionCode_id
			";

			if ( !empty($data['LpuUnitType_id']) ) {
				$filter .= " and LpuUnitType_id = :LpuUnitType_id";
				$queryParams['LpuUnitType_id'] = $data['LpuUnitType_id'];
			}

			if ( !empty($data['LpuSectionProfile_id']) ) {
				$filter .= " and ISNULL(LpuSectionProfile_id, :LpuSectionProfile_id) = :LpuSectionProfile_id";
				$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			}

			$filter .= ")";

			$filterList[] = $filter;
		}

		if ( !empty($data['LpuSectionCode_id']) ) {
			$filterList[] = "lsc.LpuSectionCode_id = :LpuSectionCode_id";
			$queryParams['LpuSectionCode_id'] = $data['LpuSectionCode_id'];
		}

		if ( !empty($data['LpuSectionCode_begDate']) ) {
			$filterList[] = "(lsc.LpuSectionCode_endDT is null or lsc.LpuSectionCode_endDT >= :LpuSectionCode_begDate)";
			$queryParams['LpuSectionCode_begDate'] = $data['LpuSectionCode_begDate'];
		}

		if ( !empty($data['LpuSectionCode_endDate']) ) {
			$filterList[] = "(lsc.LpuSectionCode_begDT is null or lsc.LpuSectionCode_begDT <= :LpuSectionCode_endDate)";
			$queryParams['LpuSectionCode_endDate'] = $data['LpuSectionCode_endDate'];
		}

		$query = "
			select
				lsc.LpuSectionCode_id,
				lsc.LpuSectionCode_Code,
				lsc.LpuSectionCode_Name,
				--lsc.LpuSectionCode_begDT,
				convert(varchar, lsc.LpuSectionCode_begDT, 104) AS LpuSectionCode_begDT,
				convert(varchar, lsc.LpuSectionCode_endDT, 104) AS LpuSectionCode_endDT
			from
				v_LpuSectionCode lsc with (nolock)
			" . (count($filterList) > 0 ? "where " . implode(' and ', $filterList) : "") . "
		";

        //echo getDebugSQL($query, $queryParams);exit;
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Проверка, является ли подразделение СМП головным
	 */
	function _lpuBuildingIsHeadSmpUnit($data) {

		if (!isset($data['LpuBuilding_id'])) {
			return false;
		}

		$query = "
			SELECT
				SUP.SmpUnitParam_id
			FROM
				v_SmpUnitParam SUP with (nolock)
			WHERE
				SUP.LpuBuilding_pid = :LpuBuilding_id
			";
		$result = $this->db->query($query, array(
			'LpuBuilding_id' => $data['LpuBuilding_id']
		));
		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	/**
	 * Получение списка типов подстанций СМП
	 */
	function getSmpUnitTypes($data) {

		$isHeadSmpUnit_result = $this->_lpuBuildingIsHeadSmpUnit($data);
		$filter = '(1=1)';
		$queryParams = array();
		
		$filter .= 'AND SUT.SmpUnitType_Code in (2,4,5)';

		if ($isHeadSmpUnit_result && !empty($isHeadSmpUnit_result[0]) && empty($isHeadSmpUnit_result[0]['SmpUnitParam_id']) ) {
			$filter .= 'AND SUT.SmpUnitType_id != :SmpUnitType_id';
			$queryParams['SmpUnitType_id'] = 6;
		}

		$query = "
			SELECT
				SUT.SmpUnitType_id,
				SUT.SmpUnitType_Name,
				SUT.SmpUnitType_Code
			FROM
				v_SmpUnitType SUT with (nolock)
			WHERE
				{$filter}
			";
			
		$result = $this->db->query($query,$queryParams);
		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получаем список подстанций со всех МО с типом СМП
	 *
	 * @param array $data Массив входящих данных для фильтрации запроса
	 * @return bool || array
	 */
	public function getLpuBuildingsForFilials($data) {

		$LpuBuildingType = $this -> loadLpuBuildingType($data);

		if($LpuBuildingType && !empty($LpuBuildingType[0]["LpuBuildingType_id"])){
			$data["LpuBuildingType_id"] = $LpuBuildingType[0]["LpuBuildingType_id"];
		}
		else{
			return array('success' => false, 'Error_Msg' => 'Ошибка при определении типа текущей подстнции');
		}

		$sql = "
			SELECT
				lb.LpuBuilding_id,
				CASE WHEN l.Lpu_id IS NULL THEN lb.LpuBuilding_Name ELSE lb.LpuBuilding_Name + ' (' + l.Lpu_Nick + ')' END as LpuBuilding_Name,
				lb.LpuBuilding_Code
			FROM
				v_LpuBuilding lb with (nolock)
				LEFT JOIN v_Lpu l with (nolock) ON(l.Lpu_id=lb.Lpu_id)
				INNER JOIN v_SmpUnitParam sup with (nolock) ON(sup.LpuBuilding_id=lb.LpuBuilding_id)
				-- Только с типом оперативный отдел
				INNER JOIN v_SmpUnitType sut with (nolock) ON(sut.SmpUnitType_id=sup.SmpUnitType_id AND sut.SmpUnitType_Code=4)
			WHERE
				-- Поля с кодом нет, поэтому выбираем по id
				lb.LpuBuildingType_id = :LpuBuildingType_id
			ORDER BY
				l.Lpu_Nick ASC,
				lb.LpuBuilding_Name ASC
		";

		$result = $this->db->query($sql, $data);
		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}

		//return $this->db->query($sql)->result_array();

		// Временно оставил старый запрос
		//$query = "
		//	SELECT
		//		LB.LpuBuilding_id,
		//		LB.LpuBuilding_Name
		//	FROM
		//		v_LpuBuilding LB with (nolock)
		//		left join v_SmpUnitParam SUP with (nolock) on SUP.LpuBuilding_id = LB.LpuBuilding_id
		//	WHERE
		//		LB.LpuBuildingType_id = 27
		//		AND ISNULL(SUP.LpuBuilding_pid,0) = 0
		//		AND LB.LpuBuilding_id != :LpuBuilding_id
		//		AND LB.Lpu_id = :Lpu_id
		//";
		//$result = $this->db->query($query,array(
		//	'Lpu_id' => $data['Lpu_id'],
		//	'LpuBuilding_id' => $data['LpuBuilding_id'],
		//));
	}

	/**
	 * Сохранение таймеров подстанции
	 */
	public function saveSmpUnitTimes($data){
		$sql = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			SET @Res = :SmpUnitTimes_id;
			EXEC p_SmpUnitTimes_insOnDuplicateKey
				@SmpUnitTimes_id = @Res output,
				@LpuBuilding_id = :LpuBuilding_id,
				@minTimeSMP = :minTimeSMP,
				@maxTimeSMP = :maxTimeSMP,
				@minTimeNMP = :minTimeNMP,
				@maxTimeNMP = :maxTimeNMP,
				@minResponseTimeNMP = :minResponseTimeNMP,
				@maxResponseTimeNMP = :maxResponseTimeNMP,
				
				@minResponseTimeET = :minResponseTimeET,
				@minResponseTimeETNMP = :minResponseTimeETNMP,
				@maxResponseTimeET = :maxResponseTimeET,
				@maxResponseTimeETNMP = :maxResponseTimeETNMP,
				@ArrivalTimeET = :ArrivalTimeET,
				@ArrivalTimeETNMP = :ArrivalTimeETNMP,
				@ServiceTimeET = :ServiceTimeET,
				@DispatchTimeET = :DispatchTimeET,
				@LunchTimeET = :LunchTimeET,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @Res as SmpUnitTimes_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$params = array(
			'SmpUnitTimes_id' => null,
			'pmUser_id' => $data['pmUser_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'minTimeSMP' => (int)$data['minTimeSMP'],
			'maxTimeSMP' => (int)$data['maxTimeSMP'],
			'minTimeNMP' => (int)$data['minTimeNMP'],
			'maxTimeNMP' => (int)$data['maxTimeNMP'],
			'minResponseTimeNMP' => (int)$data['minResponseTimeNMP'],
			'maxResponseTimeNMP' => (int)$data['maxResponseTimeNMP'],
			
			'minResponseTimeET' => (int)$data['minResponseTimeET'],
			'minResponseTimeETNMP' => (int)$data['minResponseTimeETNMP'],
			'maxResponseTimeET' => (int)$data['maxResponseTimeET'],
			'maxResponseTimeETNMP' => (int)$data['maxResponseTimeETNMP'],
			'ArrivalTimeET' => (int)$data['ArrivalTimeET'],
			'ArrivalTimeETNMP' => (int)$data['ArrivalTimeETNMP'],
			'ServiceTimeET' => (int)$data['ServiceTimeET'],
			'DispatchTimeET' => (int)$data['DispatchTimeET'],
			'LunchTimeET' => (int)$data['LunchTimeET']
		);
		//var_dump(getDebugSQL($sql, $params)); exit;

		return $this->db->query($sql, $params)->result_array();
	}

	/**
	 * Сохранение параметров подстанции
	 */
	public function saveSmpUnitParams($data){
		if (!empty($data['SmpUnitParam_id'])) {
			$procedure = 'p_SmpUnitParam_upd';
		} else {
			$procedure = 'p_SmpUnitParam_ins';
			$data['SmpUnitParam_id'] = null;
		}

		if (empty($data['LpuBuilding_pid'])) {
			$data['LpuBuilding_pid'] = null;
		}

		$sql = "
			DECLARE
				@Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			SET @Res = :SmpUnitParam_id;
			EXEC ".$procedure."
				@SmpUnitParam_id = @Res output,
				@SmpUnitType_id = :SmpUnitType_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuBuilding_pid = :LpuBuilding_pid,
				@SmpUnitParam_IsAutoBuilding = :SmpUnitParam_IsAutoBuilding,
				@SmpUnitParam_IsOverCall = :SmpUnitParam_IsOverCall,
				@SmpUnitParam_IsCallSenDoc = :SmpUnitParam_IsCallSenDoc,
				@SmpUnitParam_IsCall112 = :SmpUnitParam_IsCall112,
				@SmpUnitParam_IsSignalBeg = :SmpUnitParam_IsSignalBeg,
				@SmpUnitParam_IsSignalEnd = :SmpUnitParam_IsSignalEnd,
				@SmpUnitParam_IsKTPrint = :SmpUnitParam_IsKTPrint,
				@SmpUnitParam_IsAutoEmergDuty = :SmpUnitParam_IsAutoEmergDuty,
				@SmpUnitParam_IsAutoEmergDutyClose = :SmpUnitParam_IsAutoEmergDutyClose,
				@SmpUnitParam_IsSendCall = :SmpUnitParam_IsSendCall,
				@SmpUnitParam_IsViewOther = :SmpUnitParam_IsViewOther,
				@SmpUnitParam_IsCancldCall = :SmpUnitParam_IsCancldCall,
				@SmpUnitParam_IsCancldDisp = :SmpUnitParam_IsCancldDisp,
				@SmpUnitParam_IsCallControll = :SmpUnitParam_IsCallControll,
				@SmpUnitParam_IsSaveTreePath = :SmpUnitParam_IsSaveTreePath,
				@SmpUnitParam_IsShowAllCallsToDP = :SmpUnitParam_IsShowAllCallsToDP,
				@SmpUnitParam_IsShowCallCount = :SmpUnitParam_IsShowCallCount,
				@SmpUnitParam_IsNoMoreAssignCall = :SmpUnitParam_IsNoMoreAssignCall,
				@SmpUnitParam_MaxCallCount = :SmpUnitParam_MaxCallCount,
				@SmpUnitParam_IsCallApproveSend = :SmpUnitParam_IsCallApproveSend,
				@SmpUnitParam_IsNoTransOther = :SmpUnitParam_IsNoTransOther,
				@SmpUnitParam_IsDenyCallAnswerDisp = :SmpUnitParam_IsDenyCallAnswerDisp,
				@SmpUnitParam_IsDispNoControl = :SmpUnitParam_IsDispNoControl,
				@SmpUnitParam_IsDocNoControl = :SmpUnitParam_IsDocNoControl,
				@SmpUnitParam_IsDispOtherControl = :SmpUnitParam_IsDispOtherControl,
				@SmpUnitParam_IsGroupSubstation = :SmpUnitParam_IsGroupSubstation,
				@Lpu_eid = :Lpu_eid,
				@LpuBuilding_eid = :LpuBuilding_eid,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @Res as SmpUnitParam_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$params = array(
			'pmUser_id' => $data['pmUser_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'LpuBuilding_pid' => $data['LpuBuilding_pid'],
			'SmpUnitType_id' => $data['SmpUnitType_id'],
			'SmpUnitParam_id' => $data['SmpUnitParam_id'],
			'SmpUnitParam_IsAutoBuilding' => $data['SmpUnitParam_IsAutoBuilding'],
			'SmpUnitParam_IsCall112' => $data['SmpUnitParam_IsCall112'],
			'SmpUnitParam_IsOverCall' => $data['SmpUnitParam_IsOverCall'],
			'SmpUnitParam_IsCallSenDoc' => $data['SmpUnitParam_IsCallSenDoc'],
			'SmpUnitParam_IsSignalBeg' => $data['SmpUnitParam_IsSignalBeg'],
			'SmpUnitParam_IsSignalEnd' => $data['SmpUnitParam_IsSignalEnd'],
			'SmpUnitParam_IsKTPrint' => $data['SmpUnitParam_IsKTPrint'],
			'SmpUnitParam_IsAutoEmergDuty' => $data['SmpUnitParam_IsAutoEmergDuty'],
			'SmpUnitParam_IsAutoEmergDutyClose' => $data['SmpUnitParam_IsAutoEmergDutyClose'],
			'SmpUnitParam_IsSendCall' => $data['SmpUnitParam_IsSendCall'],
			'SmpUnitParam_IsViewOther' => $data['SmpUnitParam_IsViewOther'],
			'SmpUnitParam_IsCallControll' => $data['SmpUnitParam_IsCallControll'],
			'SmpUnitParam_IsSaveTreePath' => $data['SmpUnitParam_IsSaveTreePath'],
			'SmpUnitParam_IsShowAllCallsToDP' => $data['SmpUnitParam_IsShowAllCallsToDP'],
			'SmpUnitParam_IsCancldCall' => $data['SmpUnitParam_IsCancldCall'],
			'SmpUnitParam_IsCancldDisp' => $data['SmpUnitParam_IsCancldDisp'],
			'SmpUnitParam_IsNoMoreAssignCall' => $data['SmpUnitParam_IsNoMoreAssignCall'],
			'SmpUnitParam_IsShowCallCount' => $data['SmpUnitParam_IsShowCallCount'],
			'SmpUnitParam_MaxCallCount' => $data['SmpUnitParam_MaxCallCount'],
			'Lpu_eid' => $data['Lpu_eid'],
			'LpuBuilding_eid' => $data['LpuBuilding_eid'],
			//'SmpUnitParam_IsAutoHome' => $data['SmpUnitParam_IsAutoHome'],
			//'SmpUnitParam_IsPrescrHome' => $data['SmpUnitParam_IsPrescrHome'],
			'SmpUnitParam_IsCallApproveSend' => $data['SmpUnitParam_IsCallApproveSend'],
			'SmpUnitParam_IsNoTransOther' => $data['SmpUnitParam_IsNoTransOther'],
			'SmpUnitParam_IsDenyCallAnswerDisp' => $data['SmpUnitParam_IsDenyCallAnswerDisp'],
			'SmpUnitParam_IsDispNoControl' => $data['SmpUnitParam_IsDispNoControl'],
			'SmpUnitParam_IsDocNoControl' => $data['SmpUnitParam_IsDocNoControl'],
			'SmpUnitParam_IsDispOtherControl' => $data['SmpUnitParam_IsDispOtherControl'],
			'SmpUnitParam_IsGroupSubstation' => $data['SmpUnitParam_IsGroupSubstation']
		);
		//var_dump(getDebugSQL($sql, $params)); exit;
		return $this->queryResult($sql, $params);
	}

	/**
	 * Получение информации о подстанции СМП
	 */
	public function getSmpUnitData($data){
		$sql = "
			SELECT TOP 1
				SUP.SmpUnitParam_id,
				SUP.LpuBuilding_id,
				SUP.SmpUnitType_id,
				SUT.SmpUnitType_Code,
				SUP.LpuBuilding_pid,
				LB.Lpu_id,
				CASE WHEN ISNULL(LB.LpuBuilding_IsUsingMicrophone, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsUsingMicrophone
				--CASE WHEN ISNULL(SUP.SmpUnitParam_IsAutoHome, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsAutoHome,
				--CASE WHEN ISNULL(SUP.SmpUnitParam_IsPrescrHome, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsPrescrHome
			FROM
				v_SmpUnitParam SUP with (nolock)
				left join v_SmpUnitType SUT with (nolock) ON(SUT.SmpUnitType_id=SUP.SmpUnitType_id)
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = SUP.LpuBuilding_id
			WHERE
				SUP.LpuBuilding_id = :LpuBuilding_id
		";

		$result = $this->db->query($sql, array(
				'LpuBuilding_id' => $data['LpuBuilding_id']
			))->result_array();

		if (empty($result)) {
			$result = array(array('LpuBuilding_id' => $data['LpuBuilding_id'], 'SmpUnitParam_id' => null, 'SmpUnitType_id' => null, "LpuBuilding_pid" => null));
		}

		return $result;
	}

	/**
	 * Получение информации о таймерах подстанции СМП
	 */
	public function getLpuBuildingData($data){

		$sql = "
			SELECT top 1
				LB.LpuBuilding_id,
				LB.LpuBuilding_IsPrint,
				LB.LpuBuildingType_id,
				LB.Lpu_id,
				SUP.LpuBuilding_pid,
				SUP.SmpUnitParam_id,
				SUP.SmpUnitType_id,
				T.SmpUnitType_Code,
				SUP.Lpu_eid,
				SUP.LpuBuilding_eid,

				CASE WHEN ISNULL(SUP.SmpUnitParam_IsAutoBuilding, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsAutoBuilding,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsCall112, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCall112,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsSignalBeg, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsSignalBeg,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsSignalEnd, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsSignalEnd,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsOverCall, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsOverCall,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsCallSenDoc, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCallSenDoc,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsKTPrint, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsKTPrint,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsAutoEmergDuty, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsAutoEmergDuty,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsAutoEmergDutyClose, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsAutoEmergDutyClose,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsSendCall, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsSendCall,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsViewOther, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsViewOther,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsCancldCall, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCancldCall,
			 	CASE WHEN ISNULL(SUP.SmpUnitParam_IsCancldDisp, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCancldDisp,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsCallControll, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCallControll,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsSaveTreePath, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsSaveTreePath,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsNoMoreAssignCall, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsNoMoreAssignCall,
				--CASE WHEN ISNULL(SUP.SmpUnitParam_IsAutoHome, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsAutoHome,
				--CASE WHEN ISNULL(SUP.SmpUnitParam_IsPrescrHome, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsPrescrHome,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsCallApproveSend, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsCallApproveSend,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsNoTransOther, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsNoTransOther,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsDenyCallAnswerDisp, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsDenyCallAnswerDisp,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsDispNoControl, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsDispNoControl,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsDocNoControl, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsDocNoControl,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsDispOtherControl, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsDispOtherControl,
				CASE WHEN ISNULL(SUP.SmpUnitParam_IsGroupSubstation, 1) = 1 THEN 'false' else 'true' END as SmpUnitParam_IsGroupSubstation,
				CASE WHEN SUP.SmpUnitParam_IsShowAllCallsToDP IS NULL AND dbo.getregion() in (30, 59) THEN 'true' else
					CASE WHEN ISNULL(SUP.SmpUnitParam_IsShowAllCallsToDP, 1) = 1 THEN 'false' else 'true' END
				END as SmpUnitParam_IsShowAllCallsToDP,
				CASE WHEN SUP.SmpUnitParam_IsShowCallCount IS NULL AND dbo.getregion() not in (91) THEN 'true' else
					CASE WHEN ISNULL(SUP.SmpUnitParam_IsShowCallCount, 1) = 1 THEN 'false' else 'true' END
				END as SmpUnitParam_IsShowCallCount,

				SUP.SmpUnitParam_MaxCallCount,

				ISNULL(LB.LpuBuildingSmsType_id, 1) as LpuBuildingSmsType_id,
				
				CASE WHEN ISNULL(LB.LpuBuilding_setDefaultAddressCity, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_setDefaultAddressCity,
				CASE WHEN ISNULL(LB.LpuBuilding_IsEmergencyTeamDelay, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsEmergencyTeamDelay,
				CASE WHEN ISNULL(LB.LpuBuilding_IsUsingMicrophone, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsUsingMicrophone,
				CASE WHEN ISNULL(LB.LpuBuilding_IsWithoutBalance, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsWithoutBalance,
							
				CASE WHEN ISNULL(LB.LpuBuilding_IsCallCancel, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsCallCancel,
				CASE WHEN ISNULL(LB.LpuBuilding_IsCallDouble, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsCallDouble,
				CASE WHEN ISNULL(LB.LpuBuilding_IsCallSpecTeam, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsCallSpecTeam,
				CASE WHEN ISNULL(LB.LpuBuilding_IsCallReason, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsCallReason,
				CASE WHEN ISNULL(LB.LpuBuilding_IsDenyCallAnswerDoc, 1) = 1 THEN 'false' else 'true' END as LpuBuilding_IsDenyCallAnswerDoc,

				COALESCE(PSUT.minTimeSMP, SUT.minTimeSMP, 0) as minTimeSMP,
				COALESCE(PSUT.maxTimeSMP, SUT.maxTimeSMP, 0) as maxTimeSMP,
				COALESCE(PSUT.minTimeNMP, SUT.minTimeNMP, 0) as minTimeNMP,
				COALESCE(PSUT.maxTimeNMP, SUT.maxTimeNMP, 0) as maxTimeNMP,
				COALESCE(PSUT.minResponseTimeNMP, SUT.minResponseTimeNMP, 0) as minResponseTimeNMP,
				COALESCE(PSUT.maxResponseTimeNMP, SUT.maxResponseTimeNMP, 0) as maxResponseTimeNMP,
				
				COALESCE(PSUT.minResponseTimeET, SUT.minResponseTimeET, 0.25) as minResponseTimeET,
				COALESCE(PSUT.minResponseTimeETNMP, SUT.minResponseTimeETNMP, 0.25) as minResponseTimeETNMP,
				COALESCE(PSUT.maxResponseTimeET, SUT.maxResponseTimeET, 2) as maxResponseTimeET,
				COALESCE(PSUT.maxResponseTimeETNMP, SUT.maxResponseTimeETNMP, 2) as maxResponseTimeETNMP,
				COALESCE(PSUT.ArrivalTimeET, SUT.ArrivalTimeET, 20) as ArrivalTimeET,
				COALESCE(PSUT.ArrivalTimeETNMP, SUT.ArrivalTimeETNMP, 20) as ArrivalTimeETNMP,
				COALESCE(PSUT.ServiceTimeET, SUT.ServiceTimeET, 40) as ServiceTimeET,
				COALESCE(PSUT.DispatchTimeET, SUT.DispatchTimeET, 15) as DispatchTimeET,
				COALESCE(PSUT.LunchTimeET, SUT.LunchTimeET) as LunchTimeET
			FROM
				v_LpuBuilding LB with (nolock)
				outer apply (
					select top 1 *
					from v_SmpUnitTimes with (nolock)
					where LpuBuilding_id = LB.LpuBuilding_id
				) SUT
				outer apply (
					select top 1 *
					from v_SmpUnitParam with (nolock)
					where LpuBuilding_id = LB.LpuBuilding_id
					order by SmpUnitParam_id desc
				) SUP
				left join v_SmpUnitType T with (nolock) ON(T.SmpUnitType_id=SUP.SmpUnitType_id)
				outer apply (
					select top 1 *
					from v_SmpUnitTimes with (nolock)
					where LpuBuilding_id = SUP.LpuBuilding_pid
				) PSUT
			WHERE
				LB.LpuBuilding_id = :LpuBuilding_id
		";

		$result = $this->db->query($sql, array(
			'LpuBuilding_id' => $data['LpuBuilding_id']
		))->result_array();

		return $result;
	}

	/**
	 * Получение адреса для структурного уровня лпу
	 */
	function getAddressByLpuStructure($data) {
		$params = array();

		if (!empty($data['LpuUnit_id'])) {
			$object = "LpuUnit";
			$params['object_value'] = $data['LpuUnit_id'];
			$address = "Address";
		} else if (!empty($data['LpuBuilding_id'])) {
			$object = "LpuBuilding";
			$params['object_value'] = $data['LpuBuilding_id'];
			$address = "Address";
		} else if (!empty($data['Lpu_id'])) {
			$object = "Lpu";
			$params['object_value'] = $data['Lpu_id'];
			$address = "PAddress";
		} else if (!empty($data['Org_id'])) {
			$object = "Org";
			$params['object_value'] = $data['Org_id'];
			$address = "PAddress";
		}

		$query = "
			select top 1
				A.Address_Zip,
				A.KLCountry_id,
				A.KLRgn_id,
				A.KLSubRgn_id,
				A.KLCity_id,
				A.KLTown_id,
				A.KLStreet_id,
				A.Address_House,
				A.Address_Corpus,
				A.Address_Flat,
				A.Address_Address
			from
				v_{$object} Obj with(nolock)
				left join v_Address A with(nolock) on A.Address_id = Obj.{$address}_id
			where
				Obj.{$object}_id = :object_value
		";

		//echo getDebugSQL($query, $params);exit;
		$result = $this->db->query($query, $params);

		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при получении адреса');
		}

		$response = $result->result('array');

		return array('data' => $response[0]);
	}

	/**
	 * Сохранение информации об обслуживании отделения
	 */
	function saveLpuSectionService($data) {
		$params = $data;

		if ($params['LpuSectionService_id'] > 0) {
			$procedure = 'p_LpuSectionService_upd';
		} else {
			$params['LpuSectionService_id'] = null;
			$procedure = 'p_LpuSectionService_ins';
		}

		$query = "
			declare
				@LpuSectionService_id bigint = :LpuSectionService_id,
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec {$procedure}
				@LpuSectionService_id = @LpuSectionService_id output,
				@LpuSection_id = :LpuSection_id,
				@LpuSection_did = :LpuSection_did,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuSectionService_id as LpuSectionService_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление информации об обслуживании отделения
	 */
	function deleteLpuSectionService($data) {
		$params = array('LpuSectionService_id' => $data['LpuSectionService_id']);

		$query = "
			declare
				@Error_Code int = 0,
				@Error_Message varchar(4000) = '';
			exec p_LpuSectionService_del
				@LpuSectionService_id = :LpuSectionService_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Формирование строки грида обслуживаемых отделений
	 */
	function getRowLpuSectionService($data) {
		$params = array(
			'LpuSectionService_id' => $data['LpuSectionService_id'],
			'LpuSection_id' => $data['LpuSection_id'],
			'LpuSection_did' => $data['LpuSection_did'],
			'RecordStatus_Code' => $data['RecordStatus_Code']
		);

		$query = "
			select top 1
				:LpuSectionService_id as LpuSectionService_id,
				:LpuSection_id as LpuSection_id,
				:RecordStatus_Code as RecordStatus_Code,
				dLS.LpuSection_id as LpuSection_did,
				dLS.LpuSection_FullName+', '+dLUT.LpuUnitType_Name as LpuSection_Name,
				cast(dLB.LpuBuilding_Code as varchar)+'. '+dLB.LpuBuilding_Name as LpuBuilding_Name
			from v_LpuSection dLS with(nolock)
				left join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where dLS.LpuSection_id = :LpuSection_did
		";

		$response = $this->getFirstRowFromQuery($query, $params);
		if ($response) {
			$response = array('success' => true, 'data' => $response);
		} else {
			$response = array('success' => false);
		}

		return $response;
	}

	/**
	 * Возвращает список обслуживаемых отделений
	 */
	function loadLpuSectionServiceGrid($data) {
		$params = array('LpuSection_id' => $data['LpuSection_id']);

		$query = "
			select
				LSS.LpuSectionService_id,
				1 as RecordStatus_Code,
				LSS.LpuSection_id,
				LSS.LpuSection_did,
				dLS.LpuSection_FullName+', '+dLUT.LpuUnitType_Name as LpuSection_Name,
				cast(dLB.LpuBuilding_Code as varchar)+'. '+dLB.LpuBuilding_Name as LpuBuilding_Name
			from v_LpuSectionService LSS with(nolock)
				left join v_LpuSection dLS with(nolock) on dLS.LpuSection_id = LSS.LpuSection_did
				left join v_LpuUnit dLU with(nolock) on dLU.LpuUnit_id = dLS.LpuUnit_id
				left join v_LpuUnitType dLUT with(nolock) on dLUT.LpuUnitType_id = dLU.LpuUnitType_id
				left join v_LpuBuilding dLB with(nolock) on dLB.LpuBuilding_id = dLU.LpuBuilding_id
			where LSS.LpuSection_id = :LpuSection_id
		";

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Получение количества групп отделений по типу группы в МО
	 */
	function getLpuUnitCountByType($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuUnitType_SysNick' => $data['LpuUnitType_SysNick'],
		);

		if (empty($data['Lpu_id'])) {
			return array(array('LpuUnitCount' => 0));
		}

		$query = "
			select top 1 count(LU.LpuUnit_id) as LpuUnitCount
			from v_LpuUnit LU with(nolock)
			where LU.Lpu_id = :Lpu_id and LU.LpuUnitType_SysNick like :LpuUnitType_SysNick
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Возвращает количество обслуживаемых отделений
	 */
	function getLpuSectionServiceCount($data) {
		$params = array('LpuSection_id' => $data['LpuSection_id']);

		$query = "
			select count(LSS.LpuSectionService_id) as Count
			from v_LpuSectionService LSS with(nolock)
			where LSS.LpuSection_id = :LpuSection_id and LSS.LpuSection_did is not null
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return false;
		}
		$response = $result->result('array');
		return array('success' => true, 'Count' => $response[0]['Count']);
	}
	/**
	 * Функция получения списка отделений со службами определённого типа
	 * @param type $data
	 * @return boolean
	 */
	public function getLpuWithMedServiceList($data) {

		if (empty($data['MedServiceType_Code'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Тип службы'));
		}

		$query = '
			SELECT DISTINCT
				MS.MedService_id,
				COALESCE(ISNULL(L.Lpu_Nick,ISNULL(L.Lpu_Name,\'\'))+\' \ \'+ISNULL(LB.LpuBuilding_Nick,ISNULL(LB.LpuBuilding_Name,\'\'))+\' \ \'+ISNULL(MS.MedService_Nick,\'\'),\'Наименование не определено\') as MedService_Nick
			FROM v_MedService MS with (nolock)
				left join v_MedServiceType MST with (nolock) ON MST.MedServiceType_id = MS.MedServiceType_id
				left join v_LpuBuilding LB with (nolock) on MS.LpuBuilding_id = LB.LpuBuilding_id
				left join v_Lpu_all L with (nolock) ON L.Lpu_id = MS.Lpu_id
			WHERE
				MST.MedServiceType_Code = :MedServiceType_Code
			';
		$result = $this->db->query($query, array(
			'MedServiceType_Code'=>$data['MedServiceType_Code']
		));

		if ( !is_object($result) ) {
			return false;
		}
		return $result->result('array');

	}
	/**
	 * Функция сохранения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 * @param type $data
	 * @return boolean
	 */
	public function saveForenCorpServingMedServices($data) {

		//Функция проверки результата метода другой модели
		$checkResult = function($data = array()) {
			if (is_array($data) && sizeof($data) == 0 ) {
				return true;
			} else
				return !(!$data || (is_array($data) && isset($data[0]) && isset($data[0]['Error_Msg']) && !empty($data[0]['Error_Msg'])));
		};

		if (empty($data['MedService_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор обслуживаемого отделения'));
		}
		if (empty($data['MedService_ForenCrim_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор медико-криминалистической службы'));
		}
		if (empty($data['MedService_ForenChem_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор судебно-химической службы'));
		}
		if (empty($data['MedService_ForenHist_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор судебно-гистологической службы'));
		}
		if (empty($data['MedService_ForenBio_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор судебно-гистологической службы'));
		}


		$this->load->model('MedServiceLink_model', 'msl_model');

		$this->db->trans_begin();

		//
		// Сначала удалим предыдущие связи
		//

		$MedServiceLinkParams = array(
			array( 'field'=>'MedService_ForenCrim_id', 'MedServiceLinkType_id'=> 7),
			array( 'field'=>'MedService_ForenChem_id', 'MedServiceLinkType_id'=> 11),
			array( 'field'=>'MedService_ForenHist_id', 'MedServiceLinkType_id'=> 13),
			array( 'field'=>'MedService_ForenBio_id', 'MedServiceLinkType_id'=> 12),
		);

		//Получаем все записи по каждому типу связи служб


		foreach ($MedServiceLinkParams as $param) {

			$this->msl_model->setMedServiceLinkType_id($param['MedServiceLinkType_id']);

			$selectResult = $this->msl_model->loadList(array(
				'MedService_id'=>$data['MedService_id'],
				'MedServiceLinkType_id'=>$param['MedServiceLinkType_id']
			));

			if (!$checkResult($selectResult)) {
				$this->db->trans_rollback();
				return $selectResult;
			} else {

				//Удаляем все полученные связи

				foreach ($selectResult as $key => $value) {
					$this->msl_model->setMedServiceLink_id($value['MedServiceLink_id']);
					$deleteResult = $this->msl_model->delete();
					if (!$checkResult($deleteResult)) {
						$this->db->trans_rollback();
						return $deleteResult;
					}
				}
			}
		}

		$this->msl_model->setMedService_id($data['MedService_id']);

		foreach ($MedServiceLinkParams as $param) {

			$this->msl_model->setMedServiceLinkType_id($param['MedServiceLinkType_id']);
			$this->msl_model->setMedService_lid($data[$param['field']]);

			$saveResult = $this->msl_model->save();
			if (!$checkResult($saveResult)) {
				$this->db->trans_rollback();
				return $saveResult;
			}
			$saveResult = $this->msl_model->setMedServiceLink_id(null);

		}
		$this->db->trans_commit();
		return array(array('success'=>true, 'Error_Msg'=>''));

	}

	/**
	 * Функция получения обслуживающих отделений для службы судебно-медицинской экспертизы трупов
	 * @return boolean
	 */
	public function loadForenCorpServingMedServices($data) {
		//Функция проверки результата метода другой модели
		$checkResult = function($data = array()) {
			if (is_array($data) && sizeof($data) == 0 ) {
				return true;
			} else
				return !(!$data || (is_array($data) && isset($data[0]) && isset($data[0]['Error_Msg']) && !empty($data[0]['Error_Msg'])));
		};

		if (empty($data['MedService_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор обслуживаемого отделения'));
		}

		$this->load->model('MedServiceLink_model', 'msl_model');

		$resultArray = array('success'=>true);

		$MedServiceLinkParams = array(
			array( 'field'=>'MedService_ForenCrim_id', 'MedServiceLinkType_id'=> 7),
			array( 'field'=>'MedService_ForenChem_id', 'MedServiceLinkType_id'=> 11),
			array( 'field'=>'MedService_ForenHist_id', 'MedServiceLinkType_id'=> 13),
			array( 'field'=>'MedService_ForenBio_id', 'MedServiceLinkType_id'=> 12),
		);

		foreach ($MedServiceLinkParams as $param) {
			$selectResult = $this->msl_model->loadList(array(
				'MedService_id'=>$data['MedService_id'],
				'MedServiceLinkType_id'=>$param['MedServiceLinkType_id']
			));

			If (!$checkResult($selectResult)) {
				return $selectResult;
			}
			$resultArray["{$param['field']}"] = (isset($selectResult[0]))?$selectResult[0]['MedService_lid']: NULL;

		}

		return array($resultArray);
	}
	/**
	 * Функция сохранения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 * @param type $data
	 * @return boolean
	 */
	public function saveForenHistServingMedServices($data) {

		//Функция проверки результата метода другой модели
		$checkResult = function($data = array()) {
			if (is_array($data) && sizeof($data) == 0 ) {
				return true;
			} else
				return !(!$data || (is_array($data) && isset($data[0]) && isset($data[0]['Error_Msg']) && !empty($data[0]['Error_Msg'])));
		};

		if (empty($data['MedService_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор обслуживаемого отделения'));
		}
		if (empty($data['MedService_ForenChem_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор судебно-химической службы'));
		}

		$this->load->model('MedServiceLink_model', 'msl_model');

		$this->db->trans_begin();

		//
		// Сначала удалим предыдущие связи
		//

		$MedServiceLinkParams = array(
			array( 'field'=>'MedService_ForenChem_id', 'MedServiceLinkType_id'=> 11),
		);

		//Получаем все записи по каждому типу связи служб


		foreach ($MedServiceLinkParams as $param) {

			$this->msl_model->setMedServiceLinkType_id($param['MedServiceLinkType_id']);

			$selectResult = $this->msl_model->loadList(array(
				'MedService_id'=>$data['MedService_id'],
				'MedServiceLinkType_id'=>$param['MedServiceLinkType_id']
			));

			if (!$checkResult($selectResult)) {
				$this->db->trans_rollback();
				return $selectResult;
			} else {

				//Удаляем все полученные связи

				foreach ($selectResult as $key => $value) {
					$this->msl_model->setMedServiceLink_id($value['MedServiceLink_id']);
					$deleteResult = $this->msl_model->delete();
					if (!$checkResult($deleteResult)) {
						$this->db->trans_rollback();
						return $deleteResult;
					}
				}
			}
		}

		$this->msl_model->setMedService_id($data['MedService_id']);

		foreach ($MedServiceLinkParams as $param) {

			$this->msl_model->setMedServiceLinkType_id($param['MedServiceLinkType_id']);
			$this->msl_model->setMedService_lid($data[$param['field']]);

			$saveResult = $this->msl_model->save();
			if (!$checkResult($saveResult)) {
				$this->db->trans_rollback();
				return $saveResult;
			}
			$saveResult = $this->msl_model->setMedServiceLink_id(null);

		}
		$this->db->trans_commit();
		return array(array('success'=>true, 'Error_Msg'=>''));

	}

	/**
	 * Функция получения обслуживающих отделений для медико-криминалистической / судебно-гистологической службы
	 * @return boolean
	 */
	public function loadForenHistServingMedServices($data) {
		//Функция проверки результата метода другой модели
		$checkResult = function($data = array()) {
			if (is_array($data) && sizeof($data) == 0 ) {
				return true;
			} else
				return !(!$data || (is_array($data) && isset($data[0]) && isset($data[0]['Error_Msg']) && !empty($data[0]['Error_Msg'])));
		};

		if (empty($data['MedService_id'])) {
			return array(array('succes'=>false,'Error_Msg'=>'Не задан обязательный параметр: Идентификатор обслуживаемого отделения'));
		}

		$this->load->model('MedServiceLink_model', 'msl_model');

		$resultArray = array('success'=>true);

		$MedServiceLinkParams = array(
			array( 'field'=>'MedService_ForenChem_id', 'MedServiceLinkType_id'=> 11),
		);

		foreach ($MedServiceLinkParams as $param) {
			$selectResult = $this->msl_model->loadList(array(
				'MedService_id'=>$data['MedService_id'],
				'MedServiceLinkType_id'=>$param['MedServiceLinkType_id']
			));

			If (!$checkResult($selectResult)) {
				return $selectResult;
			}
			$resultArray["{$param['field']}"] = (isset($selectResult[0]))?$selectResult[0]['MedService_lid']: NULL;

		}

		return array($resultArray);
	}

	/**
	 * Подсчет количества записей, помеченных как "основной врач"
	 * @return int
	 */
	public function checkMainMedPersonal($data, $Lpu_id) {
		$result = array(
			'count' => -1,
			'fio' => ''
		);

		if ( !is_array($data) || count($data) == 0 || empty($Lpu_id) ) {
			return $result;
		}

		$MedPersonal_id = 0;
		$MedStaffRegion_id = 0;
		$MedStaffRegionToDelete = array();

		for ( $j = 0; $j < count($data); $j++ ) {
			if ( !empty($data[$j]['status']) && $data[$j]['status'] == '3' ) {
				if ( !empty($data[$j]['MedStaffRegion_id']) && $data[$j]['MedStaffRegion_id'] > 0 ) {
					$MedStaffRegionToDelete[] = $data[$j]['MedStaffRegion_id'];
				}
			}
			else if ( !empty($data[$j]['MedStaffRegion_isMain']) && $data[$j]['MedStaffRegion_isMain'] == 2 && !empty($data[$j]['MedPersonal_id']) ) {
				$MedPersonal_id = $data[$j]['MedPersonal_id'];
				$MedStaffRegion_id = $data[$j]['MedStaffRegion_id'];
			}
		}

		if ( !empty($MedPersonal_id) ) {
			$query = "
				select mp.Person_Fio
				from v_MedStaffRegion msr with (nolock)
					outer apply (
						select top 1 Person_Fio
						from v_MedPersonal with (nolock)
						where MedPersonal_id = :MedPersonal_id
					) mp
				where MedStaffRegion_id != ISNULL(:MedStaffRegion_id, 0)
					and Lpu_id = :Lpu_id
					and MedPersonal_id = :MedPersonal_id
					and ISNULL(MedStaffRegion_isMain, 1) = 2
					" . (count($MedStaffRegionToDelete) > 0 ? "and MedStaffRegion_id not in (" . implode(',', $MedStaffRegionToDelete) . ")" : "") . "
			";
			$res = $this->db->query($query, array(
				 'Lpu_id' => $Lpu_id
				,'MedStaffRegion_id' => $MedStaffRegion_id
				,'MedPersonal_id' => $MedPersonal_id
			));

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( is_array($response) ) {
					$result['count'] = count($response);

					if ( count($response) > 0 ) {
						$result['fio'] = $response[0]['Person_Fio'];
					}
				}
			}
		}

		return $result;
	}

	/**
	 * Метод загрузки фотографии подразделения
	 * формирует два файла по пути вида вида:
	 * uploads/orgs/photos/[lpu_id]/LpuSection/[LpuSection_id].(jpg|png|gif)
	 * uploads/orgs/photos/[lpu_id]/LpuSection/thumbs/[LpuSection_id].(jpg|png|gif)
	 */
	function uploadOrgPhoto($data, $files) {
		/**
		 * Создание каталогов
		 */
		function createDir($path) {
			if(!is_dir($path)) { // Если нет корневой папки для хранения файлов организаций
				// то создадим ее
				$success = mkdir($path, 0777);
				if(!$success) {
					DieWithError('Не удалось создать папку "'.$path.'"');
					return false;
				}
			}
			return true;
		}
		$this->load->helper('Image_helper');
		if (!defined('ORGSPATH') || !defined('ORGSPHOTOPATH')) {
			return array('success' => false, 'Error_Msg'=>'Необходимо задать константы с указанием папок для загрузки файлов (config/promed,php): ORGSPATH и ORGSPHOTOPATH');
		}

		if(!isset($files['org_photo'])) {
			return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл.');
		}

		$source = $files['org_photo']['tmp_name'];
		// Если файл успешно загрузился в темповую директорию $source
		if(is_uploaded_file($source)) {
			// Наименование файла
			$flname = $files['org_photo']['name'];
			$fltype = $files['org_photo']['type'];
			$ext = pathinfo($flname, PATHINFO_EXTENSION);
			if ($data['Lpu_id']>0) {
				$name = $data['Lpu_id'];

				// Создание директорий, если нужно
				createDir(ORGSPATH);
				createDir(ORGSPHOTOPATH); // Корневая директория хранения фотографий подразделений
				$orgDir = ORGSPHOTOPATH.$data['Lpu_id']."/"; // Директория конкретной организации, где будут лежать фотографии
				createDir($orgDir);
				if ($data['LpuSection_id']>0) {
					//$orgDir .= $data['LpuSection_id']."/";
					$orgDir .= "LpuSection/";
					$name = $data['LpuSection_id'];
					createDir($orgDir);
				} elseif ($data['LpuUnit_id']>0) {
					$orgDir .= "LpuUnit/";
					$name = $data['LpuUnit_id'];
					createDir($orgDir);
				} elseif ($data['LpuBuilding_id']>0) {
					$orgDir .= "LpuBuilding/";
					$name = $data['LpuBuilding_id'];
					createDir($orgDir);
				}
				// Какой бы каталог не был выбран - создаем в нем папку для хранения уменьшенных копий (thumbs)
				createDir($orgDir."thumbs/");

				// удаляем все файлы с таким названием и любым расширением (если они есть)
				array_map("unlink", glob($orgDir.$name.".*"));

				// todo: Здесь можно выбирать имена (например добавляя _1, _2), что даст возможность загружать несколько фотографий

				// Расширение файла
				$name .= ".".$ext;

				// создаем уменьшенную копию изображения
				createThumb($source, $fltype, $orgDir."thumbs/".$name, 300, 300);

				// Перемещаем загруженный файл в директорию пользователя с новым именем
				move_uploaded_file($source, $orgDir.$name);

				return array(
					'success' => true,
					'file_url' => $orgDir."thumbs/".$name."?t=".time() // добавляем параметр, чтобы не застывал в кеше
				);
			} else {
				return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл, т.к. МО не определена!');
			}
		}
		else {
			return array('success' => false, 'Error_Msg'=>'Не удалось загрузить файл!');
		}
	}

	/**
	 * Метод получения фотографии подразделения
	 * На входе массив [Lpu_id (обязательно), LpuSection_id, LpuUnit_id, LpuBuilding_id]
	 * На выходе ссылка на файл вида: uploads/orgs/photos/[lpu_id]/LpuSection/thumbs/[LpuSection_id].(jpg|png|gif)
	 */
	function getOrgPhoto($data) {
		if (!defined('ORGSPHOTOPATH')) {
			return false;
		}
		$orgDir = ORGSPHOTOPATH.$data['Lpu_id']."/"; // Директория конкретной организации, где будут лежать фотографии
		if ($data['Lpu_id']>0) {
			$name = $data['Lpu_id'];
			if (isset($data['LpuSection_id']) && $data['LpuSection_id']>0) {
				$orgDir .= "LpuSection/";
				$name = $data['LpuSection_id'];
			} elseif (isset($data['LpuUnit_id']) && $data['LpuUnit_id']>0) {
				$orgDir .= "LpuUnit/";
				$name = $data['LpuUnit_id'];
			} elseif (isset($data['LpuBuilding_id']) && $data['LpuBuilding_id']>0) {
				$orgDir .= "LpuBuilding/";
				$name = $data['LpuBuilding_id'];
			}
			// ищем файл с нужным расширением и берем первый попавшися
			foreach (glob($orgDir.$name.".*") as $fn) {
				$ext = pathinfo($fn, PATHINFO_EXTENSION);
				break;
			}

			$name .= ".".(isset($ext)?$ext:"jpg");
			$orgDir .= "thumbs/";

			if (file_exists($orgDir.$name)) {
				return $orgDir.$name."?t=".time(); // добавляем параметр, чтобы не застывал в кеше
			}
		}
		return false;
	}

	/**
	 * Получение информации об участке для прикрепления
	 */
	function loadLpuRegionInfo($data){
		$params = array(
			'LpuRegion_id' => $data['LpuRegion_id']
		);
		$query = "
			select
				ISNULL(LR.LpuSection_id,0) as LpuSection_id,
				ISNULL(LS.LpuBuilding_id,0) as LpuBuilding_id,
				ISNULL(MSR.MedStaffRegion_id,0) as MedStaffRegion_id
			from v_LpuRegion LR
			left join v_LpuSection LS on LS.LpuSection_id = LR.LpuSection_id
			left join v_MedStaffRegion MSR on (
				MSR.LpuRegion_id = LR.LpuRegion_id
				and
				(MSR.MedStaffRegion_endDate is null or MSR.MedStaffRegion_endDate > dbo.tzGetDate())
				and
				ISNULL(MSR.MedStaffRegion_isMain,0) = 2
			)
			where LR.LpuRegion_id = :LpuRegion_id
		";
		$result = $this->db->query($query,$params);
		if(is_object($result)){
			$result = $result->result('array');
			return $result;
		}
		return false;
	}

	/**
	 * Сохранение доп параметров подстанции
	 */
	function saveLpuBuildingAdditionalParams($data){

		if ( !array_key_exists( 'LpuBuilding_id', $data ) || !$data['LpuBuilding_id'] ) {
			return array( array( 'Err_Msg' => 'Не указан идентификатор подразделения') );
		}

		$sql = "
        SELECT
			LpuBuilding_id
			,LpuBuilding_IsPrint
			,ISNULL(LpuBuildingSmsType_id, 1) as LpuBuildingSmsType_id
			,LpuBuilding_setDefaultAddressCity
			,LpuBuilding_IsEmergencyTeamDelay
			,LpuBuilding_IsCallCancel
			,LpuBuilding_IsCallDouble
			,LpuBuilding_IsCallSpecTeam
			,LpuBuilding_IsCallReason
			,LpuBuilding_IsWithoutBalance
			,LpuBuilding_IsDenyCallAnswerDoc
			,Lpu_id
			,LpuBuildingType_id
			,LpuBuilding_Code
			,LpuBuilding_begDate
			,LpuBuilding_endDate
			,LpuBuilding_Nick
			,LpuBuilding_Name
			,LpuBuilding_WorkTime
			,LpuBuilding_RoutePlan
			,Address_id
			,PAddress_id
			,LpuLevel_id
			,LpuLevel_cid
			,Server_id
			,LpuBuilding_IsExport
			,LpuBuilding_CmpStationCode
			,LpuBuilding_CmpSubstationCode
			,LpuBuilding_Longitude
			,LpuBuilding_Latitude
        FROM
            v_LpuBuilding
        WHERE
          	LpuBuilding_id = :LpuBuilding_id
        ";

		$query = $this->db->query($sql, array(
			'LpuBuilding_id' => $data['LpuBuilding_id']
		));

		$res = $query->result('array');
		if(count($res) > 0)
			$res = $res[0];

		$queryParams = array(
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'LpuBuilding_IsPrint' => $data['LpuBuilding_IsPrint'] == 'true' ? 2 : 1,
			'LpuBuildingSmsType_id' => $data['LpuBuildingSmsType_id'],
			'LpuBuilding_setDefaultAddressCity' => $data['LpuBuilding_setDefaultAddressCity'] == 'true' ? 2 : 1,
			'LpuBuilding_IsEmergencyTeamDelay' => $data['LpuBuilding_IsEmergencyTeamDelay'] == 'true' ? 2 : 1,
			'LpuBuilding_IsCallCancel' => $data['LpuBuilding_IsCallCancel'] == 'true' ? 2 : 1,
			'LpuBuilding_IsCallDouble' => $data['LpuBuilding_IsCallDouble'] == 'true' ? 2 : 1,
			'LpuBuilding_IsCallSpecTeam' => $data['LpuBuilding_IsCallSpecTeam'] == 'true' ? 2 : 1,
			'LpuBuilding_IsCallReason' => $data['LpuBuilding_IsCallReason'] == 'true' ? 2 : 1,
			'LpuBuilding_IsUsingMicrophone' => $data['LpuBuilding_IsUsingMicrophone'] == 'true' ? 2 : 1,
			'LpuBuilding_IsWithoutBalance' => $data['LpuBuilding_IsWithoutBalance'] == 'true' ? 2 : 1,
			'LpuBuilding_IsDenyCallAnswerDoc' => $data['LpuBuilding_IsDenyCallAnswerDoc'] == 'true' ? 2 : 1,
			'Lpu_id' => $res['Lpu_id'],
			'LpuBuildingType_id' => $res['LpuBuildingType_id'],
			'LpuBuilding_Code' => $res['LpuBuilding_Code'],
			'LpuBuilding_begDate' => $res['LpuBuilding_begDate'],
			'LpuBuilding_endDate' => $res['LpuBuilding_endDate'],
			'LpuBuilding_Nick' => $res['LpuBuilding_Nick'],
			'LpuBuilding_Name' => $res['LpuBuilding_Name'],
			'LpuBuilding_WorkTime' => $res['LpuBuilding_WorkTime'],
			'LpuBuilding_RoutePlan' => $res['LpuBuilding_RoutePlan'],
			'Address_id' => $res['Address_id'],
			'PAddress_id' => $res['PAddress_id'],
			'LpuLevel_id' => $res['LpuLevel_id'],
			'LpuLevel_cid' => $res['LpuLevel_cid'],
			'pmUser_id' => $data['pmUser_id'],
			'Server_id' => $res['Server_id'],
			'LpuBuilding_IsExport' => $res['LpuBuilding_IsExport'],
			'LpuBuilding_CmpStationCode' => $res['LpuBuilding_CmpStationCode'],
			'LpuBuilding_CmpSubstationCode' => $res['LpuBuilding_CmpSubstationCode'],
			'LpuBuilding_Longitude' => $res['LpuBuilding_Longitude'],
			'LpuBuilding_Latitude' => $res['LpuBuilding_Latitude']
		);
		$query = "
		declare
			@LpuBuilding_id bigint = :LpuBuilding_id,
			@Error_Code bigint,
			@Error_Message varchar(4000);
		exec p_LpuBuilding_upd
			@LpuBuilding_id = @LpuBuilding_id output,
				@Lpu_id = :Lpu_id,
				@LpuBuildingType_id = :LpuBuildingType_id,
				@LpuBuilding_Code = :LpuBuilding_Code,
				@LpuBuilding_begDate = :LpuBuilding_begDate,
				@LpuBuilding_endDate = :LpuBuilding_endDate,
				@LpuBuilding_Nick = :LpuBuilding_Nick,
				@LpuBuilding_Name = :LpuBuilding_Name,
				@LpuBuilding_WorkTime = :LpuBuilding_WorkTime,
				@LpuBuilding_RoutePlan = :LpuBuilding_RoutePlan,
				@Address_id = :Address_id,
				@PAddress_id = :PAddress_id,
				@LpuLevel_id = :LpuLevel_id,
				@LpuLevel_cid = :LpuLevel_cid,
				@pmUser_id = :pmUser_id,
				@Server_id = :Server_id,
				@LpuBuilding_IsExport = :LpuBuilding_IsExport,
				@LpuBuilding_CmpStationCode = :LpuBuilding_CmpStationCode,
				@LpuBuilding_CmpSubstationCode = :LpuBuilding_CmpSubstationCode,
				@LpuBuilding_Longitude = :LpuBuilding_Longitude,
				@LpuBuilding_Latitude = :LpuBuilding_Latitude,
				@LpuBuilding_IsPrint = :LpuBuilding_IsPrint,
				@LpuBuildingSmsType_id = :LpuBuildingSmsType_id,
				@LpuBuilding_setDefaultAddressCity = :LpuBuilding_setDefaultAddressCity,
				@LpuBuilding_IsEmergencyTeamDelay = :LpuBuilding_IsEmergencyTeamDelay,				
				@LpuBuilding_IsCallCancel = :LpuBuilding_IsCallCancel,
				@LpuBuilding_IsCallDouble = :LpuBuilding_IsCallDouble,
				@LpuBuilding_IsCallSpecTeam = :LpuBuilding_IsCallSpecTeam,
				@LpuBuilding_IsCallReason = :LpuBuilding_IsCallReason,
				@LpuBuilding_IsUsingMicrophone = :LpuBuilding_IsUsingMicrophone,
				@LpuBuilding_IsWithoutBalance = :LpuBuilding_IsWithoutBalance,
				@LpuBuilding_IsDenyCallAnswerDoc = :LpuBuilding_IsDenyCallAnswerDoc,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuBuilding_id as LpuBuilding_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		    ";

		$result = $this->db->query($query, $queryParams);

		return $result->result('array');

	}
	/**
	 * Получение списка МО по региону. Метод для API
	 */
	public function getLpuListByRegion($data) {
		$params = array('Region_id' => $data['Region_id']);

		$join = "";
		$fields = "";
		$crossApply = '';
		if (!empty($data['extended'])) {
			$join .= "
				left join v_Address ua (nolock) on ua.Address_id = O.UAddress_id
				left join v_Address pa (nolock) on pa.Address_id = O.PAddress_id
			";

			$fields .= "
				, ua.Address_Address as Address_Address
				, pa.Address_Address as PAddress_Address
				, case when (
								L.Lpu_IsTest IS NULL AND
								ISNULL(L.Lpu_endDate, '2030-01-01') >= @date AND
								CA_LB.LpuHasRegAvailableBuildings IS NOT NULL AND
								CA_MSF.LpuHasRegAvailableMedStaff IS NOT NULL
							) then 1 else 0 end as Can_Record
			";


			/**
				Если одновременно выполняются условия:
				Не тестовая МО (Lpu_IsTest принимает значения null или 1);
				МО открыта на текущую дату;
				Тип хотя бы одного подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
				Хотя бы в одной из групп отделений, открытой на текущую дату:
				Установлен флаг «Включить запись операторами» (LpuUnit_Enabled = 1);
				Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
				то Can_Record = 1, иначе 0.
			 */



			$crossApply .= '
			
				cross apply (
				
					Select TOP 1
						case when COUNT(LpuBuilding_id) > 0 then 1 else NULL end as LpuHasRegAvailableBuildings
					From
						v_LpuBuilding LB
					
						
					cross apply (
						
						Select TOP 1
							case when COUNT(LpuUnitType_id) > 0 then 1 else NULL end as BuildingHasRegAvailableUnits
						From
							v_LpuUnit LU
						Where
							LU.LpuBuilding_id = LB.LpuBuilding_id AND
							LU.LpuUnitType_id NOT IN (1,3,6,7,9) AND
							LU.LpuUnit_IsEnabled = 2
					
						) CA_LU
						
					Where
						LB.Lpu_id = L.Lpu_id AND
						CA_LU.BuildingHasRegAvailableUnits IS NOT NULL
						
						
				) CA_LB
					
					
				cross apply (
					
					Select TOP 1
						case when COUNT(MedStaffFact_id) > 0 then 1 else NULL end as LpuHasRegAvailableMedStaff
					From
						v_MedStaffFact MSF
					Where
						L.Lpu_id = MSF.Lpu_id AND
						MSF.RecType_id NOT IN (5,6) AND
						ISNULL(MSF.WorkData_endDate, \'2030-01-01\') > @date
						and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) <> 2
					
				) CA_MSF';

		}


		$query = "
			Declare @date date = dbo.tzGetDate()
			select
				L.Lpu_id,
				O.Org_Name,
				O.Org_Nick
				{$fields}
			from Lpu L with(nolock)
				inner join Org O with(nolock) on O.Org_id = L.Org_id
				{$join}
				{$crossApply}
			where 
				L.Region_id = :Region_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО по району. Метод для API
	 */
	public function getLpuListBySubRgn($data) {
		$params = array('KLSubRgn_id' => $data['SubRgn_id']);

		$fieldsList = array(
			"L.Lpu_id",
			"L.Lpu_Name as Org_Name",
			"L.Lpu_Nick as Org_Nick",
		);
		$joinList = array();

		if ( !empty($data['Extended']) && $data['Extended'] == 1 ) {
			/**
				Если одновременно выполняются условия:
				Не тестовая МО (Lpu_IsTest принимает значения null или 1);
				МО открыта на текущую дату;
				Тип хотя бы одного подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
				Хотя бы в одной из групп отделений, открытой на текущую дату, установлен флаг «Включить запись операторами» (LpuUnit_IsEnabled = 2);
				Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
				то Can_Record = 1, иначе 0.
			*/

			$joinList[] = "left join v_Address ua (nolock) on ua.Address_id = L.UAddress_id";
			$joinList[] = "left join v_Address pa (nolock) on pa.Address_id = L.PAddress_id";
			$joinList[] = "
				outer apply (
					select top 1 LpuUnit_id
					from v_LpuUnit with (nolock)
					where Lpu_id = L.Lpu_id
						and LpuUnitType_id not in (1, 3, 6, 7, 9)
						and (LpuUnit_begDate is null or LpuUnit_begDate <= @date)
						and (LpuUnit_endDate is null or LpuUnit_endDate >= @date)
				) LU
			";
			$joinList[] = "
				outer apply (
					select top 1 LpuUnit_id
					from v_LpuUnit with (nolock)
					where Lpu_id = L.Lpu_id
						and LpuUnit_IsEnabled = 2
						and (LpuUnit_begDate is null or LpuUnit_begDate <= @date)
						and (LpuUnit_endDate is null or LpuUnit_endDate >= @date)
				) LUIE
			";
			$joinList[] = "
				outer apply (
					select top 1 MedStaffFact_id
					from v_MedStaffFact with (nolock)
					where Lpu_id = L.Lpu_id
						and ISNULL(RecType_id, 0) not in (0, 5, 6)
						and (WorkData_begDate is null or WorkData_begDate <= @date)
						and (WorkData_endDate is null or WorkData_endDate >= @date)
				) MSF
			";

			$fieldsList[] = "ua.Address_Address";
			$fieldsList[] = "pa.Address_Address as PAddress_Address";
			$fieldsList[] = "case when
				ISNULL(L.Lpu_IsTest, 1) = 1
				and ISNULL(L.Lpu_endDate, @date + 1) >= @date
				and LU.LpuUnit_id is not null
				and LUIE.LpuUnit_id is not null
				and MSF.MedStaffFact_id is not null
				then 1
				else 0
			end as Can_Record
			";
		}

		$query = "
			declare @date datetime = cast(dbo.tzGetDate() as date);

			select
				" . implode(", ", $fieldsList) . "
			from v_Lpu L with (nolock)
				" . implode(" ", $joinList) . "
			where 
				exists (
					select top 1 OrgServiceTerr_id
					from v_OrgServiceTerr with (nolock)
					where Org_id = L.Org_id
						and KLSubRgn_id = :KLSubRgn_id
				)
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение общих данных по участку
	 */
	function getLpuRegionByID($data) {
		$params = array(
			'LpuRegion_id' => $data['LpuRegion_id'],
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			select top 1
				lr.Lpu_id, 
				lr.LpuSection_id,
				ls.LpuBuilding_id,
				lr.LpuRegionType_id,
				lr.LpuRegion_Name, 
				convert(varchar(10), lr.LpuRegion_begDate, 120) as LpuRegion_begDate, 
				convert(varchar(10), lr.LpuRegion_endDate, 120) as LpuRegion_endDate
			from
				v_LpuRegion LR with(nolock)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = lr.LpuSection_id
			where
			 	LR.LpuRegion_id = :LpuRegion_id
			 	and LR.Lpu_id = :Lpu_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение общих данных по участку
	 */
	function getLpuRegionByMO($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			select
				lr.Lpu_id, 
				lr.LpuSection_id,
				ls.LpuBuilding_id,
				lr.LpuRegionType_id,
				lr.LpuRegion_Name, 
				convert(varchar(10), lr.LpuRegion_begDate, 120) as LpuRegion_begDate, 
				convert(varchar(10), lr.LpuRegion_endDate, 120) as LpuRegion_endDate
			from
				v_LpuRegion LR with(nolock)
				left join v_LpuSection ls with (nolock) on ls.LpuSection_id = lr.LpuSection_id
			where
			 	LR.Lpu_id = :Lpu_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение информации о периоде работы врача по идентификатору
	 */
	function getLpuRegionWorkerPlaceByID($data) {
		$filter = "";
		$params = array(
			'MedStaffRegion_id' => $data['MedStaffRegion_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select top 1
				convert(varchar(10), MedStaffRegion_begDate, 120) as MedStaffRegion_begDate,
				convert(varchar(10), MedStaffRegion_endDate, 120) as MedStaffRegion_endDate,
				case when MedStaffRegion_isMain = 2 then 1 else 0 end as MedStaffRegion_isMain
			from
				v_MedStaffRegion with(nolock)
			where
			 	MedStaffRegion_id = :MedStaffRegion_id
		".$filter;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка периодов работы врачей на участке
	 */
	function getLpuRegionWorkerPlaceList($data) {
		$filter = "";
		$params = array();

		if (!empty($data['LpuRegion_id'])) {
			$filter .= " and LpuRegion_id = :LpuRegion_id";
			$params['LpuRegion_id'] = $data['LpuRegion_id'];
		}

		if (!empty($data['MedStaffFact_id'])) {
			$filter .= " and MedStaffFact_id = :MedStaffFact_id";
			$params['MedStaffFact_id'] = $data['MedStaffFact_id'];
		}

		if (!empty($data['MedStaffRegion_begDate'])) {
			$filter .= " and MedStaffRegion_begDate = :MedStaffRegion_begDate";
			$params['MedStaffRegion_begDate'] = $data['MedStaffRegion_begDate'];
		}

		if (!empty($data['MedStaffRegion_endDate'])) {
			$filter .= " and MedStaffRegion_endDate = :MedStaffRegion_endDate";
			$params['MedStaffRegion_endDate'] = $data['MedStaffRegion_endDate'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (empty($filter)) {
			return array();
		}

		$query = "
			select
				MedStaffRegion_id
			from
				v_MedStaffRegion with(nolock)
			where
			 	(1=1)
			 	{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных подразделения. Метод для API
	 */
	function getLpuBuildingById($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuBuilding_id' => $data['LpuBuilding_id']
		);
		$query = "
			select top 1
				LB.Lpu_id,
				LB.LpuBuilding_id,
				LB.Server_id,
				convert(varchar(10), LB.LpuBuilding_begDate, 120) as LpuBuilding_begDate,
				convert(varchar(10), LB.LpuBuilding_endDate, 120) as LpuBuilding_endDate,
				LB.LpuBuilding_Code,
				LB.LpuBuilding_Name,
				LB.LpuBuilding_Nick,
				LB.LpuBuildingType_id,
				LB.LpuBuilding_CmpStationCode,
				LB.LpuBuilding_CmpSubstationCode,
				LB.PAddress_id,
				LB.Address_id,
				LB.LpuBuilding_Latitude,
				LB.LpuBuilding_Longitude,
				LB.LpuBuilding_RoutePlan,
				LpuBuilding_WorkTime,
				IsExport.YesNo_Code as LpuBuilding_IsExport
			from
				v_LpuBuilding LB with(nolock)
				left join v_YesNo IsExport with(nolock) on IsExport.YesNo_id = isnull(LpuBuilding_IsExport,1)
			where
			 	LB.Lpu_id = :Lpu_id
				and LB.LpuBuilding_id = :LpuBuilding_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка подразделений. Метод для API
	 */
	function getLpuBuildingListForAPI($data) {
		$params = array();
		$filters = array('1=1');

		if (!empty($data['Lpu_id'])) {
			$filters[] = "LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['Lpu_OID'])) {
			$filters[] = "PToken.PassportToken_tid = :Lpu_OID";
			$params['Lpu_OID'] = $data['Lpu_OID'];
		}
		if (!empty($data['LpuBuilding_Code'])) {
			$filters[] = "LB.LpuBuilding_Code like :LpuBuilding_Code+'%'";
			$params['LpuBuilding_Code'] = $data['LpuBuilding_Code'];
		}
		if (!empty($data['LpuBuilding_Name'])) {
			$filters[] = "LB.LpuBuilding_Name like :LpuBuilding_Name+'%'";
			$params['LpuBuilding_Name'] = $data['LpuBuilding_Name'];
		}
		if (!empty($data['LpuBuildingType_id'])) {
			$filters[] = "LB.LpuBuildingType_id = :LpuBuildingType_id";
			$params['LpuBuildingType_id'] = $data['LpuBuildingType_id'];
		}

		$join = "";
		$fields = "";
		$crossApply = '';
		if (!empty($data['Lpu_OID'])) {
			$join .= "
				INNER join fed.v_PassportToken PToken (nolock) on PToken.Lpu_id = LB.Lpu_id
			";
		}
		if (!empty($data['extended'])) {
			$join .= "
				left join v_Address ua (nolock) on ua.Address_id = LB.Address_id
				left join v_Address pa (nolock) on pa.Address_id = LB.PAddress_id
			";

			$fields .= "
				, ua.Address_Address as UAddress_Address
				, pa.Address_Address as PAddress_Address
				, case when (
								CA_LU.BuildingHasRegAvailableUnits IS NOT NULL AND
								CA_MSF.LpuHasRegAvailableMedStaff IS NOT NULL
								
							) then 1 else 0 end as Can_Record
			";

			/**
				Если одновременно выполняются условия:
				Тип подразделения МО (LpuUnitType_id) отличен от 1, 3, 6, 7, 9;
				Хотя бы в одной из групп отделений, открытой на текущую дату подразделения МО:
				Установлен флаг «Включить запись операторами» (LpuUnit_Enabled = 1);
				Тип записи (RecType_id) хотя бы одного места работы (MedStaffFact) не равен null, 5, 6,
				то Can_Record = 1, иначе 0.
			 */


			$crossApply .= '
			
					cross apply (
						
						Select TOP 1
							case when COUNT(LpuUnitType_id) > 0 then 1 else NULL end as BuildingHasRegAvailableUnits
						From
							v_LpuUnit LU
						Where
							LU.LpuBuilding_id = LB.LpuBuilding_id AND
							LU.LpuUnitType_id NOT IN (1,3,6,7,9) AND
							LU.LpuUnit_IsEnabled = 2
					
						) CA_LU
						
						
					
				cross apply (
					
					Select TOP 1
						case when COUNT(MedStaffFact_id) > 0 then 1 else NULL end as LpuHasRegAvailableMedStaff
					From
						v_MedStaffFact MSF
					Where
						LB.LpuBuilding_id = MSF.LpuBuilding_id AND
						MSF.RecType_id NOT IN (5,6) AND
						ISNULL(MSF.WorkData_endDate, \'2030-01-01\') > @date
						and ISNULL(msf.MedStaffFactCache_IsNotShown, 0) <> 2
					
				) CA_MSF';
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			Declare @date date = dbo.tzGetDate()
			select DISTINCT
				LB.LpuBuilding_id,
				LB.LpuBuilding_Code,
				LB.LpuBuilding_Name,
				LB.LpuBuildingType_id
				{$fields}
			from
				v_LpuBuilding LB with(nolock)
				{$join}
				{$crossApply}
			where
			 	{$filters_str}
		";
		//echo getDebugSQL($query, $params); die();
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка отделений. Метод для API
	 */
	function getLpuSectionListForAPI($data) {
		$params = array();
		$filters = array('1=1');
		$join = '';

		if (!empty($data['Lpu_id'])) {
			$filters[] = "LS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['Lpu_OID'])) {
			$filters[] = "PToken.PassportToken_tid = :Lpu_OID";
			$params['Lpu_OID'] = $data['Lpu_OID'];
			$join .= ' INNER JOIN fed.v_PassportToken PToken (nolock) on PToken.Lpu_id = LS.Lpu_id ';
		}
		if (!empty($data['LpuBuilding_id'])) {
			$filters[] = "LS.LpuBuilding_id = :LpuBuilding_id";
			$params['LpuBuilding_id'] = $data['LpuBuilding_id'];
		}
		if (!empty($data['LpuSection_id'])) {
			$filters[] = "LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['LpuSection_Code'])) {
			$filters[] = "LS.LpuSection_Code = :LpuSection_Code";
			$params['LpuSection_Code'] = $data['LpuSection_Code'];
		}
		if (!empty($data['LpuSectionCode_Code'])) {
			$filters[] = "LSC.LpuSectionCode_Code = :LpuSectionCode_Code";
			$params['LpuSectionCode_Code'] = $data['LpuSectionCode_Code'];
		}
		if (!empty($data['LpuSection_Name'])) {
			$filters[] = "LS.LpuSection_Name = :LpuSection_Name";
			$params['LpuSection_Name'] = $data['LpuSection_Name'];
		}
		if (!empty($data['LpuSectionOuter_id'])) {
			$filters[] = "LS.LpuSectionOuter_id = :LpuSectionOuter_id";
			$params['LpuSectionOuter_id'] = $data['LpuSectionOuter_id'];
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				LS.LpuSection_id,
				LS.Lpu_id,
				LS.LpuBuilding_id,
				LS.LpuUnit_id,
				LU.LpuUnit_Code,
				LUT.LpuUnitType_Code,
				LU.LpuUnit_Name,
				convert(varchar(10), LS.LpuSection_setDate, 120) as LpuSection_setDate,
				convert(varchar(10), LS.LpuSection_disDate, 120) as LpuSection_disDate,
				LS.LpuSectionProfile_id,
				LS.LpuSectionProfile_Code, 
				LS.LpuSection_Code, 
				LS.LpuSectionCode_id, 
				LS.LpuSection_Name,
				LS.LpuSectionOuter_id 
			from
				v_LpuSection LS with(nolock)
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
				left join v_LpuSectionCode LSC with(nolock) on LSC.LpuSectionCode_id = LS.LpuSectionCode_id
				{$join}
			where
				{$filters_str}
			order by
				LS.LpuSection_setDate,
				LS.LpuSection_Code,
				LS.LpuSection_Name
		";
		//echo getDebugSQL($query, $params); die();
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение отделения. Метод для API
	 */
	function getLpuSectionByIdForAPI($data) {
		$join = '';
		$params = [];
		$filters = [];

		if (!empty($data['LpuSection_id'])) {
			$filters[] = "LS.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}
		if (!empty($data['LpuSectionOuter_id'])) {
			$filters[] = "LS.LpuSectionOuter_id = :LpuSectionOuter_id";
			$params['LpuSectionOuter_id'] = $data['LpuSectionOuter_id'];
		}
		if(!empty($data['LpuSection_OID'])){
			$filters[] = "FS.FRMOSection_OID = :LpuSection_OID";
			$params['LpuSection_OID'] = $data['LpuSection_OID'];
			$join .= ' INNER JOIN nsi.FRMOSection FS WITH(NOLOCK) ON FS.FRMOSection_id = LS.FRMOSection_id ';
		}

		$filters_str = implode(" and ", $filters);
		$query = "
			select
				LS.LpuSection_id,
				convert(varchar(10), LS.LpuSection_setDate, 120) as LpuSection_setDate,
				convert(varchar(10), LS.LpuSection_disDate, 120) as LpuSection_disDate,
				LS.Lpu_id,
				LS.LpuSectionProfile_id,
				LS.LpuSectionProfile_Code,
				LS.LpuUnit_id,
				LS.LpuSection_Code,
				LS.LpuSectionCode_id,
				LS.LpuSection_Name,
				LS.MesAgeGroup_id,
				LS.MESLevel_id,
				case when LS.LpuSection_IsHTMedicalCare = 2 then 1 else 0 end as LpuSection_IsHTMedicalCare,
				LS.LpuSection_KolAmbul,
				LS.LpuSection_KolJob,
				LS.LpuSection_PlanAutopShift,
				LS.LpuSection_PlanResShift,
				LS.LpuSection_PlanTrip,
				LS.LpuSection_PlanVisitDay,
				LS.LpuSection_PlanVisitShift,
				LS.LpuSectionAge_id,
				LS.LpuSectionBedProfile_id,
				LS.LpuSectionOuter_id,
				LU.LpuUnitType_id,
				LS.FRMPSubdivision_id,
				LU.LpuBuildingPass_id
			from
				v_LpuSection LS with(nolock)
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				{$join}
			where
				{$filters_str}
		";
		//echo getDebugSQL($query, $params); die();
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение отделения. Метод для API
	 */
	function getLpuSectionForAPI($data) {
		$params = array(
			'LpuSection_id' => $data['LpuSection_id']
		);

		$query = "
			select
				LS.LpuSection_id,
				LS.LpuBuilding_id,
				LU.LpuUnit_id,
				LU.LpuUnit_Code,
				LUT.LpuUnitType_Code,
				LU.LpuUnit_Name,
				convert(varchar(10), LS.LpuSection_setDate, 120) as LpuSection_setDate,
				convert(varchar(10), LS.LpuSection_disDate, 120) as LpuSection_disDate,
				LS.LpuSectionProfile_Code,
				LS.LpuSection_Code,
				LS.LpuSection_Name,
				LS.MesAgeGroup_id,
				LS.MESLevel_id,
				LS.LpuSection_IsHTMedicalCare,
				LS.LpuSection_KolAmbul,
				LS.LpuSection_KolJob,
				LS.LpuSection_PlanAutopShift,
				LS.LpuSection_PlanResShift,
				LS.LpuSection_PlanTrip,
				LS.LpuSection_PlanVisitDay,
				LS.LpuSection_PlanVisitShift,
				LS.LpuSectionAge_id,
				LS.LpuSectionBedProfile_id,
				LS.LpuSectionOuter_id,
				LS.FRMPSubdivision_id,
				LU.LpuBuildingPass_id
			from
				v_LpuSection LS with(nolock)
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuUnitType LUT with(nolock) on LUT.LpuUnitType_id = LU.LpuUnitType_id
			where
				LS.LpuSection_id = :LpuSection_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение данных о группе отделений МО по идентификатору. Метод для API
	 */
	public function getLpuUnitByIdForAPI($data) {
		$params = array();
		$filters = array('1=1');
		$join = '';
		
		if (!empty($data['Lpu_id'])) {
			$filters[] = "LU.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if (!empty($data['LpuUnit_id'])) {
			$filters[] = "LU.LpuUnit_id = :LpuUnit_id";
			$params['LpuUnit_id'] = $data['LpuUnit_id'];
		}		
		if (!empty($data['LpuUnit_OID'])) {
			$filters[] = "isnull(LU.LpuUnit_FRMOUnitID, FU.FRMOUnit_OID) = :LpuUnit_OID";
			$params['LpuUnit_OID'] = $data['LpuUnit_OID'];
			$join .= ' LEFT JOIN nsi.v_FRMOUnit FU with(NOLOCK) on FU.FRMOUnit_id = LU.FRMOUnit_id ';
		}
		
		$filters_str = implode(" and ", $filters);
		
		return $this->queryResult("
			select
				LU.LpuBuilding_id,
				LU.LpuUnitType_id,
				LU.LpuUnitTypeDop_id,
				LU.Address_id,
				LU.LpuUnit_Code,
				LU.LpuUnit_Name,
				LU.LpuUnit_Descr,
				LU.LpuUnit_Phone,
				LU.LpuUnit_IsEnabled,
				LU.LpuUnit_IsDirWithRec,
				LU.LpuUnit_ExtMedCnt,
				LU.LpuUnit_Email,
				LU.LpuUnit_IP,
				LU.LpuUnitSet_id,
				LU.LpuUnit_Guid,
				convert(varchar(10), LU.LpuUnit_begDate, 120) as LpuUnit_begDate,
				convert(varchar(10), LU.LpuUnit_endDate, 120) as LpuUnit_endDate,
				LU.LpuUnit_IsOMS,
				LU.UnitDepartType_fid,
				LU.LpuUnitProfile_fid,
				LU.LpuUnit_isStandalone,
				LU.LpuBuildingPass_id,
				LU.LpuUnit_isHomeVisit,
				LU.LpuUnit_isCMP,
				LU.LpuUnit_FRMOUnitID,
				LU.LpuUnit_FRMOid
			from
				v_LpuUnit LU with(nolock)
				{$join}
			where
				{$filters_str}
		", $params);
	}

	/**
	 * Получение списка групп отделений МО по идентификатору подразделения. Метод для API
	 */
	public function getLpuUnitListForAPI($data) {
		return $this->queryResult("
			select LpuUnit_id
			from v_LpuUnit with (nolock)
			where LpuBuilding_id = :LpuBuilding_id
		", array(
			'LpuBuilding_id' => $data['LpuBuilding_id'],
		));
	}

	/**
	 * Получения списка дополнительных профилей отделения. Метод для API
	 */
	function getLpuSectionLpuSectionProfileListForAPI($data) {
		$join = '';
		$filters = [];
		$params = [];
		if(!empty($data['LpuSection_id'])){
			$params['LpuSection_id'] = $data['LpuSection_id'];
			$filters[] = "LSLSP.LpuSection_id = :LpuSection_id";
		}
		if(!empty($data['LpuSection_OID'])){
			$params['LpuSection_OID'] = $data['LpuSection_OID'];
			$filters[] = "FS.FRMOSection_OID = :LpuSection_OID";
			$join .= ' inner JOIN nsi.FRMOSection FS WITH(NOLOCK) ON FS.FRMOSection_id = LS.FRMOSection_id ';
		}
		$filters_str = implode(" and ", $filters);
		$query = "
			select
				LSLSP.LpuSectionLpuSectionProfile_id,
				LSP.LpuSectionProfile_Code,
				convert(varchar(10), LSLSP.LpuSectionLpuSectionProfile_begDate, 120) as LpuSectionLpuSectionProfile_begDate,
				convert(varchar(10), LSLSP.LpuSectionLpuSectionProfile_endDate, 120) as LpuSectionLpuSectionProfile_endDate
			from
				v_LpuSectionLpuSectionProfile LSLSP with(nolock)
				inner join v_LpuSectionProfile LSP with(nolock) on LSP.LpuSectionProfile_id = LSLSP.LpuSectionProfile_id
				inner join v_LpuSection LS with (nolock) on LS.LpuSection_id = LSLSP.LpuSection_id
				{$join}
			where
				{$filters_str}
		";
		//echo getDebugSQL($query, $params); die();
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения списка участков. Метод для API
	 */
	function getLpuRegionListForAPI($data) {
		$filter = "";
		$params = array(
			'LpuBuilding_id' => $data['LpuBuilding_id'],
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['LpuSection_id'])) {
			$filter .= " and LR.LpuSection_id = :LpuSection_id";
			$params['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['LpuRegion_Name'])) {
			$filter .= " and LR.LpuRegion_Name = :LpuRegion_Name";
			$params['LpuRegion_Name'] = $data['LpuRegion_Name'];
		}

		$query = "
			select
				LR.LpuRegion_id
			from
				v_LpuRegion LR (nolock)
				inner join v_LpuSection LS (nolock) on LS.LpuSection_id = LR.LpuSection_id
			where
				LS.LpuBuilding_id = :LpuBuilding_id
				and LS.Lpu_id = :Lpu_id
				{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения списка участков по МО. Метод для API
	 */
	function getLpuRegionListByMOForAPI($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
			select
				lr.Lpu_id, 
				lr.LpuSection_id,
				ls.LpuBuilding_id,
				lr.LpuRegionType_id,
				lr.LpuRegion_Name, 
				convert(varchar(10), lr.LpuRegion_begDate, 120) as LpuRegion_begDate, 
				convert(varchar(10), lr.LpuRegion_endDate, 120) as LpuRegion_endDate
			from
				v_LpuRegion LR (nolock)
				left join v_LpuSection ls (nolock) on ls.LpuSection_id = lr.LpuSection_id
			where
				LR.Lpu_id = :Lpu_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения списка палат. Метод для API
	 */
	function getLpuSectionWardListForAPI($data) {
		$filter = "";
		$params = array(
			'LpuSection_id' => $data['LpuSection_id']
		);

		if (!empty($data['LpuSectionWard_Name'])) {
			$filter .= " and LSW.LpuSectionWard_Name = :LpuSectionWard_Name";
			$params['LpuSectionWard_Name'] = $data['LpuSectionWard_Name'];
		}

		if (!empty($data['LpuSectionWard_Floor'])) {
			$filter .= " and LSW.LpuSectionWard_Floor = :LpuSectionWard_Floor";
			$params['LpuSectionWard_Floor'] = $data['LpuSectionWard_Floor'];
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				LSW.LpuSectionWard_id
			from
				v_LpuSectionWard LSW (nolock)
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LSW.LpuSection_id
			where
				LSW.LpuSection_id = :LpuSection_id
				{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение палаты. Метод для API
	 */
	function getLpuSectionWardByIdForAPI($data) {
		$filter = "";
		$params = array(
			'LpuSectionWard_id' => $data['LpuSectionWard_id']
		);
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LS.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				LSW.LpuSection_id,
				LSW.LpuSectionWard_Name,
				LSW.LpuSectionWard_Floor,
				LSW.LpuWardType_id, 
				LSW.Sex_id, 
				LSW.LpuSectionWard_MainPlace,
				LSW.LpuSectionWard_DopPlace,
				LSW.LpuSectionWard_BedRepair,
				LSW.LpuSectionWard_Square, 
				LSW.LpuSectionWard_DayCost,
				LSW.LpuSectionWard_Views,
				LS.Lpu_id,
				convert(varchar(10), LSW.LpuSectionWard_setDate, 120) as LpuSectionWard_setDate,
				convert(varchar(10), LSW.LpuSectionWard_disDate, 120) as LpuSectionWard_disDate
			from
				v_LpuSectionWard LSW (nolock)
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LSW.LpuSection_id
			where
				LSW.LpuSectionWard_id = :LpuSectionWard_id
		".$filter;
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения списка объектов комфортности. Метод для API
	 */
	function getLpuSectionWardComfortLinkListForAPI($data) {
		$filter = "";
		$params = array(
			'LpuSectionWard_id' => $data['LpuSectionWard_id']
		);

		if (!empty($data['DChamberComfort_id'])) {
			$filter .= " and LSWCL.DChamberComfort_id = :DChamberComfort_id";
			$params['DChamberComfort_id'] = $data['DChamberComfort_id'];
		}
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and LS.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				LSWCL.LpuSectionWardComfortLink_id
			from
				fed.v_LpuSectionWardComfortLink LSWCL (nolock)
				left join dbo.v_LpuSectionWard LSW (nolock) on LSW.LpuSectionWard_id = LSWCL.LpuSectionWard_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LSW.LpuSection_id
			where
				LSWCL.LpuSectionWard_id = :LpuSectionWard_id
				{$filter}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получения объекта комфортности. Метод для API
	 */
	function getLpuSectionWardComfortLinkForAPI($data) {
		$params = array(
			'LpuSectionWardComfortLink_id' => $data['LpuSectionWardComfortLink_id']
		);

		$query = "
			select
				LSWCL.LpuSectionWardComfortLink_id,
				LSWCL.LpuSectionWard_id,
				LSWCL.DChamberComfort_id,
				LSWCL.LpuSectionWardComfortLink_Count,
				LS.Lpu_id
			from
				fed.v_LpuSectionWardComfortLink LSWCL (nolock)
				left join dbo.v_LpuSectionWard LSW (nolock) on LSW.LpuSectionWard_id = LSWCL.LpuSectionWard_id
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LSW.LpuSection_id
			where
				LSWCL.LpuSectionWardComfortLink_id = :LpuSectionWardComfortLink_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Получение идентификатора вида группы отделений по коду
	 */
	function getLpuUnitTypeId($LpuUnitType_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 LpuUnitType_id
			from v_LpuUnitType with(nolock)
			where LpuUnitType_Code = :LpuUnitType_Code
		", array(
			'LpuUnitType_Code' => $LpuUnitType_Code
		), true);
	}

	/**
	 * Получение идентификатора профиля отделения по коду
	 */
	function getLpuSectionProfileId($LpuSectionProfile_Code) {
		return $this->getFirstResultFromQuery("
			select top 1 LpuSectionProfile_id 
			from v_LpuSectionProfile with(nolock) 
			where LpuSectionProfile_Code = :LpuSectionProfile_Code
		", array(
			'LpuSectionProfile_Code' => $LpuSectionProfile_Code
		), true);
	}

	/**
	 * Получение параметров службы НМП
	 */
	function getNmpParams($data) {
		$params = array('MedService_id' => $data['MedService_id']);
		$query = "
			select top 1
				WT.MedService_id,
				convert(varchar(5), WT.LpuHMPWorkTime_MoFrom, 108) as LpuHMPWorkTime_MoFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_MoTo, 108) as LpuHMPWorkTime_MoTo,
				convert(varchar(5), WT.LpuHMPWorkTime_TuFrom, 108) as LpuHMPWorkTime_TuFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_TuTo, 108) as LpuHMPWorkTime_TuTo,
				convert(varchar(5), WT.LpuHMPWorkTime_WeFrom, 108) as LpuHMPWorkTime_WeFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_WeTo, 108) as LpuHMPWorkTime_WeTo,
				convert(varchar(5), WT.LpuHMPWorkTime_ThFrom, 108) as LpuHMPWorkTime_ThFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_ThTo, 108) as LpuHMPWorkTime_ThTo,
				convert(varchar(5), WT.LpuHMPWorkTime_FrFrom, 108) as LpuHMPWorkTime_FrFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_FrTo, 108) as LpuHMPWorkTime_FrTo,
				convert(varchar(5), WT.LpuHMPWorkTime_SaFrom, 108) as LpuHMPWorkTime_SaFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_SaTo, 108) as LpuHMPWorkTime_SaTo,
				convert(varchar(5), WT.LpuHMPWorkTime_SuFrom, 108) as LpuHMPWorkTime_SuFrom,
				convert(varchar(5), WT.LpuHMPWorkTime_SuTo, 108) as LpuHMPWorkTime_SuTo
			from 
				v_LpuHMPWorkTime WT with(nolock)
			where
				WT.MedService_id = :MedService_id
		";
		$response = $this->queryResult($query, $params);
		if (!is_array($response)) {
			return false;
		}

		$this->_params['session'] = $data['session'];
		$this->resetGlobalOptions();

		$options = $this->globalOptions['globals'];

		if (count($response) == 0) {
			$response[0] = array(
				'MedService_id' => $data['MedService_id'],
				'LpuHMPWorkTime_MoFrom' => $options['nmp_monday_beg_time'],
				'LpuHMPWorkTime_MoTo' => $options['nmp_monday_end_time'],
				'LpuHMPWorkTime_TuFrom' => $options['nmp_tuesday_beg_time'],
				'LpuHMPWorkTime_TuTo' => $options['nmp_tuesday_end_time'],
				'LpuHMPWorkTime_WeFrom' => $options['nmp_wednesday_beg_time'],
				'LpuHMPWorkTime_WeTo' => $options['nmp_wednesday_end_time'],
				'LpuHMPWorkTime_ThFrom' => $options['nmp_thursday_beg_time'],
				'LpuHMPWorkTime_ThTo' => $options['nmp_thursday_end_time'],
				'LpuHMPWorkTime_FrFrom' => $options['nmp_friday_beg_time'],
				'LpuHMPWorkTime_FrTo' => $options['nmp_friday_end_time'],
				'LpuHMPWorkTime_SaFrom' => $options['nmp_saturday_beg_time'],
				'LpuHMPWorkTime_SaTo' => $options['nmp_saturday_end_time'],
				'LpuHMPWorkTime_SuFrom' => $options['nmp_sunday_beg_time'],
				'LpuHMPWorkTime_SuTo' => $options['nmp_sunday_end_time'],
			);
		}

		return $response;
	}

	/**
	 * Сохранение параметров службы НМП
	 */
	function saveNmpParams($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedService_id' => $data['MedService_id'],
			'LpuHMPWorkTime_MoFrom' => !empty($data['LpuHMPWorkTime_MoFrom'])?$data['LpuHMPWorkTime_MoFrom']:null,
			'LpuHMPWorkTime_MoTo' => !empty($data['LpuHMPWorkTime_MoTo'])?$data['LpuHMPWorkTime_MoTo']:null,
			'LpuHMPWorkTime_TuFrom' => !empty($data['LpuHMPWorkTime_TuFrom'])?$data['LpuHMPWorkTime_TuFrom']:null,
			'LpuHMPWorkTime_TuTo' => !empty($data['LpuHMPWorkTime_TuTo'])?$data['LpuHMPWorkTime_TuTo']:null,
			'LpuHMPWorkTime_WeFrom' => !empty($data['LpuHMPWorkTime_WeFrom'])?$data['LpuHMPWorkTime_WeFrom']:null,
			'LpuHMPWorkTime_WeTo' => !empty($data['LpuHMPWorkTime_WeTo'])?$data['LpuHMPWorkTime_WeTo']:null,
			'LpuHMPWorkTime_ThFrom' => !empty($data['LpuHMPWorkTime_ThFrom'])?$data['LpuHMPWorkTime_ThFrom']:null,
			'LpuHMPWorkTime_ThTo' => !empty($data['LpuHMPWorkTime_ThTo'])?$data['LpuHMPWorkTime_ThTo']:null,
			'LpuHMPWorkTime_FrFrom' => !empty($data['LpuHMPWorkTime_FrFrom'])?$data['LpuHMPWorkTime_FrFrom']:null,
			'LpuHMPWorkTime_FrTo' => !empty($data['LpuHMPWorkTime_FrTo'])?$data['LpuHMPWorkTime_FrTo']:null,
			'LpuHMPWorkTime_SaFrom' => !empty($data['LpuHMPWorkTime_SaFrom'])?$data['LpuHMPWorkTime_SaFrom']:null,
			'LpuHMPWorkTime_SaTo' => !empty($data['LpuHMPWorkTime_SaTo'])?$data['LpuHMPWorkTime_SaTo']:null,
			'LpuHMPWorkTime_SuFrom' => !empty($data['LpuHMPWorkTime_SuFrom'])?$data['LpuHMPWorkTime_SuFrom']:null,
			'LpuHMPWorkTime_SuTo' => !empty($data['LpuHMPWorkTime_SuTo'])?$data['LpuHMPWorkTime_SuTo']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@LpuHMPWorkTime_id bigint
				
			set @LpuHMPWorkTime_id = (
				select top 1 LpuHMPWorkTime_id  from v_LpuHMPWorkTime with(nolock) where MedService_id = :MedService_id
			)
			
			if @LpuHMPWorkTime_id is null
				exec p_LpuHMPWorkTime_ins
				@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
				@Lpu_id = :Lpu_id,
				@MedService_id = :MedService_id,
				@LpuHMPWorkTime_MoFrom = :LpuHMPWorkTime_MoFrom,
				@LpuHMPWorkTime_MoTo = :LpuHMPWorkTime_MoTo,
				@LpuHMPWorkTime_TuFrom = :LpuHMPWorkTime_TuFrom,
				@LpuHMPWorkTime_TuTo = :LpuHMPWorkTime_TuTo,
				@LpuHMPWorkTime_WeFrom = :LpuHMPWorkTime_WeFrom,
				@LpuHMPWorkTime_WeTo = :LpuHMPWorkTime_WeTo,
				@LpuHMPWorkTime_ThFrom = :LpuHMPWorkTime_ThFrom,
				@LpuHMPWorkTime_ThTo = :LpuHMPWorkTime_ThTo,
				@LpuHMPWorkTime_FrFrom = :LpuHMPWorkTime_FrFrom,
				@LpuHMPWorkTime_FrTo = :LpuHMPWorkTime_FrTo,
				@LpuHMPWorkTime_SaFrom = :LpuHMPWorkTime_SaFrom,
				@LpuHMPWorkTime_SaTo = :LpuHMPWorkTime_SaTo,
				@LpuHMPWorkTime_SuFrom = :LpuHMPWorkTime_SuFrom,
				@LpuHMPWorkTime_SuTo = :LpuHMPWorkTime_SuTo,
				@pmUser_id = :pmUser_id
			else
				exec p_LpuHMPWorkTime_upd
				@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
				@Lpu_id = :Lpu_id,
				@MedService_id = :MedService_id,
				@LpuHMPWorkTime_MoFrom = :LpuHMPWorkTime_MoFrom,
				@LpuHMPWorkTime_MoTo = :LpuHMPWorkTime_MoTo,
				@LpuHMPWorkTime_TuFrom = :LpuHMPWorkTime_TuFrom,
				@LpuHMPWorkTime_TuTo = :LpuHMPWorkTime_TuTo,
				@LpuHMPWorkTime_WeFrom = :LpuHMPWorkTime_WeFrom,
				@LpuHMPWorkTime_WeTo = :LpuHMPWorkTime_WeTo,
				@LpuHMPWorkTime_ThFrom = :LpuHMPWorkTime_ThFrom,
				@LpuHMPWorkTime_ThTo = :LpuHMPWorkTime_ThTo,
				@LpuHMPWorkTime_FrFrom = :LpuHMPWorkTime_FrFrom,
				@LpuHMPWorkTime_FrTo = :LpuHMPWorkTime_FrTo,
				@LpuHMPWorkTime_SaFrom = :LpuHMPWorkTime_SaFrom,
				@LpuHMPWorkTime_SaTo = :LpuHMPWorkTime_SaTo,
				@LpuHMPWorkTime_SuFrom = :LpuHMPWorkTime_SuFrom,
				@LpuHMPWorkTime_SuTo = :LpuHMPWorkTime_SuTo,
				@pmUser_id = :pmUser_id
				
			select @LpuHMPWorkTime_id as LpuHMPWorkTime_id, @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			return $this->createError('','Ошибка при сохранении параметров службы НМП');
		}
		return $resp;
	}

	/**
	 * Получения списка объектов комфортности. Метод для API
	 */
	public function getMOById($data) {
		return $this->queryResult("
			select
				Lpu_Nick as Org_Nick,
				Lpu_Name as Org_Name
			from
				v_Lpu (nolock)
			where
				Lpu_id = :Lpu_id
		", $data);
	}

	/**
	 * Получение списка связей МО с бюро МСЭ
	 */
	function loadLpuMseLinkGrid($data) {
		$filters = array('1=1');
		$params = array();

		if (!empty($data['Lpu_bid'])) {
			$filters[] = "bL.Lpu_id = :Lpu_bid";
			$params['Lpu_bid'] = $data['Lpu_bid'];
		}
		if (!empty($data['Lpu_oid'])) {
			$filters[] = "L.Lpu_id = :Lpu_oid";
			$params['Lpu_oid'] = $data['Lpu_oid'];
		}
		if (!empty($data['LpuMseLink_begDate'])) {
			$filters[] = "LML.LpuMseLink_begDate = :LpuMseLink_begDate";
			$params['LpuMseLink_begDate'] = $data['LpuMseLink_begDate'];
		}
		if (!empty($data['LpuMseLink_endDate'])) {
			$filters[] = "LML.LpuMseLink_endDate = :LpuMseLink_endDate";
			$params['LpuMseLink_endDate'] = $data['LpuMseLink_endDate'];
		}
		if (!empty($data['MedService_id'])) {
			$filters[] = "LML.MedService_id = :MedService_id";
			$params['MedService_id'] = $data['MedService_id'];
		}
		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filters[] = "(LML.LpuMseLink_endDate is null or LML.LpuMseLink_endDate > @date)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filters[] = "LML.LpuMseLink_endDate <= @date";
		}

		$filters_str = implode(" and ", $filters);

		$query = "
			-- variables
			declare @date date = dbo.tzGetDate()
			-- end variables
			select
				-- select
				LML.LpuMseLink_id,
				LML.MedService_id,
				L.Lpu_id as Lpu_oid,
				L.Lpu_Nick,
				bL.Lpu_id as Lpu_bid,
				bL.Lpu_Nick as Lpu_bNick,
				MS.MedService_Nick,
				convert(varchar(10), LML.LpuMseLink_begDate, 104) as LpuMseLink_begDate,
				convert(varchar(10), LML.LpuMseLink_endDate, 104) as LpuMseLink_endDate
				-- end select
			from
				-- from
				v_LpuMseLink LML with(nolock)
				inner join v_Lpu bL with(nolock) on bL.Lpu_id = LML.Lpu_bid
				inner join v_Lpu L with(nolock) on L.Lpu_id = LML.Lpu_id
				left join v_MedService MS with(nolock) on LML.MedService_id = MS.MedService_id
				-- end from
			where
				-- where
				{$filters_str}
				-- end where
			order by
				-- order by
				LML.LpuMseLink_begDate
				-- end order by
		";
				
		$result = $this->queryResult(getLimitSQLPH($query, $data['start'], $data['limit']), $params);
		$result_count = $this->queryResult(getCountSQLPH($query), $params);

		if (!is_array($result) || !is_array($result_count)) {
			return false;
		}

		return array(
			'totalCount' => $result_count[0]['cnt'],
			'data' => $result
		);
	}

	/**
	 * Получение данных для редактирования связи МО с бюро МСЭ
	 */
	function loadLpuMseLinkForm($data) {
		$params = array('LpuMseLink_id' => $data['LpuMseLink_id']);
		$query = "
			select top 1
				LML.LpuMseLink_id,
				LML.Lpu_id as Lpu_oid,
				LML.Lpu_bid,
				LML.MedService_id,
				convert(varchar(10), LML.LpuMseLink_begDate, 104) as LpuMseLink_begDate,
				convert(varchar(10), LML.LpuMseLink_endDate, 104) as LpuMseLink_endDate
			from v_LpuMseLink LML with(nolock)
			where LML.LpuMseLink_id = :LpuMseLink_id
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение связи МО с бюро МСЭ
	 */
	function saveLpuMseLink($data) {
		$params = array(
			'LpuMseLink_id' => !empty($data['LpuMseLink_id'])?$data['LpuMseLink_id']:null,
			'Lpu_oid' => $data['Lpu_oid'],
			'Lpu_bid' => $data['Lpu_bid'],
			'MedService_id' => $data['MedService_id'],
			'LpuMseLink_begDate' => $data['LpuMseLink_begDate'],
			'LpuMseLink_endDate' =>!empty($data['LpuMseLink_endDate'])?$data['LpuMseLink_endDate']:null,
			'pmUser_id' => $data['pmUser_id'],
		);

		if (!empty($params['LpuMseLink_endDate']) && date_create($params['LpuMseLink_begDate']) > date_create($params['LpuMseLink_endDate'])) {
			return $this->createError('','Дата закрытия не может быть меньше даты отрытия');
		}

		$query = "
			declare @bigDate date = dateadd(year, 100, dbo.tzGetDate())
			declare @begDate date = :LpuMseLink_begDate
			declare @endDate date = :LpuMseLink_endDate
			select top 1 count(*) as cnt
			from v_LpuMseLink with(nolock)
			where LpuMseLink_id != isnull(:LpuMseLink_id, 0)
			and MedService_id = :MedService_id
			and Lpu_id = :Lpu_oid
			and LpuMseLink_begDate < isnull(@endDate, @bigDate)
			and isnull(LpuMseLink_endDate, @bigDate) > @begDate
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('','Ошибка при проверке существования связей');
		}
		if ($count > 0) {
			return $this->createError('','Связь Бюро МСЭ с данной МО уже существует');
		}

		if (empty($params['LpuMseLink_id'])) {
			$procedure = 'p_LpuMseLink_ins';
		} else {
			$procedure = 'p_LpuMseLink_upd';
		}
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint,
				@Res bigint = :LpuMseLink_id;
			exec {$procedure}
				@LpuMseLink_id = @Res output,
				@Lpu_id = :Lpu_oid,
				@Lpu_bid = :Lpu_bid,
				@MedService_id = :MedService_id,
				@LpuMseLink_begDate = :LpuMseLink_begDate,
				@LpuMseLink_endDate = :LpuMseLink_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output
			select @Res as LpuMseLink_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранение связи МО с бюро МСЭ
	 */
	function deleteLpuMseLink($data) {
		$params = array('LpuMseLink_id' => $data['LpuMseLink_id']);
		$query = "
			declare
				@Error_Message varchar(4000),
				@Error_Code bigint;
			exec p_LpuMseLink_del
				@LpuMseLink_id = :LpuMseLink_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		return $this->queryResult($query, $params);
	}


	/**
	 * Получение списка профилей (ФРМО)
	 */
	function getLpuUnitProfile($data){
		$queryParams = array();
		$query = "
			SELECT
				LUT.LpuUnitProfile_id as LpuUnitProfile_fid
				,LUT.LpuUnitProfile_Name
				,LUT.LpuUnitProfile_pid
				,LUT.LpuUnitProfile_Form30
				,LUT.UnitDepartType_id
			FROM
				fed.LpuUnitProfile LUT with (nolock)

			";
		$result = $this->db->query($query,$queryParams);
		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * Получение списка профилей (ФРМО)
	 */
	function getFRMPSubdivisionType($data) {
		$query = "
			SELECT
				id,
				name,
				fullname,
				parent
			FROM
				persis.v_FRMPSubdivision FRMPS with (nolock)
			ORDER BY
				FRMPS.parent
			";
		//echo getDebugSQL($query, $data);die();
		$result = $this->db->query($query);
		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получение МО обслуживания адреса (МО обслуживания активного вызова) по участку
	 */
	function getLpuAddress($data){
		if(empty($data['KLTown_id']) && empty($data['KLCity_id'])){
			return false;
		}
		$params = array();
		$where = '(lpu.lpu_endDate is null or lpu.lpu_enDdate > dbo.tzGetDate()) and (lr.LpuRegion_endDate is null or lr.LpuRegion_endDate > dbo.tzGetDate()) ';

		if( isset($data['Person_Age']) ){
			$where .= ( ($data['Person_Age']) < 18 ) ? " and LRT.LpuRegionType_SysNick in ('ped','vop')" : " and LRT.LpuRegionType_SysNick in ('ter','vop')";
		}
		if( isset($data['Lpu_id']) ){
			$where .= " and LR.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		if( isset($data['KLTown_id']) ){
			//$where .= " AND LRS.KLTown_id = :KLTown_id";
			$where .= "  AND (LRS.KLTown_id = :KLTown_id or LRS.KLCity_id = :KLTown_id)";
			$params['KLTown_id'] = $data['KLTown_id'];
		}elseif( isset($data['KLCity_id']) ){
			$where .= " AND LRS.KLCity_id = :KLCity_id";

			$params['KLCity_id'] = $data['KLCity_id'];
		}
		if(isset($data['KLHome'])){
			$where .= " AND (dbo.GetHouse(LRS.LpuRegionStreet_HouseSet, :KLHome) = 1 or LRS.LpuRegionStreet_IsAll = 2)";
			$params['KLHome'] = $data['KLHome'];
		}
		if(isset($data['KLStreet_id'])){
			$where .= " AND (LRS.KLStreet_id = :KLStreet_id or LRS.LpuRegionStreet_IsAll = 2)";
			$params['KLStreet_id'] = $data['KLStreet_id'];
		}

		$query = "
			Select TOP 1
				LRS.Server_id,
				LR.Lpu_id,
				LR.LpuRegionType_id,
				LRT.LpuRegionType_SysNick,
				LRS.LpuRegionStreet_id,
				LRS.LpuRegion_id,
				LRS.KLCountry_id,
				LRS.KLRGN_id,
				LRS.KLSubRGN_id,
				LRS.KLCity_id,
				LRS.KLTown_id,
				case IsNULL(LRS.KLTown_id,'')
				when '' then RTrim(c.KLArea_Name)+' '+ISNULL(cs.KLSocr_Nick,'')
				else RTrim(t.KLArea_Name)+' '+ISNULL(ts.KLSocr_Nick,'')
				end as KLTown_Name,
				LRS.KLStreet_id,
				RTrim(KLStreet_FullName) as KLStreet_Name,
				LRS.LpuRegionStreet_HouseSet,
				COALESCE(LU.LpuUnit_Phone, LPU.Lpu_Phone, '') as phone
			from LpuRegionStreet LRS  with (nolock)
				left join v_LpuRegion LR with (nolock) on LR.LpuRegion_id = LRS.LpuRegion_id
				left join LpuRegionType LRT with (nolock) on LRT.LpuRegionType_id = LR.LpuRegionType_id
				left join KLArea t with (nolock) on t.KLArea_id = LRS.KLTown_id
				left join KLSocr ts with (nolock) on ts.KLSocr_id = t.KLSocr_id
				left join v_KLStreet KLStreet with (nolock) on KLStreet.KLStreet_id = LRS.KLStreet_id
				left join KLArea c with (nolock) on c.Klarea_id = LRS.KLCity_id
				left join KLSocr cs with (nolock) on cs.KLSocr_id = c.KLSocr_id
				left join v_Lpu LPU with (nolock) on LR.Lpu_id = LPU.Lpu_id
				left join v_LpuSection LS with (nolock) on LR.LpuSection_id = LS.LpuSection_id
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
			WHERE
				{$where}
		";
		//var_dump(getDebugSQL($query, $params)); exit;

		$result = $this->db->query($query, $params, true);

		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	 * Получить номер телефона из настроек группы отделений 
	 */
	function getLpuPhoneMO($data){
		if(empty($data['Lpu_id'])) return false;
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);
		$query = "
			SELECT TOP 1 
				RTrim(lu.LpuUnit_Phone) as Phone
			FROM
				v_Lpu LL with(nolock)
				left join v_LpuUnit lu with(nolock) on lu.Lpu_id = LL.Lpu_id
			WHERE (1=1)
				AND lu.LpuUnit_Phone is not null
				AND lu.LpuUnitType_id=2   -- Тип группы отделений (Поликлиника)
				AND lu.LpuUnit_IsEnabled = 2   -- Признак активности
				AND LL.Lpu_id = :Lpu_id";
			
		$result = $this->db->query($query, $params, true);

		if (is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}
	
	/**
	* Получение штатного расписания
	*/
	public function getLpuStaffGridDetail($data)
	{
		if ( empty($data['LpuStaff_id']) && empty($data['Lpu_id']) ) {
			return array(array('LpuSectionBedState_cid' => null, 'Error_Code' => 1,'Error_Msg' => 'Отсутсвтуют необходимые параметры.'));
		}

		$filter = '';
		$queryParams = array();

		if ( !empty($data['LpuStaff_id']) ) {
			$filter .= ' and LpuStaff_id = :LpuStaff_id';
			$queryParams['LpuStaff_id'] = $data['LpuStaff_id'];
		}
		else {
			$filter .= ' and Lpu_id = :Lpu_id';
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$sql = "
			SELECT
				LpuStaff_id,
				convert(varchar(10), LpuStaff_ApprovalDT, 104) as LpuStaff_ApprovalDT,	--Дата утверждения
				convert(varchar(10), LpuStaff_begDate, 104) as LpuStaff_begDate,			--Дата начала
				convert(varchar(10), LpuStaff_endDate, 104) as LpuStaff_endDate,			--Дата окончания
				LpuStaff_Descript,		--Описание
				LpuStaff_Num,			--Номер штатного расписания
				Staff_id,				--Строка штатного расписания
				Lpu_id
			FROM
				dbo.v_LpuStaff with (nolock)
			WHERE (1=1)
				{$filter}
		";

		//echo getDebugSQL($sql, $queryParams);
		return $this->queryResult($sql, $queryParams);
	}
	
	/**
	* Сохранение штатного расписания
	*/
	function saveLpuStaffGridDetail($data)
	{
		$queryParams = array(
			'LpuStaff_id' => (isset($data['LpuStaff_id'])) ? $data['LpuStaff_id'] : null,
			'LpuStaff_Descript' => (isset($data['LpuStaff_Descript'])) ? $data['LpuStaff_Descript'] : null,
			'LpuStaff_Num' => (isset($data['LpuStaff_Num'])) ? $data['LpuStaff_Num'] : null,
			'Lpu_id' => $data['Lpu_id'],
			'LpuStaff_ApprovalDT' => (isset($data['LpuStaff_ApprovalDT'])) ? $data['LpuStaff_ApprovalDT'] : null,
			'LpuStaff_begDate' => (isset($data['LpuStaff_begDate'])) ? $data['LpuStaff_begDate'] : null,
			'LpuStaff_endDate' => (isset($data['LpuStaff_endDate'])) ? $data['LpuStaff_endDate'] : null,
			'pmUser_id' => $data['pmUser_id']
		);
		$where = (isset($data['LpuStaff_id'])) ? ' and LpuStaff_id != :LpuStaff_id ' : '';
		if($queryParams['LpuStaff_endDate']){
			$where .= " 
				and (
					(LpuStaff_begDate <= :LpuStaff_endDate) 
					and 
					(LpuStaff_endDate >= :LpuStaff_begDate or LpuStaff_endDate is null)
				)";
		}else{
			$where .= " 
				and (
					LpuStaff_endDate >= :LpuStaff_begDate or LpuStaff_endDate is null
				)";
		}
		$query = "
			select
				COUNT (*) as [count]
			from
				dbo.v_LpuStaff with (nolock)
			where (1=1)
				and Lpu_id = :Lpu_id
		".$where;
		
		$res = $this->db->query($query, $queryParams);
		$response = $res->result('array');
		if ( $response[0]['count'] > 0) {
			return array(0 => array('Error_Msg' => 'Период действия штатного расписания пересекается с периодом действия другого штатного расписания. Сохранение невозможно.'));
		}
		
		if (isset($data['LpuStaff_id'])){
			$proc = 'dbo.p_LpuStaff_upd';
		}else{
			$proc = 'dbo.p_LpuStaff_ins';
		}
		
		$query = "
			declare
				@LpuStaff_id bigint,
				@Error_Code bigint,
				@Error_Message varchar(4000);
			set @LpuStaff_id = :LpuStaff_id;
			exec {$proc}
				@LpuStaff_id = @LpuStaff_id output,
				@LpuStaff_Descript = :LpuStaff_Descript,
				@LpuStaff_Num = :LpuStaff_Num,
				@Lpu_id = :Lpu_id,
				@LpuStaff_ApprovalDT = :LpuStaff_ApprovalDT,
				@LpuStaff_begDate = :LpuStaff_begDate,
				@LpuStaff_endDate = :LpuStaff_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @LpuStaff_id as LpuStaff_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
		";
		// echo getDebugSQL($query, $data);exit;
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
	 * @param array $data
	 * @return false|int
	 */
	function hasMedStaffFactInAIDSCenter($data) {
		$params = array(
			'MedPersonal_id' => $data['MedPersonal_id'],
		);
		$query = "
			select top 1
				count(*) as cnt
			from
				v_MedStaffFact MSF with(nolock)
				inner join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = MSF.LpuBuilding_id
			where
				MSF.MedPersonal_id = :MedPersonal_id
				and isnull(LB.LpuBuilding_IsAIDSCenter, 1) = 2
		";
		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return false;
		}
		return $count > 0;
	 }

	/**
	 * Загрузка списка МО для формы "Выбор МО для управления"
	 */
	public function getLpuListWithSmp(){

		$sql = "
			SELECT DISTINCT
				l.Lpu_id,
				l.Lpu_Nick
			FROM
				v_Lpu l with (nolock)
				LEFT JOIN v_LpuBuilding lb with (nolock) ON(l.Lpu_id=lb.Lpu_id)
			WHERE

				l.Lpu_begDate <= dbo.tzGetDate() and
				(l.Lpu_endDate is null or l.Lpu_endDate > dbo.tzGetDate()) and
				lb.LpuBuildingType_id = 27 and
				lb.LpuBuilding_begDate <= dbo.tzGetDate() and
				(lb.LpuBuilding_endDate is null or lb.LpuBuilding_endDate > dbo.tzGetDate())
			ORDER BY
				l.Lpu_Nick ASC
		";

		return $this->db->query($sql)->result_array();
	}

	/**
	 * Загрузка списка Функциональных подразделений по СУР
	 */
	public function getFpList($data){

		$sql = "
			SELECT
				GetFP.FPID,
				GetFP.CodeRu,
				GetFP.NameRU
			FROM
				r101.GetMO (nolock)
				INNER JOIN r101.GetFP (nolock) on GetFP.MOID = GetMO.ID
			WHERE
				GetMO.Lpu_id = :Lpu_id
		";

		return $this->queryResult($sql, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Загрузка списка Функциональных подразделений по СУР
	 */
	public function getLpuSectionBedProfileLinkFed(){
		return $this->queryResult("
			select
				LpuSectionBedProfileLink_id,
				LpuSectionBedProfile_id,
				LpuSectionBedProfile_fedid
			from
				fed.LpuSectionBedProfileLink (nolock)
		", array());
	}

	/**
	 * Загрузка атрибутов отделения
	 */
	public function getLpuSectionAttributes($data) {
		if(empty($data['LpuSection_id'])) return false;
		$params = array('LpuSection_id' => $data['LpuSection_id']);
		$sql = "
			select AttributeSign_Code
			from AttributeSignValue ASV with (nolock)
				inner join AttributeSign [AS] with (nolock) on [AS].AttributeSign_id = ASV.AttributeSign_id
			where
				AttributeSignValue_TablePKey = :LpuSection_id
				and [AS].AttributeSign_TableName = 'dbo.LpuSection'
				and [AS].AttributeSign_Code = 13
		";
		$resp = $this->queryResult($sql, $params);
		return $resp;
	}

	/**
	 * получение основного и дополнительных профилей отделения
	 */
	function getLpuStructureProfileAll($data){
		if(empty($data['LpuSection_id'])) return false;
		$sql = "
			select
				lsp.LpuSectionProfile_id
				,lsp.LpuSectionProfile_Code
				,lsp.LpuSectionProfile_Name
			from dbo.v_LpuSectionLpuSectionProfile lslsp with (nolock)
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = lslsp.LpuSectionProfile_id
			where
				lslsp.LpuSection_id = :LpuSection_id
			UNION ALL
			select
				ls.LpuSectionProfile_id
				,lsp.LpuSectionProfile_Code
				,lsp.LpuSectionProfile_Name
			from dbo.v_LpuSection ls with (nolock)
				inner join v_LpuSectionProfile lsp with (nolock) on lsp.LpuSectionProfile_id = ls.LpuSectionProfile_id
			where
				ls.LpuSection_id = :LpuSection_id
		";

		return $this->queryResult($sql, array(
			'LpuSection_id' => $data['LpuSection_id']
		));
	}
}

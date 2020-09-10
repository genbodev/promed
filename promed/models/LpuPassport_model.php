<?php
/**
* LpuPassport_model - модель, для работы с таблицей LpuPassport
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      май 2010
*/

class LpuPassport_model extends swModel {

	public $inputRules = array(
		'createPassportMO' => array(
			array('field' => 'Lpu_f003mcod', 'label' => 'Федеральный реестровый код', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Lpu_begDate', 'label' => 'Дата начала деятельности', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Oktmo_id', 'label' => 'Идентификатор ОКТМО', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Org_OKPO', 'label' => 'Идентификатор ОКПО', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Org_OKATO', 'label' => 'Идентификатор ОКАТО', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Okogu_id', 'label' => 'Идентификатор ОКОГУ', 'rules' => '', 'type' => 'int'),
			array('field' => 'Okved_id', 'label' => 'Идентификатор ОКВЭД', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Okfs_id', 'label' => 'Идентификатор ОКФС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_OKDP', 'label' => 'Идентификатор ОКДП', 'rules' => '', 'type' => 'string'),
			array('field' => 'Okopf_id', 'label' => 'Идентификатор ОКОПФ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Lpu_PensRegNum', 'label' => 'Рег номер в ПФ РФ', 'rules' => '', 'type' => 'int'),
			array('field' => 'UAddress_id', 'label' => 'Юридический адрес', 'rules' => '', 'type' => 'int'),
			array('field' => 'PAddress_id', 'label' => 'Фактический адрес', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_INN', 'label' => 'ИНН', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Org_Email', 'label' => 'Адрес электронной почты', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_Www', 'label' => 'Адрес сайта', 'rules' => '', 'type' => 'string'),
			array('field' => 'DepartAffilType_id', 'label' => 'Ведомственная принадлежность', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_KPP', 'label' => 'КПП', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Lpu_RegDate', 'label' => 'Дата регистрации', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'Lpu_DocReg', 'label' => 'Документ о регистрации', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Org_OGRN', 'label' => 'Идентификатор ОГРН', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Org_id', 'label' => 'Регистрирующий орган', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Org_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuPmuType_id', 'label' => 'Тип МО для ПМУ', 'rules' => '', 'type' => 'int'),
			array('field' => 'MesAgeLpuType_id', 'label' => 'Тип МО по возрасту', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSubjectionLevel_id', 'label' => 'Идентификатор уровня подчиненности МО', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_gid', 'label' => 'Идентификатор головного учеждения', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_VizitFact', 'label' => 'Количество посещений в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'InstitutionLevel_id', 'label' => 'Идентификатор уровня учреждения в иерархии сети', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_MaxDistansePoint', 'label' => 'Расстояние до наиболее удаленной точки территориального обслуживания (км)', 'rules' => '', 'type' => 'float'),
			array('field' => 'DLocationLpu_id', 'label' => 'Идентификатор местоположения учреждения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsVideo', 'label' => 'Наличие видеонаблюдения территорий и помещений', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsMetalDoors', 'label' => 'Наличие металлических входных дверей', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsSecur', 'label' => 'Наличие охраны', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsAccompanying', 'label' => 'Проживание сопровождающих лиц', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsFenceTer', 'label' => 'Ограждение территории', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PasportMO_IsTerLimited', 'label' => 'Приспособленность территории для пациентов с ограниченными возможностями', 'rules' => 'required', 'type' => 'id')
		),
		'updatePassportMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'Lpu_f003mcod', 'label' => 'Федеральный реестровый код', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_begDate', 'label' => 'Дата начала деятельности', 'rules' => 'trim', 'type' => 'date'),
			array('field' => 'Oktmo_id', 'label' => 'Идентификатор ОКТМО', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'Org_OKPO', 'label' => 'Идентификатор ОКПО', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_OKATO', 'label' => 'Идентификатор ОКАТО', 'rules' => '', 'type' => 'string'),
			array('field' => 'Okogu_id', 'label' => 'Идентификатор ОКОГУ', 'rules' => '', 'type' => 'int'),
			array('field' => 'Okved_id', 'label' => 'Идентификатор ОКВЭД', 'rules' => '', 'type' => 'id'),
			array('field' => 'Okfs_id', 'label' => 'Идентификатор ОКФС', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_OKDP', 'label' => 'Идентификатор ОКДП', 'rules' => '', 'type' => 'string'),
			array('field' => 'Okopf_id', 'label' => 'Идентификатор ОКОПФ', 'rules' => '', 'type' => 'id'),
			array('field' => 'Lpu_PensRegNum', 'label' => 'Рег номер в ПФ РФ', 'rules' => '', 'type' => 'int'),
			array('field' => 'UAddress_id', 'label' => 'Юридический адрес', 'rules' => '', 'type' => 'int'),
			array('field' => 'PAddress_id', 'label' => 'Фактический адрес', 'rules' => '', 'type' => 'int'),
			array('field' => 'Org_INN', 'label' => 'ИНН', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_Email', 'label' => 'Адрес электронной почты', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_Www', 'label' => 'Адрес сайта', 'rules' => '', 'type' => 'string'),
			array('field' => 'DepartAffilType_id', 'label' => 'Ведомственная принадлежность', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_KPP', 'label' => 'КПП', 'rules' => '', 'type' => 'string'),
			array('field' => 'Lpu_RegDate', 'label' => 'Дата регистрации', 'rules' => '', 'type' => 'date'),
			array('field' => 'Lpu_DocReg', 'label' => 'Документ о регистрации', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_OGRN', 'label' => 'Идентификатор ОГРН', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_id', 'label' => 'Регистрирующий орган', 'rules' => '', 'type' => 'id'),
			array('field' => 'Org_Phone', 'label' => 'Телефон', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuPmuType_id', 'label' => 'Тип МО для ПМУ', 'rules' => '', 'type' => 'int'),
			array('field' => 'MesAgeLpuType_id', 'label' => 'Тип МО по возрасту', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuSubjectionLevel_id', 'label' => 'Идентификатор уровня подчиненности МО', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_gid', 'label' => 'Идентификатор головного учеждения', 'rules' => '', 'type' => 'int'),
			array('field' => 'Lpu_VizitFact', 'label' => 'Количество посещений в смену', 'rules' => '', 'type' => 'int'),
			array('field' => 'InstitutionLevel_id', 'label' => 'Идентификатор уровня учреждения в иерархии сети', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_MaxDistansePoint', 'label' => 'Расстояние до наиболее удаленной точки территориального обслуживания (км)', 'rules' => '', 'type' => 'float'),
			array('field' => 'DLocationLpu_id', 'label' => 'Идентификатор местоположения учреждения', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsVideo', 'label' => 'Наличие видеонаблюдения территорий и помещений', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsMetalDoors', 'label' => 'Наличие металлических входных дверей', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsSecur', 'label' => 'Наличие охраны', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsAccompanying', 'label' => 'Проживание сопровождающих лиц', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsFenceTer', 'label' => 'Ограждение территории', 'rules' => '', 'type' => 'id'),
			array('field' => 'PasportMO_IsTerLimited', 'label' => 'Приспособленность территории для пациентов с ограниченными возможностями', 'rules' => '', 'type' => 'id')
		),
		'getLpuPeriodOMSlistByMo' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getLpuPeriodOMS' => array(
			array(
				'field' => 'LpuPeriodOMS_id',
				'label' => 'Идентификатор периода по ОМС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLpuPeriodDLOlistByMo' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getLpuPeriodDLO' => array(
			array(
				'field' => 'LpuPeriodDLO_id',
				'label' => 'Идентификатор периода по ДЛО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOInfoSysListByMo' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getMOInfoSys' => array(
			array(
				'field' => 'MOInfoSys_id',
				'label' => 'Идентификатор ИС',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getSpecializationMOListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getSpecializationMO' => array(
			array(
				'field' => 'SpecializationMO_id',
				'label' => 'Идентификатор специализации организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMedTechnologyListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getMedTechnologyListByLpuBuildingPass' => array(
			array(
				'field' => 'LpuBuildingPass_id',
				'label' => 'Идентификатор здания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMedTechnology' => array(
			array(
				'field' => 'MedTechnology_id',
				'label' => 'Идентификатор медицинской технологии',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMedUslugaListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getMedUsluga' => array(
			array(
				'field' => 'MedUsluga_id',
				'label' => 'Идентификатор медицинской услуги',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getUslugaComplexLpuListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getUslugaComplexLpu' => array(
			array(
				'field' => 'UslugaComplexLpu_id',
				'label' => 'Идентификатор направления оказания медицинской помощи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPlfObjectsListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPlfObjects' => array(
			array(
				'field' => 'PlfObjectCount_id',
				'label' => 'Идентификатор направления оказания медицинской помощи',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getFunctionTimeListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getFunctionTime' => array(
			array(
				'field' => 'FunctionTime_id',
				'label' => 'Идентификатор периода функционирования учреждения',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPitanListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPitan' => array(
			array(
				'field' => 'PitanFormTypeLink_id',
				'label' => 'Идентификатор таблицы связей питания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPlfListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getPlf' => array(
			array(
				'field' => 'PlfDocTypeLink_id',
				'label' => 'Идентификатор таблицы связей природных леебных факторов и МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getOrgServiceTerrListByMO' => array(
			array(
				'field' => 'Org_id',
				'label' => 'Идентификатор организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getOrgServiceTerr' => array(
			array(
				'field' => 'OrgServiceTerr_id',
				'label' => 'Идентификатор территории обслуживания',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getOrgHeadListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getOrgHead' => array(
			array(
				'field' => 'OrgHead_id',
				'label' => 'Идентификатор руководящей единицы организации',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOArrivalListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOArrival' => array(
			array(
				'field' => 'MOArrival_id',
				'label' => 'Идентификатор заезда',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDisSanProtectionListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getDisSanProtection' => array(
			array(
				'field' => 'DisSanProtection_id',
				'label' => 'Идентификатор округа горно-санитарной охраны',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getKurortStatusDocListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getKurortStatusDoc' => array(
			array(
				'field' => 'KurortStatusDoc_id',
				'label' => 'Идентификатор статуса курорта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getKurortTypeLinkListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getKurortTypeLink' => array(
			array(
				'field' => 'KurortTypeLink_id',
				'label' => 'Идентификатор типа курорта',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOAreaObjectListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOAreaObject' => array(
			array(
				'field' => 'MOAreaObject_id',
				'label' => 'Идентификатор объекта инфраструктуры',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOAreaListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getMOAreaByNameAndMember' => array(
			array(
				'field' => 'MOArea_Name',
				'label' => 'Наименование площадки',
				'rules' => 'required',
				'type' => 'string'
			),
			array(
				'field' => 'MOArea_Member',
				'label' => 'Идентификатор участка',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getMOArea' => array(
			array(
				'field' => 'MOArea_id',
				'label' => 'Идентификатор площадки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getTransportConnectListByMOArea' => array(
			array(
				'field' => 'MOArea_id',
				'label' => 'Идентификатор площадки',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getTransportConnect' => array(
			array(
				'field' => 'TransportConnect_id',
				'label' => 'Идентификатор связи с транспортными узлами',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'getLpuBuildingPassListByMO' => array(
			array(
				'field' => 'Lpu_id',
				'label' => 'Идентификатор МО',
				'rules' => 'required',
				'type' => 'id',
				'equalsession' => true
			)
		),
		'getLpuBuildingPassByBuildingIdent' => array(
			array(
				'field' => 'LpuBuildingPass_BuildingIdent',
				'label' => 'Идентификатор здания',
				'rules' => 'required',
				'type' => 'string'
			)
		),
		'getLpuBuildingPass' => array(
			array(
				'field' => 'LpuBuildingPass_id',
				'label' => 'Идентификатор здания МО',
				'rules' => 'required',
				'type' => 'id'
			)
		),
		'createLpuPeriodOMS' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuPeriodOMS_begDate', 'label' => 'Дата включения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuPeriodOMS_endDate', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date')
		),
		'updateLpuPeriodOMS' => array(
			array('field' => 'LpuPeriodOMS_id', 'label' => 'Идентификатор периода по ОМС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuPeriodOMS_begDate', 'label' => 'Дата включения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuPeriodOMS_endDate', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date')
		),
		'deleteLpuPeriodOMS' => array(
			array('field' => 'LpuPeriodOMS_id', 'label' => 'Идентификатор периода по ОМС', 'rules' => 'required', 'type' => 'id')
		),
		'createMOAreaObject' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'DObjInfrastructure_id', 'label' => 'Идентификатор наименования объекта инфраструктуры', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MOAreaObject_Count', 'label' => 'Количество объектов инфраструктуры', 'rules' => '', 'type' => 'int'),
			array('field' => 'MOAreaObject_Member', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'string')
		),
		'updateMOAreaObject' => array(
			array('field' => 'MOAreaObject_id', 'label' => 'Идентификатор объекта инфраструктуры', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DObjInfrastructure_id', 'label' => 'Идентификатор наименования объекта инфраструктуры', 'rules' => '', 'type' => 'id'),
			array('field' => 'MOAreaObject_Count', 'label' => 'Количество объектов инфраструктуры', 'rules' => '', 'type' => 'int'),
			array('field' => 'MOAreaObject_Member', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'string')
		),
		'deleteMOAreaObject' => array(
			array('field' => 'MOAreaObject_id', 'label' => 'Идентификатор объекта инфраструктуры', 'rules' => 'required', 'type' => 'id')
		),
		'createMOArea' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'MOArea_Name', 'label' => 'Наименование площадки', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MOArea_Member', 'label' => 'Идентификатор участка', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MoArea_Right', 'label' => 'Право на земельный участок', 'rules' => '', 'type' => 'string'),
			array('field' => 'MoArea_Space', 'label' => 'Площадь участка', 'rules' => '', 'type' => 'float'),
			array('field' => 'MoArea_KodTer', 'label' => 'Код территории', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MoArea_OrgDT', 'label' => 'Дата организации', 'rules' => '', 'type' => 'date'),
			array('field' => 'MoArea_AreaSite', 'label' => 'Площадь площадки', 'rules' => '', 'type' => 'float'),
			array('field' => 'OKATO_id', 'label' => 'Идентификатор кода ОКАТО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => '', 'type' => 'id')
		),
		'updateMOArea' => array(
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MOArea_Name', 'label' => 'Наименование площадки', 'rules' => '', 'type' => 'string'),
			array('field' => 'MOArea_Member', 'label' => 'Идентификатор участка', 'rules' => '', 'type' => 'string'),
			array('field' => 'MoArea_Right', 'label' => 'Право на земельный участок', 'rules' => '', 'type' => 'string'),
			array('field' => 'MoArea_Space', 'label' => 'Площадь участка', 'rules' => '', 'type' => 'float'),
			array('field' => 'MoArea_KodTer', 'label' => 'Код территории', 'rules' => '', 'type' => 'string'),
			array('field' => 'MoArea_OrgDT', 'label' => 'Дата организации', 'rules' => '', 'type' => 'date'),
			array('field' => 'MoArea_AreaSite', 'label' => 'Площадь площадки', 'rules' => '', 'type' => 'float'),
			array('field' => 'OKATO_id', 'label' => 'Идентификатор кода ОКАТО', 'rules' => '', 'type' => 'id'),
			array('field' => 'Address_id', 'label' => 'Идентификатор адреса', 'rules' => '', 'type' => 'id')
		),
		'deleteMOArea' => array(
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => 'required', 'type' => 'id')
		),
		'createTransportConnect' => array(
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'TransportConnect_Airport', 'label' => 'Ближайший аэропорт', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisAirport', 'label' => 'Расстояние до аэропорта, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Heliport', 'label' => 'Ближайшая вертолетная площадка', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisHeliport', 'label' => 'Расстояние до вертолетной площадки, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Station', 'label' => 'Ближайшая станция', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisStation', 'label' => 'Расстояние до станции, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Railway', 'label' => 'Ближайший автовокзал', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisRailway', 'label' => 'Расстояние до автовокзала, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_MainRoad', 'label' => 'Ближайшая главная дорога', 'rules' => '', 'type' => 'string')
		),
		'updateTransportConnect' => array(
			array('field' => 'TransportConnect_id', 'label' => 'Идентификатор связи с транспортными узлами', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => '', 'type' => 'id'),
			array('field' => 'TransportConnect_Airport', 'label' => 'Ближайший аэропорт', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisAirport', 'label' => 'Расстояние до аэропорта, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Heliport', 'label' => 'Ближайшая вертолетная площадка', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisHeliport', 'label' => 'Расстояние до вертолетной площадки, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Station', 'label' => 'Ближайшая станция', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisStation', 'label' => 'Расстояние до станции, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_Railway', 'label' => 'Ближайший автовокзал', 'rules' => '', 'type' => 'string'),
			array('field' => 'TransportConnect_DisRailway', 'label' => 'Расстояние до автовокзала, км.', 'rules' => '', 'type' => 'int'),
			array('field' => 'TransportConnect_MainRoad', 'label' => 'Ближайшая главная дорога', 'rules' => '', 'type' => 'string')
		),
		'deleteTransportConnect' => array(
			array('field' => 'TransportConnect_id', 'label' => 'Идентификатор связи с транспортными узлами', 'rules' => 'required', 'type' => 'id')
		),
		'createLpuBuildingPass' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuBuildingPass_AmbPlace', 'label' => 'Амбулаторные места', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_BuildingIdent', 'label' => 'Идентификатор здания (Уникальный номер здания по учету учреждения)', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_PowerProjBed', 'label' => 'Мощность по проекту (число коек)', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_PowerProjViz', 'label' => 'Мощность по проекту (число посещений)', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsBalance', 'label' => 'Признак на балансе', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_Name', 'label' => 'Наименование здания', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'BuildingAppointmentType_id', 'label' => 'Идентификатор назначения здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_BuildVol', 'label' => 'Объем здания', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_TotalArea', 'label' => 'Общая площадь', 'rules' => '', 'type' => 'float'),
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_OfficeArea', 'label' => 'Площадь кабинетов', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_BedArea', 'label' => 'Площадь коечных отделений', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_EffBuildVol', 'label' => 'Полезная площадь', 'rules' => '', 'type' => 'float'),
			array('field' => 'PropertyType_id', 'label' => 'Форма владения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_StatPlace', 'label' => 'Стационарные места', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_MedWorkCabinet', 'label' => 'Число кабинетов врачебного приема', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingType_id', 'label' => 'Вид здания по применению', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsVentil', 'label' => 'Признак вентиляции', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_YearRepair', 'label' => 'Год последней реконструкции (капитального ремонта)', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_YearBuilt', 'label' => 'Дата постройки', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_YearProjDoc', 'label' => 'Дата разработки проектной документации', 'rules' => '', 'type' => 'date'),
			array('field' => 'BuildingTechnology_id', 'label' => 'Идентификатор типа по классу технологий', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'BuildingHoldConstrType_id', 'label' => 'Идентификатор несущей конструкции', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_NumProj', 'label' => 'Номер проекта', 'rules' => '', 'type' => 'string'),
			array('field' => 'BuildingOverlapType_id', 'label' => 'Идентификатор типа перекрытий', 'rules' => '', 'type' => 'id'),
			array('field' => 'BuildingCurrentState_id', 'label' => 'Идентификатор текущего состояния здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'BuildingType_id', 'label' => 'Идентификатор типа проекта здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_Floors', 'label' => 'Этажность', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsDomesticGas', 'label' => 'Признак наличия бытового газоснабжения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'DHotWater_id', 'label' => 'Идентификатор горячего водоснабжения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DCanalization_id', 'label' => 'Идентификатор канализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsAirCond', 'label' => 'Признак наличия кондиционирования', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsMedGas', 'label' => 'Признак наличия лечебного газоснабжения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'DHeating_id', 'label' => 'Идентификатор отопления', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsColdWater', 'label' => 'Признак наличия холодного водоснабжения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_HostLift', 'label' => 'Число медицинских лифтов', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_PassLift', 'label' => 'Число пассажирских лифтов', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsElectric', 'label' => 'Признак наличия электроснабжения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'FuelType_id', 'label' => 'Идентификатор вида топлива отопления', 'rules' => '', 'type' => 'id'),
			array('field' => 'DLink_id', 'label' => 'Идентификатор канала связи', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsFreeEnergy', 'label' => 'Наличие независимых источников энергоснабжения', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_ValDT', 'label' => 'Дата оценки стоимости', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_WearPersent', 'label' => 'Процент износа', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_ResidualCost', 'label' => 'Остаточная стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_PurchaseCost', 'label' => 'Первоначальная стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_FactVal', 'label' => 'Фактическая стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_IsAutoFFSig', 'label' => 'Наличие автоматической пожарной сигнализации в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsFFOutSignal', 'label' => 'Наличие вывода сигнала о срабатывании систем противопожарной защиты в подразделении пожарной охраны в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsCallButton', 'label' => 'Наличие кнопки (брелока) экстренного вызова милиции в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_CountDist', 'label' => 'Количество нарушений требований пожарной безопасности', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsEmergExit', 'label' => 'Наличие эвакуационных путей и выходов в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_StretProtect', 'label' => 'Признак обеспеченности персонала здания учреждения носилками для эвакуации маломобильных пациентов', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_RespProtect', 'label' => 'Признак обеспеченности персонала здания учреждения средствами индивидуальной защиты органов дыхания', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsSecurAlarm', 'label' => 'Наличие охранной сигнализации в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsFFWater', 'label' => 'Наличие противопожарного водоснабжения в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsConnectFSecure', 'label' => 'Наличие прямой телефонной связи с подразделением пожарной охраны здания', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsWarningSys', 'label' => 'Наличие системы оповещения и управления эвакуацией людей при пожаре в здании', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_FSDis', 'label' => 'Удаление от ближайшего пожарного подразделения.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_IsBuildEmerg', 'label' => 'Признак аварийного состояния', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedCap', 'label' => 'Признак требования капитального ремонта', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedRec', 'label' => ' Признак требования реконструкции', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedDem', 'label' => 'Признак требования сноса', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateLpuBuildingPass' => array(
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_AmbPlace', 'label' => 'Амбулаторные места', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_BuildingIdent', 'label' => 'Идентификатор здания (Уникальный номер здания по учету учреждения)', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_PowerProjBed', 'label' => 'Мощность по проекту (число коек)', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_PowerProjViz', 'label' => 'Мощность по проекту (число посещений)', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsBalance', 'label' => 'Признак на балансе', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_Name', 'label' => 'Наименование здания', 'rules' => '', 'type' => 'string'),
			array('field' => 'BuildingAppointmentType_id', 'label' => 'Идентификатор назначения здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_BuildVol', 'label' => 'Объем здания', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_TotalArea', 'label' => 'Общая площадь', 'rules' => '', 'type' => 'float'),
			array('field' => 'MOArea_id', 'label' => 'Идентификатор площадки, занимаемой организацией', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_OfficeArea', 'label' => 'Площадь кабинетов', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_BedArea', 'label' => 'Площадь коечных отделений', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_EffBuildVol', 'label' => 'Полезная площадь', 'rules' => '', 'type' => 'float'),
			array('field' => 'PropertyType_id', 'label' => 'Форма владения', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_StatPlace', 'label' => 'Стационарные места', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_MedWorkCabinet', 'label' => 'Число кабинетов врачебного приема', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingType_id', 'label' => 'Вид здания по применению', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsVentil', 'label' => 'Признак вентиляции', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_YearRepair', 'label' => 'Год последней реконструкции (капитального ремонта)', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_YearBuilt', 'label' => 'Дата постройки', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_YearProjDoc', 'label' => 'Дата разработки проектной документации', 'rules' => '', 'type' => 'date'),
			array('field' => 'BuildingTechnology_id', 'label' => 'Идентификатор типа по классу технологий', 'rules' => '', 'type' => 'id'),
			array('field' => 'BuildingHoldConstrType_id', 'label' => 'Идентификатор несущей конструкции', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_NumProj', 'label' => 'Номер проекта', 'rules' => '', 'type' => 'string'),
			array('field' => 'BuildingOverlapType_id', 'label' => 'Идентификатор типа перекрытий', 'rules' => '', 'type' => 'id'),
			array('field' => 'BuildingCurrentState_id', 'label' => 'Идентификатор текущего состояния здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'BuildingType_id', 'label' => 'Идентификатор типа проекта здания', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_Floors', 'label' => 'Этажность', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsDomesticGas', 'label' => 'Признак наличия бытового газоснабжения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'DHotWater_id', 'label' => 'Идентификатор горячего водоснабжения', 'rules' => '', 'type' => 'id'),
			array('field' => 'DCanalization_id', 'label' => 'Идентификатор канализации', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsAirCond', 'label' => 'Признак наличия кондиционирования', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsMedGas', 'label' => 'Признак наличия лечебного газоснабжения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'DHeating_id', 'label' => 'Идентификатор отопления', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsColdWater', 'label' => 'Признак наличия холодного водоснабжения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_HostLift', 'label' => 'Число медицинских лифтов', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_PassLift', 'label' => 'Число пассажирских лифтов', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsElectric', 'label' => 'Признак наличия электроснабжения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'FuelType_id', 'label' => 'Идентификатор вида топлива отопления', 'rules' => '', 'type' => 'id'),
			array('field' => 'DLink_id', 'label' => 'Идентификатор канала связи', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_IsFreeEnergy', 'label' => 'Наличие независимых источников энергоснабжения', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_ValDT', 'label' => 'Дата оценки стоимости', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuBuildingPass_WearPersent', 'label' => 'Процент износа', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_ResidualCost', 'label' => 'Остаточная стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_PurchaseCost', 'label' => 'Первоначальная стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_FactVal', 'label' => 'Фактическая стоимость.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_IsAutoFFSig', 'label' => 'Наличие автоматической пожарной сигнализации в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsFFOutSignal', 'label' => 'Наличие вывода сигнала о срабатывании систем противопожарной защиты в подразделении пожарной охраны в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsCallButton', 'label' => 'Наличие кнопки (брелока) экстренного вызова милиции в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_CountDist', 'label' => 'Количество нарушений требований пожарной безопасности', 'rules' => '', 'type' => 'int'),
			array('field' => 'LpuBuildingPass_IsEmergExit', 'label' => 'Наличие эвакуационных путей и выходов в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_StretProtect', 'label' => 'Признак обеспеченности персонала здания учреждения носилками для эвакуации маломобильных пациентов', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_RespProtect', 'label' => 'Признак обеспеченности персонала здания учреждения средствами индивидуальной защиты органов дыхания', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsSecurAlarm', 'label' => 'Наличие охранной сигнализации в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsFFWater', 'label' => 'Наличие противопожарного водоснабжения в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsConnectFSecure', 'label' => 'Наличие прямой телефонной связи с подразделением пожарной охраны здания', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsWarningSys', 'label' => 'Наличие системы оповещения и управления эвакуацией людей при пожаре в здании', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_FSDis', 'label' => 'Удаление от ближайшего пожарного подразделения.', 'rules' => '', 'type' => 'float'),
			array('field' => 'LpuBuildingPass_IsBuildEmerg', 'label' => 'Признак аварийного состояния', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedCap', 'label' => 'Признак требования капитального ремонта', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedRec', 'label' => ' Признак требования реконструкции', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuBuildingPass_IsNeedDem', 'label' => 'Признак требования сноса', 'rules' => '', 'type' => 'api_flag')
		),
		'deleteLpuBuildingPass' => array(
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания МО', 'rules' => 'required', 'type' => 'id')
		),
		'createLpuPeriodDLO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuPeriodDLO_begDate', 'label' => 'Дата включения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuPeriodDLO_endDate', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date')
		),
		'updateLpuPeriodDLO' => array(
			array('field' => 'LpuPeriodDLO_id', 'label' => 'Идентификатор периода по ДЛО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuPeriodDLO_begDate', 'label' => 'Дата включения', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuPeriodDLO_endDate', 'label' => 'Дата исключения', 'rules' => '', 'type' => 'date')
		),
		'deleteLpuPeriodDLO' => array(
			array('field' => 'LpuPeriodDLO_id', 'label' => 'Идентификатор периода по ДЛО', 'rules' => 'required', 'type' => 'id')
		),
		'createMOInfoSys' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'DInfSys_id', 'label' => 'Идентификатор типа ИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'MOInfoSys_Name', 'label' => 'Наименование ИС', 'rules' => 'required', 'type' => 'string'),
            array('field' => 'MOInfoSys_Cost', 'label' => 'Стоимость ИС в рублях', 'rules' => '', 'type' => 'float'),
            array('field' => 'MOInfoSys_CostYear', 'label' => 'Стоимость сопровождения ИС в год в рублях', 'rules' => '', 'type' => 'float'),
            array('field' => 'MOInfoSys_IsMainten', 'label' => 'Признак сопровождения', 'rules' => 'required', 'type' => 'api_flag'),
            array('field' => 'MOInfoSys_NameDeveloper', 'label' => 'Наименование разработчика', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'MOInfoSys_IntroDT', 'label' => 'Дата внедрения', 'rules' => 'required', 'type' => 'date')
		),
		'updateMOInfoSys' => array(
			array('field' => 'MOInfoSys_id', 'label' => 'Идентификатор ИС', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DInfSys_id', 'label' => 'Идентификатор типа ИС', 'rules' => '', 'type' => 'id'),
			array('field' => 'MOInfoSys_Name', 'label' => 'Наименование ИС', 'rules' => '', 'type' => 'string'),
            array('field' => 'MOInfoSys_Cost', 'label' => 'Стоимость ИС в рублях', 'rules' => '', 'type' => 'float'),
            array('field' => 'MOInfoSys_CostYear', 'label' => 'Стоимость сопровождения ИС в год в рублях', 'rules' => '', 'type' => 'float'),
            array('field' => 'MOInfoSys_IsMainten', 'label' => 'Признак сопровождения', 'rules' => '', 'type' => 'api_flag'),
            array('field' => 'MOInfoSys_NameDeveloper', 'label' => 'Наименование разработчика', 'rules' => '', 'type' => 'string'),
			array('field' => 'MOInfoSys_IntroDT', 'label' => 'Дата внедрения', 'rules' => '', 'type' => 'date')
		),
		'deleteMOInfoSys' => array(
			array('field' => 'MOInfoSys_id', 'label' => 'Идентификатор ИС', 'rules' => 'required', 'type' => 'id')
		),
		'createSpecializationMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'Mkb10Code_cid', 'label' => 'Идентификатор класса по МКБ-10', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'SpecializationMO_MedProfile', 'label' => 'Медицинский профиль', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'SpecializationMO_IsDepAftercare', 'label' => 'Наличие отделения долечивания', 'rules' => 'required', 'type' => 'api_flag'),
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии МО', 'rules' => 'trim', 'type' => 'id')
		),
		'updateSpecializationMO' => array(
			array('field' => 'SpecializationMO_id', 'label' => 'Идентификатор специализации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Mkb10Code_cid', 'label' => 'Идентификатор класса по МКБ-10', 'rules' => '', 'type' => 'id'),
			array('field' => 'SpecializationMO_MedProfile', 'label' => 'Медицинский профиль', 'rules' => '', 'type' => 'string'),
			array('field' => 'SpecializationMO_IsDepAftercare', 'label' => 'Наличие отделения долечивания', 'rules' => '', 'type' => 'api_flag'),
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии МО', 'rules' => 'trim', 'type' => 'id')
		),
		'deleteSpecializationMO' => array(
			array('field' => 'SpecializationMO_id', 'label' => 'Идентификатор специализации', 'rules' => 'required', 'type' => 'id')
		),
		'createLpuLicence' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuLicence_Ser', 'label' => 'Серия лицензии', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuLicence_RegNum', 'label' => 'Регистрационный номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuLicence_Num', 'label' => 'Номер лицензии', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'Org_id', 'label' => 'Выдавшая организация', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicence_setDate', 'label' => 'Дата выдачи лицензии', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuLicence_begDate', 'label' => 'Начало действия лицензии', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuLicence_endDate', 'label' => 'Окончание действия лицензии', 'rules' => '', 'type' => 'date'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор нас. пункта', 'rules' => '', 'type' => 'id')
		),
		'updateLpuLicence' => array(
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicence_Ser', 'label' => 'Серия лицензии', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuLicence_RegNum', 'label' => 'Регистрационный номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'LpuLicence_Num', 'label' => 'Номер лицензии', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_id', 'label' => 'Выдавшая организация', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuLicence_setDate', 'label' => 'Дата выдачи лицензии', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LpuLicence_begDate', 'label' => 'Начало действия лицензии', 'rules' => '', 'type' => 'date'),
			array('field' => 'LpuLicence_endDate', 'label' => 'Окончание действия лицензии', 'rules' => '', 'type' => 'date'),
			array('field' => 'KLCountry_id', 'label' => 'Идентификатор страны', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLRgn_id', 'label' => 'Идентификатор региона', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLSubRgn_id', 'label' => 'Идентификатор района', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLCity_id', 'label' => 'Идентификатор города', 'rules' => '', 'type' => 'id'),
			array('field' => 'KLTown_id', 'label' => 'Идентификатор нас. пункта', 'rules' => '', 'type' => 'id')
		),
		'getLpuLicence' => array(
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии', 'rules' => 'required', 'type' => 'id')
		),
		'deleteLpuLicence' => array(
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuLicenceByLpu' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true)
		),
		'getLpuLicenceByLpuLicenceNumSetOrgSetDate' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'LpuLicence_Num', 'label' => 'Номер лицензии', 'rules' => '', 'type' => 'string'),
			array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => 'trim', 'type' => 'id'),
			array('field' => 'LpuLicence_setDate', 'label' => 'Дата выдачи лицензии', 'rules' => '', 'type' => 'date')
		),
		'createLpuLicenceOperationLink' => array(
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicenceOperationLink_Date', 'label' => 'Дата операции', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'LicsOperation_id', 'label' => 'Наименование операции', 'rules' => 'required', 'type' => 'id')
		),
		'updateLpuLicenceOperationLink' => array(
			array('field' => 'LpuLicenceOperationLink_id', 'label' => 'Идентификатор операции по лицензии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicenceOperationLink_Date', 'label' => 'Дата операции', 'rules' => '', 'type' => 'date'),
			array('field' => 'LicsOperation_id', 'label' => 'Наименование операции', 'rules' => '', 'type' => 'id')
		),
		'deleteLpuLicenceOperationLink' => array(
			array('field' => 'LpuLicenceOperationLink_id', 'label' => 'Идентификатор операции по лицензии', 'rules' => 'required', 'type' => 'id')
		),
		'createLpuLicenceProfile' => array(
			array('field' => 'LpuLicence_id', 'label' => 'Идентификатор лицензии МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicenceProfileType_id', 'label' => 'Идентификатор вида лицензий по профилю', 'rules' => 'required', 'type' => 'id')
		),
		'updateLpuLicenceProfile' => array(
			array('field' => 'LpuLicenceProfile_id', 'label' => 'Идентификатор профиля лицензии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuLicenceProfileType_id', 'label' => 'Идентификатор вида лицензий по профилю', 'rules' => '', 'type' => 'id')
		),
		'deleteLpuLicenceProfile' => array(
			array('field' => 'LpuLicenceProfile_id', 'label' => 'Идентификатор профиля лицензии', 'rules' => 'required', 'type' => 'id'),
		),
		'createMedTechnology' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'MedTechnology_Name', 'label' => 'Наименование медицинской технологии', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'TechnologyClass_id', 'label' => 'Идентификатор класса технологии', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания', 'rules' => 'required', 'type' => 'id')
		),
		'updateMedTechnology' => array(
			array('field' => 'MedTechnology_id', 'label' => 'Идентификатор специализации', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedTechnology_Name', 'label' => 'Наименование медицинской технологии', 'rules' => '', 'type' => 'string'),
			array('field' => 'TechnologyClass_id', 'label' => 'Идентификатор класса технологии', 'rules' => '', 'type' => 'id'),
			array('field' => 'LpuBuildingPass_id', 'label' => 'Идентификатор здания', 'rules' => '', 'type' => 'id')
		),
		'deleteMedTechnology' => array(
			array('field' => 'MedTechnology_id', 'label' => 'Идентификатор специализации', 'rules' => 'required', 'type' => 'id')
		),
		'createMedUsluga' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'MedUsluga_LicenseNum', 'label' => 'Номер лицензии', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'DUslugi_id', 'label' => 'Идентификатор наименования медуслуги', 'rules' => 'required', 'type' => 'id')
		),
		'updateMedUsluga' => array(
			array('field' => 'MedUsluga_id', 'label' => 'Идентификатор медицинской услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MedUsluga_LicenseNum', 'label' => 'Номер лицензии', 'rules' => '', 'type' => 'string'),
			array('field' => 'DUslugi_id', 'label' => 'Идентификатор наименования медуслуги', 'rules' => '', 'type' => 'id')
		),
		'deleteMedUsluga' => array(
			array('field' => 'MedUsluga_id', 'label' => 'Идентификатор медицинской услуги', 'rules' => 'required', 'type' => 'id')
		),
		'createUslugaComplexLpu' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplexLpu_begDate', 'label' => 'Дата начала оказания услуги', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'UslugaComplexLpu_endDate', 'label' => 'Дата окончания оказания услуги', 'rules' => '', 'type' => 'date')
		),
		'updateUslugaComplexLpu' => array(
			array('field' => 'UslugaComplexLpu_id', 'label' => 'Идентификатор направления оказания медицинской помощи', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'UslugaComplex_id', 'label' => 'Идентификатор услуги', 'rules' => '', 'type' => 'id'),
			array('field' => 'UslugaComplexLpu_begDate', 'label' => 'Дата начала оказания услуги', 'rules' => '', 'type' => 'date'),
			array('field' => 'UslugaComplexLpu_endDate', 'label' => 'Дата окончания оказания услуги', 'rules' => '', 'type' => 'date')
		),
		'deleteUslugaComplexLpu' => array(
			array('field' => 'UslugaComplexLpu_id', 'label' => 'Идентификатор направления оказания медицинской помощи', 'rules' => 'required', 'type' => 'id')
		),
		'createPlfObjects' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'PlfObjects_id', 'label' => 'Идентификатор наименования объекта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PlfObjectCount_Count', 'label' => 'Количество мест/объектов', 'rules' => 'required', 'type' => 'int')
		),
		'updatePlfObjects' => array(
			array('field' => 'PlfObjectCount_id', 'label' => 'Идентификатор объекта/места использования природного лечебного фактора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PlfObjects_id', 'label' => 'Идентификатор наименования объекта', 'rules' => '', 'type' => 'id'),
			array('field' => 'PlfObjectCount_Count', 'label' => 'Количество мест/объектов', 'rules' => '', 'type' => 'int')
		),
		'deletePlfObjects' => array(
			array('field' => 'PlfObjectCount_id', 'label' => 'Идентификатор объекта/места использования природного лечебного фактора', 'rules' => 'required', 'type' => 'id')
		),
		'createFunctionTime' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'InstitutionFunction_id', 'label' => 'Идентификатор периода функционирования учреждения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'FunctionTime_begDate', 'label' => 'Дата начала периода функционирования учреждения', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'FunctionTime_endDate', 'label' => 'Дата окончания периода функционирования учреждения', 'rules' => '', 'type' => 'date')
		),
		'updateFunctionTime' => array(
			array('field' => 'FunctionTime_id', 'label' => 'Идентификатор периода функционирования учреждения', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'InstitutionFunction_id', 'label' => 'Идентификатор периода функционирования учреждения', 'rules' => '', 'type' => 'id'),
			array('field' => 'FunctionTime_begDate', 'label' => 'Дата начала периода функционирования учреждения', 'rules' => '', 'type' => 'date'),
			array('field' => 'FunctionTime_endDate', 'label' => 'Дата окончания периода функционирования учреждения', 'rules' => '', 'type' => 'date')
		),
		'deleteFunctionTime' => array(
			array('field' => 'FunctionTime_id', 'label' => 'Идентификатор периода функционирования учреждения', 'rules' => 'required', 'type' => 'id')
		),
		'createPitan' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'PitanForm_id', 'label' => 'Идентификатор формы питания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'VidPitan_id', 'label' => 'Идентификатор вида питания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PitanCnt_id', 'label' => 'Идентификатор кратности питания', 'rules' => 'required', 'type' => 'id')
		),
		'updatePitan' => array(
			array('field' => 'PitanFormTypeLink_id', 'label' => 'Идентификатор таблицы связей питания', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PitanForm_id', 'label' => 'Идентификатор формы питания', 'rules' => '', 'type' => 'id'),
			array('field' => 'VidPitan_id', 'label' => 'Идентификатор вида питания', 'rules' => '', 'type' => 'id'),
			array('field' => 'PitanCnt_id', 'label' => 'Идентификатор кратности питания', 'rules' => '', 'type' => 'id')
		),
		'deletePitan' => array(
			array('field' => 'PitanFormTypeLink_id', 'label' => 'Идентификатор таблицы связей питания', 'rules' => 'required', 'type' => 'id')
		),
		'createPlf' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'DocTypeUsePlf_id', 'label' => 'Документ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PlfType_id', 'label' => 'Идентификатор типа природного лечебного фактора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'Plf_id', 'label' => 'Идентификатор наименования природного лечебного фактора', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'PlfDocTypeLink_Num', 'label' => 'Номер', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'PlfDocTypeLink_BegDT', 'label' => 'Дата начала действия фактора', 'rules' => '', 'type' => 'date'),
			array('field' => 'PlfDocTypeLink_EndDT', 'label' => 'Дата окончания действия фактора', 'rules' => '', 'type' => 'date'),
			array('field' => 'PlfDocTypeLink_GetDT', 'label' => 'Дата выдачи документа', 'rules' => 'required', 'type' => 'date')
		),
		'updatePlf' => array(
			array('field' => 'PlfDocTypeLink_id', 'label' => 'Идентификатор таблицы связей природных леебных факторов и МО ', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DocTypeUsePlf_id', 'label' => 'Документ', 'rules' => '', 'type' => 'id'),
			array('field' => 'PlfType_id', 'label' => 'Идентификатор типа природного лечебного фактора', 'rules' => '', 'type' => 'id'),
			array('field' => 'Plf_id', 'label' => 'Идентификатор наименования природного лечебного фактора', 'rules' => '', 'type' => 'id'),
			array('field' => 'PlfDocTypeLink_Num', 'label' => 'Номер', 'rules' => '', 'type' => 'string'),
			array('field' => 'PlfDocTypeLink_BegDT', 'label' => 'Дата начала действия фактора', 'rules' => '', 'type' => 'date'),
			array('field' => 'PlfDocTypeLink_EndDT', 'label' => 'Дата окончания действия фактора', 'rules' => '', 'type' => 'date'),
			array('field' => 'PlfDocTypeLink_GetDT', 'label' => 'Дата выдачи документа', 'rules' => '', 'type' => 'date')
		),
		'deletePlf' => array(
			array('field' => 'PlfDocTypeLink_id', 'label' => 'Идентификатор таблицы связей природных леебных факторов и МО ', 'rules' => 'required', 'type' => 'id')
		),
		'createMOArrival' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'MOArrival_EndDT', 'label' => 'Дата окончания заезда', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'MOArrival_CountPerson', 'label' => 'Количество человек', 'rules' => '', 'type' => 'int'),
			array('field' => 'MOArrival_TreatDis', 'label' => 'Длительность заезда', 'rules' => 'required', 'type' => 'int')
		),
		'updateMOArrival' => array(
			array('field' => 'MOArrival_id', 'label' => 'Идентификатор заезда', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'MOArrival_EndDT', 'label' => 'Дата окончания заезда', 'rules' => '', 'type' => 'date'),
			array('field' => 'MOArrival_CountPerson', 'label' => 'Количество человек', 'rules' => '', 'type' => 'int'),
			array('field' => 'MOArrival_TreatDis', 'label' => 'Длительность заезда', 'rules' => '', 'type' => 'int')
		),
		'deleteMOArrival' => array(
			array('field' => 'MOArrival_id', 'label' => 'Идентификатор заезда', 'rules' => 'required', 'type' => 'id')
		),
		'createDisSanProtection' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'DisSanProtection_Date', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'DisSanProtection_Doc', 'label' => 'Документ', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'DisSanProtection_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'DisSanProtection_IsProtection', 'label' => 'Признак наличия округа', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateDisSanProtection' => array(
			array('field' => 'DisSanProtection_id', 'label' => 'Идентификатор округа горно-санитарной охраны', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'DisSanProtection_Date', 'label' => 'Дата документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'DisSanProtection_Doc', 'label' => 'Документ', 'rules' => '', 'type' => 'string'),
			array('field' => 'DisSanProtection_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'DisSanProtection_IsProtection', 'label' => 'Признак наличия округа', 'rules' => '', 'type' => 'api_flag')
		),
		'deleteDisSanProtection' => array(
			array('field' => 'DisSanProtection_id', 'label' => 'Идентификатор округа горно-санитарной охраны', 'rules' => 'required', 'type' => 'id')
		),
		'createKurortStatusDoc' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'KurortStatus_id', 'label' => 'Идентификатор наименования статуса курорта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KurortStatusDoc_Date', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'KurortStatusDoc_Doc', 'label' => 'Документ', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'KurortStatusDoc_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'KurortStatusDoc_IsStatus', 'label' => 'Наличие статуса курорта', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateKurortStatusDoc' => array(
			array('field' => 'KurortStatusDoc_id', 'label' => 'Идентификатор статуса курорта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KurortStatus_id', 'label' => 'Идентификатор наименования статуса курорта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KurortStatusDoc_Date', 'label' => 'Дата документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'KurortStatusDoc_Doc', 'label' => 'Документ', 'rules' => '', 'type' => 'string'),
			array('field' => 'KurortStatusDoc_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'KurortStatusDoc_IsStatus', 'label' => 'Наличие статуса курорта', 'rules' => '', 'type' => 'api_flag')
		),
		'deleteKurortStatusDoc' => array(
			array('field' => 'KurortStatusDoc_id', 'label' => 'Идентификатор статуса курорта', 'rules' => 'required', 'type' => 'id')
		),
		'createKurortTypeLink' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id', 'equalsession' => true),
			array('field' => 'KurortType_id', 'label' => 'Идентификатор наименования типа курорта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KurortTypeLink_Date', 'label' => 'Дата документа', 'rules' => 'required', 'type' => 'date'),
			array('field' => 'KurortTypeLink_Doc', 'label' => 'Документ', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'KurortTypeLink_Num', 'label' => 'Номер документа', 'rules' => 'required', 'type' => 'string'),
			array('field' => 'KurortTypeLink_IsKurortTypeLink', 'label' => 'Наличие типа курорта', 'rules' => 'required', 'type' => 'api_flag')
		),
		'updateKurortTypeLink' => array(
			array('field' => 'KurortTypeLink_id', 'label' => 'Идентификатор типа курорта', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'KurortType_id', 'label' => 'Идентификатор наименования типа курорта', 'rules' => '', 'type' => 'id'),
			array('field' => 'KurortTypeLink_Date', 'label' => 'Дата документа', 'rules' => '', 'type' => 'date'),
			array('field' => 'KurortTypeLink_Doc', 'label' => 'Документ', 'rules' => '', 'type' => 'string'),
			array('field' => 'KurortTypeLink_Num', 'label' => 'Номер документа', 'rules' => '', 'type' => 'string'),
			array('field' => 'KurortTypeLink_IsKurortTypeLink', 'label' => 'Наличие типа курорта', 'rules' => '', 'type' => 'api_flag')
		),
		'deleteKurortTypeLink' => array(
			array('field' => 'KurortTypeLink_id', 'label' => 'Идентификатор типа курорта', 'rules' => 'required', 'type' => 'id')
		),
		'createLpuHousehold' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuHousehold_Name', 'label' => 'Наименование', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_ContactPerson', 'label' => 'Контактное лицо', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_ContactPhone', 'label' => 'Контактный телефон', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_CadNumber', 'label' => 'Кадастровый номер', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuHousehold_CoordLat', 'label' => 'Координаты (широта)', 'rules' => 'trim|required', 'type' => 'float'),
			array('field' => 'LpuHousehold_CoordLon', 'label' => 'Координаты (долгота)', 'rules' => 'trim|required', 'type' => 'float'),
			array('field' => 'PAddress_id', 'label' => 'Адрес хозяйства фактический', 'rules' => 'required', 'type' => 'id'),
		),
		'updateLpuHousehold' => array(
			array('field' => 'LpuHousehold_id', 'label' => 'Идентификатор домового хозяйства', 'rules' => 'required', 'type' => 'id'),
			array('field' => 'LpuHousehold_Name', 'label' => 'Наименование', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_ContactPerson', 'label' => 'Контактное лицо', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_ContactPhone', 'label' => 'Контактный телефон', 'rules' => 'trim|required', 'type' => 'string'),
			array('field' => 'LpuHousehold_CadNumber', 'label' => 'Кадастровый номер', 'rules' => 'trim', 'type' => 'string'),
			array('field' => 'LpuHousehold_CoordLat', 'label' => 'Координаты (широта)', 'rules' => 'trim|required', 'type' => 'float'),
			array('field' => 'LpuHousehold_CoordLon', 'label' => 'Координаты (долгота)', 'rules' => 'trim|required', 'type' => 'float'),
			array('field' => 'PAddress_id', 'label' => 'Адрес хозяйства фактический', 'rules' => 'required', 'type' => 'id'),
		),
		'deleteLpuHousehold' => array(
			array('field' => 'LpuHousehold_id', 'label' => 'Идентификатор', 'rules' => 'required', 'type' => 'id')
		),
		'getLpuHouseHoldByMO' => array(
			array('field' => 'Lpu_id', 'label' => 'Идентификатор МО', 'rules' => 'required', 'type' => 'id')
		),
	);

	/**
	 *	Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}
	
	/**
	 *	Получение для печати основной информации из паспорта МО
	 */
	function getLpuPassportMainDataForPrint($data)
	{
		$query = "
			select
				rtrim(lp.Lpu_Nick) as Lpu_Nick,
				rtrim(lp.Lpu_Name) as Lpu_Name,
				rtrim(lp.Lpu_Email) as Lpu_Email,
				rtrim(isnull(lec.LpuLicence_Ser, '')) + ' ' + rtrim(isnull(lec.LpuLicence_Num, '')) + ' ' + isnull(convert(varchar,cast(lec.LpuLicence_endDate as datetime),104), '') as Lpu_Licence,
				rtrim(isnull(uaddr.Address_Address, '')) as Lpu_UAddress,
				rtrim(isnull(og.Org_Www, '')) as Lpu_Www,
				rtrim(isnull(lp.Lpu_ErInfo, '')) as Lpu_ErInfo
			from
				v_Lpu as lp (nolock)
				outer apply (
					select top 1
						LpuLicence_Ser,
						LpuLicence_Num,
						LpuLicence_endDate
					from
						LpuLicence (nolock)
					where
						Lpu_id = lp.Lpu_id and
						VidDeat_id = 5 and
						(LpuLicence_endDate is null or LpuLicence_endDate > dbo.tzGetDate())
				) as lec
				inner join Org as og (nolock) on lp.Org_id = og.Org_id
				left join Address as uaddr (nolock) on uaddr.Address_id = lp.UAddress_id
			where
				Lpu_id = ?
		";
		$res = $this->db->query($query, array($data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 *	Получение для печати основной информации о группе отделений
	 */
	function getLpuUnitPassportMainDataForPrint($data)
	{
		$query = "
			select
				rtrim(lp.LpuUnit_id) as LpuUnit_id,
				rtrim(lp.LpuUnit_Name) as LpuUnit_Name,
				rtrim(addr.Address_Address) as LpuUnit_Address,
				rtrim(lp.LpuUnit_Phone) as LpuUnit_Phone,
				rtrim(lp.LpuUnit_Email) as LpuUnit_Email,
				rtrim(lp.LpuUnit_IP) as LpuUnit_IP
			from
				v_LpuUnit as lp (nolock)
				left join Address as addr (nolock) on addr.Address_id = lp.Address_id
			where
				Lpu_id = ?
		";
		$res = $this->db->query($query, array($data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 *	Получение для печати информации о руководителе организации
	 */
	function getLpuPassportHeadDataForPrint($data)
	{
		$query = "
			select
				oh.OrgHeadPost_id,
				rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHead_FIO,
				rtrim(ohp.OrgHeadPost_Name) as OrgHeadPost_Name,
				rtrim(isnull(oh.OrgHead_Email, '')) as OrgHead_Email,
				rtrim(isnull(oh.OrgHead_Phone, '')) as OrgHead_Phone,
				rtrim(isnull(oh.OrgHead_Mobile, '')) as OrgHead_Mobile,
				rtrim(isnull(oh.OrgHead_CommissNum, '')) as OrgHead_CommissNum,
				rtrim(isNull(convert(varchar,cast(oh.OrgHead_CommissDate as datetime),104),'')) as OrgHead_CommissDate,
				rtrim(isnull(oh.OrgHead_Address, '')) as OrgHead_Address
			from
				v_OrgHead as oh (nolock)
				inner join v_PersonState as ps (nolock) on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp (nolock) on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where
				oh.Lpu_id = ? and LpuUnit_id is null
		";
		$res = $this->db->query($query, array($data['Lpu_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 *	Получение для печати информации о руководителе группы отделений
	 */
	function getLpuUnitPassportHeadDataForPrint($data)
	{
		$query = "
			select
				oh.OrgHeadPost_id,
				rtrim(ps.Person_SurName) + ' ' + rtrim(ps.Person_FirName) + ' ' + rtrim(isnull(ps.Person_SecName, '')) as OrgHead_FIO,
				rtrim(ohp.OrgHeadPost_Name) as OrgHeadPost_Name,
				rtrim(isnull(oh.OrgHead_Email, '')) as OrgHead_Email,
				rtrim(isnull(oh.OrgHead_Phone, '')) as OrgHead_Phone,
				rtrim(isnull(oh.OrgHead_Mobile, '')) as OrgHead_Mobile,
				rtrim(isnull(oh.OrgHead_CommissNum, '')) as OrgHead_CommissNum,
				rtrim(isNull(convert(varchar,cast(oh.OrgHead_CommissDate as datetime),104),'')) as OrgHead_CommissDate,
				rtrim(isnull(oh.OrgHead_Address, '')) as OrgHead_Address
			from
				v_OrgHead as oh (nolock)
				inner join v_PersonState as ps (nolock) on oh.Person_id = ps.Person_id
				inner join OrgHeadPost as ohp (nolock) on ohp.OrgHeadPost_id = oh.OrgHeadPost_id
			where
				LpuUnit_id = ? and LpuUnit_id is not null
		";
		$res = $this->db->query($query, array($data['LpuUnit_id']));
		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 *	Получение данных для паспорта МО
	 */
	function getLpuPassport($data) {//
		$addFields = '';
		$addJoin = '';

		if ( in_array(getRegionNick(), array('perm', 'msk')) ) {
			$addFields = '
				,Lpu.TOUZType_id
				,Lpu.Org_tid
				,case
					when Lpu.Lpu_InterCode is not null then Lpu.Lpu_InterCode
					else ISNULL(l.Lpu_InterCode, 0) + 1
				end as Lpu_InterCode
			';

			$addJoin = '
				outer apply (
					select max(Lpu_InterCode) as Lpu_InterCode
					from v_Lpu with (nolock)
				) l
			';
		} else if ( in_array(getRegionNick(), array('astra', 'kareliya', 'krym', 'penza', 'pskov', 'buryatiya', 'vologda','khak', 'yakutiya')) ) {
			$addFields = '
				,case when PasportMO.PasportMO_IsAssignNasel = 2 then 1 else 0 end as PasportMO_IsAssignNasel
			';
		} else if ( getRegionNick() == 'kz' ) {
			$addFields = "
				,LpuNomen.LpuNomen_id
				,LpuNomen.LpuNomen_Name
				,Okogu.Okogu_Name
				,LpuInfo.LpuInfo_BIN
				,LpuInfo.PropertyClass_id
				,LpuInfo.LpuInfo_AkkrNum
				,rtrim(IsNull(convert(varchar,cast(LpuInfo.LpuInfo_AkkrDate as datetime),104),'')) as LpuInfo_AkkrDate
				,LpuInfo.SubjectionType_id
				,LpuInfo.LpuInfo_Area
				,LpuInfo.LpuInfo_Distance
				,LpuSUR.ID as LpuSUR_id
			";

			$addJoin = '
				left join passport101.v_LpuInfo as LpuInfo (nolock) on LpuInfo.Lpu_id = Lpu.Lpu_id
				left join passport101.v_LpuNomen as LpuNomen (nolock) on LpuNomen.LpuNomen_id = LpuInfo.LpuNomen_id
				left join v_Okogu as Okogu (nolock) on Okogu.Okogu_id = Org.Okogu_id
				left join r101.GetMO LpuSUR with(nolock) on LpuSUR.Lpu_id = Lpu.Lpu_id
			';
		} else if ( getRegionNick() == 'by' ) {
			$addFields = '
				,LpuSpec.LpuSpecType_id
			';

			$addJoin = '
				outer apply (
					select top 1 LpuSpecType_id
					from passport201.v_LpuSpec with (nolock)
					where Lpu_id = Lpu.Lpu_id
					order by LpuSpec_id desc
				) LpuSpec
			';
		}

		$query = "
			SELECT TOP 1
				Lpu.Lpu_id,
				Lpu.Org_id,
				Lpu.Server_id,
				rtrim(Lpu.Lpu_Nick) as Lpu_Nick,
				rtrim(Lpu.Lpu_Name) as Lpu_Name,
				RTrim(IsNull(convert(varchar,cast(Lpu.Lpu_begDate as datetime),104),'')) as Lpu_begDate,
				RTrim(IsNull(convert(varchar,cast(Lpu.Lpu_endDate as datetime),104),'')) as Lpu_endDate,
				Lpu.Lpu_pid,
				Lpu.Lpu_nid,
				Lpu.Lpu_Ouz,
				Lpu.Lpu_f003mcod,
				Lpu.Lpu_RegNomN2,
				Lpu.Lpu_Email,
				Lpu.Lpu_IsEmailFixed,
				Lpu.LpuSubjectionLevel_id,
				Lpu.LpuLevel_id,
				Lpu.LpuLevel_cid,
				Lpu.Lpu_VizitFact,
				Lpu.Lpu_KoikiFact,
				Lpu.Lpu_AmbulanceCount,
				Lpu.Lpu_FondOsn,
				Lpu.Lpu_FondEquip,
				Org.Org_Www as Lpu_Www,
				Org.Org_Phone as Lpu_Phone,
				Org.Org_Worktime as Lpu_Worktime,
				Lpu.Org_StickNick as Lpu_StickNick,
				Lpu.Lpu_StickAddress,
				Lpu.Lpu_Okato,
				Lpu.Oktmo_id,
				Lpu.Lpu_KPN as Org_KPN,
				Oktmo.Oktmo_Code as Oktmo_Name,
				Lpu.LpuPmuType_id,
				Lpu.LpuPmuClass_id,
				Lpu.LpuType_id,
				lt.LpuType_Name,
				Lpu.MesAgeLpuType_id,
				isnull(Lpu.Lpu_IsMse, 1) as Lpu_IsMse,
				
				Lpu.LpuOwnership_id,
				Lpu.MOAreaFeature_id,
				Lpu.lpu_founder,
				Lpu.LpuBuildingPass_mid,

				Org.Okfs_id,
				Org.Okopf_id,
				Org.Org_OKPO,
				Org.Org_INN,
				Org.Org_KPP,
				Org.Org_OGRN,
				Org.Org_OKDP,
				Org.Okogu_id,
				Org.Okved_id,
				Org.Org_ONMSZCode,
				Org.Org_pid,
				Org.Org_RegName,
				OrgInfo.OrgInfo_Info as Lpu_MedCare,
				Lpu.Lpu_ErInfo,
				Lpu.Lpu_IsAllowInternetModeration,
				Lpu.Lpu_DistrictRate,
				Lpu.Org_lid,
				Lpu.Lpu_IsLab,
				Lpu.Lpu_IsTest,
				RTrim(IsNull(convert(varchar,cast(Lpu.Lpu_RegDate as datetime),104),'')) as Lpu_RegDate,
				Lpu.Lpu_RegNum,
				Lpu.Lpu_DocReg,
				Lpu.Lpu_PensRegNum,
				Lpu.Lpu_FSSRegNum,
				UAD.Address_id as UAddress_id,
				UAD.Address_Zip as UAddress_Zip,
				UAD.KLCountry_id as UKLCountry_id,
				UAD.KLRGN_id as UKLRGN_id,
				UAD.KLSubRGN_id as UKLSubRGN_id,
				UAD.KLCity_id as UKLCity_id,
				UAD.KLTown_id as UKLTown_id,
				UAD.KLStreet_id as UKLStreet_id,
				UAD.Address_House as UAddress_House,
				UAD.Address_Corpus as UAddress_Corpus,
				UAD.Address_Flat as UAddress_Flat,
				UAD.Address_Address as UAddress_Address,
				UAD.Address_Address as UAddress_AddressText,
				PAD.Address_id as PAddress_id,
				PAD.Address_Zip as PAddress_Zip,
				PAD.KLCountry_id as PKLCountry_id,
				PAD.KLRGN_id as PKLRGN_id,
				PAD.KLSubRGN_id as PKLSubRGN_id,
				PAD.KLCity_id as PKLCity_id,
				PAD.KLTown_id as PKLTown_id,
				PAD.KLStreet_id as PKLStreet_id,
				PAD.Address_House as PAddress_House,
				PAD.Address_Corpus as PAddress_Corpus,
				PAD.Address_Flat as PAddress_Flat,
				PAD.Address_Address as PAddress_Address,
				PAD.Address_Address as PAddress_AddressText,
                PasportMO.PasportMO_id,
                PasportMO.Lpu_gid,
                PasportMO.InstitutionLevel_id,
                PasportMO.DLocationLpu_id,
                PasportMO.PasportMO_MaxDistansePoint,
                PasportMO.PasportMO_IsFenceTer,
                case when ISNULL(PasportMO.PasportMO_IsNoFRMP,1) = 2 then 'true' else 'false' end as PasportMO_IsNoFRMP,
                PasportMO.PasportMO_IsSecur,
                PasportMO.PasportMO_IsMetalDoors,
                PasportMO.PasportMO_IsVideo,
                PasportMO.PasportMO_IsTerLimited,
                PasportMO.PasportMO_IsAccompanying,
                PasportMO.DepartAffilType_id,
				
				PasportMO.PasportMO_KolServ,
				PasportMO.PasportMO_KolServSel,
				PasportMO.PasportMO_KolServDet,
				PasportMO.PasportMO_KolCmpMes,
				PasportMO.PasportMO_KolCmpPay,
				PasportMO.PasportMO_KolCmpWage,
				PasportMO.PasportMO_Popul,
				PasportMO.PasportMO_CityPopul,
				PasportMO.PasportMO_TownPopul,
				PasportMO.LpuLevel_id as FedLpuLevel_id,
				RTrim(IsNull(convert(varchar,cast(PasportMO.PasportMO_calcDate as datetime),104),'')) as PasportMO_calcDate,
				
                --PasportMO.PasportMO_Station,
                --PasportMO.PasportMO_DisStation,
                --PasportMO.PasportMO_Airport,
                --PasportMO.PasportMO_DisAirport,
                --PasportMO.PasportMO_Railway,
                --PasportMO.PasportMO_Disrailway,
                --PasportMO.PasportMO_Heliport,
                --PasportMO.PasportMO_DisHeliport,
                --PasportMO.PasportMO_MainRoad,
				CASE WHEN MS.MedServiceType_id=19 THEN 1 ELSE 0 END AS isCMP,
				ISNULL(DSHLP.DataStorage_Value,1) as Lpu_HasLocalPacsServer,
				ISNULL(DSPIP.DataStorage_Value,'') as Lpu_LocalPacsServerIP,
				ISNULL(DSPAE.DataStorage_Value,'') as Lpu_LocalPacsServerAetitle,
				ISNULL(DSPP.DataStorage_Value,'') as Lpu_LocalPacsServerPort,
				ISNULL(DSPWP.DataStorage_Value,'') as Lpu_LocalPacsServerWadoPort,
				
				ISNULL(DSCT.DataStorage_Value,3) as OftenCallers_CallTimes,
				ISNULL(DSSD.DataStorage_Value,30) as OftenCallers_SearchDays,
				ISNULL(DSFD.DataStorage_Value,365) as OftenCallers_FreeDays,
				ISNULL(Lpu.Lpu_IsSecret,1) as Lpu_IsSecret,
				LLT.LpuLevelType_id,
				PToken.PassportToken_tid,
				LLT.LevelType_id,
				CSC.CmpStationCategory_id
				" . $addFields . "
			FROM v_Lpu Lpu (nolock)
				outer apply (
					select
						MedServiceType_id
					from
						v_MedService as MedService (nolock)
					where
						MedService.Lpu_id = Lpu.Lpu_id and
						MedServiceType_id = 19
				) as MS
				
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'Lpu_HasLocalPacsServer' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSHLP
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'Lpu_LocalPacsServerIP' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSPIP
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'Lpu_LocalPacsServerAetitle' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSPAE
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'Lpu_LocalPacsServerPort' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSPP
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'Lpu_LocalPacsServerWadoPort' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSPWP

				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'OftenCallers_CallTimes' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSCT
				
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'OftenCallers_SearchDays' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSSD
				
				outer apply (
					select
						DataStorage_Value
					from
						v_DataStorage as DS (nolock)
					where
						DS.DataStorage_Name = 'OftenCallers_FreeDays' AND
						DS.Lpu_id = Lpu.Lpu_id
				) as DSFD
				outer apply (
					select top 1
						PasportMO_id,
						Lpu_id,
						Lpu_gid,
						InstitutionLevel_id,
						DLocationLpu_id,
                        PasportMO_MaxDistansePoint,
                        PasportMO_IsFenceTer,
                        PasportMO_IsAssignNasel,
                        PasportMO_IsNoFRMP,
                        PasportMO_IsSecur,
                        PasportMO_IsMetalDoors,
                        PasportMO_IsVideo,
                        PasportMO_IsTerLimited,
                        PasportMO_IsAccompanying,
                        DepartAffilType_id,
						
						PasportMO_KolServ,
						PasportMO_KolServSel,
						PasportMO_KolServDet,
						PasportMO_KolCmpMes,
						PasportMO_KolCmpPay,
						PasportMO_KolCmpWage,
						PasportMO_Popul,
						PasportMO_CityPopul,
						PasportMO_TownPopul,
						LpuLevel_id,
						PasportMO_calcDate
						
                        --PasportMO_Station,
                        --PasportMO_DisStation,
                        --PasportMO_Airport,
                        --PasportMO_DisAirport,
                        --PasportMO_Railway,
                        --PasportMO_Disrailway,
                        --PasportMO_Heliport,
                        --PasportMO_DisHeliport,
                        --PasportMO_MainRoad,
					from
						fed.v_PasportMO with (nolock)
					where
						Lpu_id = :Lpu_id
				    order by PasportMO_id desc
				) as PasportMO
				left join v_Org as Org (nolock) on Lpu.Org_id = Org.Org_id
				left join v_OrgInfo as OrgInfo (nolock) on OrgInfo.Org_id = Org.Org_id AND OrgInfo.OrgInfoType_id = 1
				left join [Address] PAD (nolock) on PAD.Address_id = Lpu.PAddress_id
				left join [Address] UAD (nolock) on UAD.Address_id = Lpu.UAddress_id
				left join v_LpuType lt (nolock) on lt.LpuType_id = Lpu.LpuType_id
				left join v_Oktmo Oktmo (nolock) on Oktmo.Oktmo_id = Org.Oktmo_id
				left join fed.v_PassportToken PToken (nolock) on PToken.Lpu_id = Lpu.Lpu_id
				outer apply(
					select top 1 CmpStationCategory_id
					from v_LpuCmpStationCategory with(nolock)
					where Lpu_id = Lpu.Lpu_id
				) CSC
				outer apply (
					select top 1
						 LpuLevelType_id
						,LevelType_id
					from v_LpuLevelType with (nolock)
					where Lpu_id = Lpu.Lpu_id
				) LLT
				" . $addJoin . "
			WHERE Lpu.Lpu_id = :Lpu_id
		";

		//echo getDebugSql($query, $data);die;
		$res = $this->db->query($query, array('Lpu_id' => $data['Lpu_id'] ));
		if ( is_object($res) ) {
			$response = $res->result('array');

			if (is_array($response) && !empty($response[0]['Lpu_id']) && empty($response[0]['Error_Msg'])) {
				foreach ($response[0] as $key => &$value) {
					if (in_array($key, array('PasportMO_IsFenceTer', 'PasportMO_IsSecur', 'PasportMO_IsMetalDoors', 'PasportMO_IsVideo', 'PasportMO_IsAccompanying', 'PasportMO_IsTerLimited', ))) {
						$value == 2?$value=true:$value=false;
					}
				}
			}

			return $response;
		}
		else
			return false;
	}
	
	/**
	* Сохраняет данные формы периода ОМС
	*/
	function saveLpuPeriodOMS($data) {

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuPeriodOMS_id' => $data['LpuPeriodOMS_id'],
			'LpuPeriodOMS_begDate' => $data['LpuPeriodOMS_begDate'],
			'LpuPeriodOMS_endDate' => $data['LpuPeriodOMS_endDate'],
			'LpuPeriodOMS_DogNum' => $data['LpuPeriodOMS_DogNum'],
			'LpuPeriodOMS_RegNumC' => $data['LpuPeriodOMS_RegNumC'],
			'LpuPeriodOMS_RegNumN' => $data['LpuPeriodOMS_RegNumN'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		if ( $data['LpuPeriodOMS_endDate'] && $data['LpuPeriodOMS_begDate'] > $data['LpuPeriodOMS_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}		

		$query = "Select
			COUNT (*) as [count]
				from LpuPeriodOMS (nolock)
			where
				[Lpu_id] = :Lpu_id and
				LpuPeriodOMS_pid is null and
				([LpuPeriodOMS_begDate] <= :LpuPeriodOMS_endDate or :LpuPeriodOMS_endDate is null) and
				([LpuPeriodOMS_endDate] >= :LpuPeriodOMS_begDate or [LpuPeriodOMS_endDate] is null) and
				([LpuPeriodOMS_id] != :LpuPeriodOMS_id or :LpuPeriodOMS_id is null)";

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Периоды по ОМС не могут пересекаться.'));
		}		
		$query = "Select
			LpuPeriodOMS_DogNum,
			convert(varchar(10), LpuPeriodOMS_begDate, 104) as LpuPeriodOMS_begDate
				from LpuPeriodOMS (nolock)
			where
			((LpuPeriodOMS_pid = :LpuPeriodOMS_id  and :LpuPeriodOMS_id is not null ) and (([LpuPeriodOMS_begDate] >= :LpuPeriodOMS_endDate and :LpuPeriodOMS_endDate is not null )
				or  [LpuPeriodOMS_begDate] < :LpuPeriodOMS_begDate  ))";

		//echo getDebugSQL($query, $queryParams);
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if (is_array($response) && count($response) > 0) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => "Дата договора {$response[0]['LpuPeriodOMS_DogNum']}, {$response[0]['LpuPeriodOMS_begDate']} превышает дату закрытия периода работы организации в системе ОМС."));
		}	
		/**---*/
		if ($trans_good === true) {
		
			if ( !isset($data['LpuPeriodOMS_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuPeriodOMS_id;
				exec p_LpuPeriodOMS_" . $procedure_action . "
					@Lpu_id = :Lpu_id,
					@LpuPeriodOMS_id = @Res output,
					@LpuPeriodOMS_begDate = :LpuPeriodOMS_begDate,
					@LpuPeriodOMS_endDate = :LpuPeriodOMS_endDate,
					@LpuPeriodOMS_DogNum = :LpuPeriodOMS_DogNum,
					@LpuPeriodOMS_RegNumC = :LpuPeriodOMS_RegNumC,
					@LpuPeriodOMS_RegNumN = :LpuPeriodOMS_RegNumN,		 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuPeriodOMS_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);
			
			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;
		
	}
	/**
	* Сохраняет данные формы периода ОМС
	*/
	function saveLpuOMS($data) {

		$queryParams = array(
			'Lpu_id'=>$data['Lpu_id'],
			'Org_id' => $data['Org_id'],
			'LpuPeriodOMS_id' => $data['LpuPeriodOMS_id'],
			'LpuPeriodOMS_begDate' => $data['LpuPeriodOMS_begDate'],
			'LpuPeriodOMS_pid' => $data['LpuPeriodOMS_pid'],
			'LpuPeriodOMS_DogNum' => $data['LpuPeriodOMS_DogNum'],
			'LpuPeriodOMS_RegNumC' => $data['LpuPeriodOMS_RegNumC'],
			'LpuPeriodOMS_RegNumN' => $data['LpuPeriodOMS_RegNumN'],
			'LpuPeriodOMS_Descr' => $data['LpuPeriodOMS_Descr'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		/*if ( $data['LpuPeriodOMS_endDate'] && $data['LpuPeriodOMS_begDate'] > $data['LpuPeriodOMS_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}*/	

		$query = "Select
			COUNT (*) as [count]
				from LpuPeriodOMS (nolock)
			where
				[LpuPeriodOMS_id] = :LpuPeriodOMS_pid and
				([LpuPeriodOMS_begDate] <= :LpuPeriodOMS_begDate) and
				([LpuPeriodOMS_endDate] >= :LpuPeriodOMS_begDate or [LpuPeriodOMS_endDate] is null)";
				
		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] <= 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата договора выходит за пределы периода работы организации в системе ОМС.'));
		}		

		if ($trans_good === true) {
		
			if ( !isset($data['LpuPeriodOMS_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuPeriodOMS_id;
				exec p_LpuPeriodOMS_" . $procedure_action . "
					@Lpu_id = :Lpu_id,
					@Org_id = :Org_id,
					@LpuPeriodOMS_id = @Res output,
					@LpuPeriodOMS_pid = :LpuPeriodOMS_pid,
					@LpuPeriodOMS_begDate = :LpuPeriodOMS_begDate,
					@LpuPeriodOMS_DogNum = :LpuPeriodOMS_DogNum,
					@LpuPeriodOMS_RegNumC = :LpuPeriodOMS_RegNumC,
					@LpuPeriodOMS_RegNumN = :LpuPeriodOMS_RegNumN,
					@LpuPeriodOMS_Descr = :LpuPeriodOMS_Descr,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuPeriodOMS_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);
			
			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;
		
	}
    /**
	* Сохраняет данные формы периода ОМС
	*/
	function saveKurortStatus($data) {

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'KurortStatusDoc_id' => ($data['KurortStatusDoc_id'])?($data['KurortStatusDoc_id']):(null),
			'KurortStatus_id' => $data['KurortStatus_id'],
			'KurortStatusDoc_IsStatus' => ($data['KurortStatusDoc_IsStatus'] == 'on')?(2):(1),
			'KurortStatusDoc_Doc' => $data['KurortStatusDoc_Doc'],
			'KurortStatusDoc_Num' => $data['KurortStatusDoc_Num'],
			'KurortStatusDoc_Date' => $data['KurortStatusDoc_Date'],
			'pmUser_id' => $data['pmUser_id']
		);

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_KurortStatusDoc (nolock)
            where
                (KurortStatus_id = :KurortStatus_id) and
                (KurortStatusDoc_IsStatus = :KurortStatusDoc_IsStatus) and
                (KurortStatusDoc_Doc = :KurortStatusDoc_Doc) and
                (KurortStatusDoc_Num = :KurortStatusDoc_Num) and
                (KurortStatusDoc_Date = :KurortStatusDoc_Date) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            //$trans_result =
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['KurortStatusDoc_id']) ) {
				$procedure_action = "ins";
			}
        else {
				$procedure_action = "upd";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :KurortStatusDoc_id;
				exec fed.p_KurortStatusDoc_" . $procedure_action . "
					@KurortStatusDoc_id = @Res output,
					@Lpu_id = :Lpu_id,
					@KurortStatusDoc_IsStatus = :KurortStatusDoc_IsStatus,
					@KurortStatus_id = :KurortStatus_id,
					@KurortStatusDoc_Doc = :KurortStatusDoc_Doc,
					@KurortStatusDoc_Num = :KurortStatusDoc_Num,
					@KurortStatusDoc_Date = :KurortStatusDoc_Date,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as KurortStatusDoc_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
        else {
				$trans_result = false;
			}

		//}

		return $trans_result;

	}

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadKurortStatus($data)
    {
        $filter = "(1=1)";
        $params = array('Lpu_id' => $data['Lpu_id']);

        if (isset($data['KurortStatusDoc_id']))
        {
            $filter .= ' and doc.KurortStatusDoc_id = :KurortStatusDoc_id';
            $params['KurortStatusDoc_id'] = $data['KurortStatusDoc_id'];
        }
        $query = "
            Select
                doc.KurortStatus_id,
                doc.KurortStatusDoc_id,
                doc.KurortStatusDoc_IsStatus,
                --case when doc.KurortStatusDoc_IsStatus = 1 then 'true' else 'false' end as KurortStatusDoc_IsStatus,
                stat.KurortStatus_Name,
                doc.KurortStatusDoc_Doc,
                doc.KurortStatusDoc_Num,
                RTrim(IsNull(convert(varchar,cast(doc.KurortStatusDoc_Date as datetime),104),'')) as KurortStatusDoc_Date
            from fed.v_KurortStatusDoc doc with (nolock)
            left join fed.v_KurortStatus stat with (nolock) on doc.KurortStatus_id = stat.KurortStatus_id
            where
             Lpu_id = :Lpu_id and {$filter}";
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Получение статуса курорта. Метод для API.
	 */
    function loadKurortStatusById($data)
    {
        $query = "
            Select
                doc.KurortStatus_id,
                doc.KurortStatusDoc_id,
                doc.KurortStatusDoc_IsStatus,
                doc.KurortStatusDoc_Doc,
                doc.KurortStatusDoc_Num,
                convert(varchar(10), doc.KurortStatusDoc_Date, 120) as KurortStatusDoc_Date,
                Lpu_id
            from fed.v_KurortStatusDoc doc with (nolock)
            where
            	doc.KurortStatusDoc_id = :KurortStatusDoc_id
            	and doc.Lpu_id = :Lpu_id
        ";
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadDisSanProtection($data)
    {
        $filter = "(1=1)";
        $params = array('Lpu_id' => $data['Lpu_id']);

        if (isset($data['DisSanProtection_id']))
        {
            $filter .= ' and DisSanProtection_id = :DisSanProtection_id';
            $params['DisSanProtection_id'] = $data['DisSanProtection_id'];
        }

        $query = "
            Select
                DisSanProtection_id,
                DisSanProtection_Doc,
                DisSanProtection_Num,
                RTrim(IsNull(convert(varchar,cast(DisSanProtection_Date as datetime),104),'')) as DisSanProtection_Date,
                case when DisSanProtection_IsProtection = 2 then 'true' else 'false' end as DisSanProtection_IsProtection
            from fed.v_DisSanProtection with (nolock)
            where
                Lpu_id = :Lpu_id and {$filter}
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Получение данных округа горно-санитарной охраны. Метод для API.
	 */
    function loadDisSanProtectionById($data)
    {
        $query = "
            Select
                DisSanProtection_id,
                DisSanProtection_IsProtection,
                DisSanProtection_Doc,
                DisSanProtection_Num,
                convert(varchar(10), DisSanProtection_Date, 120) as DisSanProtection_Date,
                Lpu_id
            from fed.v_DisSanProtection with (nolock)
            where
                DisSanProtection_id = :DisSanProtection_id
                and Lpu_id = :Lpu_id
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет данные формы периода ОМС
     */
    function saveDisSanProtection($data) {

        $queryParams = array(
            'Lpu_id' => $data['Lpu_id'],
            'DisSanProtection_id' => ($data['DisSanProtection_id'])?($data['DisSanProtection_id']):(null),
            'DisSanProtection_Doc' => $data['DisSanProtection_Doc'],
            'DisSanProtection_IsProtection' => ($data['DisSanProtection_IsProtection'] == 'on')?(2):(1),
            'DisSanProtection_Num' => $data['DisSanProtection_Num'],
            'DisSanProtection_Date' => $data['DisSanProtection_Date'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_DisSanProtection (nolock)
            where
                (DisSanProtection_Doc = :DisSanProtection_Doc) and
                (DisSanProtection_IsProtection = :DisSanProtection_IsProtection) and
                (DisSanProtection_Num = :DisSanProtection_Num) and
                (DisSanProtection_Date = :DisSanProtection_Date) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            //$trans_result =
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['DisSanProtection_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :DisSanProtection_id;
				exec fed.p_DisSanProtection_" . $procedure_action . "
					@DisSanProtection_id = @Res output,
					@Lpu_id = :Lpu_id,
					@DisSanProtection_IsProtection = :DisSanProtection_IsProtection,
					@DisSanProtection_Doc = :DisSanProtection_Doc,
					@DisSanProtection_Num = :DisSanProtection_Num,
					@DisSanProtection_Date = :DisSanProtection_Date,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as DisSanProtection_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Сохраняет данные заезда
     */
    function saveMOArrival($data) {

        $queryParams = array(
            'Lpu_id' => $data['Lpu_id'],
            'MOArrival_id' => ($data['MOArrival_id'])?($data['MOArrival_id']):(null),
            'MOArrival_CountPerson' => $data['MOArrival_CountPerson'],
            'MOArrival_TreatDis' => $data['MOArrival_TreatDis'],
            'MOArrival_EndDT' => $data['MOArrival_EndDT'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_MOArrival (nolock)
            where
                (MOArrival_EndDT = :MOArrival_EndDT) and
                (MOArrival_TreatDis = :MOArrival_TreatDis) and
                (MOArrival_CountPerson = :MOArrival_CountPerson) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            //$trans_result =
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['MOArrival_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MOArrival_id;
				exec fed.p_MOArrival_" . $procedure_action . "
					@MOArrival_id = @Res output,
					@Lpu_id = :Lpu_id,
					@MOArrival_CountPerson = :MOArrival_CountPerson,
					@MOArrival_TreatDis = :MOArrival_TreatDis,
					@MOArrival_EndDT = :MOArrival_EndDT,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MOArrival_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadMOArrival($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['MOArrival_id']))
        {
            $filter .= ' and MOArrival_id = :MOArrival_id';
            $params['MOArrival_id'] = $data['MOArrival_id'];
        }

        $query = "
            Select
                MOArrival_id,
                MOArrival_CountPerson,
                MOArrival_TreatDis,
                RTrim(IsNull(convert(varchar,cast(MOArrival_EndDT as datetime),104),'')) as MOArrival_EndDT
            from fed.v_MOArrival with (nolock)
            where
              Lpu_id = :Lpu_id and {$filter}
        ";
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Внутренний метод для сохранения\обновления физического адреса
	 */
	function savePAddress($data, $form_prefix = ''){

		if (!isset($data[$form_prefix.'PAddress_id']))
			$action = "ins";
		else
			$action = "upd";

		$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :PAddress_id;

					exec p_Address_" . $action . "
						@Server_id = :Server_id,
						@Address_id = @Res output,
						@KLAreaType_id = NULL,
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

					select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
				";

		$queryParams = array(
			'PAddress_id' => $data[$form_prefix.'PAddress_id'],
			'Address_Zip' => $data[$form_prefix.'PAddress_Zip'],
			'KLCountry_id' => $data[$form_prefix.'PKLCountry_id'],
			'KLRgn_id' => $data[$form_prefix.'PKLRGN_id'],
			'KLSubRgn_id' => $data[$form_prefix.'PKLSubRGN_id'],
			'KLCity_id' => $data[$form_prefix.'PKLCity_id'],
			'KLTown_id' => $data[$form_prefix.'PKLTown_id'],
			'KLStreet_id' => $data[$form_prefix.'PKLStreet_id'],
			'Address_House' => $data[$form_prefix.'PAddress_House'],
			'Address_Corpus' => $data[$form_prefix.'PAddress_Corpus'],
			'Address_Flat' => $data[$form_prefix.'PAddress_Flat'],
			'Address_Address' => $data[$form_prefix.'PAddress_Address'],

			'Server_id' => $data['Server_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$res = $this->db->query($query, $queryParams);

		if ( !is_object($res) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}

		$response = $res->result('array');

		if (!is_array($response) || count($response) == 0) {
			$this->rollbackTransaction();
			return array(
				array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')')
			);
		}

		return $response[0]['Address_id'];
	}

	/**
	 *	Функция получения списка домовых хозяйств.
	 */
	function loadLpuHouseholdGrid($data)
	{
		$params['Lpu_id'] = $data['LPEW_Lpu_id'];

		$query = "
            SELECT
            	HH.LpuHousehold_id,
            	HH.Lpu_id,
            	HH.LpuHousehold_Name,
            	HH.LpuHousehold_ContactPerson,
            	HH.LpuHousehold_ContactPhone,
            	HH.LpuHousehold_CadNumber,
				HH.LpuHousehold_CoordLat,
				HH.LpuHousehold_CoordLon,
				HH.PAddress_id,
				PADDR.Address_Address as LpuHousehold_Address
            FROM
            	fed.v_LpuHousehold as HH WITH (nolock)
			left join v_Address as PADDR (nolock) on PADDR.Address_id = HH.PAddress_id
            WHERE
              	Lpu_id = :Lpu_id
        ";
		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Функция получения записи домового хозяйства.
	 */
	function getLpuHouseholdRecord($data)
	{
		$form_prefix = isset($data['formPrefix']) ? $data['formPrefix'] : '';
		$params['LpuHousehold_id'] = $data['LpuHousehold_id'];

		$query = "
            SELECT TOP 1
            	HH.LpuHousehold_id,
            	HH.Lpu_id as LPEW_Lpu_id,
            	HH.LpuHousehold_Name,
            	HH.LpuHousehold_ContactPerson,
            	HH.LpuHousehold_ContactPhone,
            	HH.LpuHousehold_CadNumber,
				HH.LpuHousehold_CoordLat,
				HH.LpuHousehold_CoordLon,
				HH.PAddress_id as {$form_prefix}PAddress_id,
				PADDR.Address_Zip as {$form_prefix}PAddress_Zip,
				PADDR.KLCountry_id as {$form_prefix}PKLCountry_id,
				PADDR.KLRGN_id as {$form_prefix}PKLRGN_id,
				PADDR.KLSubRGN_id as {$form_prefix}PKLSubRGN_id,
				PADDR.KLCity_id as {$form_prefix}PKLCity_id,
				PADDR.KLTown_id as {$form_prefix}PKLTown_id,
				PADDR.KLStreet_id as {$form_prefix}PKLStreet_id,
				PADDR.Address_House as {$form_prefix}PAddress_House,
				PADDR.Address_Corpus as {$form_prefix}PAddress_Corpus,
				PADDR.Address_Flat as {$form_prefix}PAddress_Flat,
				PADDR.Address_Address as {$form_prefix}PAddress_Address,
				PADDR.Address_Address as LpuHousehold_Address
            FROM
            	fed.v_LpuHousehold as HH WITH (nolock)
			left join v_Address as PADDR (nolock) on PADDR.Address_id = HH.PAddress_id
            WHERE
              	LpuHousehold_id = :LpuHousehold_id
        ";
		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Функция сохранения записи домового хозяйства.
	 */
	function saveLpuHouseholdRecord($data)
	{
		$form_prefix = isset($data['formPrefix']) ? $data['formPrefix'] : '';

		if (!isset($data['LPEW_Lpu_id']))
			return false;

		$pAddressSaveResult = $this->savePAddress($data, $form_prefix);

		if (!empty($pAddressSaveResult[0]['Error_Msg'])) {
			$this->rollbackTransaction();
			return $pAddressSaveResult;
		}

		$params = array(
			'Lpu_id' => $data['LPEW_Lpu_id'],

			'LpuHousehold_id' =>
				($data['LpuHousehold_id'])
					? ($data['LpuHousehold_id'])
					: (null),

			'LpuHousehold_Name' => $data['LpuHousehold_Name'],
			'LpuHousehold_ContactPerson' => $data['LpuHousehold_ContactPerson'],
			'LpuHousehold_ContactPhone' => $data['LpuHousehold_ContactPhone'],
			'LpuHousehold_CadNumber' => $data['LpuHousehold_CadNumber'],
			'LpuHousehold_CoordLat' => $data['LpuHousehold_CoordLat'],
			'LpuHousehold_CoordLon' => $data['LpuHousehold_CoordLon'],
			'PAddress_id' => $pAddressSaveResult,
			'pmUser_id' => $data['pmUser_id']
		);

		$action = isset($data['LpuHousehold_id']) ? "upd" : "ins";

		$query = "
				DECLARE
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);

				SET @Res = :LpuHousehold_id;

				EXEC fed.p_LpuHousehold_" . $action . "
					@LpuHousehold_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuHousehold_Name = :LpuHousehold_Name,
					@LpuHousehold_ContactPerson = :LpuHousehold_ContactPerson,
					@LpuHousehold_ContactPhone = :LpuHousehold_ContactPhone,
					@LpuHousehold_CadNumber = :LpuHousehold_CadNumber,
					@LpuHousehold_CoordLat = :LpuHousehold_CoordLat,
					@LpuHousehold_CoordLon = :LpuHousehold_CoordLon,
					@PAddress_id = :PAddress_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuHousehold_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

		$result = $this->db->query($query, $params);


		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Получение данных по оснащенности компютерным оборудованием
	 */
	function loadLpuComputerEquipment($data)
	{
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "(1=1)";

		if (isset($data['ComputerEquip_id']))
		{
			$filter .= ' and ComputerEquip_id = :ComputerEquip_id';
			$params['ComputerEquip_id'] = $data['ComputerEquip_id'];
		}

		if (isset($data['ComputerEquip_Year']))
		{
			$filter .= ' and YEAR(eq.ComputerEquip_Year) = :ComputerEquip_Year';
			$params['ComputerEquip_Year'] = $data['ComputerEquip_Year'];
		}

		if (isset($data['Period_id']))
		{
			$filter .= ' and eq.Period_id = :Period_id';
			$params['Period_id'] = $data['Period_id'];
		}

		if (isset($data['Device_id']))
		{
			$filter .= ' and eq.Device_id = :Device_id';
			$params['Device_id'] = $data['Device_id'];
		}

		if (isset($data['ComputerEquip_UsageColumn']))
		{
			$colName = $data['ComputerEquip_UsageColumn'];
			$filter .= " and {$colName} > 0";
		}

		$query = "
            SELECT
            	eq.ComputerEquip_id,
            	eq.ComputerEquip_DevCnt,
            	YEAR(eq.ComputerEquip_Year) as ComputerEquip_Year,
				eq.ComputerEquip_MedPAmb,
				eq.ComputerEquip_MedPStac,
				eq.ComputerEquip_AHDAmb,
				eq.ComputerEquip_AHDStac,
				eq.ComputerEquip_MedStatCab,
				eq.ComputerEquip_other,
				eq.Device_id,
				device.Device_Name,
            	pdv.Device_id as Device_pid,
            	period.Period_Name,
            	period.Period_id,
            	(
            		ISNULL(ComputerEquip_MedPAmb, 0) + ISNULL(ComputerEquip_MedPStac, 0)
            		+ ISNULL(ComputerEquip_AHDAmb, 0) + ISNULL(ComputerEquip_AHDStac, 0)
            		+ ISNULL(ComputerEquip_other, 0)
            	) as ComputerEquip_Total,
            	CASE
					WHEN
						pdv.Device_Name IS NULL
					THEN
						device.Device_Name
            		ELSE
            			pdv.Device_Name
				END as Device_Cat
            FROM
            	passport.v_ComputerEquip AS eq WITH (nolock)
			LEFT JOIN
				passport.v_Device AS device with (nolock) on device.Device_id = eq.Device_id
			LEFT JOIN
				passport.v_Device AS pdv with (nolock) on device.Device_pid = pdv.Device_id
			LEFT JOIN
				passport.v_Period AS period with (nolock) on period.Period_id = eq.Period_id
            WHERE
              	Lpu_id = :Lpu_id and {$filter}
        ";
		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Проверка на возможность удаления родительской категории;
	 * 	поля: категория, год, период;
	 */
	function checkBeforeDeleteComputerEquip($data) {

		$params['ComputerEquip_id'] = $data['ComputerEquip_id'];

		$query = "
            SELECT TOP 1
            	eq.Lpu_id,
            	eq.ComputerEquip_id,
            	YEAR(eq.ComputerEquip_Year) as ComputerEquip_Year,
				eq.Period_id,
				device.Device_Code
            FROM
            	passport.v_ComputerEquip  AS eq WITH (nolock)
			LEFT JOIN
				passport.v_Device AS device with (nolock) on device.Device_id = eq.Device_id
			WHERE
				eq.ComputerEquip_id = :ComputerEquip_id
        ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			$res = $result->result('array');

			if (count($res) > 0)
				return $this->checkLpuComputerEquipmentUniqRecord($res[0]);
			else
				return false;

		} else
			return false;
	}

	/**
	 *	Проверка на уникальность записи комп.оборудования;
	 * 	поля: категория, год, период;
	 */
	function checkLpuComputerEquipmentUniqRecord($data)
	{
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "(1=1)";

		if (isset($data['Device_Code']))	{

			$filter .= ' and device.Device_pid = :Device_Code';
			$params['Device_Code'] = $data['Device_Code'];

		}

		if (isset($data['Device_id']))	{

			$filter .= ' and eq.Device_id = :Device_id';
			$params['Device_id'] = $data['Device_id'];

		}

		if (isset($data['ComputerEquip_Year']))	{

			$filter .= ' and YEAR(eq.ComputerEquip_Year) = :ComputerEquip_Year';
			$params['ComputerEquip_Year'] = $data['ComputerEquip_Year'];

		}

		if (isset($data['Period_id']))	{

			$filter .= ' and eq.Period_id = :Period_id';
			$params['Period_id'] = $data['Period_id'];

		} else {

			$filter .= ' and eq.Period_id IS NULL';
		}

		$query = "
            SELECT TOP 1
            	eq.ComputerEquip_id
            FROM
            	passport.v_ComputerEquip AS eq WITH (nolock)
			LEFT JOIN
				passport.v_Device AS device with (nolock) on device.Device_id = eq.Device_id
			WHERE
				eq.Lpu_id = :Lpu_id and {$filter}
        ";

		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Проверка на количество использования в родительской категории
	 */
	function checkLpuComputerEquipmentParentDeviceUsage($data)
	{
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "(1=1)";

		if (isset($data['Device_id']))	{

			$filter .= ' and Device_id = :Device_id';
			$params['Device_id'] = $data['Device_id'];
		}

		if (isset($data['ComputerEquip_Year']))	{

			$filter .= ' and YEAR(ComputerEquip_Year) = :ComputerEquip_Year';
			$params['ComputerEquip_Year'] = $data['ComputerEquip_Year'];
		}

		if (isset($data['Period_id']))	{

			$filter .= ' and Period_id = :Period_id';
			$params['Period_id'] = $data['Period_id'];

		} else {

			$filter .= ' and Period_id IS NULL';
		}

		$query = "
            SELECT TOP 1
            	ComputerEquip_MedPAmb,
				ComputerEquip_MedPStac,
				ComputerEquip_AHDAmb,
				ComputerEquip_AHDStac,
				ComputerEquip_other,
            	ComputerEquip_id,
            	(
            		ISNULL(ComputerEquip_MedPAmb, 0) + ISNULL(ComputerEquip_MedPStac, 0)
            		+ ISNULL(ComputerEquip_AHDAmb, 0) + ISNULL(ComputerEquip_AHDStac, 0)
            		+ ISNULL(ComputerEquip_other, 0)
            	) as ComputerEquip_Total
            FROM
            	passport.v_ComputerEquip WITH (nolock)
			WHERE
				Lpu_id = :Lpu_id and {$filter}
        ";


		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Проверка на количество использования в дочерних категориях
	 */
	function checkLpuComputerEquipmentChildDeviceUsage($data)
	{
		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "(1=1)";

		if (isset($data['Device_id']))	{

			$filter .= " and Device_id IN (
					SELECT
						Device_id
					FROM
						passport.v_Device WITH (nolock)
					WHERE
						Device_pid = (SELECT TOP 1 Device_pid FROM passport.v_Device WITH (nolock) WHERE Device_id = :Device_id)
				)";

			$params['Device_id'] = $data['Device_id'];
		}

		if (isset($data['ComputerEquip_Year']))	{

			$filter .= ' and YEAR(ComputerEquip_Year) = :ComputerEquip_Year';
			$params['ComputerEquip_Year'] = $data['ComputerEquip_Year'];
		}

		if (isset($data['Period_id']))	{

			$filter .= ' and Period_id = :Period_id';
			$params['Period_id'] = $data['Period_id'];

		} else {

			$filter .= ' and Period_id IS NULL';
		}

		$query = "
            SELECT
            	ComputerEquip_MedPAmb,
				ComputerEquip_MedPStac,
				ComputerEquip_AHDAmb,
				ComputerEquip_AHDStac,
				ComputerEquip_other,
            	ComputerEquip_id,
            	(
            		ISNULL(ComputerEquip_MedPAmb, 0) + ISNULL(ComputerEquip_MedPStac, 0)
            		+ ISNULL(ComputerEquip_AHDAmb, 0) + ISNULL(ComputerEquip_AHDStac, 0)
            		+ ISNULL(ComputerEquip_other, 0)
            	) as ComputerEquip_Total
            FROM
            	passport.v_ComputerEquip WITH (nolock)
			WHERE
				Lpu_id = :Lpu_id and {$filter}
        ";

		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Получение всех уникальных годов комп. оборудования
	 */
	function loadLpuComputerEquipmentYearsUniq($data)
	{
		$params['Lpu_id'] = $data['Lpu_id'];

		$query = "
            SELECT DISTINCT
            	YEAR(ComputerEquip_Year) as ComputerEquip_Year
            FROM
            	passport.v_ComputerEquip WITH (nolock)
			WHERE
				Lpu_id = :Lpu_id
			ORDER BY YEAR(ComputerEquip_Year) DESC
        ";

		$result = $this->db->query($query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Получение периодов для компьютерного оборудования
	 */
	function loadLpuComputerEquipmentYearPeriods()
	{
		$query = "
            SELECT
            	Period_id,
            	Period_Name
            FROM
            	passport.v_Period WITH (nolock)
        ";

		$result = $this->db->query($query);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 *	Получение компьютерного оборудования
	 */
	function loadLpuComputerEquipmentDevices($data = NULL)
	{
		$params = array();

		if ( empty($data) ) {
			$query = "
				SELECT
					Device_id,
					Device_Code,
					Device_Name
				FROM
					passport.v_Device WITH (nolock)
				WHERE
					Device_pid is null
				ORDER BY CAST(Device_Code AS float)
			";
		}
		else {
			$query = "
				if ( exists (select top 1 Device_id from passport.v_Device WITH (nolock) where Device_pid = :parent_id) )
					begin
						SELECT
							Device_id,
							Device_Code,
							Device_Name
						FROM
							passport.v_Device WITH (nolock)
						WHERE
							Device_pid = :parent_id
						ORDER BY CAST(Device_Code AS float)
					end
				else
					begin
						SELECT
							Device_id,
							Device_Code,
							Device_Name
						FROM
							passport.v_Device WITH (nolock)
						WHERE
							Device_id = :parent_id
						ORDER BY CAST(Device_Code AS float)
					end
			";

			$params['parent_id'] = $data['parent_id'];
		}

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохраняем компьютерное оснащение
	 */
	function saveLpuComputerEquipment($data) {


		if (!isset($data['Lpu_id']))
			return false;

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'ComputerEquip_id' =>
				($data['ComputerEquip_id'])
					? ($data['ComputerEquip_id'])
					: (null),
			'Device_id' => $data['Device_id'],
			'Period_id' => $data['Period_id'],
			'ComputerEquip_DevCnt' => $data['ComputerEquip_DevCnt'],
			'ComputerEquip_Year' => $data['ComputerEquip_Year'],

			'ComputerEquip_MedPAmb' => ($data['ComputerEquip_MedPAmb'] > 0) ? $data['ComputerEquip_MedPAmb'] : null,
			'ComputerEquip_MedPStac' => ($data['ComputerEquip_MedPStac']  > 0) ? $data['ComputerEquip_MedPStac'] : null,
			'ComputerEquip_AHDAmb' => ($data['ComputerEquip_AHDAmb']  > 0) ? $data['ComputerEquip_AHDAmb'] : null,
			'ComputerEquip_AHDStac' => ($data['ComputerEquip_AHDStac']  > 0) ? $data['ComputerEquip_AHDStac'] : null,
			'ComputerEquip_MedStatCab' => ($data['ComputerEquip_MedStatCab']  > 0) ? $data['ComputerEquip_MedStatCab'] : null,
			'ComputerEquip_other' => ($data['ComputerEquip_other']  > 0) ? $data['ComputerEquip_other'] : null,

			'pmUser_id' => $data['pmUser_id']
		);

		$action = isset($data['ComputerEquip_id']) ? "upd" : "ins";

		$query = "
				DECLARE
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);

				SET @Res = :ComputerEquip_id;

				EXEC passport.p_ComputerEquip_" . $action . "
					@ComputerEquip_id = @Res output,
					@Lpu_id = :Lpu_id,
					@Device_id = :Device_id,
					@Period_id = :Period_id,
					@ComputerEquip_DevCnt = :ComputerEquip_DevCnt,
					@ComputerEquip_Year = :ComputerEquip_Year,
					@ComputerEquip_MedPAmb = :ComputerEquip_MedPAmb,
					@ComputerEquip_MedPStac = :ComputerEquip_MedPStac,
					@ComputerEquip_AHDAmb = :ComputerEquip_AHDAmb,
					@ComputerEquip_AHDStac = :ComputerEquip_AHDStac,
					@ComputerEquip_MedStatCab = :ComputerEquip_MedStatCab,
					@ComputerEquip_other = :ComputerEquip_other,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as ComputerEquip_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

		$result = $this->db->query($query, $params);


		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 * Удаляем запись компьютерного оснащения
	 */
	function deleteLpuComputerEquipment($data)
	{
		$params = array('ComputerEquip_id' => $data['ComputerEquip_id']);

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_ComputerEquip_del
				@ComputerEquip_id = :ComputerEquip_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, $params);

		return (is_object($result))
			? $result->result('array')
			: false;
	}

	/**
	 * Получаем данные для печати
	 */
	function getLpuCompterEquipPrintData($data)
	{
		$temp['cat'] = array();
		$temp['data'] = array();

		$params['Lpu_id'] = $data['Lpu_id'];
		$filter = "(1=1)";

		if (isset($data['ComputerEquip_Year']))	{

			$filter .= ' and YEAR(ComputerEquip_Year) = :ComputerEquip_Year ';
			$params['ComputerEquip_Year'] = $data['ComputerEquip_Year'];
		}

		// для вывода всех категорий
		$query = "
            SELECT
            	Device_id,
            	Device_Name,
            	Device_Code,
            	Device_pid
            FROM
            	passport.v_Device WITH (nolock)
			order by
				cast(Device_Code as float)
        ";

		$catResult = $this->db->query($query, $params);

		if (is_object($catResult)) {
			$temp['cat'] = $catResult->result('array');

			//освобождаем память, т.к. у нас дальше еще один запрос
			$catResult->free_result();
		}

		// для вывода всех устройств за год
		$query = "
            SELECT
            	eq.Device_id,
            	dvc.Device_Code,
            	sum(eq.ComputerEquip_MedPAmb) as ComputerEquip_MedPAmb,
				sum(eq.ComputerEquip_MedPStac) as ComputerEquip_MedPStac,
				sum(eq.ComputerEquip_AHDAmb) as ComputerEquip_AHDAmb,
				sum(eq.ComputerEquip_AHDStac) as ComputerEquip_AHDStac,
				sum(eq.ComputerEquip_MedStatCab) as ComputerEquip_MedStatCab,
				sum(eq.ComputerEquip_other) as ComputerEquip_other,
				sum(
            	(
            		ISNULL(eq.ComputerEquip_MedPAmb, 0) + ISNULL(eq.ComputerEquip_MedPStac, 0)
            		+ ISNULL(eq.ComputerEquip_AHDAmb, 0) + ISNULL(eq.ComputerEquip_AHDStac, 0)
            		+ ISNULL(eq.ComputerEquip_other, 0)
            	)) as Total
            FROM
            	passport.v_ComputerEquip AS eq WITH (nolock)
			LEFT JOIN
				passport.v_Device AS dvc with (nolock) on dvc.Device_id = eq.Device_id
			WHERE
				Lpu_id = :Lpu_id and {$filter}
			GROUP BY
				eq.Device_id, dvc.Device_Code
        ";

		$result = $this->db->query($query, $params);

		if (is_object($result)) {

			$result->result('array');
			$temp['data'] = $result->result_array;

			//освобождаем память
			$result->free_result();
		}

		if ($temp['cat'] && $temp['data']) {

			$id_col_name = 'Device_id';
			$code_col_name = 'Device_Code';
			$collection = $temp['data'];

			$id_list = array_column($collection, $id_col_name);

			//"спасибо" проектировщикам за это:
			$code_list = array('9.1', '9.2', '9.6', '11');

			foreach ($temp['cat'] as $cat) {

				$cat_key = $cat[$id_col_name];
				$cat_code = $cat[$code_col_name];

				$array_name = 'main_cats';

				// если категория полученного устройства = категории, мерджим к ней количество
				if (in_array($cat_key, $id_list)) {

					$key = array_search($cat_key, $id_list);
					$merged = array_merge($cat, $collection[$key]);

					// если устройство в категориях $code_list, выделяем их в отдельный массив
					if (in_array($cat_code, $code_list))
						$printData['medstatcabs'][$cat_code] = (object) $merged;
					$printData['main_cats'][] = (object) $merged;// иначе просто возращаем категорию
				} else
					$printData['main_cats'][] = (object) $cat;
			}


		}
		return $printData;
	}

    /**
	 *	Получение данных заезда. Метод для API.
	 */
    function loadMOArrivalById($data)
    {
        $query = "
            Select
                MOArrival_id,
                MOArrival_CountPerson,
                MOArrival_TreatDis,
                convert(varchar(10), MOArrival_EndDT, 120) as MOArrival_EndDT,
                Lpu_id
            from fed.v_MOArrival with (nolock)
            where
            	MOArrival_id = :MOArrival_id
            	and Lpu_id = :Lpu_id
        ";
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет тип курорта
     */
    function saveKurortTypeLink($data) {

        $queryParams = array(
            'Lpu_id' => $data['Lpu_id'],
            'KurortTypeLink_id' => ($data['KurortTypeLink_id'])?($data['KurortTypeLink_id']):(null),
            'KurortType_id' => $data['KurortType_id'],
            'KurortTypeLink_IsKurortTypeLink' => ($data['KurortTypeLink_IsKurortTypeLink'] == 'on')?(2):(1),
            'KurortTypeLink_Doc' => $data['KurortTypeLink_Doc'],
            'KurortTypeLink_Num' => $data['KurortTypeLink_Num'],
            'KurortTypeLink_Date' => $data['KurortTypeLink_Date'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_KurortTypeLink (nolock)
            where
                (KurortType_id = :KurortType_id) and
                (KurortTypeLink_IsKurortTypeLink = :KurortTypeLink_IsKurortTypeLink) and
                (KurortTypeLink_Doc = :KurortTypeLink_Doc) and
                (KurortTypeLink_Num = :KurortTypeLink_Num) and
                (KurortTypeLink_Date = :KurortTypeLink_Date) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            //$trans_result =
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['KurortTypeLink_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :KurortTypeLink_id;
				exec fed.p_KurortTypeLink_" . $procedure_action . "
					@KurortTypeLink_id = @Res output,
					@Lpu_id = :Lpu_id,
					@KurortType_id = :KurortType_id,
					@KurortTypeLink_IsKurortTypeLink = :KurortTypeLink_IsKurortTypeLink,
					@KurortTypeLink_Doc = :KurortTypeLink_Doc,
					@KurortTypeLink_Num = :KurortTypeLink_Num,
					@KurortTypeLink_Date = :KurortTypeLink_Date,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as KurortTypeLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }
    /**
     * Загружает список типов куротов
     */
    function loadKurortTypeLink($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['KurortTypeLink_id']))
        {
            $filter .= ' and KTL.KurortTypeLink_id = :KurortTypeLink_id';
            $params['KurortTypeLink_id'] = $data['KurortTypeLink_id'];
        }

        $query = "
            Select
                KTL.KurortTypeLink_id,
                KT.KurortType_Name,
                KTL.KurortType_id,
                KTL.KurortTypeLink_IsKurortTypeLink,
                KTL.KurortTypeLink_Doc,
                KTL.KurortTypeLink_Num,
                RTrim(IsNull(convert(varchar,cast(KTL.KurortTypeLink_Date as datetime),104),'')) as KurortTypeLink_Date
            from fed.v_KurortTypeLink KTL with (nolock)
            left join fed.v_KurortType KT with (nolock) on KTL.KurortType_id = KT.KurortType_id
            where
                Lpu_id = :Lpu_id and {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает тип курорта. Метод для API.
     */
    function loadKurortTypeLinkById($data)
    {
        $query = "
            Select
                KTL.KurortTypeLink_id,
                KTL.KurortType_id,
                KTL.KurortTypeLink_IsKurortTypeLink,
                KTL.KurortTypeLink_Doc,
                KTL.KurortTypeLink_Num,
                convert(varchar(10), KTL.KurortTypeLink_Date, 120) as KurortTypeLink_Date,
                Lpu_id
            from fed.v_KurortTypeLink KTL with (nolock)
            where
                KTL.KurortTypeLink_id = :KurortTypeLink_id
                and KTL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет площадки занимаемой организации
     */
    function saveMOArea($data) {

        $queryParams = array(
            'MOArea_id' => $data['MOArea_id'],
            'OKATO_id' => $data['OKATO_id'],
            'MOArea_Name' => $data['MOArea_Name'],
            'MOArea_Member' => $data['MOArea_Member'],
            'MoArea_Right' => $data['MoArea_Right'],
            'MoArea_Space' => $data['MoArea_Space'],
            'MoArea_KodTer' => $data['MoArea_KodTer'],
            'MoArea_OrgDT' => $data['MoArea_OrgDT'],
            'MoArea_AreaSite' => $data['MoArea_AreaSite'],
			'MoArea_OKATO' => $data['MoArea_OKATO'],
			'Address_id' => $data['Address_id'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_MOArea (nolock)
            where
                (MOArea_Name = :MOArea_Name) and
                (MOArea_Member = :MOArea_Member) and
                (MoArea_KodTer = :MoArea_KodTer) and
                Lpu_id = :Lpu_id and
				MOArea_id <> ISNULL(:MOArea_id, 0)
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            //$trans_result =
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }
		
		// Сохраняем или редактируем адрес
		if ( !isset($data['Address_Address']) ) {
			$data['Address_id'] = NULL;
		}
		else {
			if ( !isset($data['Address_id']) ) {
				$procedure_action = "ins";
			}
			else {
				$procedure_action = "upd";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :Address_id;

				exec p_Address_" . $procedure_action . "
					@Server_id = :Server_id,
					@Address_id = @Res output,

					@KLCountry_id = :KLCountry_id,
					@KLRgn_id = :KLRgn_id,
					@KLSubRgn_id = :KLSubRgn_id,
					@KLCity_id = :KLCity_id,
					@KLTown_id = :KLTown_id,
					@KLAreaType_id = :KLAreaType_id,
					@KLStreet_id = :KLStreet_id,
					@Address_Zip = :Address_Zip,
					@Address_House = :Address_House,
					@Address_Corpus = :Address_Corpus,
					@Address_Flat = :Address_Flat,
					@Address_Address = :Address_Address,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output

				select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
			";

			$queryaddrParams = array(
				'Address_id' => $data['Address_id'],
				'Server_id' => $data['Server_id'],
				'KLCountry_id' => $data['KLCountry_id'],
				'KLRgn_id' => $data['KLRGN_id'],
				'KLSubRgn_id' => $data['KLSubRGN_id'],
				'KLCity_id' => $data['KLCity_id'],
				'KLTown_id' => $data['KLTown_id'],
				'KLAreaType_id' => $data['KLAreaType_id'],
				'KLStreet_id' => $data['KLStreet_id'],
				'Address_Zip' => $data['Address_Zip'],
				'Address_House' => $data['Address_House'],
				'Address_Corpus' => $data['Address_Corpus'],
				'Address_Flat' => $data['Address_Flat'],
				'Address_Address' => $data['Address_Address'],
				'pmUser_id' => $data['pmUser_id']
			);
			$res = $this->db->query($query, $queryaddrParams);

			if ( is_object($res) ) {
				$response = $res->result('array');

				if ( isset($response[0]) && strlen($response[0]['Error_Msg']) == 0 ) {
					$queryParams['Address_id'] = $response[0]['Address_id'];
				}
				else {
					return $response;
				}
			}
			else {
				return false;
			}
		}

        if ( !isset($data['MOArea_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@lpu bigint,
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MOArea_id;

				if ( @Res is not null )
					set @lpu = (select top 1 Lpu_id from fed.MOArea with (nolock) where MOArea_id = @Res)
				else
					set @lpu = :Lpu_id;

				exec fed.p_MOArea_" . $procedure_action . "
					@MOArea_id = @Res output,
					@Lpu_id = @lpu,
					@MOArea_Name = :MOArea_Name,
					@MOArea_Member = :MOArea_Member,
					@MoArea_Right = :MoArea_Right,
					@MoArea_Space = :MoArea_Space,
					@MoArea_KodTer = :MoArea_KodTer,
					@MoArea_OrgDT = :MoArea_OrgDT,
					@MoArea_AreaSite = :MoArea_AreaSite,
					@MoArea_OKATO = :MoArea_OKATO,
					@OKATO_id = :OKATO_id,
					@Address_id = :Address_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MOArea_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }
    /**
     * Загружает список площадок занимаемых организацией
     */
    function loadMOArea($data)
    {
        $filter = "";
        $params = array();

        if (isset($data['Lpu_id']))
        {
            $filter .= ' and ma.Lpu_id = :Lpu_id';
            $params['Lpu_id'] = $data['Lpu_id'];
        }

        if (isset($data['MOArea_id']))
        {
            $filter .= ' and ma.MOArea_id = :MOArea_id';
            $params['MOArea_id'] = $data['MOArea_id'];
        }

        if (empty($filter)) {
        	return array();
		}

        $query = "
            Select
                ma.MOArea_id,
                ma.Lpu_id,
                ma.MOArea_Name,
                ma.MOArea_Member,
                ma.MoArea_Right,
                ma.MoArea_Space,
                ma.MoArea_KodTer,
                ma.MoArea_AreaSite,
				OKATO.OKATO_Code as MoArea_OKATO,
				ma.Address_id,
				ma.OKATO_id,
				adr.Address_Zip,
				adr.KLCountry_id,
				adr.KLRGN_id,
				adr.KLSubRGN_id,
				adr.KLCity_id,
				adr.KLTown_id,
				adr.KLStreet_id,
				adr.Address_House,
				adr.Address_Corpus,
				adr.Address_Flat,
				adr.Address_Address,
				adr.Address_Address as Address_AddressText,
                RTrim(IsNull(convert(varchar,cast(ma.MoArea_OrgDT as datetime),104),'')) as MoArea_OrgDT
            from fed.v_MOArea ma with (nolock)
				left join v_Address_all adr (nolock) on adr.Address_id = ma.Address_id
				left join nsi.v_OKATO OKATO (nolock) on OKATO.OKATO_id = ma.OKATO_id
            where
                (1=1)
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет объект инфраструктуры
     */
    function saveMOAreaObject($data) {

        $queryParams = array(
            'MOAreaObject_id' => $data['MOAreaObject_id'],
            'DObjInfrastructure_id' => $data['DObjInfrastructure_id'],
            'MOAreaObject_Count' => $data['MOAreaObject_Count'],
            'MOAreaObject_Member' => $data['MOAreaObject_Member'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_MOAreaObject (nolock)
            where
                (DObjInfrastructure_id = :DObjInfrastructure_id) and
                (MOAreaObject_Count = :MOAreaObject_Count) and
                (MOAreaObject_Member = :MOAreaObject_Member) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['MOAreaObject_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@lpu bigint,
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MOAreaObject_id;

				if ( @Res is not null )
					set @lpu = (select top 1 Lpu_id from fed.MOAreaObject with (nolock) where MOAreaObject_id = @Res)
				else
					set @lpu = :Lpu_id;

				exec fed.p_MOAreaObject_" . $procedure_action . "
					@MOAreaObject_id = @Res output,
					@Lpu_id = @lpu,
					@DObjInfrastructure_id = :DObjInfrastructure_id,
					@MOAreaObject_Count = :MOAreaObject_Count,
					@MOAreaObject_Member = :MOAreaObject_Member,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MOAreaObject_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }
    /**
     * Загружает список объектов инфраструктуры
     */
    function loadMOAreaObject($data)
    {
        $filter = "";
        $params = array();

        if (isset($data['MOAreaObject_id']))
        {
            $filter .= ' and MOAO.MOAreaObject_id = :MOAreaObject_id';
            $params['MOAreaObject_id'] = $data['MOAreaObject_id'];
        }

        if (isset($data['Lpu_id']))
        {
            $filter .= ' and MOAO.Lpu_id = :Lpu_id';
            $params['Lpu_id'] = $data['Lpu_id'];
        }

		if (empty($filter)) {
			return array();
		}

        $query = "
            Select
                MOAO.MOAreaObject_id,
                MOAO.Lpu_id,
                MOAO.DObjInfrastructure_id,
                DOI.DObjInfrastructure_Name,
                MOAO.MOAreaObject_Count,
                MOAO.MOAreaObject_Member
            from fed.v_MOAreaObject MOAO with (nolock)
            left join fed.v_DObjInfrastructure DOI with (nolock) on MOAO.DObjInfrastructure_id = DOI.DObjInfrastructure_id
            where
            	(1=1)
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет объект инфраструктуры
     */
    function saveMOInfoSys($data) {

        $queryParams = array(
            'DInfSys_id' => $data['DInfSys_id'],
            'Lpu_id' => $data['Lpu_id'],
            'MOInfoSys_id' => $data['MOInfoSys_id'],
            'MOInfoSys_IntroDT' => $data['MOInfoSys_IntroDT'],
            'MOInfoSys_IsMainten' => $data['MOInfoSys_IsMainten'],
            'MOInfoSys_Name' => $data['MOInfoSys_Name'],
            'MOInfoSys_NameDeveloper' => $data['MOInfoSys_NameDeveloper'],
            'MOInfoSys_Cost' => $data['MOInfoSys_Cost'],
            'MOInfoSys_CostYear' => $data['MOInfoSys_CostYear'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_MOInfoSys (nolock)
            where
                (DInfSys_id = :DInfSys_id) and
                (MOInfoSys_IntroDT = :MOInfoSys_IntroDT) and
                (MOInfoSys_Name = :MOInfoSys_Name) and
                (MOInfoSys_NameDeveloper = :MOInfoSys_NameDeveloper) and
                (MOInfoSys_id != :MOInfoSys_id) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['MOInfoSys_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MOInfoSys_id;
				exec fed.p_MOInfoSys_" . $procedure_action . "
					@MOInfoSys_id = @Res output,
					@Lpu_id = :Lpu_id,
					@DInfSys_id = :DInfSys_id,
					@MOInfoSys_IntroDT = :MOInfoSys_IntroDT,
					@MOInfoSys_IsMainten = :MOInfoSys_IsMainten,
					@MOInfoSys_Name = :MOInfoSys_Name,
					@MOInfoSys_NameDeveloper = :MOInfoSys_NameDeveloper,
					@MOInfoSys_Cost = :MOInfoSys_Cost,
					@MOInfoSys_CostYear = :MOInfoSys_CostYear,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MOInfoSys_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Загружает список объектов инфраструктуры
     */
    function loadMOInfoSys($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['MOInfoSys_id']))
        {
            $filter .= ' and MOS.MOInfoSys_id = :MOInfoSys_id';
            $params['MOInfoSys_id'] = $data['MOInfoSys_id'];
        }

        $query = "
            Select
                DIS.DInfSys_Name,
                MOS.DInfSys_id,
                MOS.MOInfoSys_id,
                MOS.MOInfoSys_IsMainten,
                MOS.MOInfoSys_Name,
                MOS.MOInfoSys_NameDeveloper,
                MOS.MOInfoSys_Cost,
                MOS.MOInfoSys_CostYear,
                RTrim(IsNull(convert(varchar,cast(MOS.MOInfoSys_IntroDT as datetime),104),'')) as MOInfoSys_IntroDT
            from fed.v_MOInfoSys MOS with (nolock)
            left join fed.v_DInfSys DIS with (nolock) on MOS.DInfSys_id = DIS.DInfSys_id
            where
                Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     *  Загружает информационную систему МО. Метод для API.
     */
    function loadMOInfoSysById($data)
    {
        $query = "
            Select
                MOS.Lpu_id,
                MOS.DInfSys_id,
                MOS.MOInfoSys_id,
                MOS.MOInfoSys_IsMainten,
                MOS.MOInfoSys_Name,
                MOS.MOInfoSys_NameDeveloper,
                MOS.MOInfoSys_Cost,
                MOS.MOInfoSys_CostYear,
                RTrim(IsNull(convert(varchar,cast(MOS.MOInfoSys_IntroDT as datetime),104),'')) as MOInfoSys_IntroDT
            from fed.v_MOInfoSys MOS with (nolock)
            where
                MOS.MOInfoSys_id = :MOInfoSys_id
                and MOS.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет объект инфраструктуры
     */
    function saveMedUsluga($data) {

        $queryParams = array(
            'DUslugi_id' => $data['DUslugi_id'],
            'Lpu_id' => $data['Lpu_id'],
            'MedUsluga_id' => $data['MedUsluga_id'],
            'MedUsluga_LicenseNum' => $data['MedUsluga_LicenseNum'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_MedUsluga (nolock)
            where
                (MedUsluga_LicenseNum = :MedUsluga_LicenseNum) and
                (DUslugi_id = :DUslugi_id) and
                Lpu_id != :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }


        if ( !isset($data['MedUsluga_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MedUsluga_id;
				exec fed.p_MedUsluga_" . $procedure_action . "
					@MedUsluga_id = @Res output,
					@DUslugi_id = :DUslugi_id,
					@Lpu_id = :Lpu_id,
					@MedUsluga_LicenseNum = :MedUsluga_LicenseNum,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MedUsluga_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Загружает список объектов инфраструктуры
     */
    function loadMedUsluga($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['MedUsluga_id']))
        {
            $filter .= ' and MedUsluga_id = :MedUsluga_id';
            $params['MedUsluga_id'] = $data['MedUsluga_id'];
        }

        $query = "
            Select
                LEFT(DU.DUslugi_Name,LEN(DU.DUslugi_Name)-1) as DUslugi_Name,
                MU.DUslugi_id,
                MU.MedUsluga_id,
                MU.MedUsluga_LicenseNum
            from fed.v_MedUsluga MU with (nolock)
            left join fed.v_DUslugi DU with (nolock) on MU.DUslugi_id = DU.DUslugi_id
            where
                Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает мед услугу. Метод для API.
     */
    function loadMedUslugaById($data)
    {
        $query = "
            Select
                MU.DUslugi_id,
                MU.MedUsluga_id,
                MU.MedUsluga_LicenseNum,
                MU.Lpu_id
            from fed.v_MedUsluga MU with (nolock)
            where
                MU.MedUsluga_id = :MedUsluga_id
                and MU.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет объект инфраструктуры
     */
    function saveMedTechnology($data) {

        $queryParams = array(
            'MedTechnology_id' => $data['MedTechnology_id'],
            'Lpu_id' => $data['Lpu_id'],
            'MedTechnology_Name' => $data['MedTechnology_Name'],
            'TechnologyClass_id' => $data['TechnologyClass_id'],
            'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.MedTechnology (nolock)
            where
                MedTechnology_Name = :MedTechnology_Name and
                TechnologyClass_id = :TechnologyClass_id and
                LpuBuildingPass_id = :LpuBuildingPass_id and
                Lpu_id = :Lpu_id and
                MedTechnology_id != :MedTechnology_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['MedTechnology_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :MedTechnology_id;
				exec fed.p_MedTechnology_" . $procedure_action . "
					@MedTechnology_id = @Res output,
					@MedTechnology_Name = :MedTechnology_Name,
					@Lpu_id = :Lpu_id,
					@TechnologyClass_id = :TechnologyClass_id,
					@LpuBuildingPass_id = :LpuBuildingPass_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as MedTechnology_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Загружает список объектов инфраструктуры
     */
    function loadMedTechnology($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['MedTechnology_id']))
        {
            $filter .= ' and MedTechnology_id = :MedTechnology_id';
            $params['MedTechnology_id'] = $data['MedTechnology_id'];
        }

        $query = "
            Select
                LBP.LpuBuildingPass_Name,
                MT.LpuBuildingPass_id,
                MT.Lpu_id,
                MT.TechnologyClass_id,
                TC.TechnologyClass_Name,
                MT.MedTechnology_id,
                MT.MedTechnology_Name
            from fed.MedTechnology MT with (nolock)
            left join LpuBuildingPass LBP with (nolock) on LBP.LpuBuildingPass_id = MT.LpuBuildingPass_id
            left join passport.v_TechnologyClass TC with (nolock) on TC.TechnologyClass_id = MT.TechnologyClass_id
            where
                MT.Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает медтехнологию. Метод для API.
     */
    function loadMedTechnologyById($data)
    {
        $query = "
            Select
                MT.LpuBuildingPass_id,
                MT.Lpu_id,
                MT.TechnologyClass_id,
                MT.MedTechnology_id,
                MT.MedTechnology_Name
            from fed.MedTechnology MT with (nolock)
            where
                MT.MedTechnology_id = :MedTechnology_id
            	and MT.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет объект инфраструктуры
     */
    function savePitanFormTypeLink($data) {

        $queryParams = array(
            'PitanFormTypeLink_id' => $data['PitanFormTypeLink_id'],
            'Lpu_id' => $data['Lpu_id'],
            'VidPitan_id' => $data['VidPitan_id'],
            'PitanCnt_id' => $data['PitanCnt_id'],
            'PitanForm_id' => $data['PitanForm_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        if(!empty($data['PitanFormTypeLink_id'])){
        	$where = " and PitanFormTypeLink_id <> :PitanFormTypeLink_id";
        } else {
        	$where = "";
        }

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_PitanFormTypeLink (nolock)
            where
                VidPitan_id = :VidPitan_id and
                PitanCnt_id = :PitanCnt_id and
                PitanForm_id = :PitanForm_id and
                Lpu_id = :Lpu_id
                {$where}
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['PitanFormTypeLink_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PitanFormTypeLink_id;
				exec fed.p_PitanFormTypeLink_" . $procedure_action . "
					@PitanFormTypeLink_id = @Res output,
					@VidPitan_id = :VidPitan_id,
					@Lpu_id = :Lpu_id,
					@PitanCnt_id = :PitanCnt_id,
					@PitanForm_id = :PitanForm_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as PitanFormTypeLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Загружает список объектов инфраструктуры
     */
    function loadPitanFormTypeLink($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['PitanFormTypeLink_id']))
        {
            $filter .= ' and PitanFormTypeLink_id = :PitanFormTypeLink_id';
            $params['PitanFormTypeLink_id'] = $data['PitanFormTypeLink_id'];
        }

        $query = "
            Select
                PFTL.PitanFormTypeLink_id,
                PFTL.VidPitan_id,
                VP.VidPitan_Name,
                PFTL.PitanCnt_id,
                PC.PitanCnt_Name,
                PFTL.PitanForm_id,
                PF.PitanForm_Name
            from fed.v_PitanFormTypeLink PFTL with (nolock)
            left join fed.v_VidPitan VP with (nolock) on PFTL.VidPitan_id = VP.VidPitan_id
            left join fed.v_PitanCnt PC with (nolock) on PFTL.PitanCnt_id = PC.PitanCnt_id
            left join fed.v_PitanForm PF with (nolock) on PFTL.PitanForm_id = PF.PitanForm_id

            where
                Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает питание. Метод для API.
     */
    function loadPitanFormTypeLinkById($data)
    {
        $query = "
            Select
                PFTL.PitanFormTypeLink_id,
                PFTL.VidPitan_id,
                PFTL.PitanCnt_id,
                PFTL.PitanForm_id,
                PFTL.Lpu_id
            from fed.v_PitanFormTypeLink PFTL with (nolock)
            where
                PFTL.PitanFormTypeLink_id = :PitanFormTypeLink_id
                and PFTL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет  природный лечебный фактор
     */
    function savePlfDocTypeLink($data) {

        $queryParams = array(
            'PlfDocTypeLink_id' => $data['PlfDocTypeLink_id'],
            'DocTypeUsePlf_id' => $data['DocTypeUsePlf_id'],
            'Plf_id' => $data['Plf_id'],
            'PlfType_id' => $data['PlfType_id'],
            'PlfDocTypeLink_Num' => $data['PlfDocTypeLink_Num'],
            'PlfDocTypeLink_BegDT' => $data['PlfDocTypeLink_BegDT'],
            'PlfDocTypeLink_EndDT' => $data['PlfDocTypeLink_EndDT'],
            'PlfDocTypeLink_GetDT' => $data['PlfDocTypeLink_GetDT'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        if(!empty($data['PlfDocTypeLink_id'])){
        	$where = " and PlfDocTypeLink_id <> :PlfDocTypeLink_id";
        } else {
        	$where = "";
        }

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_PlfDocTypeLink (nolock)
            where
                DocTypeUsePlf_id = :DocTypeUsePlf_id and
                Plf_id = :Plf_id and
                PlfType_id = :PlfType_id and
                PlfDocTypeLink_Num = :PlfDocTypeLink_Num and
                PlfDocTypeLink_GetDT = :PlfDocTypeLink_GetDT and
                Lpu_id = :Lpu_id
                {$where}
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['PlfDocTypeLink_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PlfDocTypeLink_id;
				exec fed.p_PlfDocTypeLink_" . $procedure_action . "
					@PlfDocTypeLink_id = @Res output,
					@DocTypeUsePlf_id = :DocTypeUsePlf_id,
					@Plf_id = :Plf_id,
					@PlfDocTypeLink_BegDT = :PlfDocTypeLink_BegDT,
					@PlfDocTypeLink_EndDT = :PlfDocTypeLink_EndDT,
					@PlfDocTypeLink_GetDT = :PlfDocTypeLink_GetDT,
					@PlfDocTypeLink_Num = :PlfDocTypeLink_Num,
					@PlfType_id = :PlfType_id,
					@Lpu_id = :Lpu_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as PlfDocTypeLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Загружает список природных лечебных факторов
     */
    function loadPlfDocTypeLink($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['PlfDocTypeLink_id']))
        {
            $filter .= ' and PlfDocTypeLink_id = :PlfDocTypeLink_id';
            $params['PlfDocTypeLink_id'] = $data['PlfDocTypeLink_id'];
        }

        $query = "
            Select
                PDTL.PlfDocTypeLink_id,
                PDTL.DocTypeUsePlf_id,
                PDTL.Plf_id,
                PDTL.PlfType_id,
                DTUP.DocTypeUsePlf_Name,
                P.Plf_Name,
                PT.PlfType_Name,
                RTrim(IsNull(convert(varchar,cast(PDTL.PlfDocTypeLink_BegDT as datetime),104),'')) as PlfDocTypeLink_BegDT,
                RTrim(IsNull(convert(varchar,cast(PDTL.PlfDocTypeLink_EndDT as datetime),104),'')) as PlfDocTypeLink_EndDT,
                RTrim(IsNull(convert(varchar,cast(PDTL.PlfDocTypeLink_GetDT as datetime),104),'')) as PlfDocTypeLink_GetDT,
                PDTL.PlfDocTypeLink_Num
            from fed.v_PlfDocTypeLink PDTL with (nolock)
            left join fed.v_DocTypeUsePlf DTUP with (nolock) on PDTL.DocTypeUsePlf_id = DTUP.DocTypeUsePlf_id
            left join fed.v_Plf P with (nolock) on PDTL.Plf_id = P.Plf_id
            left join fed.v_PlfType PT with (nolock) on PDTL.PlfType_id = PT.PlfType_id

            where
                Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает природный лечебный фактор
     */
    function loadPlfDocTypeLinkById($data)
    {
        $query = "
            Select
                PDTL.PlfDocTypeLink_id,
                PDTL.DocTypeUsePlf_id,
                PDTL.Plf_id,
                PDTL.PlfType_id,
                convert(varchar(10), PDTL.PlfDocTypeLink_BegDT, 120) as PlfDocTypeLink_BegDT,
                convert(varchar(10), PDTL.PlfDocTypeLink_EndDT, 120) as PlfDocTypeLink_EndDT,
                convert(varchar(10), PDTL.PlfDocTypeLink_GetDT, 120) as PlfDocTypeLink_GetDT,
                PDTL.PlfDocTypeLink_Num,
                PDTL.Lpu_id
            from fed.v_PlfDocTypeLink PDTL with (nolock)
            where
                PDTL.PlfDocTypeLink_id = :PlfDocTypeLink_id
                and PDTL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет  Объекты/места использования природных лечебных факторов
     */
    function savePlfObjectCount($data) {

        $queryParams = array(
            'PlfObjectCount_id' => $data['PlfObjectCount_id'],
            'PlfObjectCount_Count' => $data['PlfObjectCount_Count'],
            'PlfObjects_id' => $data['PlfObjects_id'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_PlfObjectCount (nolock)
            where
                PlfObjectCount_Count = :PlfObjectCount_Count and
                PlfObjectCount_id <> isnull(:PlfObjectCount_id,0) and
                PlfObjects_id = :PlfObjects_id and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['PlfObjectCount_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PlfObjectCount_id;
				exec fed.p_PlfObjectCount_" . $procedure_action . "
					@PlfObjectCount_id = @Res output,
					@PlfObjects_id = :PlfObjects_id,
					@PlfObjectCount_Count = :PlfObjectCount_Count,
					@Lpu_id = :Lpu_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as PlfObjectCount_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadPlfObjectCount($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['PlfObjectCount_id']))
        {
            $filter .= ' and PlfObjectCount_id = :PlfObjectCount_id';
            $params['PlfObjectCount_id'] = $data['PlfObjectCount_id'];
        }

        $query = "
            Select
                POC.PlfObjectCount_Count,
                POC.PlfObjectCount_id,
                POC.PlfObjects_id,
                PO.PlfObjects_Name
            from fed.v_PlfObjectCount POC with (nolock)
            left join fed.v_PlfObjects PO with (nolock) on POC.PlfObjects_id = PO.PlfObjects_id
            where
                Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Получение объекта/места использования природного лечебного фактора. Метод для API.
	 */
    function loadPlfObjectCountBuId($data)
    {
        $query = "
            Select
                POC.PlfObjectCount_Count,
                POC.PlfObjectCount_id,
                POC.PlfObjects_id,
                POC.Lpu_id
            from fed.v_PlfObjectCount POC with (nolock)
            where
                POC.PlfObjectCount_id = :PlfObjectCount_id
                and POC.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     *  Сохраняет специализацию организации
     */
    function saveSpecializationMO($data) {
        $queryParams = array(
            'SpecializationMO_id' => $data['SpecializationMO_id'],
            'Mkb10Code_id' => $data['Mkb10Code_id'],
            'Mkb10Code_cid' => $data['Mkb10CodeClass_id'],
            //'SpecializationMO_IsDepAftercare' => ($data['SpecializationMO_IsDepAftercare']=='on')?(1):(2),
			'SpecializationMO_IsDepAftercare' => $data['SpecializationMO_IsDepAftercare'],
            'SpecializationMO_MedProfile' => $data['SpecializationMO_MedProfile'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'Lpu_id' => $data['Lpu_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_SpecializationMO (nolock)
            where
                (Mkb10Code_id = :Mkb10Code_id) and
                (Mkb10Code_cid = :Mkb10Code_cid) and
                (SpecializationMO_IsDepAftercare = :SpecializationMO_IsDepAftercare) and
                (SpecializationMO_MedProfile = :SpecializationMO_MedProfile) and
                (LpuLicence_id = :LpuLicence_id) and
                (SpecializationMO_id != :SpecializationMO_id) and
                Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        if ( !isset($data['SpecializationMO_id']) ) {
            $procedure_action = "ins";
        }
        else {
            $procedure_action = "upd";
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :SpecializationMO_id;
				exec fed.p_SpecializationMO_" . $procedure_action . "
					@SpecializationMO_id = @Res output,
					@Mkb10Code_id = :Mkb10Code_id,
					@SpecializationMO_MedProfile = :SpecializationMO_MedProfile,
					@LpuLicence_id = :LpuLicence_id,
					@Mkb10Code_cid = :Mkb10Code_cid,
					@SpecializationMO_IsDepAftercare = :SpecializationMO_IsDepAftercare,
					@Lpu_id = :Lpu_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as SpecializationMO_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadSpecializationMO($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['SpecializationMO_id']))
        {
            $filter .= ' and SpecializationMO_id = :SpecializationMO_id';
            $params['SpecializationMO_id'] = $data['SpecializationMO_id'];
        }

        $query = "
            Select
                SMO.SpecializationMO_id,
                SMO.SpecializationMO_IsDepAftercare,
                SMO.Mkb10Code_id,
                SMO.Mkb10Code_cid as Mkb10CodeClass_id,
                mkbС.Mkb10Code_RecCode + '.' + mkbС.Mkb10Code_Name as Mkb10CodeClass_Name,
                SMO.SpecializationMO_MedProfile,
                SMO.LpuLicence_id,
                LL.LpuLicence_Num,
				mkb.Mkb10Code_StateCode
            from fed.v_SpecializationMO SMO with (nolock)
            left join v_LpuLicence LL with (nolock) on LL.LpuLicence_id = SMO.LpuLicence_id
            left join fed.v_Mkb10Code mkb with (nolock) on mkb.Mkb10Code_id = SMO.Mkb10Code_id
            left join fed.v_Mkb10Code mkbС with (nolock) on mkbС.Mkb10Code_id = SMO.Mkb10Code_cid
            where
                SMO.Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Получение специализации МО. Метод для API.
	 */
    function loadSpecializationMOById($data)
    {
        $query = "
            Select
                SpecializationMO_id,
                SpecializationMO_IsDepAftercare,
                Mkb10Code_id,
                Mkb10Code_cid,
                SpecializationMO_MedProfile,
                LpuLicence_id,
				Lpu_id
            from fed.v_SpecializationMO with (nolock)
            where
                SpecializationMO_id = :SpecializationMO_id
                and Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет  операцию с лицензией ЛПУ
     */
    function saveLpuLicenceOperationLink($data) {

        if ( isset($data['LpuLicenceOperationLink_id']) && $data['LpuLicenceOperationLink_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['LpuLicenceOperationLink_id'] = 0;
            $procedure_action = "ins";
        }

        $queryParams = array(
            'LpuLicenceOperationLink_id' => $data['LpuLicenceOperationLink_id'],
            'LicsOperation_id' => $data['LicsOperation_id'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'LpuLicenceOperationLink_Date' => date("Y-m-d", strtotime($data['LpuLicenceOperationLink_Date'])),
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_LpuLicenceOperationLink (nolock)
            where
                (LpuLicenceOperationLink_Date = :LpuLicenceOperationLink_Date) and
                (LpuLicence_id = :LpuLicence_id) and
                (LicsOperation_id = :LicsOperation_id)
        ";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuLicenceOperationLink_id;
				exec fed.p_LpuLicenceOperationLink_" . $procedure_action . "
					@LpuLicenceOperationLink_id = @Res output,
					@LicsOperation_id = :LicsOperation_id,
					@LpuLicence_id = :LpuLicence_id,
					@LpuLicenceOperationLink_Date = :LpuLicenceOperationLink_Date,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuLicenceOperationLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Сохраняет профили лицензий
     */
    function saveLpuLicenceLink($data) {

        if ( isset($data['LpuLicenceLink_id']) && $data['LpuLicenceLink_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['LpuLicenceLink_id'] = 0;
            $procedure_action = "ins";
        }

        $queryParams = array(
            'LpuLicenceLink_id' => $data['LpuLicenceLink_id'],
            'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                LpuLicenceLink (nolock)
            where
                (LpuLicence_id = :LpuLicence_id) and
                (LpuSectionProfile_id = :LpuSectionProfile_id)
        ";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись указанный профиль для даннной лицензии уже существует.'));
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuLicenceLink_id;
				exec p_LpuLicenceLink_" . $procedure_action . "
					@LpuLicenceLink_id = @Res output,
					@LpuSectionProfile_id = :LpuSectionProfile_id,
					@LpuLicence_id = :LpuLicence_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuLicenceLink_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Сохраняет  приложение к лицензии ЛПУ (Казахстан)
     */
    function saveLpuLicenceDop($data) {

        if ( isset($data['LpuLicenceDop_id']) && $data['LpuLicenceDop_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['LpuLicenceDop_id'] = 0;
            $procedure_action = "ins";
        }

        $queryParams = array(
            'LpuLicenceDop_id' => $data['LpuLicenceDop_id'],
            'LpuLicenceDop_Num' => $data['LpuLicenceDop_Num'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'LpuLicenceDop_setDate' => date("Y-m-d", strtotime($data['LpuLicenceDop_setDate'])),
            'pmUser_id' => $data['pmUser_id']
        );

        /*$query = "
            select
                COUNT (*) as [count]
            from
                passport101.LpuLicenceDop  (nolock)
            where
                (LpuLicenceOperationLink_Date = :LpuLicenceOperationLink_Date) and
                (LpuLicence_id = :LpuLicence_id) and
                (LicsOperation_id = :LicsOperation_id)
        ";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }*/

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuLicenceDop_id;
				exec passport101.p_LpuLicenceDop_" . $procedure_action . "
					@LpuLicenceDop_id = @Res output,
					@LpuLicenceDop_Num = :LpuLicenceDop_Num,
					@LpuLicence_id = :LpuLicence_id,
					@LpuLicenceDop_setDate = :LpuLicenceDop_setDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuLicenceDop_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');

	        if (is_array($trans_result) && !empty($trans_result[0]['Error_Msg'])){
                return array(0 => array('Error_Msg' => 'Ошибка при сохранении приложения к лицензии'.$trans_result[0]['Error_Msg']));
	        }
        }
        else {
            $trans_result = false;
        }

        return $trans_result;

    }

    /**
     * Сохраняет вид лицензии МО
     */
    function saveLpuLicenceProfile($data) {

        if ( isset($data['LpuLicenceProfile_id']) && $data['LpuLicenceProfile_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['LpuLicenceProfile_id'] = 0;
            $procedure_action = "ins";
        }

        $queryParams = array(
            'LpuLicenceProfile_id' => $data['LpuLicenceProfile_id'],
            'LpuLicenceProfileType_id' => $data['LpuLicenceProfileType_id'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                fed.v_LpuLicenceProfile (nolock)
            where
                (LpuLicence_id = :LpuLicence_id) and
                (LpuLicenceProfileType_id = :LpuLicenceProfileType_id) and
                (LpuLicenceProfile_id <> ISNULL(:LpuLicenceProfile_id, 0))
        ";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuLicenceProfile_id;
				exec fed.p_LpuLicenceProfile_" . $procedure_action . "
					@LpuLicenceProfile_id = @Res output,
					@LpuLicenceProfileType_id = :LpuLicenceProfileType_id,
					@LpuLicence_id = :LpuLicence_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuLicenceProfile_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

    /**
     * Сохраняет направления оказания медицинской помощи
     */
    function saveUslugaComplexLpu($data) {

        if ( isset($data['UslugaComplexLpu_id']) && $data['UslugaComplexLpu_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['UslugaComplexLpu_id'] = 0;
            $procedure_action = "ins";
        }

        $queryParams = array(
            'UslugaComplexLpu_id' => $data['UslugaComplexLpu_id'],
            'UslugaComplex_id' => $data['UslugaComplex_id'],
            'Lpu_id' => $data['Lpu_id'],
            'UslugaComplexLpu_begDate' => $data['UslugaComplexLpu_begDate'],
            'UslugaComplexLpu_endDate' => $data['UslugaComplexLpu_endDate'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                passport.v_UslugaComplexLpu (nolock)
            where
                (Lpu_id = :Lpu_id) and
                (UslugaComplex_id = :UslugaComplex_id) and
                (UslugaComplexLpu_id <> :UslugaComplexLpu_id)
        ";

        //echo getDebugSQL($query, $queryParams);die;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0 ) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
        }

        $query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :UslugaComplexLpu_id;
				exec passport.p_UslugaComplexLpu_" . $procedure_action . "
					@UslugaComplexLpu_id = @Res output,
					@UslugaComplex_id = :UslugaComplex_id,
					@Lpu_id = :Lpu_id,
					@UslugaComplexLpu_begDate = :UslugaComplexLpu_begDate,
					@UslugaComplexLpu_endDate = :UslugaComplexLpu_endDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as UslugaComplexLpu_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

        $res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }


    /**
     * Загружает список направлений оказания медицинской помощи
     */
    function loadUslugaComplexLpu($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['UslugaComplexLpu_id']))
        {
            $filter .= ' and UslugaComplexLpu_id = :UslugaComplexLpu_id';
            $params['UslugaComplexLpu_id'] = $data['UslugaComplexLpu_id'];
        }

        $query = "
            Select
                UCL.UslugaComplexLpu_id,
                UCL.UslugaComplex_id,
                UCL.Lpu_id,
                UC.UslugaComplex_Code,
                UC.UslugaComplex_Name,
                RTrim(IsNull(convert(varchar,cast(UCL.UslugaComplexLpu_begDate as datetime),104),'')) as UslugaComplexLpu_begDate,
                RTrim(IsNull(convert(varchar,cast(UCL.UslugaComplexLpu_endDate as datetime),104),'')) as UslugaComplexLpu_endDate
            from passport.v_UslugaComplexLpu UCL with (nolock)
	            left join dbo.v_UslugaComplex UC with (nolock) on UC.UslugaComplex_id = UCL.UslugaComplex_id
            where
                UCL.Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает направление оказания медицинской помощи. Метод для API.
     */
    function loadUslugaComplexLpuById($data)
    {
        $query = "
            Select
                UCL.UslugaComplexLpu_id,
                UCL.UslugaComplex_id,
                UCL.Lpu_id,
                convert(varchar(10), UCL.UslugaComplexLpu_begDate, 120) as UslugaComplexLpu_begDate,
                convert(varchar(10), UCL.UslugaComplexLpu_endDate, 120) as UslugaComplexLpu_endDate
            from passport.v_UslugaComplexLpu UCL with (nolock)
            where
                UCL.UslugaComplexLpu_id = :UslugaComplexLpu_id
                and UCL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }


    /**
     * Сохраняет расходный материал
     */
    function saveConsumables($data) {

        if ( isset($data['Consumables_id']) && $data['Consumables_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['Consumables_id'] = 0;
            $procedure_action = "ins";
        }

        $query = "
            select
                COUNT (*) as [count]
            from
                passport.v_Consumables (nolock)
            where
                (MedProductCard_id = :MedProductCard_id) and
                (Consumables_Name = :Consumables_Name) and
                (Consumables_id <> ISNULL(:Consumables_id, 0))
        ";

        //echo getDebugSQL($query, $data);die;
        $res = $this->db->query($query, $data);

        if (is_object($res)){
            $response = $res->result('array');

            if ( $response[0]['count'] > 0 ) {
                return array(0 => array('Error_Msg' => 'Расходный материал с введенным наименованием уже существует'));
            }
        } else {
            return false;
        }

        $query = "
            declare
                @Res bigint,
                @ErrCode bigint,
                @ErrMsg varchar(4000);
            set @Res = :Consumables_id;
            exec passport.p_Consumables_" . $procedure_action . "
                @Consumables_id = @Res output,
                @Consumables_Name = :Consumables_Name,
                @MedProductCard_id = :MedProductCard_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @Res as Consumables_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
        ";

        //echo getDebugSQL($query, $data);
        $res = $this->db->query($query, $data);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

    /**
     * Сохраняет причину простоя
     */
    function saveDowntime($data) {
		if ( isset($data['Downtime_id']) && $data['Downtime_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['Downtime_id'] = 0;
            $procedure_action = "ins";
        }

        $data['Downtime_begDate'] = date('Y-m-d', strtotime($data['Downtime_begDate']));

		if ( !empty($data['Downtime_endDate']) ) {
			$data['Downtime_endDate'] = date('Y-m-d', strtotime($data['Downtime_endDate']));
		}

        $query = "
            select top 1
                DC.DowntimeCause_Name
            from
                passport.v_Downtime D (nolock)
                left join passport.DowntimeCause DC with (nolock) on DC.DowntimeCause_id = D.DowntimeCause_id
            where
                (D.MedProductCard_id = :MedProductCard_id) and
                (D.DowntimeCause_id = :DowntimeCause_id) and
                (D.Downtime_begDate = :Downtime_begDate) and
                (D.Downtime_id <> ISNULL(:Downtime_id, 0))
        ";

        //echo getDebugSQL($query, $data);die;
        $res = $this->db->query($query, $data);

        if (is_object($res)){
            $response = $res->result('array');

            if ( !empty($response[0]['DowntimeCause_Name']) ) {
                return array(0 => array('Error_Msg' => 'Причина простоя - '.$response[0]['DowntimeCause_Name'].' с указаной датой начала уже существует'));
            }
        } else {
            return false;
        }


        $query = "
            declare
                @Res bigint,
                @ErrCode bigint,
                @ErrMsg varchar(4000);
            set @Res = :Downtime_id;
            exec passport.p_Downtime_" . $procedure_action . "
                @Downtime_id = @Res output,
                @MedProductCard_id = :MedProductCard_id,
                @Downtime_begDate = :Downtime_begDate,
                @Downtime_endDate = :Downtime_endDate,
                @DowntimeCause_id = :DowntimeCause_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @Res as Downtime_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
        ";

        //echo getDebugSQL($query, $data);
        $res = $this->db->query($query, $data);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

	/**
	 * Получение записи «Эксплуатационные данные». Метод для API.
	 */
	function getWorkDataForAPI($data, $onlyMedCard = false) {
		$filter = "";
		$params = array();

		if (!empty($data['WorkData_id'])) {
			$filter .= " and WD.WorkData_id = :WorkData_id";
			$params['WorkData_id'] = $data['WorkData_id'];
		} else if (!empty($data['MedProductCard_id']) && !empty($data['WorkData_WorkPeriod'])) {
			$filter .= " and WD.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
			$filter .= " and WD.WorkData_WorkPeriod = :WorkData_WorkPeriod";
			$params['WorkData_WorkPeriod'] = $data['WorkData_WorkPeriod'];
		} else if($onlyMedCard && !empty($data['MedProductCard_id'])) {
			$filter .= " and WD.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else {
			return array();
		}
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				WD.WorkData_id,
				WD.MedProductCard_id,
				convert(varchar(10), WD.WorkData_WorkPeriod, 120) as WorkData_WorkPeriod,
				WD.WorkData_DayChange,
				WD.WorkData_CountUse,
				WD.WorkData_KolDay,
				WD.WorkData_AvgUse
			from
				passport.v_WorkData WD (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = WD.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 *  Получение списка периодов по ОМС в МО. Метод для API.
	 */
	function getLpuPeriodOMSlistForAPI($data) {
		$query = "
			select
				LpuPeriodOMS_id
			from
				v_LpuPeriodOMS (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 *  Получение атрибутов периода по ОМС по идентификатору. Метод для API.
	 */
	function getLpuPeriodOMSForAPI($data) {
		$query = "
			select
				convert(varchar(10), LpuPeriodOMS_begDate, 120) as LpuPeriodOMS_begDate,
				convert(varchar(10), LpuPeriodOMS_endDate, 120) as LpuPeriodOMS_endDate
			from
				v_LpuPeriodOMS (nolock)
			where
				LpuPeriodOMS_id = :LpuPeriodOMS_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'LpuPeriodOMS_id' => $data['LpuPeriodOMS_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 *  Получение списка периодов по ДЛО в МО. Метод для API.
	 */
	function getLpuPeriodDLOlistForAPI($data) {
		$query = "
			select
				LpuPeriodDLO_id
			from
				v_LpuPeriodDLO (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 *  Получение атрибутов периода по ДЛО по идентификатору. Метод для API.
	 */
	function getLpuPeriodDLOForAPI($data) {
		$filter = "";
		$params = array(
			'LpuPeriodDLO_id' => $data['LpuPeriodDLO_id'],			
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				convert(varchar(10), LpuPeriodDLO_begDate, 120) as LpuPeriodDLO_begDate,
				convert(varchar(10), LpuPeriodDLO_endDate, 120) as LpuPeriodDLO_endDate
			from
				v_LpuPeriodDLO (nolock)
			where
				LpuPeriodDLO_id = :LpuPeriodDLO_id
		".$filter;

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка информационных систем по МО. Метод для API.
	 */
	function getMOInfoSysListForAPI($data) {
		$query = "
			select
				MOInfoSys_id
			from
				fed.v_MOInfoSys (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов информационной системы по идентификатору. Метод для API.
	 */
	function getMOInfoSysForAPI($data) {
		$query = "
			select
				DInfSys_id,
				MOInfoSys_Cost,
				MOInfoSys_CostYear,
				case when MOInfoSys_IsMainten = 2 then 1 else 0 end as MOInfoSys_IsMainten,
				MOInfoSys_Name,
				MOInfoSys_NameDeveloper,
				convert(varchar(10), MOInfoSys_IntroDT, 120) as MOInfoSys_IntroDT
			from
				fed.v_MOInfoSys (nolock)
			where
				MOInfoSys_id = :MOInfoSys_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'MOInfoSys_id' => $data['MOInfoSys_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение списка специализаций по МО. Метод для API.
	 */
	function getSpecializationMOListForAPI($data) {
		$query = "
			select
				SpecializationMO_id
			from
				fed.v_SpecializationMO (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов специализации по идентификатору. Метод для API.
	 */
	function getSpecializationMOForAPI($data) {
		$query = "
			select
				Mkb10Code_cid,
				SpecializationMO_MedProfile,
				case when SpecializationMO_IsDepAftercare = 2 then 1 else 0 end as SpecializationMO_IsDepAftercare,
				LpuLicence_id
			from
				fed.v_SpecializationMO (nolock)
			where
				SpecializationMO_id = :SpecializationMO_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'SpecializationMO_id' => $data['SpecializationMO_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение списка медтехнологий по МО. Метод для API.
	 */
	function getMedTechnologyListForAPI($data) {
		$query = "
			select
				MedTechnology_id
			from
				fed.v_MedTechnology (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение списка медтехнологий по МО. Метод для API.
	 */
	function getMedTechnologyListByLpuBuildingPassForAPI($data) {
		$query = "
			select
				MedTechnology_id
			from
				fed.v_MedTechnology (nolock)
			where
				LpuBuildingPass_id = :LpuBuildingPass_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов медтехнологии по идентификатору. Метод для API.
	 */
	function getMedTechnologyForAPI($data) {
		$query = "
			select
				MedTechnology_Name,
				TechnologyClass_id,
				LpuBuildingPass_id
			from
				fed.v_MedTechnology (nolock)
			where
				MedTechnology_id = :MedTechnology_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'MedTechnology_id' => $data['MedTechnology_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение списка медицинских услуг по МО. Метод для API.
	 */
	function getMedUslugaListForAPI($data) {
		$query = "
			select
				MedUsluga_id
			from
				fed.v_MedUsluga (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов медицинской услуги по идентификатору. Метод для API.
	 */
	function getMedUslugaForAPI($data) {
		$query = "
			select
				MedUsluga_LicenseNum,
				DUslugi_id
			from
				fed.v_MedUsluga (nolock)
			where
				MedUsluga_id = :MedUsluga_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'MedUsluga_id' => $data['MedUsluga_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение списка направлений оказания медицинской помощи по МО. Метод для API.
	 */
	function getUslugaComplexLpuListForAPI($data) {
		$query = "
			select
				UslugaComplexLpu_id
			from
				passport.v_UslugaComplexLpu (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов направления оказания медицинской помощи по идентификатору. Метод для API.
	 */
	function getUslugaComplexLpuForAPI($data) {
		$filter = '';
		$params = array(
			'UslugaComplexLpu_id' => $data['UslugaComplexLpu_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				UslugaComplex_id,
				convert(varchar(10), UslugaComplexLpu_begDate, 120) as UslugaComplexLpu_begDate,
				convert(varchar(10), UslugaComplexLpu_endDate, 120) as UslugaComplexLpu_endDate
			from
				passport.v_UslugaComplexLpu (nolock)
			where
				UslugaComplexLpu_id = :UslugaComplexLpu_id
		".$filter;

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка направлений оказания медицинской помощи по МО. Метод для API.
	 */
	function getPlfObjectsListForAPI($data) {
		$query = "
			select
				PlfObjectCount_id
			from
				fed.v_PlfObjectCount (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов направления оказания медицинской помощи по идентификатору. Метод для API.
	 */
	function getPlfObjectsForAPI($data) {
		$filter = "";
		$params = array(
			'PlfObjectCount_id' => $data['PlfObjectCount_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				PlfObjects_id,
				PlfObjectCount_Count
			from
				fed.v_PlfObjectCount (nolock)
			where
				PlfObjectCount_id = :PlfObjectCount_id
		".$filter;

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка периодов функционирования по МО. Метод для API.
	 */
	function getFunctionTimeListForAPI($data) {
		$query = "
			select
				FunctionTime_id
			from
				passport.v_FunctionTime (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов периода функционирования по идентификатору. Метод для API.
	 */
	function getFunctionTimeForAPI($data) {
		$filter = "";
		$params = array(
			'FunctionTime_id' => $data['FunctionTime_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				InstitutionFunction_id,
				convert(varchar(10), FunctionTime_begDate, 120) as FunctionTime_begDate,
				convert(varchar(10), FunctionTime_endDate, 120) as FunctionTime_endDate
			from
				passport.v_FunctionTime (nolock)
			where
				FunctionTime_id = :FunctionTime_id
		".$filter;

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка питаний по МО. Метод для API.
	 */
	function getPitanListForAPI($data) {
		$query = "
			select
				PitanFormTypeLink_id
			from
				fed.v_PitanFormTypeLink (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов питания по идентификатору. Метод для API.
	 */
	function getPitanForAPI($data) {
		$filter = "";
		$params = array(
			'PitanFormTypeLink_id' => $data['PitanFormTypeLink_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				PitanForm_id,
				VidPitan_id,
				PitanCnt_id
			from
				fed.v_PitanFormTypeLink (nolock)
			where
				PitanFormTypeLink_id = :PitanFormTypeLink_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка природных лечебных факторов по МО. Метод для API.
	 */
	function getPlfListForAPI($data) {
		$query = "
			select
				PlfDocTypeLink_id
			from
				fed.v_PlfDocTypeLink (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов природного лечебного фактора по идентификатору. Метод для API.
	 */
	function getPlfForAPI($data) {
		$filter = "";
		$params = array(
			'PlfDocTypeLink_id' => $data['PlfDocTypeLink_id']
		);
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				DocTypeUsePlf_id,
				PlfType_id,
				Plf_id,
				PlfDocTypeLink_Num,
				convert(varchar(10), PlfDocTypeLink_BegDT, 120) as PlfDocTypeLink_BegDT,
				convert(varchar(10), PlfDocTypeLink_EndDT, 120) as PlfDocTypeLink_EndDT,
				convert(varchar(10), PlfDocTypeLink_GetDT, 120) as PlfDocTypeLink_GetDT
			from
				fed.v_PlfDocTypeLink (nolock)
			where
				PlfDocTypeLink_id = :PlfDocTypeLink_id
		".$filter;

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка территорий обслуживания. Метод для API.
	 */
	function getOrgServiceTerrListForAPI($data) {
		$filter = "";
		$params = array(
			'Org_id' => $data['Org_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and L.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				OST.OrgServiceTerr_id
			from
				v_OrgServiceTerr OST (nolock)
				left join v_Lpu L with (nolock) on L.Org_id = OST.Org_id
			where
				OST.Org_id = :Org_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение атрибутов территории обслуживания по идентификатору. Метод для API.
	 */
	function getOrgServiceTerrForAPI($data) {
		$filter = "";
		$params = array(
			'OrgServiceTerr_id' => $data['OrgServiceTerr_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and L.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				OST.KLCountry_id,
				OST.KLRgn_id,
				OST.KLSubRgn_id,
				OST.KLCity_id,
				OST.KLTown_id,
				OST.KLAreaType_id
			from
				v_OrgServiceTerr OST (nolock)
				left join v_Lpu L with (nolock) on L.Org_id = OST.Org_id
			where
				OrgServiceTerr_id = :OrgServiceTerr_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка руководства по МО. Метод для API.
	 */
	function getOrgHeadListForAPI($data) {
		$query = "
			select
				OrgHead_id
			from
				v_OrgHead (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение руководства организации по идентификатору. Метод для API.
	 */
	function getOrgHeadForAPI($data) {
		$filter = "";
		$params = array(
			'OrgHead_id' => $data['OrgHead_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				Person_id,
				OrgHeadPost_id,
				OrgHead_Phone, 
				OrgHead_Fax,
				OrgHead_Email,
				convert(varchar(10), OrgHead_CommissDate, 120) as OrgHead_CommissDate
			from
				v_OrgHead (nolock)
			where
				OrgHead_id = :OrgHead_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка заездов по МО. Метод для API.
	 */
	function getMOArrivalListForAPI($data) {
		$query = "
			select
				MOArrival_id
			from
				fed.v_MOArrival (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов заезда по идентификатору. Метод для API.
	 */
	function getMOArrivalForAPI($data) {
		$filter = "";
		$params = array(
			'MOArrival_id' => $data['MOArrival_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				convert(varchar(10), MOArrival_EndDT, 120) as MOArrival_EndDT,
				MOArrival_CountPerson,
				MOArrival_TreatDis
			from
				fed.v_MOArrival (nolock)
			where
				MOArrival_id = :MOArrival_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка округов горно-санитарной охраны. Метод для API.
	 */
	function getDisSanProtectionListForAPI($data) {
		$query = "
			select
				DisSanProtection_id
			from
				fed.v_DisSanProtection (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов округа горно-санитарной охраны по идентификатору. Метод для API.
	 */
	function getDisSanProtectionForAPI($data) {
		$filter = "";
		$params = array(
			'DisSanProtection_id' => $data['DisSanProtection_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				convert(varchar(10), DisSanProtection_Date, 120) as DisSanProtection_Date,
				DisSanProtection_Doc,
				DisSanProtection_Num,
				case when DisSanProtection_IsProtection = 2 then 1 else 0 end as DisSanProtection_IsProtection
			from
				fed.v_DisSanProtection (nolock)
			where
				DisSanProtection_id = :DisSanProtection_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка статусов курорта по МО. Метод для API.
	 */
	function getKurortStatusDocListForAPI($data) {
		$query = "
			select
				KurortStatusDoc_id
			from
				fed.v_KurortStatusDoc (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрубутов статуса курорта по идентификатору. Метод для API.
	 */
	function getKurortStatusDocForAPI($data) {
		$filter = "";
		$params = array('KurortStatusDoc_id' => $data['KurortStatusDoc_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				KurortStatus_id,
				convert(varchar(10), KurortStatusDoc_Date, 120) as KurortStatusDoc_Date,
				KurortStatusDoc_Doc,
				KurortStatusDoc_Num,
				case when KurortStatusDoc_IsStatus = 2 then 1 else 0 end as KurortStatusDoc_IsStatus
			from
				fed.v_KurortStatusDoc (nolock)
			where
				KurortStatusDoc_id = :KurortStatusDoc_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка типов курорта по МО. Метод для API.
	 */
	function getKurortTypeLinkListForAPI($data) {
		$query = "
			select
				KurortTypeLink_id
			from
				fed.v_KurortTypeLink (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение типа курорта по идентификатору. Метод для API.
	 */
	function getKurortTypeLinkForAPI($data) {
		$filter = "";
		$params = array('KurortTypeLink_id' => $data['KurortTypeLink_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				KurortType_id,
				convert(varchar(10), KurortTypeLink_Date, 120) as KurortTypeLink_Date,
				KurortTypeLink_Doc,
				KurortTypeLink_Num,
				case when KurortTypeLink_IsKurortTypeLink = 2 then 1 else 0 end as KurortTypeLink_IsKurortTypeLink
			from
				fed.v_KurortTypeLink (nolock)
			where
				KurortTypeLink_id = :KurortTypeLink_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка объектов инфраструктуры по МО. Метод для API.
	 */
	function getMOAreaObjectListForAPI($data) {
		$query = "
			select
				MOAreaObject_id
			from
				fed.v_MOAreaObject (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение объекта инфраструктуры по идентификатору. Метод для API.
	 */
	function getMOAreaObjectForAPI($data) {
		$filter = "";
		$params = array('MOAreaObject_id' => $data['MOAreaObject_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				DObjInfrastructure_id,
				MOAreaObject_Count,
				MOAreaObject_Member
			from
				fed.v_MOAreaObject (nolock)
			where
				MOAreaObject_id = :MOAreaObject_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка площадок, занимаемых организацией. Метод для API.
	 */
	function getMOAreaListForAPI($data) {
		$query = "
			select
				MOArea_id
			from
				fed.v_MOArea (nolock)
			where
				Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение идентификатора площадки по наименованию площадки и идентификатору участка. Метод для API.
	 */
	function getMOAreaListByNameAndMemberForAPI($data) {
		$query = "
			select
				MOArea_id
			from
				fed.v_MOArea (nolock)
			where
				MOArea_Name = :MOArea_Name
				and MOArea_Member = :MOArea_Member
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'MOArea_Name' => $data['MOArea_Name'],
			'MOArea_Member' => $data['MOArea_Member'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение атрибутов площадки, занимаемой организацией, по идентификатору. Метод для API.
	 */
	function getMOAreaForAPI($data) {
		$filter = "";
		$params = array(
			'MOArea_id' => $data['MOArea_id']
		);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				MOArea_Name,
				MOArea_Member,
				MoArea_Right,
				MoArea_Space,
				MoArea_KodTer,
				convert(varchar(10), MoArea_OrgDT, 120) as MoArea_OrgDT,
				OKATO_id,
				Address_id
			from
				fed.v_MOArea (nolock)
			where
				MOArea_id = :MOArea_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка связей площадки с транспортными узлами. Метод для API.
	 */
	function getTransportConnectListForAPI($data) {
		$filter = "";
		$params = array('MOArea_id' => $data['MOArea_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and MA.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				TR.TransportConnect_id
			from
				passport.v_TransportConnect TR (nolock)
				left join passport.MOArea MA with (nolock) on MA.MOArea_id = TR.MOArea_id
			where
				TR.MOArea_id = :MOArea_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение атрибутов связи с транспортными узлами по идентификатору. Метод для API.
	 */
	function getTransportConnectForAPI($data) {
		$filter = "";
		$params = array('TransportConnect_id' => $data['TransportConnect_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and MA.Lpu_id = :Lpu_id";
		}
		$query = "
			select
				TR.MOArea_id, 
				TR.TransportConnect_Airport,
				TR.TransportConnect_DisAirport,
				TR.TransportConnect_Heliport,
				TR.TransportConnect_DisHeliport,
				TR.TransportConnect_Station,
				TR.TransportConnect_DisStation,
				TR.TransportConnect_Railway,
				TR.TransportConnect_DisRailway,
				TR.TransportConnect_MainRoad
			from
				passport.v_TransportConnect TR (nolock)
				left join passport.MOArea MA with (nolock) on MA.MOArea_id = TR.MOArea_id
			where
				TR.TransportConnect_id = :TransportConnect_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка зданий МО. Метод для API.
	 */
	function getLpuBuildingPassListForAPI($data) {
		$filter = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['LpuBuildingPass_BuildingIdent'])) {
			$filter .= " and LpuBuildingPass_BuildingIdent = :LpuBuildingPass_BuildingIdent";
			$queryParams['LpuBuildingPass_BuildingIdent'] = $data['LpuBuildingPass_BuildingIdent'];
		}

		if (!empty($data['forIdent']) && empty($filter)) {
			return array();
		}

		$query = "
			select
				LpuBuildingPass_id
			from
				v_LpuBuildingPass (nolock)
			where
				Lpu_id = :Lpu_id
				{$filter}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получение атрибутов здания МО по идентификатору. Метод для API.
	 */
	function getLpuBuildingPassForAPI($data) {
		$filter = "";
		$params = array('LpuBuildingPass_id' => $data['LpuBuildingPass_id']);
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and Lpu_id = :Lpu_id";
		}
		$query = "
			select
				Lpu_id,
				LpuBuildingPass_AmbPlace,
				LpuBuildingPass_BuildingIdent,
				LpuBuildingPass_PowerProjBed,
				LpuBuildingPass_PowerProjViz,
				case when LpuBuildingPass_IsBalance = 2 then 1 else 0 end as LpuBuildingPass_IsBalance,
				LpuBuildingPass_Name,
				BuildingAppointmentType_id,
				LpuBuildingPass_BuildVol,
				LpuBuildingPass_TotalArea,
				MOArea_id,
				LpuBuildingPass_OfficeArea,
				LpuBuildingPass_BedArea,
				LpuBuildingPass_EffBuildVol,
				PropertyType_id, 
				LpuBuildingPass_StatPlace,
				LpuBuildingPass_MedWorkCabinet,
				LpuBuildingType_id,
				LpuBuildingPass_IsVentil,
				convert(varchar(10), LpuBuildingPass_YearRepair, 120) as LpuBuildingPass_YearRepair,
				convert(varchar(10), LpuBuildingPass_YearBuilt, 120) as LpuBuildingPass_YearBuilt,
				convert(varchar(10), LpuBuildingPass_YearProjDoc, 120) as LpuBuildingPass_YearProjDoc,
				BuildingTechnology_id,
				BuildingHoldConstrType_id,
				LpuBuildingPass_NumProj,
				BuildingOverlapType_id,
				BuildingCurrentState_id,
				BuildingType_id,
				LpuBuildingPass_Floors,
				case when LpuBuildingPass_IsDomesticGas = 2 then 1 else 0 end as LpuBuildingPass_IsDomesticGas,
				DHotWater_id, 
				DCanalization_id,
				case when LpuBuildingPass_IsAirCond = 2 then 1 else 0 end as LpuBuildingPass_IsAirCond,
				case when LpuBuildingPass_IsMedGas = 2 then 1 else 0 end as LpuBuildingPass_IsMedGas,
				DHeating_id,
				case when LpuBuildingPass_IsColdWater = 2 then 1 else 0 end as LpuBuildingPass_IsColdWater,
				LpuBuildingPass_HostLift,
				LpuBuildingPass_PassLift,
				case when LpuBuildingPass_IsElectric = 2 then 1 else 0 end as LpuBuildingPass_IsElectric,
				FuelType_id,
				DLink_id,
				case when LpuBuildingPass_IsFreeEnergy = 2 then 1 else 0 end as LpuBuildingPass_IsFreeEnergy,
				convert(varchar(10), LpuBuildingPass_ValDT, 120) as LpuBuildingPass_ValDT,
				LpuBuildingPass_WearPersent,
				LpuBuildingPass_ResidualCost,
				LpuBuildingPass_PurchaseCost,    
				LpuBuildingPass_FactVal,
				case when LpuBuildingPass_IsAutoFFSig = 2 then 1 else 0 end as LpuBuildingPass_IsAutoFFSig,
				case when LpuBuildingPass_IsFFOutSignal = 2 then 1 else 0 end as LpuBuildingPass_IsFFOutSignal,
				case when LpuBuildingPass_IsCallButton = 2 then 1 else 0 end as LpuBuildingPass_IsCallButton,
				LpuBuildingPass_CountDist,
				case when LpuBuildingPass_IsEmergExit = 2 then 1 else 0 end as LpuBuildingPass_IsEmergExit,
				LpuBuildingPass_StretProtect,
				case when LpuBuildingPass_RespProtect = 2 then 1 else 0 end as LpuBuildingPass_RespProtect,
				case when LpuBuildingPass_IsSecurAlarm = 2 then 1 else 0 end as LpuBuildingPass_IsSecurAlarm,
				case when LpuBuildingPass_IsFFWater = 2 then 1 else 0 end as LpuBuildingPass_IsFFWater,
				case when LpuBuildingPass_IsConnectFSecure = 2 then 1 else 0 end as LpuBuildingPass_IsConnectFSecure,
				case when LpuBuildingPass_IsWarningSys = 2 then 1 else 0 end as LpuBuildingPass_IsWarningSys,
				LpuBuildingPass_FSDis,
				case when LpuBuildingPass_IsBuildEmerg = 2 then 1 else 0 end as LpuBuildingPass_IsBuildEmerg,
				case when LpuBuildingPass_IsNeedCap = 2 then 1 else 0 end as LpuBuildingPass_IsNeedCap,
				case when LpuBuildingPass_IsNeedRec = 2 then 1 else 0 end as LpuBuildingPass_IsNeedRec,
				case when LpuBuildingPass_IsNeedDem = 2 then 1 else 0 end as LpuBuildingPass_IsNeedDem
			from
				v_LpuBuildingPass (nolock)
			where
				LpuBuildingPass_id = :LpuBuildingPass_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение атрибутов здания МО по идентификатору. Метод для API.
	 */
	function getLpuBuildingPassForAPIPut($data) {
		$query = "
			select
				Lpu_id,
				LpuBuildingPass_AmbPlace,
				LpuBuildingPass_BuildingIdent,
				LpuBuildingPass_PowerProjBed,
				LpuBuildingPass_PowerProjViz,
				LpuBuildingPass_IsBalance,
				LpuBuildingPass_Name,
				BuildingAppointmentType_id,
				LpuBuildingPass_BuildVol,
				LpuBuildingPass_TotalArea,
				MOArea_id,
				LpuBuildingPass_OfficeArea,
				LpuBuildingPass_BedArea,
				LpuBuildingPass_EffBuildVol,
				PropertyType_id, 
				LpuBuildingPass_StatPlace,
				LpuBuildingPass_MedWorkCabinet,
				LpuBuildingType_id,
				LpuBuildingPass_IsVentil,
				convert(varchar(10), LpuBuildingPass_YearRepair, 120) as LpuBuildingPass_YearRepair,
				convert(varchar(10), LpuBuildingPass_YearBuilt, 120) as LpuBuildingPass_YearBuilt,
				convert(varchar(10), LpuBuildingPass_YearProjDoc, 120) as LpuBuildingPass_YearProjDoc,
				BuildingTechnology_id,
				BuildingHoldConstrType_id,
				LpuBuildingPass_NumProj,
				BuildingOverlapType_id,
				BuildingCurrentState_id,
				BuildingType_id,
				LpuBuildingPass_Floors,
				LpuBuildingPass_IsDomesticGas,
				DHotWater_id, 
				DCanalization_id,
				LpuBuildingPass_IsAirCond,
				LpuBuildingPass_IsMedGas,
				DHeating_id,
				LpuBuildingPass_IsColdWater,
				LpuBuildingPass_HostLift,
				LpuBuildingPass_PassLift,
				LpuBuildingPass_IsElectric,
				FuelType_id,
				DLink_id,
				LpuBuildingPass_IsFreeEnergy,
				convert(varchar(10), LpuBuildingPass_ValDT, 120) as LpuBuildingPass_ValDT,
				LpuBuildingPass_WearPersent,
				LpuBuildingPass_ResidualCost,
				LpuBuildingPass_PurchaseCost,    
				LpuBuildingPass_FactVal,
				LpuBuildingPass_IsAutoFFSig,
				LpuBuildingPass_IsFFOutSignal,
				LpuBuildingPass_IsCallButton,
				LpuBuildingPass_CountDist,
				LpuBuildingPass_IsEmergExit,
				LpuBuildingPass_StretProtect,
				LpuBuildingPass_RespProtect,
				LpuBuildingPass_IsSecurAlarm,
				LpuBuildingPass_IsFFWater,
				LpuBuildingPass_IsConnectFSecure,
				LpuBuildingPass_IsWarningSys,
				LpuBuildingPass_FSDis,
				LpuBuildingPass_IsBuildEmerg,
				LpuBuildingPass_IsNeedCap,
				LpuBuildingPass_IsNeedRec,
				LpuBuildingPass_IsNeedDem
			from
				v_LpuBuildingPass (nolock)
			where
				LpuBuildingPass_id = :LpuBuildingPass_id
				and Lpu_id = :Lpu_id
		";

		return $this->queryResult($query, array(
			'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
			'Lpu_id' => $data['Lpu_id']
		));
	}

	/**
	 * Получение записи «Начисление износа». Метод для API.
	 */
	function getAmortizationForAPI($data, $onlyMedCard = false) {
		$filter = "";
		$params = array();

		if (!empty($data['Amortization_id'])) {
			$filter .= " and A.Amortization_id = :Amortization_id";
			$params['Amortization_id'] = $data['Amortization_id'];
		} else if (!empty($data['MedProductCard_id']) && !empty($data['Amortization_setDate'])) {
			$filter .= " and A.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
			$filter .= " and A.Amortization_setDate = :Amortization_setDate";
			$params['Amortization_setDate'] = $data['Amortization_setDate'];
		} else if($onlyMedCard && !empty($data['MedProductCard_id'])){
			$filter .= " and A.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else {
			return array();
		}
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				A.Amortization_id,
				A.MedProductCard_id,
				convert(varchar(10), A.Amortization_setDate, 120) as Amortization_setDate,
				A.Amortization_FactCost,
				A.Amortization_WearPercent,
				A.Amortization_ResidCost
			from
				passport.v_Amortization A (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = A.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение  записи «Свидетельство о проверке». Метод для API.
	 */
	function getMeasureFundCheckForAPI($data, $onlyMedCard = false) {
		$filter = "";
		$params = array();

		if (!empty($data['MeasureFundCheck_id'])) {
			$filter .= " and MFC.MeasureFundCheck_id = :MeasureFundCheck_id";
			$params['MeasureFundCheck_id'] = $data['MeasureFundCheck_id'];
		} else if (!empty($data['MedProductCard_id']) && !empty($data['MeasureFundCheck_Number']) && !empty($data['MeasureFundCheck_endDate'])) {
			$filter .= " and MFC.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
			$filter .= " and MFC.MeasureFundCheck_Number = :MeasureFundCheck_Number";
			$params['MeasureFundCheck_Number'] = $data['MeasureFundCheck_Number'];
			$filter .= " and MFC.MeasureFundCheck_endDate = :MeasureFundCheck_endDate";
			$params['MeasureFundCheck_endDate'] = $data['MeasureFundCheck_endDate'];
		} else if($onlyMedCard && !empty($data['MedProductCard_id'])){
			$filter .= " and MFC.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else {
			return array();
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				MFC.MeasureFundCheck_id,
				-- MFC.MedProductCard_id,
				MFC.MeasureFundCheck_Number,
				convert(varchar(10), MFC.MeasureFundCheck_setDate, 120) as MeasureFundCheck_setDate,
				convert(varchar(10), MFC.MeasureFundCheck_endDate, 120) as MeasureFundCheck_endDate
			from
				passport.v_MeasureFundCheck MFC (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = MFC.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение записи «Расходные материалы»
	 */
	function getConsumablesForAPI($data,$onlyMedCard = false) {
		$filter = "";
		$params = array();

		if (!empty($data['Consumables_id'])) {
			$filter .= " and C.Consumables_id = :Consumables_id";
			$params['Consumables_id'] = $data['Consumables_id'];
		} else if (!empty($data['MedProductCard_id']) && !empty($data['Consumables_Name'])) {
			$filter .= " and C.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
			$filter .= " and C.Consumables_Name = :Consumables_Name";
			$params['Consumables_Name'] = $data['Consumables_Name'];
		} else if(!empty($data['MedProductCard_id']) && $onlyMedCard){
			$filter .= " and C.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else {
			return array();
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}


		$query = "
			select
				C.Consumables_id,
				C.Consumables_Name
			from
				passport.v_Consumables C (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = C.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение Медицинского изделия. Метод для API.
	 */
	function getMedProductCardForAPI($data) {
		$filter = "";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['MedProductCard_id'])) {
			$filter .= " and MPC.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else if (!empty($data['AccountingData_InventNumber'])) {
			$filter .= " and AD.AccountingData_InventNumber = :AccountingData_InventNumber";
			$params['AccountingData_InventNumber'] = $data['AccountingData_InventNumber'];
		} else {
			return array();
		}

		$query = "
			select
				MPC.MedProductCard_id,
				MPC.MedProductClass_id,
				AD.AccountingData_InventNumber,
				MPC.MedProductCard_SerialNumber,
				MPC.MedProductCard_BoardNumber,
				MPC.MedProductCard_Phone,
				MPC.MedProductCard_Glonass,
				AD.AccountingData_RegNumber,
				MPC.LpuBuilding_id,
				MPC.LpuUnit_id,
				MPC.LpuSection_id,
				MPC.Org_id,
				convert(varchar(10), MPC.MedProductCard_begDate, 120) as MedProductCard_begDate,
				MPC.MedProductCard_UsePeriod,
				case when MPC.MedProductCard_IsEducatAct = 2 then 1 else 0 end as MedProductCard_IsEducatAct,
				case when MPC.MedProductCard_IsOutsorc = 2 then 1 else 0 end as MedProductCard_IsOutsorc,
				case when MPC.MedProductCard_IsNoAvailLpu = 2 then 1 else 0 end as MedProductCard_IsNoAvailLpu,
				convert(varchar(10), RC.RegCertificate_endDate, 120) as RegCertificate_endDate,
				convert(varchar(10), RC.RegCertificate_setDate, 120) as RegCertificate_setDate,
				RC.RegCertificate_Number,
				RC.RegCertificate_OrderNumber,
				RC.RegCertificate_MedProductName,
				RC.Org_regid,
                RC.Org_prid,
                RC.Org_decid,
				MPC.MedProductCard_Options,
				MPC.MedProductCard_OtherParam,
				case when MF.MeasureFund_IsMeasure = 2 then 1 else 0 end as MeasureFund_IsMeasure,
				MF.MeasureFund_Range,
				MF.OkeiLink_id,
				MF.MeasureFund_RegNumber,
                MF.MeasureFund_AccuracyClass,
                convert(varchar(10), AD.AccountingData_buyDate, 120) as AccountingData_buyDate,
                convert(varchar(10), AD.AccountingData_begDate, 120) as AccountingData_begDate,
                GC.GosContract_Number,
      			convert(varchar(10), AD.AccountingData_setDate, 120) as AccountingData_setDate,
				GC.FinancingType_id,
				AD.AccountingData_ProductCost,
				AD.PropertyType_id,
				convert(varchar(10), AD.AccountingData_endDate, 120) as AccountingData_endDate,
				convert(varchar(10), GC.GosContract_setDate, 120) as GosContract_setDate,
				AD.AccountingData_BuyCost,
                AD.DeliveryType_id,
                MPC.MedProductCard_DocumentTO,
                case when MPC.MedProductCard_IsContractTO = 2 then 1 else 0 end as MedProductCard_IsContractTO,
                MPC.Org_toid,
                case when MPC.MedProductCard_IsOrgLic = 2 then 1 else 0 end as MedProductCard_IsOrgLic,
                case when MPC.MedProductCard_IsLpuLic = 2 then 1 else 0 end as MedProductCard_IsLpuLic,
                case when MPC.MedProductCard_IsRepair = 2 then 1 else 0 end as MedProductCard_IsRepair,
                case when MPC.MedProductCard_IsSpisan = 2 then 1 else 0 end as MedProductCard_IsSpisan,
                convert(varchar(10), MPC.MedProductCard_RepairDate, 120) as MedProductCard_RepairDate,
                convert(varchar(10), MPC.MedProductCard_SpisanDate, 120) as MedProductCard_SpisanDate,
                MPC.MedProductCard_SetResource,
                MPC.MedProductCard_AvgProcTime
			from
				passport.v_MedProductCard MPC (nolock)
				inner join passport.v_MedProductClass MPCL with (nolock) on MPCL.MedProductClass_id = MPC.MedProductClass_id
				left join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_RegCertificate RC with (nolock) on MPC.MedProductCard_id = RC.MedProductCard_id
                left join passport.v_MeasureFund MF with (nolock) on MPC.MedProductCard_id = MF.MedProductCard_id
                left join passport.v_GosContract GC with (nolock) on MPC.MedProductCard_id = GC.MedProductCard_id
			where
				MPCL.Lpu_id = :Lpu_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка Медицинских изделий. Метод для API.
	 */
	function getMedProductCardListForAPI($data) {
		$params = array();
		$filter = "";
		
		if (!empty($data['MedProductClass_id'])) {
			$params['MedProductClass_id'] = $data['MedProductClass_id'];
		} else {
			return array();
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		
		$query = "
			select
				MPC.MedProductCard_id
			from
				passport.v_MedProductCard MPC (nolock)
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				MPC.MedProductClass_id = :MedProductClass_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение количества Медицинских изделий. Метод для API.
	 */
	function getMedProductCardCountForAPI($data) {
		$params = array();
		$filter = '';

		if (!empty($data['MedProductClass_id'])) {
			$params['MedProductClass_id'] = $data['MedProductClass_id'];
		} else {
			return array(array('cnt'=>0));
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		
		$query = "
			select
				count(MPD.MedProductCard_id) as cnt
			from
				passport.v_MedProductCard MPD (nolock)
				inner join LpuBuilding LB with (nolock) on MPD.LpuBuilding_id = LB.LpuBuilding_id
			where
				MPD.MedProductClass_id = :MedProductClass_id
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка МО. Метод для API.
	 */
	function getLpuListForAPI($data) {
		$filter = "(1=1)";
		$queryParams = array();
		if (!empty($data['Org_ids'])) {
			$filter .= " and Org_id in ('".implode("','", $data['Org_ids'])."')";
		} else {
			return array();
		}
		
		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		return $this->queryResult("
			select
				Lpu_id,
				Lpu_Name as Org_Name,
				Lpu_Nick as Org_Nick
			from
				v_Lpu (nolock)
			where
				{$filter}
		", $queryParams);
	}

	/**
	 * Получение записи «Простой МИ» по идентификатору. Метод для API.
	 */
	function getDowntimeForAPI($data) {
		$filter = "";
		$params = array();

		if (!empty($data['MedProductCard_id']) && !empty($data['Downtime_begDate']) && !empty($data['DowntimeCause_id'])) {
			$filter .= " and D.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
			$filter .= " and D.Downtime_begDate = :Downtime_begDate";
			$params['Downtime_begDate'] = $data['Downtime_begDate'];
			$filter .= " and D.DowntimeCause_id = :DowntimeCause_id";
			$params['DowntimeCause_id'] = $data['DowntimeCause_id'];
		} else if (!empty($data['MedProductCard_id'])) {
			$filter .= " and D.MedProductCard_id = :MedProductCard_id";
			$params['MedProductCard_id'] = $data['MedProductCard_id'];
		} else if (!empty($data['Downtime_id'])) {
			$filter .= " and D.Downtime_id = :Downtime_id";
			$params['Downtime_id'] = $data['Downtime_id'];
		} else {
			return array();
		}
		if (!empty($data['Lpu_id'])) {
			$filter .= " and LB.Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			select
				D.Downtime_id,
				D.MedProductCard_id,
				convert(varchar(10), D.Downtime_begDate, 120) as Downtime_begDate,
				convert(varchar(10), D.Downtime_endDate, 120) as Downtime_endDate,
				D.DowntimeCause_id
			from
				passport.v_Downtime D (nolock)
				left join passport.v_MedProductCard MPC with(nolock) on MPC.MedProductCard_id = D.MedProductCard_id
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $params);
	}

    /**
     * Сохраняет причину простоя
     */
    function saveWorkData($data) {
		if ( isset($data['WorkData_id']) && $data['WorkData_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['WorkData_id'] = 0;
            $procedure_action = "ins";
        }

        $data['WorkData_WorkPeriod'] = date('Y-m-d', strtotime($data['WorkData_WorkPeriod']));

        $query = "
            select
                COUNT (*) as [count]
            from
                passport.v_WorkData (nolock)
            where
                (MedProductCard_id = :MedProductCard_id) and
                (WorkData_WorkPeriod = :WorkData_WorkPeriod) and
                (WorkData_id <> ISNULL(:WorkData_id, 0))
        ";

        //echo getDebugSQL($query, $data);die;
        $res = $this->db->query($query, $data);

        if (is_object($res)){
            $response = $res->result('array');

            if ( $response[0]['count'] > 0 ) {
                return array(0 => array('Error_Msg' => 'Запись об эксплуатации с указанным периодом уже существует'));
            }
        } else {
            return false;
        }

        if (!isset($data['WorkData_AvgUse']))  {
        	if ($data['WorkData_DayChange'] * $data['WorkData_KolDay'] > 0) {
				$data['WorkData_AvgUse'] = $data['WorkData_CountUse'] / ($data['WorkData_DayChange'] * $data['WorkData_KolDay']);
			} else {
				$data['WorkData_AvgUse'] = null;
			}
		}


        $query = "
            declare
                @Res bigint,
                @ErrCode bigint,
                @ErrMsg varchar(4000);
            set @Res = :WorkData_id;
            exec passport.p_WorkData_" . $procedure_action . "
                @WorkData_id = @Res output,
                @MedProductCard_id = :MedProductCard_id,
                @WorkData_WorkPeriod = :WorkData_WorkPeriod,
                @WorkData_DayChange = :WorkData_DayChange,
                @WorkData_CountUse = :WorkData_CountUse,
                @WorkData_AvgUse = :WorkData_AvgUse,
                @WorkData_KolDay = :WorkData_KolDay,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @Res as WorkData_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
        ";

        //echo getDebugSQL($query, $data);
        $res = $this->db->query($query, $data);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
            if (!empty($trans_result[0]) && is_array($trans_result[0])) {
				$trans_result[0]['WorkData_AvgUse'] = $data['WorkData_AvgUse'];
			}
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

    /**
     * Сохраняет причину простоя
     */
    function saveMeasureFundCheck($data) {
        if ( isset($data['MeasureFundCheck_id']) && $data['MeasureFundCheck_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['MeasureFundCheck_id'] = 0;
            $procedure_action = "ins";
        }

        $data['MeasureFundCheck_setDate'] = date('Y-m-d', strtotime($data['MeasureFundCheck_setDate']));
        $data['MeasureFundCheck_endDate'] = date('Y-m-d', strtotime($data['MeasureFundCheck_endDate']));

        $query = "
            select
                COUNT (*) as [count]
            from
                passport.v_MeasureFundCheck (nolock)
            where
                (MedProductCard_id = :MedProductCard_id) and
                (MeasureFundCheck_setDate = :MeasureFundCheck_setDate) and
                (MeasureFundCheck_id <> ISNULL(:MeasureFundCheck_id, 0))
        ";

        //echo getDebugSQL($query, $data);die;
        $res = $this->db->query($query, $data);

        if (is_object($res)){
            $response = $res->result('array');

            if ( $response[0]['count'] > 0 ) {
                return array(0 => array('Error_Msg' => 'Запись об эксплуатации с указанным периодом уже существует'));
            }
        } else {
            return false;
        }

        $query = "
            declare
                @Res bigint,
                @ErrCode bigint,
                @ErrMsg varchar(4000);
            set @Res = :MeasureFundCheck_id;
            exec passport.p_MeasureFundCheck_" . $procedure_action . "
                @MeasureFundCheck_id = @Res output,
                @MedProductCard_id = :MedProductCard_id,
                @MeasureFundCheck_setDate = :MeasureFundCheck_setDate,
                @MeasureFundCheck_Number = :MeasureFundCheck_Number,
                @MeasureFundCheck_endDate = :MeasureFundCheck_endDate,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @Res as MeasureFundCheck_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
        ";

        //echo getDebugSQL($query, $data);
        $res = $this->db->query($query, $data);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

    /**
     * Сохраняет описание износа
     */
    function saveAmortization($data) {
		if ( isset($data['Amortization_id']) && $data['Amortization_id'] > 0 ) {
            $procedure_action = "upd";
        }
        else {
            $data['Amortization_id'] = 0;
            $procedure_action = "ins";
        }

        $query = "
            select
                COUNT (*) as [count]
            from
                passport.v_Amortization (nolock)
            where
                (MedProductCard_id = :MedProductCard_id) and
                (cast(Amortization_setDate as varchar(10)) = :Amortization_setDate ) and
                (Amortization_id <> ISNULL(:Amortization_id, 0))
        ";

        //echo getDebugSQL($query, $data);die;
        $res = $this->db->query($query, $data);

        if (is_object($res)){
            $response = $res->result('array');

            if ( $response[0]['count'] > 0 ) {
                return array(0 => array('Error_Msg' => 'Описание износа с веденной датой оценки уже существует'));
            }
        } else {
            return false;
        }

        $data['Amortization_setDate'] = date('Y-m-d', strtotime($data['Amortization_setDate']));

        $query = "
            declare
                @Res bigint,
                @ErrCode bigint,
                @ErrMsg varchar(4000);
            set @Res = :Amortization_id;
            exec passport.p_Amortization_" . $procedure_action . "
                @Amortization_id = @Res output,
                @Amortization_setDate = :Amortization_setDate,
                @Amortization_WearPercent = :Amortization_WearPercent,
                @Amortization_FactCost = :Amortization_FactCost,
                @Amortization_ResidCost = :Amortization_ResidCost,
                @MedProductCard_id = :MedProductCard_id,
                @pmUser_id = :pmUser_id,
                @Error_Code = @ErrCode output,
                @Error_Message = @ErrMsg output;
            select @Res as Amortization_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
        ";

        //echo getDebugSQL($query, $data);
        $res = $this->db->query($query, $data);

        if ( is_object($res) ) {
            $trans_result = $res->result('array');
        }
        else {
            $trans_result = false;
        }

        return $trans_result;
    }

	/**
	 *	Загрузка операции с лицензиями
	 */
    function loadLpuLicenceOperationLink($data)
    {
        $params = array('LpuLicence_id' => $data['LpuLicence_id']);
        $filter = "(1=1)";

        if (isset($data['LpuLicenceOperationLink_id']))
        {
            $filter .= ' and LLOL.LpuLicenceOperationLink_id = :LpuLicenceOperationLink_id';
            $params['LpuLicenceOperationLink_id'] = $data['LpuLicenceOperationLink_id'];
        }
        if(empty($data['fromAPI'])){
        	$filter .= ' and LO.LicsOperation_Code not in (1, 2, 3)';
        }
		if (!empty($data['Lpu_id'])) {
			$params['Lpu_id'] = $data['Lpu_id'];
			$filter .= " and LL.Lpu_id = :Lpu_id";
		}

        $query = "
            Select
                LLOL.LpuLicenceOperationLink_id,
                LLOL.LpuLicence_id,
                rtrim(isNull(convert(varchar,cast(LLOL.LpuLicenceOperationLink_Date as datetime),104),'')) as LpuLicenceOperationLink_Date,
                LO.LicsOperation_id,
                LO.LicsOperation_Name
            from fed.v_LpuLicenceOperationLink LLOL with (nolock)
            left join fed.v_LicsOperation LO with (nolock) on LLOL.LicsOperation_id = LO.LicsOperation_id
			left join dbo.LpuLicence LL with (nolock) on LLOL.LpuLicence_id = LL.LpuLicence_id
            where
                LLOL.LpuLicence_id = :LpuLicence_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Загрузка операции с лицензиями. Метод для API.
	 */
    function loadLpuLicenceOperationLinkById($data)
    {
        $query = "
            Select
                LLOL.LpuLicenceOperationLink_id,
                LLOL.LpuLicence_id,
                rtrim(isNull(convert(varchar,cast(LLOL.LpuLicenceOperationLink_Date as datetime),104),'')) as LpuLicenceOperationLink_Date,
                LLOL.LicsOperation_id
            from fed.v_LpuLicenceOperationLink LLOL with (nolock)
            	left join v_LpuLicence LL with (nolock) on LL.LpuLicence_id = LLOL.LpuLicence_id
            where
                LLOL.LpuLicenceOperationLink_id = :LpuLicenceOperationLink_id
                and LL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка операции с лицензиями
	 */
	function loadLpuLicenceLink($data)
	{
		$params = array('LpuLicence_id' => $data['LpuLicence_id']);
		$filter = "(1=1)";

		if (isset($data['LpuLicenceLink_id'])) {
			$filter .= ' and LLL.LpuLicenceLink_id = :LpuLicenceLink_id';
			$params['LpuLicenceLink_id'] = $data['LpuLicenceLink_id'];
		}

		$query = "
			Select
				LLL.LpuLicenceLink_id,
				LLL.LpuLicence_id,
				LSP.LpuSectionProfile_id,
				LSP.LpuSectionProfile_Code,
				LSP.LpuSectionProfile_Name
			from v_LpuLicenceLink LLL with (nolock)
			left join v_LpuSectionProfile LSP with (nolock) on LLL.LpuSectionProfile_id = LSP.LpuSectionProfile_id
			where
				LpuLicence_id = :LpuLicence_id and
				{$filter}
		";

		//echo getDebugSQL($query, $params);
		$result = $this->db->query($query, $params);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Загрузка операции с лицензиями (Казахстан)
	 */
    function loadLpuLicenceDop($data)
    {
        $params = array('LpuLicence_id' => $data['LpuLicence_id']);
        $filter = "(1=1)";

        if (isset($data['LpuLicenceDop_id']))
        {
            $filter .= ' and LpuLicenceDop_id = :LpuLicenceDop_id';
            $params['LpuLicenceDop_id'] = $data['LpuLicenceDop_id'];
        }

        $query = "
            Select
                LpuLicenceDop_id,
                LpuLicence_id,
                LpuLicenceDop_Num,
                rtrim(isNull(convert(varchar,cast(LpuLicenceDop_setDate as datetime),104),'')) as LpuLicenceDop_setDate
            from passport101.LpuLicenceDop with (nolock)
            where
                LpuLicence_id = :LpuLicence_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка расходных материалов
	 */
    function loadConsumables($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);
        $filter = "(1=1)";

        if (isset($data['Consumables_id']))
        {
            $filter .= ' and Consumables_id = :Consumables_id';
            $params['Consumables_id'] = $data['Consumables_id'];
        }

        $query = "
            Select
                Consumables_id,
                MedProductCard_id,
                Consumables_Name
            from
                passport.v_Consumables with (nolock)
            where
                MedProductCard_id = :MedProductCard_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка расходных материалов
	 */
    function loadDowntime($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);
        $filter = "(1=1)";

        if (isset($data['Downtime_id']))
        {
            $filter .= ' and DT.Downtime_id = :Downtime_id';
            $params['Downtime_id'] = $data['Downtime_id'];
        }

        $query = "
            Select
                DT.Downtime_id,
                DT.MedProductCard_id,
                DC.DowntimeCause_Name,
                RTrim(IsNull(convert(varchar,cast(DT.Downtime_begDate as datetime),104),'')) as Downtime_begDate,
                RTrim(IsNull(convert(varchar,cast(DT.Downtime_endDate as datetime),104),'')) as Downtime_endDate,
                DT.DowntimeCause_id
            from
                passport.v_Downtime DT with (nolock)
                left join passport.v_DowntimeCause DC with (nolock) on DC.DowntimeCause_id = DT.DowntimeCause_id
            where
                DT.MedProductCard_id = :MedProductCard_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка эксплуатационных данных
	 */
    function loadWorkData($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);
        $filter = "(1=1)";

        if (isset($data['WorkData_id']))
        {
            $filter .= ' and WorkData_id = :WorkData_id';
            $params['WorkData_id'] = $data['WorkData_id'];
        }

        $query = "
            Select
                WorkData_id,
                MedProductCard_id,
                RTrim(IsNull(convert(varchar,cast(WorkData_WorkPeriod as datetime),104),'')) as WorkData_WorkPeriod,
                WorkData_DayChange,
                WorkData_CountUse,
                WorkData_KolDay,
                WorkData_AvgUse
            from
                passport.v_WorkData with (nolock)
            where
                MedProductCard_id = :MedProductCard_id and
                {$filter}
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка эксплуатационных данных
	 */
    function loadMeasureFundCheck($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);
        $filter = "(1=1)";

        if (isset($data['MeasureFundCheck_id']))
        {
            $filter .= ' and MeasureFundCheck_id = :MeasureFundCheck_id';
            $params['MeasureFundCheck_id'] = $data['MeasureFundCheck_id'];
        }

        $query = "
            Select
                MeasureFundCheck_id,
                MedProductCard_id,
                RTrim(IsNull(convert(varchar,cast(MeasureFundCheck_setDate as datetime),104),'')) as MeasureFundCheck_setDate,
                MeasureFundCheck_Number,
                RTrim(IsNull(convert(varchar,cast(MeasureFundCheck_endDate as datetime),104),'')) as MeasureFundCheck_endDate
            from
                passport.v_MeasureFundCheck with (nolock)
            where
                MedProductCard_id = :MedProductCard_id and
                {$filter}
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка данных медицинского изделия
	 */
    function loadMedProductCardData($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);

		$ufaFields = "";
		if( getRegionNick() == 'ufa') {
			$ufaFields = "
				MedProductCard_IsAvailibleSpecialists,
				MedProductCard_IsClockMode,
			";
		}

        $query = "
            Select
                MPC.MedProductCard_id,
                MPC.MedProductClass_id,
                O.Org_Name,
      			RTrim(IsNull(convert(varchar,cast(MPC.MedProductCard_begDate as datetime),104),'')) as MedProductCard_begDate,
                MPC.MedProductCard_UsePeriod,
                MPC.MedProductCard_SerialNumber,
                MPC.MedProductCard_BoardNumber,
                MPC.MedProductCard_Phone,
                MPC.MedProductCard_Glonass,
                MPC.MedProductCard_Options,
                MPC.MedProductCard_OtherParam,
                MPC.LpuBuilding_id,
                MPC.Org_id,
                MPC.LpuSection_id,
                MPC.LpuUnit_id,
                MPC.MedProductCard_IsRepair,
                MPC.MedProductCard_IsSpisan,
                MPC.MedProductCard_IsOrgLic,
                MPC.MedProductCard_IsLpuLic,
				MPC.MedProductCard_IsContractTO,
				MPC.MedProductCard_UsePeriod,
      			RTrim(IsNull(convert(varchar,cast(MPC.MedProductCard_RepairDate as datetime),104),'')) as MedProductCard_RepairDate,
      			RTrim(IsNull(convert(varchar,cast(MPC.MedProductCard_SpisanDate as datetime),104),'')) as MedProductCard_SpisanDate,
                MPC.MedProductCard_DocumentTO,
                MPC.MedProductCard_SetResource,
                MPC.MedProductCard_AvgProcTime,
                MPC.MedProductCard_IsEducatAct,
                MPC.MedProductCard_IsOutsorc,
				MPC.MedProductCard_IsNoAvailLpu,
				MPC.MedProductCard_IsNotFRMO,
                LB.LpuBuilding_Name,
                AD.AccountingData_InventNumber,
                AD.AccountingData_RegNumber,
      			RTrim(IsNull(convert(varchar,cast(AD.AccountingData_buyDate as datetime),104),'')) as AccountingData_buyDate,
      			RTrim(IsNull(convert(varchar,cast(AD.AccountingData_setDate as datetime),104),'')) as AccountingData_setDate,
      			RTrim(IsNull(convert(varchar,cast(AD.AccountingData_begDate as datetime),104),'')) as AccountingData_begDate,
      			RTrim(IsNull(convert(varchar,cast(AD.AccountingData_endDate as datetime),104),'')) as AccountingData_endDate,
                AD.AccountingData_BuyCost,
                AD.AccountingData_ProductCost,
                AD.DeliveryType_id,
                AD.PropertyType_id,
      			RTrim(IsNull(convert(varchar,cast(RC.RegCertificate_setDate as datetime),104),'')) as RegCertificate_setDate,
      			RTrim(IsNull(convert(varchar,cast(RC.RegCertificate_endDate as datetime),104),'')) as RegCertificate_endDate,
                RC.RegCertificate_Number,
                RC.RegCertificate_OrderNumber,
                RC.RegCertificate_MedProductName,
                MPC.Org_toid,
                RC.Org_regid,
                RC.Org_prid,
                RC.Org_decid,
                O_reg.Org_Name as Org_regid_Name,
                O_prid.Org_Name as Org_prid_Name,
                O_dec.Org_Name as Org_dec_Name,
                O_toid.Org_Name as Org_toid_Name,
                MF.MeasureFund_Range,
                IsMeasure.YesNo_Code as MeasureFund_IsMeasure,
                MF.MeasureFund_RegNumber,
                MF.MeasureFund_AccuracyClass,
                GC.GosContract_Number,
      			RTrim(IsNull(convert(varchar,cast(GC.GosContract_setDate as datetime),104),'')) as GosContract_setDate,
                GC.FinancingType_id,
                MF.OkeiLink_id,
                DT.Downtime_Comment,
				MPT.MedProductType_Code,
				MPC.MedProductCauseType_id,
				MPC.MedProductCard_Cause,
				$ufaFields
				MPC.PrincipleWorkType_id,
				MPC.MedProductClassForm_fsubid,
				MPC.MedProductClassForm_secid,
				MPC.MedProductClassForm_ssubid,
				MPC.MedProductClassForm_strid,
				MPC.MedProductCard_AETitle,
				MPC.MedProductCard_IsWorkList,
				MPC.LpuEquipmentPacs_id
            from
                passport.v_MedProductCard MPC with (nolock)
                left join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_RegCertificate RC with (nolock) on MPC.MedProductCard_id = RC.MedProductCard_id
                left join passport.v_MeasureFund MF with (nolock) on MPC.MedProductCard_id = MF.MedProductCard_id
                left join passport.v_GosContract GC with (nolock) on MPC.MedProductCard_id = GC.MedProductCard_id
                left join passport.v_Downtime DT with (nolock) on MPC.MedProductCard_id = DT.MedProductCard_id
				left join passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id                
				left join passport.v_MedProductType MPT with (nolock) on MPT.MedProductType_id = MPCl.MedProductType_id
				left join passport.v_MedProductCauseType MPCT with (nolock) on MPCT.MedProductCauseType_id = MPC.MedProductCauseType_id
                left join v_Org O with (nolock) on O.Org_id = MPC.Org_id
                left join v_Org O_reg with (nolock) on O_reg.Org_id = RC.Org_regid
                left join v_Org O_prid with (nolock) on O_prid.Org_id = RC.Org_prid
                left join v_Org O_dec with (nolock) on O_dec.Org_id = RC.Org_decid
                left join v_Org O_toid with (nolock) on O_toid.Org_id = MPC.Org_toid
                left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = MPC.LpuBuilding_id
				left join v_YesNo IsMeasure with(nolock) on IsMeasure.YesNo_id = MF.MeasureFund_IsMeasure
            where
                MPC.MedProductCard_id = :MedProductCard_id
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            $response =  $result->result('array');

            foreach (array('MedProductCard_IsRepair','MedProductCard_IsSpisan', 'MedProductCard_IsContractTO', 'MedProductCard_IsOrgLic', 'MedProductCard_IsLpuLic','MedProductCard_IsEducatAct', 'MedProductCard_IsNoAvailLpu', 'MedProductCard_IsOutsorc', 'MedProductCard_IsWorkList', 'MedProductCard_IsNotFRMO') as $row) {
                foreach ($response as $key => &$value) {
                    if ($value[$row] == 2) {
                        $value[$row] = 1;
                    } else if ($value[$row] == 1) {
                        $value[$row] = 0;
                    }
                }
            }

            return $response;
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка оценки износа
	 */
    function loadAmortization($data)
    {
        $params = array('MedProductCard_id' => $data['MedProductCard_id']);
        $filter = "(1=1)";

        if (isset($data['Amortization_id']))
        {
            $filter .= ' and CO.Amortization_id = :Amortization_id';
            $params['Amortization_id'] = $data['Amortization_id'];
        }

        $query = "
            Select
                Amortization_id,
                MedProductCard_id,
    			RTrim(IsNull(convert(varchar,cast(Amortization_setDate as datetime),104),'')) as Amortization_setDate,
                Amortization_FactCost,
                Amortization_WearPercent,
                Amortization_ResidCost
            from
                passport.v_Amortization with (nolock)
            where
                MedProductCard_id = :MedProductCard_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            $response = $result->result('array');

            //обрезаем лишние символы у % износа
            if(is_array($response) && count($response) > 0) {
                foreach ($response as $key => &$value) {
                    if (!empty($value['Amortization_WearPercent'])) {
                        $value['Amortization_WearPercent'] = substr($value['Amortization_WearPercent'], 0, strlen($value['Amortization_WearPercent']) - 2) ;
                    }

                    if (!empty($value['Amortization_FactCost']) && substr($value['Amortization_FactCost'], 0, 1) == '.') {
                        $value['Amortization_FactCost'] = '0'.$value['Amortization_FactCost'];
                    }

                    if (!empty($value['Amortization_WearPercent']) && substr($value['Amortization_WearPercent'], 0, 1) == '.') {
                        $value['Amortization_WearPercent'] = '0'.$value['Amortization_WearPercent'];
                    }

                    if (!empty($value['Amortization_ResidCost']) && substr($value['Amortization_ResidCost'], 0, 1) == '.') {
                        $value['Amortization_ResidCost'] = '0'.$value['Amortization_ResidCost'];
                    }
                }
            }

            return $response;
        }
        else {
            return false;
        }
    }

	/**
	 *	Загрузка видов лицензий
	 */
    function loadLpuLicenceProfile($data)
    {
        $params = array('LpuLicence_id' => $data['LpuLicence_id']);
        $filter = "(1=1)";

        if (!empty($data['LpuLicenceProfile_id']) && $data['LpuLicenceProfile_id'] > 0){
            $filter .= ' and LLP.LpuLicenceProfile_id = :LpuLicenceProfile_id';
            $params['LpuLicenceProfile_id'] = $data['LpuLicenceProfile_id'];
        }

        $query = "
            Select
                LLP.LpuLicenceProfile_id,
                LLP.LpuLicence_id,
                LLP.LpuLicenceProfileType_id,
                LLPT.LpuLicenceProfileType_Name,
                LLPT.LpuLicenceProfileType_Code
            from fed.v_LpuLicenceProfile LLP with (nolock)
            left join fed.v_LpuLicenceProfileType LLPT with (nolock) on LLP.LpuLicenceProfileType_id = LLPT.LpuLicenceProfileType_id
            where
                LpuLicence_id = :LpuLicence_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Загрузка вида лицензий. Метод для API.
	 */
    function loadLpuLicenceProfileById($data)
    {
        $query = "
            Select
                LLP.LpuLicenceProfile_id,
                LLP.LpuLicence_id,
                LLP.LpuLicenceProfileType_id
            from fed.v_LpuLicenceProfile LLP with (nolock)
            	left join v_LpuLicence LL with (nolock) on LL.LpuLicence_id = LLP.LpuLicence_id
            where
                LLP.LpuLicenceProfile_id = :LpuLicenceProfile_id
                and LL.Lpu_id = :Lpu_id
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
    function loadMkb10CodeClass() {

        $sql = "
				select distinct
                    Mkb10Code_pid
                from
                    fed.Mkb10Code with (nolock)
                where Mkb10Code_pid is not null
                order by Mkb10Code_pid
        ";

        $res = $this->db->query($sql);

        if ( is_object($res) )
            return $res->result('array');
        else
            return false;
    }
	/**
	* Сохраняет данные формы периода ЛЛО
	*/
	function saveOrgWorkPeriod($data) {

		$queryParams = array(
			'Org_id' => $data['Org_id'],
			'OrgWorkPeriod_id' => $data['OrgWorkPeriod_id'],
			'OrgWorkPeriod_begDate' => $data['OrgWorkPeriod_begDate'],
			'OrgWorkPeriod_endDate' => $data['OrgWorkPeriod_endDate'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		if ( $data['OrgWorkPeriod_endDate'] && $data['OrgWorkPeriod_begDate'] > $data['OrgWorkPeriod_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}
		
		$query = "Select
			COUNT (*) as [count]
				from OrgWorkPeriod (nolock)
			where
				[Org_id] = :Org_id and
				([OrgWorkPeriod_begDate] <= :OrgWorkPeriod_endDate or :OrgWorkPeriod_endDate is null) and
				([OrgWorkPeriod_endDate] >= :OrgWorkPeriod_begDate or [OrgWorkPeriod_endDate] is null) and
				([OrgWorkPeriod_id] != :OrgWorkPeriod_id or :OrgWorkPeriod_id is null)";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Периоды работы в системе Промед не могут пересекаться.'));
		}		

		if ($trans_good === true) {

			if ( !isset($data['OrgWorkPeriod_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :OrgWorkPeriod_id;
				exec p_OrgWorkPeriod_" . $procedure_action . "
					@Org_id = :Org_id,
					@OrgWorkPeriod_id = @Res output,
					@OrgWorkPeriod_begDate = :OrgWorkPeriod_begDate,
					@OrgWorkPeriod_endDate = :OrgWorkPeriod_endDate,	 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as OrgWorkPeriod_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;

	}
	/**
	* Сохраняет данные формы периода ЛЛО
	*/
	function saveLpuPeriodDLO($data) {

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuUnit_id' => $data['LpuUnit_id'],
			'LpuPeriodDLO_id' => $data['LpuPeriodDLO_id'],
			'LpuPeriodDLO_begDate' => $data['LpuPeriodDLO_begDate'],
			'LpuPeriodDLO_endDate' => $data['LpuPeriodDLO_endDate'],
			'LpuPeriodDLO_Code' => $data['LpuPeriodDLO_Code'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		if ( $data['LpuPeriodDLO_endDate'] && $data['LpuPeriodDLO_begDate'] > $data['LpuPeriodDLO_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}
		
		$query = "Select
			COUNT (*) as [count]
				from LpuPeriodDLO (nolock)
			where
				[Lpu_id] = :Lpu_id and
				case
					when :LpuUnit_id is null then 1
					when [LpuUnit_id] = :LpuUnit_id then 1
					else 0
				end = 1 and
				([LpuPeriodDLO_begDate] <= :LpuPeriodDLO_endDate or :LpuPeriodDLO_endDate is null) and
				([LpuPeriodDLO_endDate] >= :LpuPeriodDLO_begDate or [LpuPeriodDLO_endDate] is null) and
				([LpuPeriodDLO_id] != :LpuPeriodDLO_id or :LpuPeriodDLO_id is null)";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Периоды по ЛЛО не могут пересекаться.'));
		}		

		if ($trans_good === true) {

			if ( !isset($data['LpuPeriodDLO_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuPeriodDLO_id;
				exec p_LpuPeriodDLO_" . $procedure_action . "
					@Lpu_id = :Lpu_id,
					@LpuUnit_id = :LpuUnit_id,
					@LpuPeriodDLO_id = @Res output,
					@LpuPeriodDLO_begDate = :LpuPeriodDLO_begDate,
					@LpuPeriodDLO_endDate = :LpuPeriodDLO_endDate,
					@LpuPeriodDLO_Code = :LpuPeriodDLO_Code,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuPeriodDLO_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;

	}
	
	/**
	* Сохраняет данные формы периода ДМС
	*/
	function saveLpuPeriodDMS($data) {

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuPeriodDMS_id' => $data['LpuPeriodDMS_id'],
			'LpuPeriodDMS_begDate' => $data['LpuPeriodDMS_begDate'],
			'LpuPeriodDMS_endDate' => $data['LpuPeriodDMS_endDate'],
			'LpuPeriodDMS_DogNum' => $data['LpuPeriodDMS_DogNum'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		if ( $data['LpuPeriodDMS_endDate'] && $data['LpuPeriodDMS_begDate'] > $data['LpuPeriodDMS_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}	
		
		$query = "Select
			COUNT (*) as [count]
				from LpuPeriodDMS (nolock)
			where
				[Lpu_id] = :Lpu_id and
				([LpuPeriodDMS_begDate] <= :LpuPeriodDMS_endDate or :LpuPeriodDMS_endDate is null) and
				([LpuPeriodDMS_endDate] >= :LpuPeriodDMS_begDate or [LpuPeriodDMS_endDate] is null) and
				([LpuPeriodDMS_id] != :LpuPeriodDMS_id or :LpuPeriodDMS_id is null)";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Периоды по ДМС не могут пересекаться.'));
		}		

		if ($trans_good === true) {

			if ( !isset($data['LpuPeriodDMS_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuPeriodDMS_id;
				exec p_LpuPeriodDMS_" . $procedure_action . "
					@Lpu_id = :Lpu_id,
					@LpuPeriodDMS_id = @Res output,
					@LpuPeriodDMS_begDate = :LpuPeriodDMS_begDate,
					@LpuPeriodDMS_endDate = :LpuPeriodDMS_endDate,
					@LpuPeriodDMS_DogNum = :LpuPeriodDMS_DogNum,	 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuPeriodDMS_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;

	}
	
	/**
	* Сохраняет данные формы периода Фондодержания
	*/
	function saveLpuPeriodFondHolder($data) {

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuRegionType_id' => $data['LpuRegionType_id'],
			'LpuPeriodFondHolder_id' => $data['LpuPeriodFondHolder_id'],
			'LpuPeriodFondHolder_begDate' => $data['LpuPeriodFondHolder_begDate'],
			'LpuPeriodFondHolder_endDate' => $data['LpuPeriodFondHolder_endDate'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$procedure_action = '';	
		
		$trans_good = true;
		$trans_result = array();
		
		if ( $data['LpuPeriodFondHolder_endDate'] && $data['LpuPeriodFondHolder_begDate'] > $data['LpuPeriodFondHolder_endDate'] ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Дата исключения не может быть раньше даты включения.'));
		}

		if ( $this->regionNick != 'perm' ) {
			$LpuRegionType_SysNick = $this->getFirstResultFromQuery("SELECT top 1 LpuRegionType_SysNick FROM v_LpuRegionType with (nolock) WHERE LpuRegionType_id = :LpuRegionType_id", $data);
		}

		if ( $this->regionNick == 'perm' || !in_array($LpuRegionType_SysNick, array('slug','psdet','pspod','psvz')) ) {
			// Проверка на наличие открытых участков по выбранному типу участка
			$query = "
				select top 1 1
				from v_LpuRegion LpuRegion (nolock)
				where 
					Lpu_id = :Lpu_id and
					LpuRegionType_id = :LpuRegionType_id and 
					(LpuRegion_begDate <= :LpuPeriodFondHolder_endDate or :LpuPeriodFondHolder_endDate is null) and
					(LpuRegion_endDate >= :LpuPeriodFondHolder_begDate or LpuRegion_endDate is null)
			";
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$trans_good = false;
				$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
			}
			$response = $result->result('array');
			
			if ( count($response ) == 0 ) {
				$LpuRegionType_Name = $this->getFirstResultFromQuery("SELECT LpuRegionType_Name FROM v_LpuRegionType with (nolock) WHERE LpuRegionType_id = :LpuRegionType_id", $data);
				if($LpuRegionType_Name != 'Стоматологический' || $this->regionNick=='perm')
				{
					$trans_good = false;
					$trans_result = array(0 => array('Error_Msg' => 'Невозможно добавить период по ' . ($this->regionNick == 'perm' ? 'фондодержанию' : 'участковой службе') . '. В МО отсутствуют открытые участки с типом «'.$LpuRegionType_Name.'»'));
				}
			}
		}
		
		// Проверка на периоды
		$query = "Select
			COUNT (*) as [count]
				from LpuPeriodFondHolder (nolock)
			where
				[Lpu_id] = :Lpu_id and
				([LpuPeriodFondHolder_begDate] <= :LpuPeriodFondHolder_endDate or :LpuPeriodFondHolder_endDate is null) and
				([LpuPeriodFondHolder_endDate] >= :LpuPeriodFondHolder_begDate or [LpuPeriodFondHolder_endDate] is null) and
				([LpuPeriodFondHolder_id] != :LpuPeriodFondHolder_id or :LpuPeriodFondHolder_id is null) and
				[LpuRegionType_id] = :LpuRegionType_id";

		$result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');
		
		if ( $response[0]['count'] > 0 ) {
			$trans_good = false;
			$trans_result = array(0 => array('Error_Msg' => 'Периоды по Фондодержанию с одинаковым типом участка не могут пересекаться.'));
		}
		
		if ($trans_good === true) {

			if ( !isset($data['LpuPeriodFondHolder_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000),
					@Lpu_pid bigint;
				set @Res = :LpuPeriodFondHolder_id;
				set @Lpu_pid = (select top 1 Lpu_pid from Lpu (nolock) where Lpu_id = :Lpu_id);
				exec p_LpuPeriodFondHolder_" . $procedure_action . "
					@Lpu_id = :Lpu_id,
					@Lpu_pid = @Lpu_pid,
					@LpuPeriodFondHolder_id = @Res output,
					@LpuRegionType_id = :LpuRegionType_id,
					@LpuPeriodFondHolder_begDate = :LpuPeriodFondHolder_begDate,
					@LpuPeriodFondHolder_endDate = :LpuPeriodFondHolder_endDate,	 
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuPeriodFondHolder_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			
			$res = $this->db->query($query, $queryParams);

			if ( is_object($res) ) {
				$trans_result = $res->result('array');
			}
			else {
				$trans_result = false;
			}

		}
		
		return $trans_result;

	}

	
	/**
	* Сохраняет данные формы лицензии ЛПУ
	*/
	function saveLpuLicence($data) {

		$this->beginTransaction();
		$procedure_action = '';

        $queryParams = array(
            'Server_id'=>$data['Server_id'],
            'Lpu_id' => $data['Lpu_id'],
            'LpuLicence_id' => $data['LpuLicence_id'],
            'Org_id' => $data['Org_id'],
            'VidDeat_id' => $data['VidDeat_id'],
            'LpuLicence_Ser' => $data['LpuLicence_Ser'],
            'LpuLicence_Num' => $data['LpuLicence_Num'],
            'LpuLicence_setDate' => $data['LpuLicence_setDate'],
            'LpuLicence_RegNum' => $data['LpuLicence_RegNum'],
            'KLCountry_id' => $data['KLCountry_id'],
            'KLRgn_id' => $data['KLRgn_id'],
            'KLSubRgn_id' => $data['KLSubRgn_id'],
            'KLCity_id' => $data['KLCity_id'],
            'KLTown_id' => $data['KLTown_id'],
            'LpuLicence_begDate' => $data['LpuLicence_begDate'],
            'LpuLicence_endDate' => $data['LpuLicence_endDate'],
            'pmUser_id' => $data['pmUser_id']
        );

        $query = "
            select
                COUNT (*) as [count]
            from
                v_LpuLicence (nolock)
            where
                (LpuLicence_setDate = :LpuLicence_setDate) and
                (LpuLicence_Num = :LpuLicence_Num) and
                LpuLicence_id <> ISNULL(:LpuLicence_id, 0) and
                Lpu_id = :Lpu_id
        ";

        //echo getDebugSQL($query, $queryParams); exit;
        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( $response[0]['count'] > 0) {
            return array(0 => array('Error_Msg' => 'Запись с введенными данными уже существует.'));
            $this->rollbackTransaction();
        }

		if ( !isset($data['LpuLicence_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuLicence_id;
			exec p_LpuLicence_" . $procedure_action . "
				@LpuLicence_id = @Res output,
				@Server_id = :Server_id,
				@Lpu_id = :Lpu_id,
				@Org_id = :Org_id,
				@VidDeat_id = :VidDeat_id,
				@LpuLicence_Ser = :LpuLicence_Ser,
				@LpuLicence_Num = :LpuLicence_Num,
				@LpuLicence_setDate = :LpuLicence_setDate,
				@LpuLicence_RegNum = :LpuLicence_RegNum,
				@KLCountry_id = :KLCountry_id,
				@KLRgn_id = :KLRgn_id,
				@KLSubRgn_id = :KLSubRgn_id,
				@KLCity_id = :KLCity_id,
				@KLTown_id = :KLTown_id,
				@LpuLicence_begDate = :LpuLicence_begDate,
				@LpuLicence_endDate = :LpuLicence_endDate, 
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as LpuLicence_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$result = $this->db->query($query, $queryParams);

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
        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
            $this->rollbackTransaction();
            return $queryResponse;
        }

        $response = $queryResponse[0];

        // Обрабатываем список видов лицензии
        if ( !empty($data['LpuLicenceProfileData']) ) {
            $LpuLicenceProfileData = json_decode($data['LpuLicenceProfileData'], true);

            if ( is_array($LpuLicenceProfileData) ) {
                $isLpuLicenceProfileRepeat = array();
                for ( $i = 0; $i < count($LpuLicenceProfileData); $i++ ) {
                    if (!empty($LpuLicenceProfileData[$i]['LpuLicenceProfileType_Code'])) {

                        $LpuLicenceProfile = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'LpuLicence_id' => $response['LpuLicence_id']
                        );

                        if ( empty($LpuLicenceProfileData[$i]['LpuLicenceProfile_id']) || !is_numeric($LpuLicenceProfileData[$i]['LpuLicenceProfile_id']) ) {
                            continue;
                        }

                        if ( empty($LpuLicenceProfileData[$i]['LpuLicenceProfileType_id']) || !is_numeric($LpuLicenceProfileData[$i]['LpuLicenceProfileType_id']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        if ( empty($LpuLicenceProfileData[$i]['LpuLicenceProfileType_Code']) || !is_numeric($LpuLicenceProfileData[$i]['LpuLicenceProfileType_Code']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано количество объектов';
                                return array($response);
                            */
                        }


                        $LpuLicenceProfile['LpuLicenceProfile_id'] = $LpuLicenceProfileData[$i]['LpuLicenceProfile_id'];
                        $LpuLicenceProfile['LpuLicenceProfileType_id'] = $LpuLicenceProfileData[$i]['LpuLicenceProfileType_id'];
                        $LpuLicenceProfile['LpuLicenceProfileType_Code'] = $LpuLicenceProfileData[$i]['LpuLicenceProfileType_Code'];

                        $queryResponse = $this->saveLpuLicenceProfile($LpuLicenceProfile);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при ' . ($LpuLicenceProfileData[$i]['RecordStatus_Code'] == 3 ? 'удалении' : 'сохранении') . ' объекта комфортности';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }

                        //У одной лицензии не может быть одинаковые виды
                        if (in_array($LpuLicenceProfileData[$i]['LpuLicenceProfileType_id'], $isLpuLicenceProfileRepeat)) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Нельзя сохранить одинаковые виды лицензии.';
                            return array($response);
                        } else {
                            array_push($isLpuLicenceProfileRepeat, $LpuLicenceProfileData[$i]['LpuLicenceProfileType_id']);
                        }
                    }
                }
            }
        }

        // Обрабатываем список операций над лицензиями
        if ( !empty($data['LpuLicenceOperationLinkData']) ) {
            $LpuLicenceOperationLinkData = json_decode($data['LpuLicenceOperationLinkData'], true);
            if ( is_array($LpuLicenceOperationLinkData) ) {

                for ( $j = 0; $j < count($LpuLicenceOperationLinkData); $j++ ) {
                    if (!empty($LpuLicenceOperationLinkData[$j]['LicsOperation_id'])) {
                        $LpuLicenceOperationLink = array(
                            'pmUser_id' => $data['pmUser_id'],
                            'LpuLicence_id' => $response['LpuLicence_id']
                        );

                        if ( empty($LpuLicenceOperationLinkData[$j]['LpuLicenceOperationLink_id']) || !is_numeric($LpuLicenceOperationLinkData[$j]['LpuLicenceOperationLink_id']) ) {
                            continue;
                        }

                        if ( empty($LpuLicenceOperationLinkData[$j]['LicsOperation_id']) || !is_numeric($LpuLicenceOperationLinkData[$j]['LicsOperation_id']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        if ( empty($LpuLicenceOperationLinkData[$j]['LpuLicenceOperationLink_Date'])) {
							$this->rollbackTransaction();
							$response['Error_Msg'] = 'Не указана дата операции';
							return $response;
                        }

                        $LpuLicenceOperationLink['LpuLicenceOperationLink_id'] = $LpuLicenceOperationLinkData[$j]['LpuLicenceOperationLink_id'];
                        $LpuLicenceOperationLink['LicsOperation_id'] = $LpuLicenceOperationLinkData[$j]['LicsOperation_id'];
                        $LpuLicenceOperationLink['LpuLicenceOperationLink_Date'] = $LpuLicenceOperationLinkData[$j]['LpuLicenceOperationLink_Date'];

                        $queryResponse2 = $this->saveLpuLicenceOperationLink($LpuLicenceOperationLink);

                        if ( !is_object($result) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение палаты)';
                            return $response;
                        }

                    }
                }
            }
        }

        // Обрабатываем список профилей лицензий
        if ( !empty($data['LpuLicenceLinkData']) ) {
            $LpuLicenceLinkData = json_decode($data['LpuLicenceLinkData'], true);
            if ( is_array($LpuLicenceLinkData) ) {

                for ( $j = 0; $j < count($LpuLicenceLinkData); $j++ ) {
                    if (!empty($LpuLicenceLinkData[$j]['LpuSectionProfile_id'])) {
                        $LpuLicenceLink = array(
                            'pmUser_id' => $data['pmUser_id'],
                            'LpuLicence_id' => $response['LpuLicence_id'],
                            'LpuSectionProfile_id' => $LpuLicenceLinkData[$j]['LpuSectionProfile_id'],
                            'LpuLicenceLink_id' => $LpuLicenceLinkData[$j]['LpuLicenceLink_id']
                        );

                        $queryResponse = $this->saveLpuLicenceLink($LpuLicenceLink);

                        if ( !is_object($result) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение профилей)';
                            return $response;
                        }

                    }
                }
            }
        }

        // Обрабатываем список приложений к лицензиям
        if (getRegionNick() == 'kz' && !empty($data['LpuLicenceDopData']) ) {
            $LpuLicenceDopData = json_decode($data['LpuLicenceDopData'], true);
            if ( is_array($LpuLicenceDopData) ) {

                for ( $j = 0; $j < count($LpuLicenceDopData); $j++ ) {
                    if (!empty($LpuLicenceDopData[$j]['LpuLicenceDop_Num'])) {
                        $LpuLicenceDop = array(
                            'pmUser_id' => $data['pmUser_id'],
                            'LpuLicence_id' => $response['LpuLicence_id']
                        );

                        if ( empty($LpuLicenceDopData[$j]['LpuLicenceDop_id']) || !is_numeric($LpuLicenceDopData[$j]['LpuLicenceDop_id']) ) {
                            continue;
                        }

                        if ( empty($LpuLicenceDopData[$j]['LpuLicenceDop_Num'])) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        if ( empty($LpuLicenceDopData[$j]['LpuLicenceDop_setDate']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано количество объектов';
                                return array($response);
                            */
                        }

                        $LpuLicenceDop['LpuLicenceDop_id'] = $LpuLicenceDopData[$j]['LpuLicenceDop_id'];
                        $LpuLicenceDop['LpuLicenceDop_Num'] = $LpuLicenceDopData[$j]['LpuLicenceDop_Num'];
                        $LpuLicenceDop['LpuLicenceDop_setDate'] = $LpuLicenceDopData[$j]['LpuLicenceDop_setDate'];

                        $queryResponse2 = $this->saveLpuLicenceDop($LpuLicenceDop);

                        if ( !is_object($result) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение приложения к лицензии)';
                            return $response;
                        }

                    }
                }
            }
        }

        $this->commitTransaction();

        return array($response);

	}

	/**
	* Сохраняет карточку медицинского изделия
	*/
	function saveMedProductCard($data) {

        //$this->beginTransaction();

		if ( !isset($data['MedProductCard_id']) ) {
			$procedure_action = "ins";
			$data['MedProductCard_id'] = 0;
		}
		else {
			$procedure_action = "upd";
		}
		foreach (array('MedProductCard_IsOutsorc', 'MedProductCard_IsEducatAct', 'MedProductCard_IsNoAvailLpu', 'MedProductCard_IsRepair', 'MedProductCard_IsSpisan', 'MedProductCard_IsContractTO', 'MedProductCard_IsOrgLic', 'MedProductCard_IsLpuLic', 'MedProductCard_IsWorkList') as $row) {
			$data[$row] = (isset($data[$row])) ? $data[$row] + 1 : 1;
		}

		// Если установлен флаг "Бессрочно" устанавливаем в поле "Срок службы" значение 99 - т.е. бессрочно
		if($data['MedProductCard_UsePeriod_Check'] = 'on') {
			$data['MedProductCard_UsePeriod'] = 99;
		}

		//Контроль на уникальност инвентарного номера
		$query = "
			Select
				MPC.MedProductCard_IsNoAvailLpu
			from
				passport.v_MedProductCard (nolock) MPC
				inner join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
				inner join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where 
				LB.Lpu_id = :Lpu_id and
				MPC.MedProductCard_id != :MedProductCard_id and
				AD.AccountingData_InventNumber = :AccountingData_InventNumber
		";

		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}
		$response = $result->result('array');

		if ( isset($response[0]) ) {
			if (!isSuperadmin() && $this->regionNick == 'perm' && $response[0]['MedProductCard_IsNoAvailLpu']==2) {
				return array(0 => array('Error_Msg' => 'В связи с техническими работами Медицинское изделие с данным инвентарным номером пока не доступно для добавления. Срок окончания работ - не более двух рабочих дней.'));
			} else {
				return array(0 => array('Error_Msg' => 'Медицинское изделие с данным инвентарным номером заведено ранее.'));
			}
		}

		$ufaFields = "";
		if( getRegionNick() == 'ufa' ) { //#142806
			$ufaFields = "
				@MedProductCard_isClockMode=:MedProductCard_IsClockMode,
				@MedProductCard_isAvailibleSpecialists=:MedProductCard_IsAvailibleSpecialists,
			";
		}

		if(empty($data['LpuEquipmentPacs_id'])) {
			$request = "
				SELECT LpuEquipmentPacs_id 
				FROM LpuEquipmentPacs 
				WHERE Lpu_id = :Lpu_id";
			$pacsId = $this->db->query($request, $data)->result('array');
			if (sizeof($pacsId) == 0) {
				$data['LpuEquipmentPacs_id'] = null;
			} else {
				$data['LpuEquipmentPacs_id'] = $pacsId[0]['LpuEquipmentPacs_id'];
			}
		}

		if (empty($data['MedProductCard_IsNotFRMO'])) {
			$data['MedProductCard_IsNotFRMO'] = 1;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :MedProductCard_id;
			exec passport.p_MedProductCard_" . $procedure_action . "
				@MedProductCard_id = @Res output,
				@MedProductCard_SerialNumber = :MedProductCard_SerialNumber,
				@MedProductCard_BoardNumber = :MedProductCard_BoardNumber,
				@MedProductCard_Phone = :MedProductCard_Phone,
				@MedProductCard_Glonass = :MedProductCard_Glonass,
				@LpuBuilding_id = :LpuBuilding_id,
				@MedProductCard_Options = :MedProductCard_Options,
				@MedProductCard_OtherParam = :MedProductCard_OtherParam,
				@MedProductCard_begDate = :MedProductCard_begDate,
				@MedProductCard_UsePeriod = :MedProductCard_UsePeriod,
				@Org_id = :Org_id,
				@LpuSection_id = :LpuSection_id,
				@LpuUnit_id = :LpuUnit_id,
				@MedProductCard_IsRepair = :MedProductCard_IsRepair,
				@MedProductCard_IsSpisan = :MedProductCard_IsSpisan,
				@MedProductClass_id = :MedProductClass_id,
				@MedProductCard_RepairDate = :MedProductCard_RepairDate,
				@MedProductCard_SpisanDate = :MedProductCard_SpisanDate,
				@MedProductCard_IsContractTO = :MedProductCard_IsContractTO,
				@Org_toid = :Org_toid,
				@MedProductCard_IsOrgLic = :MedProductCard_IsOrgLic,
				@MedProductCard_IsLpuLic = :MedProductCard_IsLpuLic,
				@MedProductCard_DocumentTO = :MedProductCard_DocumentTO,
				@MedProductCard_SetResource = :MedProductCard_SetResource,
				@MedProductCard_AvgProcTime = :MedProductCard_AvgProcTime,
				@MedProductCard_IsEducatAct = :MedProductCard_IsEducatAct,
				@MedProductCard_IsOutsorc = :MedProductCard_IsOutsorc,
				@MedProductCard_IsNoAvailLpu = :MedProductCard_IsNoAvailLpu,
				@MedProductCard_IsNotFRMO = :MedProductCard_IsNotFRMO,
				@MedProductCard_Cause = :MedProductCard_Cause,
				@MedProductCauseType_id = :MedProductCauseType_id,
				@PrincipleWorkType_id = :PrincipleWorkType_id,
				@MedProductClassForm_secid = :MedProductClassForm_secid,
				@MedProductClassForm_strid = :MedProductClassForm_strid,
				@MedProductClassForm_fsubid = :MedProductClassForm_fsubid,
				@MedProductClassForm_ssubid = :MedProductClassForm_ssubid,
				@MedProductCard_IsWorkList = :MedProductCard_IsWorkList,
				@MedProductCard_AETitle = :MedProductCard_AETitle,
				@LpuEquipmentPacs_id = :LpuEquipmentPacs_id,
				$ufaFields
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as MedProductCard_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

        //echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

        if ( !is_object($result) ) {
            $response['Error_Msg'] = 'Ошибка при выполнении запроса к базе данных (сохранение МИ)';
            return $response;
        }

        $queryResponse = $result->result('array');

        if (is_array($queryResponse) && count($queryResponse)>0 && isset($queryResponse[0]['MedProductCard_id'])) {
            $data['MedProductCard_id'] = $queryResponse[0]['MedProductCard_id'];
            $response_med_prod_card = $queryResponse;
        } else if ( !is_array($queryResponse) ) {
            $response['Error_Msg'] = 'Ошибка при сохранение МИ';
            return $response;
        } else {
            return $queryResponse;
        }

        //Тянем ИДшники из вязанных таблиц
        $query = "
            select
                AD.AccountingData_id,
                RC.RegCertificate_id,
                MF.MeasureFund_id,
                DT.Downtime_id,
				GC.GosContract_id
            from passport.v_MedProductCard MPC with (nolock)
                left join passport.v_AccountingData AD with (nolock) on MPC.MedProductCard_id = AD.MedProductCard_id
                left join passport.v_RegCertificate RC with (nolock) on MPC.MedProductCard_id = RC.MedProductCard_id
                left join passport.v_MeasureFund MF with (nolock) on MPC.MedProductCard_id = MF.MedProductCard_id
                left join passport.v_GosContract GC with (nolock) on MPC.MedProductCard_id = GC.MedProductCard_id
				left join passport.v_Downtime DT with (nolock) on MPC.MedProductCard_id = DT.MedProductCard_id
            where
                MPC.MedProductCard_id = :MedProductCard_id
        ";

		$result = $this->db->query($query, $data);

        if ( is_object($result) ) {
            $response = $result->result('array');

            if (is_array($response) && count($response) > 0 ) {

                if (!empty($response[0]['AccountingData_id'])){
                    $AccountingData_action = 'upd';
                    $data['AccountingData_id'] = $response[0]['AccountingData_id'];
                } else {
                    $AccountingData_action = 'ins';
                    $data['AccountingData_id'] = 0;
                }

                $query = "
                    declare
                        @Res bigint,
                        @ErrCode bigint,
                        @ErrMsg varchar(4000);
                    set @Res = :AccountingData_id;
                    exec passport.p_AccountingData_" . $AccountingData_action . "
                        @AccountingData_id = @Res output,
                        @AccountingData_InventNumber = :AccountingData_InventNumber,
                        @AccountingData_RegNumber = :AccountingData_RegNumber,                       
                        @AccountingData_buyDate = :AccountingData_buyDate,
                        @AccountingData_setDate = :AccountingData_setDate,
                        @AccountingData_begDate = :AccountingData_begDate,
                        @AccountingData_endDate = :AccountingData_endDate,
                        @AccountingData_BuyCost = :AccountingData_BuyCost,
                        @DeliveryType_id = :DeliveryType_id,
                        @PropertyType_id = :PropertyType_id,
                        @MedProductCard_id = :MedProductCard_id,
                        @AccountingData_ProductCost = :AccountingData_ProductCost,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMsg output;
                    select @Res as AccountingData_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
                ";

                $result = $this->db->query($query, $data);

                if ( !is_object($result) ) {
                    $response['Error_Msg'] = 'Ошибка при сохранение AccountingData';
                    return $response;
                }

				if (!empty($data['Org_prid'])) {

					$OrgProducer_id = $this->getFirstResultFromQuery('select OrgProducer_id from passport.OrgProducer with(nolock) where Org_id = :Org_prid and Lpu_id = :Lpu_id', $data);
					//Если такого поставщика ещё нет в текущей МО - добавляем его
					if (empty($OrgProducer_id)){

						$data['OrgProducer_id'] = 0;

						$query = "
							declare
								@Res bigint,
								@ErrCode bigint,
								@ErrMsg varchar(4000);
							set @Res = :OrgProducer_id;
							exec passport.p_OrgProducer_ins
								@OrgProducer_id = @Res output,
								@Org_id = :Org_prid,
								@Lpu_id = :Lpu_id,
								@pmUser_id = :pmUser_id,
								@Error_Code = @ErrCode output,
								@Error_Message = @ErrMsg output;
							select @Res as OrgProducer_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
						";

						$result = $this->db->query($query, $data);

						if ( !is_object($result) ) {
							$response['Error_Msg'] = 'Ошибка при сохранение OrgProducer';
							return $response;
						} else {
							$response_op = $result->result('array');
							$data['OrgProducer_id'] = $response_op[0]['OrgProducer_id'];
						}
					} else {
						$data['OrgProducer_id'] = $OrgProducer_id;
					}
				} else {
					$data['OrgProducer_id'] = null;
				}

                if (!empty($response[0]['RegCertificate_id'])){
                    $RegCertificate_action = 'upd';
                    $data['RegCertificate_id'] = $response[0]['RegCertificate_id'];
                } else {
                    $RegCertificate_action = 'ins';
                    $data['RegCertificate_id'] = 0;
                }

                $query = "
                    declare
                        @Res bigint,
                        @ErrCode bigint,
                        @ErrMsg varchar(4000);
                    set @Res = :RegCertificate_id;
                    exec passport.p_RegCertificate_" . $RegCertificate_action . "
                        @RegCertificate_id = @Res output,
                        @MedProductCard_id = :MedProductCard_id,
                        @RegCertificate_setDate = :RegCertificate_setDate,
                        @RegCertificate_endDate = :RegCertificate_endDate,
                        @RegCertificate_Number = :RegCertificate_Number,
                        @RegCertificate_OrderNumber = :RegCertificate_OrderNumber,
                        @RegCertificate_MedProductName = :RegCertificate_MedProductName,
                        @Org_regid = :Org_regid,
                        @Org_prid = :Org_prid,
                        @Org_decid = :Org_decid,
                        @OrgProducer_id = :OrgProducer_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMsg output;
                    select @Res as RegCertificate_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
                ";

                $result = $this->db->query($query, $data);

                if ( !is_object($result) ) {
                    $response['Error_Msg'] = 'Ошибка при сохранение RegCertificate';
                    return $response;
                }

                if (!empty($response[0]['GosContract_id'])){
                    $GosContract_action = 'upd';
                    $data['GosContract_id'] = $response[0]['GosContract_id'];
                } else {
                    $GosContract_action = 'ins';
                    $data['GosContract_id'] = 0;
                }

                $query = "
                    declare
                        @Res bigint,
                        @ErrCode bigint,
                        @ErrMsg varchar(4000);
                    set @Res = :GosContract_id;
                    exec passport.p_GosContract_" . $GosContract_action . "
                        @GosContract_id = @Res output,
                        @MedProductCard_id = :MedProductCard_id,
                        @GosContract_Number = :GosContract_Number,
                        @GosContract_setDate = :GosContract_setDate,
                        @FinancingType_id = :FinancingType_id,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMsg output;
                    select @Res as RegCertificate_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
                ";

                $result = $this->db->query($query, $data);

                if ( !is_object($result) ) {
                    $response['Error_Msg'] = 'Ошибка при сохранение RegCertificate';
                    return $response;
                }

                if (!empty($response[0]['MeasureFund_id'])){
                    $MeasureFund_action = 'upd';
                    $data['MeasureFund_id'] = $response[0]['MeasureFund_id'];
                } else {
                    $MeasureFund_action = 'ins';
                    $data['MeasureFund_id'] = 0;
                }
				$data['MeasureFund_IsMeasure'] = $data['MeasureFund_IsMeasure'] ? 2 : 1;

                $query = "
                    declare
                        @Res bigint,
                        @ErrCode bigint,
                        @ErrMsg varchar(4000);
                    set @Res = :MeasureFund_id;
                    exec passport.p_MeasureFund_" . $MeasureFund_action . "
                        @MeasureFund_id = @Res output,
                        @OkeiLink_id = :OkeiLink_id,
                        @MedProductCard_id = :MedProductCard_id,
                        @MeasureFund_Range = :MeasureFund_Range,
                        @MeasureFund_IsMeasure = :MeasureFund_IsMeasure,
                        @MeasureFund_RegNumber = :MeasureFund_RegNumber,
                        @MeasureFund_AccuracyClass = :MeasureFund_AccuracyClass,
                        @pmUser_id = :pmUser_id,
                        @Error_Code = @ErrCode output,
                        @Error_Message = @ErrMsg output;
                    select @Res as MeasureFund_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
                ";

                $result = $this->db->query($query, $data);

                if ( !is_object($result) ) {
                    $response['Error_Msg'] = 'Ошибка при сохранение MeasureFund';
                    return $response;
				}

            }
        } else {
            return false;
        }

		// Обрабатываем список расходных материалов
        if ( !empty($data['ConsumablesGridData']) ) {
            $ConsumablesGridData = json_decode(toUtf($data['ConsumablesGridData']), true);
			
            if ( is_array($ConsumablesGridData) ) {
                for ( $i = 0; $i < count($ConsumablesGridData); $i++ ) {
					$ConsumablesGridData[$i]['Consumables_Name'] = toAnsi($ConsumablesGridData[$i]['Consumables_Name']);
                    if (!empty($ConsumablesGridData[$i]['Consumables_Name'])) {

                        $Consumables = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'MedProductCard_id' => $data['MedProductCard_id']
                        );

                        if ( empty($ConsumablesGridData[$i]['Consumables_id']) || !is_numeric($ConsumablesGridData[$i]['Consumables_id']) ) {
                            continue;
                        }

                        if ( empty($ConsumablesGridData[$i]['Consumables_Name']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        $Consumables['Consumables_id'] = $ConsumablesGridData[$i]['Consumables_id'];
                        $Consumables['Consumables_Name'] = $ConsumablesGridData[$i]['Consumables_Name'];

                        $queryResponse = $this->saveConsumables($Consumables);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при сохранении расходного материала';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }
                    }
                }
            }
        }

		// Обрабатываем список простоев МИ
        if ( !empty($data['DowntimeGridData']) ) {
            $DowntimeGridData = json_decode(toUtf($data['DowntimeGridData']), true);

            if ( is_array($DowntimeGridData) ) {
                for ( $i = 0; $i < count($DowntimeGridData); $i++ ) {
					$DowntimeGridData[$i]['Downtime_begDate'] = toAnsi($DowntimeGridData[$i]['Downtime_begDate']);
                    if (!empty($DowntimeGridData[$i]['Downtime_begDate'])) {

                        $Downtime = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'MedProductCard_id' => $data['MedProductCard_id']
                        );

                        if ( empty($DowntimeGridData[$i]['Downtime_id']) || !is_numeric($DowntimeGridData[$i]['Downtime_id']) ) {
                            continue;
                        }

                        if ( empty($DowntimeGridData[$i]['Downtime_begDate']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        /*if ( empty($DowntimeGridData[$i]['Downtime_endDate']) ) {
                            continue;
                        }*/

                        if ( empty($DowntimeGridData[$i]['DowntimeCause_id']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }

                        $Downtime['Downtime_id'] = $DowntimeGridData[$i]['Downtime_id'];
                        $Downtime['Downtime_begDate'] = $DowntimeGridData[$i]['Downtime_begDate'];
                        $Downtime['Downtime_endDate'] = (!empty($DowntimeGridData[$i]['Downtime_endDate']) ? $DowntimeGridData[$i]['Downtime_endDate'] : null);
                        $Downtime['DowntimeCause_id'] = $DowntimeGridData[$i]['DowntimeCause_id'];

                        $queryResponse = $this->saveDowntime($Downtime);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при сохранении расходного материала';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }
                    }
                }
            }
        }

		// Обрабатываем список эксплуатационных данных
        if ( !empty($data['WorkDataGridData']) ) {
            $WorkDataGridData = json_decode(toUtf($data['WorkDataGridData']), true);

            if ( is_array($WorkDataGridData) ) {
                for ( $i = 0; $i < count($WorkDataGridData); $i++ ) {
					$WorkDataGridData[$i]['WorkData_WorkPeriod'] = toAnsi($WorkDataGridData[$i]['WorkData_WorkPeriod']);
                    if (!empty($WorkDataGridData[$i]['WorkData_WorkPeriod'])) {

                        $WorkData = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'MedProductCard_id' => $data['MedProductCard_id']
                        );

                        if ( empty($WorkDataGridData[$i]['WorkData_id']) || !is_numeric($WorkDataGridData[$i]['WorkData_id']) ) {
                            continue;
                        }

                        if ( empty($WorkDataGridData[$i]['WorkData_WorkPeriod']) ) {
                            continue;
                        }

                        if ( empty($WorkDataGridData[$i]['WorkData_DayChange']) ) {
                            continue;
                        }

                        /*if ( empty($WorkDataGridData[$i]['WorkData_AvgUse']) ) {
                            continue;
                        }*/

                        if ( empty($WorkDataGridData[$i]['WorkData_CountUse']) ) {
                            continue;
                        }

                        $WorkData['WorkData_id'] = $WorkDataGridData[$i]['WorkData_id'];
                        $WorkData['WorkData_WorkPeriod'] = $WorkDataGridData[$i]['WorkData_WorkPeriod'];
                        $WorkData['WorkData_DayChange'] = $WorkDataGridData[$i]['WorkData_DayChange'];
                        $WorkData['WorkData_CountUse'] = $WorkDataGridData[$i]['WorkData_CountUse'];
                        $WorkData['WorkData_AvgUse'] = $WorkDataGridData[$i]['WorkData_AvgUse'];
                        $WorkData['WorkData_KolDay'] = $WorkDataGridData[$i]['WorkData_KolDay'];

                        $queryResponse = $this->saveWorkData($WorkData);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при сохранении расходного материала';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }
                    }
                }
            }
        }

		// Обрабатываем список проверок медицинских изделий
        if ( !empty($data['MeasureFundCheckGridData']) ) {
            $MeasureFundCheckGridData = json_decode(toUtf($data['MeasureFundCheckGridData']), true);

            if ( is_array($MeasureFundCheckGridData) ) {
                for ( $i = 0; $i < count($MeasureFundCheckGridData); $i++ ) {
					$MeasureFundCheckGridData[$i]['MeasureFundCheck_setDate'] = toAnsi($MeasureFundCheckGridData[$i]['MeasureFundCheck_setDate']);
                    if (!empty($MeasureFundCheckGridData[$i]['MeasureFundCheck_setDate'])) {

                        $MeasureFundCheck = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'MedProductCard_id' => $data['MedProductCard_id']
                        );

                        if ( empty($MeasureFundCheckGridData[$i]['MeasureFundCheck_id']) || !is_numeric($MeasureFundCheckGridData[$i]['MeasureFundCheck_id']) ) {
                            continue;
                        }

                        if ( empty($MeasureFundCheckGridData[$i]['MeasureFundCheck_endDate']) ) {
                            continue;
                            /*
                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'Не указано наименование объекта';
                                return array($response);
                            */
                        }


                        if ( empty($MeasureFundCheckGridData[$i]['MeasureFundCheck_Number']) ) {
                            continue;
                        }

                        $MeasureFundCheck['MeasureFundCheck_id'] = $MeasureFundCheckGridData[$i]['MeasureFundCheck_id'];
                        $MeasureFundCheck['MeasureFundCheck_setDate'] = $MeasureFundCheckGridData[$i]['MeasureFundCheck_setDate'];
                        $MeasureFundCheck['MeasureFundCheck_Number'] = $MeasureFundCheckGridData[$i]['MeasureFundCheck_Number'];
                        $MeasureFundCheck['MeasureFundCheck_endDate'] = $MeasureFundCheckGridData[$i]['MeasureFundCheck_endDate'];

                        $queryResponse = $this->saveMeasureFundCheck($MeasureFundCheck);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при сохранении расходного материала';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }
                    }
                }
            }
        }

        // Обрабатываем список износов
        if ( !empty($data['AmortizationGridData']) ) {
            $AmortizationGridData = json_decode($data['AmortizationGridData'], true);
            if ( is_array($AmortizationGridData) ) {
                for ( $i = 0; $i < count($AmortizationGridData); $i++ ) {
                    if (!empty($AmortizationGridData[$i]['Amortization_setDate'])) {

                        $Amortization = array(
                        'pmUser_id' => $data['pmUser_id'],
                        'MedProductCard_id' => $data['MedProductCard_id']
                        );

                        if ( empty($AmortizationGridData[$i]['Amortization_setDate']) ) {

                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'У износа МИ не указана дата оценки';
                                return array($response);
                        }

                        if ( empty($AmortizationGridData[$i]['Amortization_WearPercent']) ) {

                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'У износа МИ не указан % износа';
                                return array($response);
                        }

                        if ( empty($AmortizationGridData[$i]['Amortization_ResidCost']) &&  $AmortizationGridData[$i]['Amortization_ResidCost'] != 0) {

                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'У износа МИ не указана остаточная стоимость';
                                return array($response);
                        }

                        if ( empty($AmortizationGridData[$i]['Amortization_FactCost']) ) {

                                $this->rollbackTransaction();
                                $response['Error_Msg'] = 'У износа МИ не указана фактическая стоимость';
                                return array($response);
                        }

                        $Amortization['Amortization_id'] = $AmortizationGridData[$i]['Amortization_id'];
                        $Amortization['Amortization_setDate'] = $AmortizationGridData[$i]['Amortization_setDate'];
                        $Amortization['Amortization_WearPercent'] = $AmortizationGridData[$i]['Amortization_WearPercent'];
                        $Amortization['Amortization_FactCost'] = $AmortizationGridData[$i]['Amortization_FactCost'];
                        $Amortization['Amortization_ResidCost'] = $AmortizationGridData[$i]['Amortization_ResidCost'];

                        $queryResponse = $this->saveAmortization($Amortization);

                        if ( !is_array($queryResponse) ) {
                            $this->rollbackTransaction();
                            $response['Error_Msg'] = 'Ошибка при сохранении описания износа';
                            return array($response);
                        } else if ( !empty($queryResponse[0]['Error_Msg']) ) {
                            $this->rollbackTransaction();
                            return $queryResponse[0];
                        }
                    }
                }
            }
        }

        //return array($response_med_prod_card);
        return $response_med_prod_card;

	}

	/**
	 *	Получение идентификатора связки мобильной группы и типа диспансеризации
	 */
	function getLpuMobileTeamLinkIdByDispClass($LpuMobileTeam_id, $DispClass_id) {
		$query = "
			select top 1
				LpuMobileTeamLink_id
			from
				v_LpuMobileTeamLink (nolock)
			where
				LpuMobileTeam_id = :LpuMobileTeam_id
				and DispClass_id = :DispClass_id
		";
		$res = $this->db->query($query, array(
			'LpuMobileTeam_id' => $LpuMobileTeam_id,
			'DispClass_id' => $DispClass_id
		));

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0 && !empty($resp[0]['LpuMobileTeamLink_id'])) {
				return $resp[0]['LpuMobileTeamLink_id'];
			}
		}
		
		return false;
	}
	
	/**
	* Сохраняет данные формы мобильной бригады ЛПУ
	*/
	function saveLpuMobileTeam($data) {
		
		$action = 'upd';
		if ( empty($data['LpuMobileTeam_id']) ) {
			$action = "ins";
			$data['LpuMobileTeam_id'] = null;
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuMobileTeam_id;
			exec p_LpuMobileTeam_" . $action . "
				@LpuMobileTeam_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuMobileTeam_begDate = :LpuMobileTeam_begDate,
				@LpuMobileTeam_endDate = :LpuMobileTeam_endDate,
				@LpuMobileTeam_Count = :LpuMobileTeam_Count,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as LpuMobileTeam_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";
		
		$res = $this->db->query($query, $data);

		if ( is_object($res) ) {
			$resp = $res->result('array');
			if (count($resp) > 0 && !empty($resp[0]['LpuMobileTeam_id'])) {
				// надо сохранить данные в LpuMobileTeamLink
				$data['LpuMobileTeam_id'] = $resp[0]['LpuMobileTeam_id'];
				
				for ($i=1; $i<=10; $i++) {
					$data['DispClass_id'] = $i;
					$data['LpuMobileTeamLink_id'] = $this->getLpuMobileTeamLinkIdByDispClass($data['LpuMobileTeam_id'], $data['DispClass_id']);
					if (!empty($data['LpuMobileTeamLink_id'])) {
						if ( empty($data['TypeBrig'.$i]) ) {
							// удаляем LpuMobileTeamLink
							$query = "
								exec p_LpuMobileTeamLink_del
									@LpuMobileTeamLink_id = :LpuMobileTeamLink_id;
							";
							$res = $this->db->query($query, $data);	
						}			
					} else {
						if ( !empty($data['TypeBrig'.$i]) ) {
							// добавляем LpuMobileTeamLink
							$query = "
								exec p_LpuMobileTeamLink_ins
									@LpuMobileTeamLink_id = null,
									@LpuMobileTeam_id = :LpuMobileTeam_id,
									@DispClass_id = :DispClass_id,
									@pmUser_id = :pmUser_id;
							";
							
							$res = $this->db->query($query, $data);	
						}
					}
				}
			}
			
			return $resp;
		}
		else {
			return false;
		}

	}

	
	/**
	 * @desc Сохранение тарифа
	 * @param array $data
	 * @return array|false
	 */
	function saveSmpTariff( $data ){
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return false;
		}
		
		if ( array_key_exists( 'CmpProfileTariff_id', $data ) && $data['CmpProfileTariff_id'] ) {
			$procedure = 'p_CmpProfileTariff_upd';
		} else {
			$procedure = 'p_CmpProfileTariff_ins';
			$data['CmpProfileTariff_id'] = 0;
		}
		
		// Проверяка совпадения диапазона тарифов с уже существующими
		if ( ( $result = $this->checkSmpTariffDateRange( $data ) ) !== true ) {
			return $result;
		}
		
		if ( $data['CmpProfileTariff__Value'] < 0 ) {
			return array( array( 'Error_Msg' => 'Значение тарифа не должно быть отрицательным.' ) );
		}

		$query = "
			DECLARE
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			SET
				@Res = :CmpProfileTariff_id;
			EXEC ".$procedure."
				@CmpProfileTariff_id = @Res output,
				@CmpProfile_id = :CmpProfile_id,
				@CmpProfileTariff__Value = :CmpProfileTariff__Value,
				@CmpProfileTariff_begDT = :CmpProfileTariff_begDT,
				@CmpProfileTariff_endDT = :CmpProfileTariff_endDT,
				@Lpu_id = :Lpu_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@TariffClass_id = :TariffClass_id,
				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			SELECT @Res as CmpProfileTariff_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$params = array(
			'CmpProfileTariff_id'		=> (int)$data['CmpProfileTariff_id'],
			'CmpProfile_id'				=> null,
			'CmpProfileTariff__Value'	=> (float)str_replace( ',', '.', $data['CmpProfileTariff__Value'] ),
			'CmpProfileTariff_begDT'	=> $data['CmpProfileTariff_begDT'],
			'CmpProfileTariff_endDT'	=> $data['CmpProfileTariff_endDT'],
			'LpuSectionProfile_id'		=> !empty($data['LpuSectionProfile_id'])?(int)$data['LpuSectionProfile_id']:null,
			'TariffClass_id'			=> (int)$data['TariffClass_id'],
			'Lpu_id'					=> (int)$data['Lpu_id'],
			'pmUser_id'					=> $data['pmUser_id'],
		);

		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		}
		
		return false;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function saveLpuTariff( $data ){

		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return false;
		}

		if ( array_key_exists( 'LpuTariff_id', $data ) && $data['LpuTariff_id'] ) {
			$procedure = 'p_LpuTariff_upd';
		} else {
			$procedure = 'p_LpuTariff_ins';
			$data['LpuTariff_id'] = 0;
		}

		// Проверяка совпадения диапазона тарифов с уже существующими
		if ( ( $result = $this->checkLpuTariffDateRange( $data ) ) !== true ) {
			return $result;
		}

		if ( $data['LpuTariff_Tariff'] < 0 ) {
			return array( array( 'Error_Msg' => 'Значение тарифа не должно быть отрицательным.' ) );
		}

		$query = "
			DECLARE
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			SET
				@Res = :LpuTariff_id;
			EXEC ".$procedure."
				@LpuTariff_id = @Res output,
				@LpuTariff_Tariff = :LpuTariff_Tariff,
				@LpuTariff_setDate = :LpuTariff_setDate,
				@LpuTariff_disDate = :LpuTariff_disDate,
				@Lpu_id = :Lpu_id,
				@TariffClass_id = :TariffClass_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			SELECT @Res as LpuTariff_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$params = array(
			'LpuTariff_id'		=> $data['LpuTariff_id'],
			'LpuTariff_Tariff'	=> (float)str_replace( ',', '.', $data['LpuTariff_Tariff'] ),
			'LpuTariff_setDate'	=> $data['LpuTariff_setDate'],
			'LpuTariff_disDate'	=> $data['LpuTariff_disDate'],
			'TariffClass_id'			=> $data['TariffClass_id'],
			'Lpu_id'					=> $data['Lpu_id'],
			'pmUser_id'					=> $data['pmUser_id'],
		);

		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		}

		return false;
	}

	/**
	 * @desc Сохранение тарифа
	 * @param array $data
	 * @return array|false
	 */
	function saveTariffDisp( $data )
	{
		if ( !empty($data['TariffDisp_id']) ) {
			$procedure = 'p_TariffDisp_upd';
		} else {
			$procedure = 'p_TariffDisp_ins';
		}
		
		// Проверяка совпадения диапазона тарифов с уже существующими
		if ( ( $result = $this->checkTariffDispDateRange( $data ) ) !== true ) {
			return $result;
		}
		
		if ( $data['TariffDisp_Tariff'] < 0 ) {
			return array( array( 'Error_Msg' => 'Значение тарифа не должно быть отрицательным.' ) );
		}
		
		if ( $data['TariffDisp_TariffDayOff'] ) {
			$data['TariffDisp_TariffDayOff'] = (float)str_replace( ',', '.', $data['TariffDisp_TariffDayOff'] );
		}

		$query = "
			DECLARE
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			SET
				@Res = :TariffDisp_id;
			EXEC ".$procedure."
				@TariffDisp_id = @Res output,
				@TariffDisp_Tariff = :TariffDisp_Tariff,
				@TariffDisp_TariffDayOff = :TariffDisp_TariffDayOff,
				@TariffDisp_begDT = :TariffDisp_begDT,
				@TariffDisp_endDT = :TariffDisp_endDT,
				@Lpu_id = :Lpu_id,
				@Sex_id = :Sex_id,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@TariffClass_id = :TariffClass_id,
				@AgeGroupDisp_id = :AgeGroupDisp_id,				
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			SELECT @Res as TariffDisp_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$params = array(
			'TariffDisp_id'		=> $data['TariffDisp_id'],
			'TariffDisp_Tariff'	=> (float)str_replace( ',', '.', $data['TariffDisp_Tariff'] ),
			'TariffDisp_TariffDayOff' => $data['TariffDisp_TariffDayOff'] > 0 ? $data['TariffDisp_TariffDayOff'] : null,
			'TariffDisp_begDT'	=> $data['TariffDisp_begDT'],
			'TariffDisp_endDT'	=> $data['TariffDisp_endDT'],
			'LpuSectionProfile_id'		=> $data['LpuSectionProfile_id'],
			'TariffClass_id'			=> $data['TariffClass_id'],
			'Lpu_id'					=> $data['Lpu_id'],
			'Sex_id'					=> $data['Sex_id'],
			'AgeGroupDisp_id'			=> $data['AgeGroupDisp_id'],
			'pmUser_id'					=> $data['pmUser_id'],
		);

		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			return $result->result( 'array' );
		}
		
		return false;
	}
	
	
	/**
	 * @desc Проверка пересечения дат тарифов
	 * 
	 * @param array $data
	 * @return true|error message
	 */
	public function checkSmpTariffDateRange( $data ){
		
		$params = array(
			'CmpProfileTariff_id' => (int)$data['CmpProfileTariff_id'],
			'CmpProfileTariff_begDT' => $data['CmpProfileTariff_begDT'],
			'CmpProfileTariff_endDT' => $data['CmpProfileTariff_endDT'],
			'LpuSectionProfile_id' => (int)$data['LpuSectionProfile_id'],
			'TariffClass_id' => (int)$data['TariffClass_id'],
			'Lpu_id' => (int)$data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$query = "
			DECLARE
				@date_start DATE,
				@date_end DATE;

			SET @date_start = :CmpProfileTariff_begDT;
			SET @date_end = :CmpProfileTariff_endDT;

			SELECT
				CmpProfileTariff_id
			FROM
				v_CmpProfileTariff (nolock)
			WHERE
				CmpProfileTariff_id <> ISNULL(:CmpProfileTariff_id, 0)
				AND Lpu_id = :Lpu_id
				AND LpuSectionProfile_id = :LpuSectionProfile_id
				AND TariffClass_id = :TariffClass_id
				AND (	
					/* Дата начала меньше или равна существующей, а окончание не указано или в полученный диапазон попадает существующий тариф */
					( @date_start <= CmpProfileTariff_begDT AND ( @date_end >= CmpProfileTariff_endDT OR @date_end IS NULL ) )
					OR
					/* Дата начала больше или равна существующей и меньше или равна существующему окончанию */
					( @date_start >= CmpProfileTariff_begDT AND ( @date_start <= CmpProfileTariff_endDT OR CmpProfileTariff_endDT IS NULL ) )
					OR
					/* Дата окончания больше или равна существующей дате начала и меньше сущетствующего окончания */
					( @date_end >= CmpProfileTariff_begDT AND ( @date_end <= CmpProfileTariff_endDT OR CmpProfileTariff_endDT IS NULL ) )
				)
		";
		
		$result = $this->db->query( $query, $params );
		
		if ( !is_object( $result ) ) {
			return array( array( 'Error_Msg' => 'Во время проверки пересечения диапазона дат тарифов произошла ошибка.' ) );
		}
		
		if ( sizeof( $result->result('array') ) ) {
			return array( array( 'Error_Msg' => 'Диапазон дат тарифа пересекается с уже существующим.' ) );
		}
		
		return true;
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	public function checkLpuTariffDateRange( $data ){

		$params = array(
			'LpuTariff_id' => (int)$data['LpuTariff_id'],
			'LpuTariff_setDate' => $data['LpuTariff_setDate'],
			'LpuTariff_disDate' => $data['LpuTariff_disDate'],
			'TariffClass_id' => (int)$data['TariffClass_id'],
			'Lpu_id' => (int)$data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			DECLARE
				@date_start DATE,
				@date_end DATE;

			SET @date_start = :LpuTariff_setDate;
			SET @date_end = :LpuTariff_disDate;

			SELECT
				LpuTariff_id
			FROM
				v_LpuTariff (nolock)
			WHERE
				LpuTariff_id <> ISNULL(:LpuTariff_id, 0)
				AND Lpu_id = :Lpu_id
				AND TariffClass_id = :TariffClass_id
				AND (
					/* Дата начала меньше или равна существующей, а окончание не указано или в полученный диапазон попадает существующий тариф */
					( @date_start <= LpuTariff_setDate AND ( @date_end >= LpuTariff_disDate OR @date_end IS NULL ) )
					OR
					/* Дата начала больше или равна существующей и меньше или равна существующему окончанию */
					( @date_start >= LpuTariff_setDate AND ( @date_start <= LpuTariff_disDate OR LpuTariff_disDate IS NULL ) )
					OR
					/* Дата окончания больше или равна существующей дате начала и меньше сущетствующего окончания */
					( @date_end >= LpuTariff_setDate AND ( @date_end <= LpuTariff_disDate OR LpuTariff_disDate IS NULL ) )
				)
		";

		$result = $this->db->query( $query, $params );

		if ( !is_object( $result ) ) {
			return array( array( 'Error_Msg' => 'Во время проверки пересечения диапазона дат тарифов произошла ошибка.' ) );
		}

		if ( sizeof( $result->result('array') ) ) {
			return array( array( 'Error_Msg' => 'Диапазон дат тарифа пересекается с уже существующим.' ) );
		}

		return true;
	}

	/**
	 * @desc Проверка пересечения дат тарифов
	 * 
	 * @param array $data
	 * @return true|error message
	 */
	public function checkTariffDispDateRange( $data ){
		
		$params = array(
			'TariffDisp_id' => $data['TariffDisp_id'],
			'TariffDisp_begDT' => $data['TariffDisp_begDT'],
			'TariffDisp_endDT' => $data['TariffDisp_endDT'],
			'LpuSectionProfile_id' => $data['LpuSectionProfile_id'],
			'TariffClass_id' => $data['TariffClass_id'],
			'AgeGroupDisp_id' => $data['AgeGroupDisp_id'],
			'TariffClass_id' => $data['TariffClass_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id'],
			'Sex_id' => $data['Sex_id']
		);
		
		$query = "
			DECLARE
				@date_start DATE,
				@date_end DATE;

			SET @date_start = :TariffDisp_begDT;
			SET @date_end = :TariffDisp_endDT;

			SELECT
				TariffDisp_id
			FROM
				v_TariffDisp (nolock)
			WHERE
				TariffDisp_id <> ISNULL(:TariffDisp_id, 0)
				AND Lpu_id = :Lpu_id
				AND ISNULL(AgeGroupDisp_id, 0) = ISNULL(:AgeGroupDisp_id, 0)
				AND ISNULL(LpuSectionProfile_id, 0) = ISNULL(:LpuSectionProfile_id, 0)
				AND TariffClass_id = :TariffClass_id
				AND Sex_id = :Sex_id
				AND (	
					/* Дата начала меньше или равна существующей, а окончание не указано или в полученный диапазон попадает существующий тариф */
					( @date_start <= TariffDisp_begDT AND ( @date_end >= TariffDisp_endDT OR @date_end IS NULL ) )
					OR
					/* Дата начала больше или равна существующей и меньше или равна существующему окончанию */
					( @date_start >= TariffDisp_begDT AND ( @date_start <= TariffDisp_endDT OR TariffDisp_endDT IS NULL ) )
					OR
					/* Дата окончания больше или равна существующей дате начала и меньше сущетствующего окончания */
					( @date_end >= TariffDisp_begDT AND ( @date_end <= TariffDisp_endDT OR TariffDisp_endDT IS NULL ) )
				)
		";
		
		$result = $this->db->query( $query, $params );
		
		if ( !is_object( $result ) ) {
			return array( array( 'Error_Msg' => 'Во время проверки пересечения диапазона дат тарифов произошла ошибка.' ) );
		}
		
		if ( sizeof( $result->result('array') ) ) {
			return array( array( 'Error_Msg' => 'Диапазон дат тарифа пересекается с уже существующим.' ) );
		}
		
		return true;
	}
	
	
	/**
	 * @desc Удаляет тариф
	 * 
	 * @param array $data Данные
	 * @return type
	 */
	function deleteSmpTariff( $data ) {
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return false;
		}
		
		if ( !array_key_exists( 'CmpProfileTariff_id', $data ) || !$data['CmpProfileTariff_id'] ) {
			return false;
		}
		
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_CmpProfileTariff_delUsingLpuId
				@CmpProfileTariff_id = :CmpProfileTariff_id,
				@Lpu_id = :Lpu_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, array(
			'CmpProfileTariff_id' => $data['CmpProfileTariff_id'],
			'Lpu_id' => $data['Lpu_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return array( 'Error_Msg' => 'Во время удаления тарифа произошла ошибка.' );
	}

	/**
	 * @param $data
	 * @return array|bool
	 */
	function deleteTariffLpu( $data ) {

		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return false;
		}

		if ( !array_key_exists( 'LpuTariff_id', $data ) || !$data['LpuTariff_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_lpuTariff_del
				@LpuTariff_id = :LpuTariff_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'LpuTariff_id' => $data['LpuTariff_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return array( 'Error_Msg' => 'Во время удаления тарифа произошла ошибка.' );
	}

	/**
	 * Получение списка ников тарифов на МО
	 */
	function getLpuTariffClassList($lpu_id, $date) {
		$query = "
			select distinct TC.TariffClass_SysNick
			from v_LpuTariff LT with(nolock)
			inner join v_TariffClass TC with(nolock) on TC.TariffClass_id = LT.TariffClass_id
			where LT.Lpu_id = :Lpu_id
			and LT.LpuTariff_setDate <= :date
			and (LT.LpuTariff_disDate is null or LT.LpuTariff_disDate > :date)
		";
		$params = array('Lpu_id' => $lpu_id, 'date' => $date);

		$result = $this->queryResult($query, $params);

		$tariff_class_list = array();
		if ($result) {
			foreach($result as $item) {
				$tariff_class_list[] = $item['TariffClass_SysNick'];
			}
		}
		return $tariff_class_list;
	}

	/**
	 * @desc Удаляет вид лицензии
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteLpuLicenceProfile( $data ) {

		if ( !array_key_exists( 'LpuLicenceProfile_id', $data ) || !$data['LpuLicenceProfile_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC fed.p_LpuLicenceProfile_del
				@LpuLicenceProfile_id = :LpuLicenceProfile_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'LpuLicenceProfile_id' => $data['LpuLicenceProfile_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления вида лицензии произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет операцию с лицензией
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteLpuLicenceOperationLink( $data ) {

		if ( !array_key_exists( 'LpuLicenceOperationLink_id', $data ) || !$data['LpuLicenceOperationLink_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC fed.p_LpuLicenceOperationLink_del
				@LpuLicenceOperationLink_id = :LpuLicenceOperationLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'LpuLicenceOperationLink_id' => $data['LpuLicenceOperationLink_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления вида лицензии произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет операцию с лицензией
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteLpuLicenceLink( $data ) {

		if ( !array_key_exists( 'LpuLicenceLink_id', $data ) || !$data['LpuLicenceLink_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_LpuLicenceLink_del
				@LpuLicenceLink_id = :LpuLicenceLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'LpuLicenceLink_id' => $data['LpuLicenceLink_id'],
			'pmUser_id' => $data['pmUser_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления профиля лицензии произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет расходный материал
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteConsumables( $data ) {

		if ( !array_key_exists( 'Consumables_id', $data ) || !$data['Consumables_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_Consumables_del
				@Consumables_id = :Consumables_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'Consumables_id' => $data['Consumables_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления расходного материала.' );
        }
	}

	/**
	 * @desc Удаляет расходный материал
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteAmortization( $data ) {

		if ( !array_key_exists( 'Amortization_id', $data ) || !$data['Amortization_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_Amortization_del
				@Amortization_id = :Amortization_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'Amortization_id' => $data['Amortization_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления оценки износа произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет запись о эксплуатации материала
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteWorkData( $data ) {

		if ( !array_key_exists( 'WorkData_id', $data ) || !$data['WorkData_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_WorkData_del
				@WorkData_id = :WorkData_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'WorkData_id' => $data['WorkData_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления информации о эксплуатации материала произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет запись о простое МИ
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteDowntime( $data ) {

		if ( !array_key_exists( 'Downtime_id', $data ) || !$data['Downtime_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_Downtime_del
				@Downtime_id = :Downtime_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'Downtime_id' => $data['Downtime_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления простоя МИ произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет запись о свидетельстве проверки средств измерения
	 *
	 * @param array $data Данные
	 * @return type
	 */
	function deleteMeasureFundCheck( $data ) {

		if ( !array_key_exists( 'MeasureFundCheck_id', $data ) || !$data['MeasureFundCheck_id'] ) {
			return false;
		}

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_MeasureFundCheck_del
				@MeasureFundCheck_id = :MeasureFundCheck_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, array(
			'MeasureFundCheck_id' => $data['MeasureFundCheck_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Во время удаления оценки износа произошла ошибка.' );
        }
	}

	/**
	 * @desc Удаляет карточку медицинского изделия
	 * @param array $data Данные
	 * @return type
	 */
	function deleteMedProductCard( $data ) {

		if ( !array_key_exists( 'MedProductCard_id', $data ) || !$data['MedProductCard_id'] ) {
			return false;
		}

		//Удаляем производителя МИ если его больше нет ни у одного МИ в данной МО
		$OrgProducer_id = $this->getFirstResultFromQuery('
			select
				OrgProducer_id
			from
				passport.RegCertificate with(nolock)
			where
				MedProductCard_id = :MedProductCard_id
				and OrgProducer_id in (
					select
						OrgProducer_id
					from
						passport.RegCertificate with(nolock)
					group by
						OrgProducer_id
						having COUNT(OrgProducer_id) = 1
				)', $data);
		if (!empty($OrgProducer_id)){

			$query = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000);
				EXEC passport.p_OrgProducer_del
					@OrgProducer_id = :OrgProducer_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";

			$result = $this->db->query( $query, array('OrgProducer_id' => $OrgProducer_id));

			if ( !is_object( $result ) ) {
				return array( 'Error_Msg' => 'Ошибка во время удаления производителя МИ.' );
			}
		}

        //Удаляем записи из свзанных таблиц
        $main_id = array('key' => 'MedProductCard_id', 'value' =>$data['MedProductCard_id']);
        $linked_tables = array(
			array('schema' => 'passport', 'table' => 'Consumables'),
			array('schema' => 'passport', 'table' => 'Amortization'),
			array('schema' => 'passport', 'table' => 'AccountingData'),
			array('schema' => 'passport', 'table' => 'RegCertificate'),
			array('schema' => 'passport', 'table' => 'MeasureFund'),
			array('schema' => 'passport', 'table' => 'GosContract'),
			array('schema' => 'passport', 'table' => 'Downtime'),
			array('schema' => 'passport', 'table' => 'Consumables'),
			array('schema' => 'passport', 'table' => 'MeasureFundCheck')
		);

        $delete_from_linked = $this->deleteRecordsFromLinkedTables($main_id, $linked_tables);

        if (!$delete_from_linked || !empty($delete_from_linked['Error_Msg'])){
            return array('Error_Msg' => $delete_from_linked['Error_Msg']);
        }

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC passport.p_MedProductCard_del
				@MedProductCard_id = :MedProductCard_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, $data);

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Ошибка во время удаления карты МИ.' );
        }
	}

	/**
	 * @desc Удаляет здание МО
	 * @param array $data Данные
	 * @return type
	 */
	function deleteLpuBuildingPass( $data ) {

		if ( !array_key_exists( 'LpuBuildingPass_id', $data ) || !$data['LpuBuildingPass_id'] ) {
			return false;
		}

        //Удаляем записи из свзанных таблиц
        $main_id = array('key' => 'LpuBuildingPass_id', 'value' =>$data['LpuBuildingPass_id']);
        $linked_tables = array(
			array('schema' => 'fed', 'table' => 'MedTechnology')
		);

		if (getRegionNumber() == '101'){
			array_push($linked_tables, array('schema' => 'passport101', 'table' => 'BuildingLpu'));
		}

        $delete_from_linked = $this->deleteRecordsFromLinkedTables($main_id, $linked_tables);

        if (!$delete_from_linked || !empty($delete_from_linked['Error_Msg'])){
            return array('Error_Msg' => $delete_from_linked['Error_Msg']);
        }

		//удаляем связи здания МО с отделениями
		$query = "
			update
				LpuSection
			set
				LpuBuildingPass_id = null
			where
				LpuBuildingPass_id = :LpuBuildingPass_id
		";

		$result = $this->db->query($query, array('LpuBuildingPass_id' => $data['LpuBuildingPass_id']));

		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC dbo.p_LpuBuildingPass_del
				@LpuBuildingPass_id = :LpuBuildingPass_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query( $query, $data);

		if ( is_object( $result ) ) {
			return $result->result('array');
		} else {
		    return array( 'Error_Msg' => 'Ошибка во время удаления здания МО.' );
        }
	}


	/**
	 * @desc Удаляет тариф
	 * 
	 * @param array $data Данные
	 * @return type
	 */
	function deleteTariffDisp( $data )
	{
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_TariffDisp_del
				@TariffDisp_id = :TariffDisp_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$result = $this->db->query( $query, array(
			'TariffDisp_id' => $data['TariffDisp_id']
		));

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return array( 'Error_Msg' => 'Во время удаления тарифа произошла ошибка.' );
	}
	
	/**
	 * @desc Удаляет оборудование
	 * 
	 * @param array $data Данные
	 * @return type
	 */
	function deleteEquipment( $data )
	{
		if ($data['LpuEquipmentPacs_id'] > 0) {
			
			$query = "
				select top 1
					EvnUslugaParAssociatedResearches_id
				from
					EvnUslugaParAssociatedResearches (nolock)
				where
					LpuEquipmentPacs_id = :LpuEquipmentPacs_id					
			";
			$res = $this->db->query($query, array(
				'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id']				
			));			
			if ( is_object($res) ) {
				$resp = $res->result('array');
				if (count($resp) > 0 && !empty($resp[0]['EvnUslugaParAssociatedResearches_id'])) {
					return array( 'Error_Msg' => 'Невозможно удалить PACS с услугами. Удалите связанные услуги.' );
				}
			}
			
			
			$query = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000);
				EXEC p_LpuEquipmentPacs_del
					@LpuEquipmentPacs_id = :LpuEquipmentPacs_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query( $query, array(
				'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id']
			));			
		} else {		
			$query = "
				DECLARE
					@ErrCode int,
					@ErrMessage varchar(4000);
				EXEC p_LpuEquipment_del
					@LpuEquipment_id = :LpuEquipment_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMessage output;
				SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
			$result = $this->db->query( $query, array(
				'LpuEquipment_id' => $data['LpuEquipment_id']
			));	
		}
		if ( is_object( $result ) ) {
			$queryUpdData = "UPDATE dbo.MedService
							SET LpuEquipmentPacs_id = NULL, pmUser_updID = :pmUser_updID
							WHERE LpuEquipmentPacs_id = :LpuEquipmentPacs_id";
			$resUdpData = $this->db->query($queryUpdData, array(
				'LpuEquipmentPacs_id' => $data['LpuEquipmentPacs_id'],
				'pmUser_updID' => $data['pmUser_id']
			));
			if($resUdpData === false) {
				return array('Error_Msg' => 'Невозможно удалить PACS-сервер.');
			}

			return $result->result('array');
		}
		return array( 'Error_Msg' => 'Во время удаления оборудования произошла ошибка.' );
	}

	/**
	* Сохраняет данные формы транспорта ЛПУ
	*/
	function saveLpuTransport($data) {
		
		$procedure_action = '';	

		if ( !isset($data['LpuTransport_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuTransport_id;
			exec p_LpuTransport_" . $procedure_action . "
				@LpuTransport_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuTransport_Name = :LpuTransport_Name,
				@LpuTransport_Producer = :LpuTransport_Producer,
				@LpuTransport_ReleaseDT = :LpuTransport_ReleaseDT,
				@LpuTransport_PurchaseDT = :LpuTransport_PurchaseDT,
				@LpuTransport_Model = :LpuTransport_Model,
				@LpuTransport_Supplier = :LpuTransport_Supplier,
				@LpuTransport_RegNum = :LpuTransport_RegNum,
				@LpuTransport_EngineNum = :LpuTransport_EngineNum,
				@LpuTransport_BodyNum = :LpuTransport_BodyNum,
				@LpuTransport_ChassiNum = :LpuTransport_ChassiNum,
				@LpuTransport_StartUpDT = :LpuTransport_StartUpDT,
				@LpuTransport_WearPersent = :LpuTransport_WearPersent,
				@LpuTransport_PurchaseCost = :LpuTransport_PurchaseCost,
				@LpuTransport_ResidualCost = :LpuTransport_ResidualCost,
				@LpuTransport_ValuationDT = :LpuTransport_ValuationDT,
				@LpuTransport_IsNationProj = :LpuTransport_IsNationProj,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as LpuTransport_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuTransport_id' => $data['LpuTransport_id'],
			'LpuTransport_Name' => $data['LpuTransport_Name'],
			'LpuTransport_Producer' => $data['LpuTransport_Producer'],
			'LpuTransport_ReleaseDT' => $data['LpuTransport_ReleaseDT'],
			'LpuTransport_PurchaseDT' => $data['LpuTransport_PurchaseDT'],
			'LpuTransport_Model' => $data['LpuTransport_Model'],
			'LpuTransport_Supplier' => $data['LpuTransport_Supplier'],
			'LpuTransport_RegNum' => $data['LpuTransport_RegNum'],
			'LpuTransport_EngineNum' => $data['LpuTransport_EngineNum'],
			'LpuTransport_BodyNum' => $data['LpuTransport_BodyNum'],
			'LpuTransport_ChassiNum' => $data['LpuTransport_ChassiNum'],
			'LpuTransport_StartUpDT' => $data['LpuTransport_StartUpDT'],
			'LpuTransport_WearPersent' => $data['LpuTransport_WearPersent'],
			'LpuTransport_PurchaseCost' => $data['LpuTransport_PurchaseCost'],
			'LpuTransport_ResidualCost' => $data['LpuTransport_ResidualCost'],
			'LpuTransport_ValuationDT' => $data['LpuTransport_ValuationDT'],
			'LpuTransport_IsNationProj' => $data['LpuTransport_IsNationProj'],
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
	

	/**
	* Сохраняет данные формы оборудования ЛПУ
	*/
	function saveLpuEquipment($data) {
		
		$procedure_action = '';		
		
		if ($data['LpuEquipmentType_id'] == '4') {
			if ( !isset($data['LpuEquipmentPacs_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}
			
			if (!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',$data['PACS_ip_local'])) {
				return array(array('success'=>false,'Error_Msg'=>'Локальный IP адрес не соответствует стандарту'));
			}
			
			if (!preg_match('/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/',$data['PACS_ip_vip'])) {
				return array(array('success'=>false,'Error_Msg'=>'Локальный IP адрес не соответствует стандарту'));
			}
			
			
			$this->db->trans_begin();
			
			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuEquipmentPacs_id;
				exec p_LpuEquipmentPacs_" . $procedure_action . "
					@LpuEquipmentPacs_id = @Res output,
					@Lpu_id = :Lpu_id,
					@PACS_name = :PACS_name,
					@PACS_ip_local = :PACS_ip_local,
					@PACS_ip_vip = :PACS_ip_vip,
					@PACS_port = :PACS_port,
					@PACS_wado = :PACS_wado,
					@PACS_aet = :PACS_aet,
					@PACS_Interval = :PACS_Interval,
					@PACS_Interval_TimeType_id = :PACS_Interval_TimeType_id,
					@LpuPacsCompressionType_id = :LpuPacsCompressionType_id,
					@PACS_StudyAge = :PACS_StudyAge,
					@PACS_Age_TimeType_id = :PACS_Age_TimeType_id,
					@PACS_DeleteFromDb = :PACS_DeleteFromDb,
					@PACS_ExcludeTimeFrom = :PACS_ExcludeTimeFrom,
					@PACS_ExcludeTimeTo = :PACS_ExcludeTimeTo,
					@PACS_DeletePatientsWithoutStudies = :PACS_DeletePatientsWithoutStudies,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuEquipmentPacs_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$queryParams = array(
				'LpuEquipmentPacs_id' => isset($data['LpuEquipmentPacs_id'])?$data['LpuEquipmentPacs_id']:null,
				'Lpu_id' => $data['Lpu_id'],
				'PACS_name' => $data['PACS_name'],
				'PACS_ip_local' => $data['PACS_ip_local'],
				'PACS_ip_vip' => $data['PACS_ip_vip'],
				'PACS_port' => $data['PACS_port'],
				'PACS_wado' => $data['PACS_wado'],
				'PACS_aet' => $data['PACS_aet'],
				'PACS_Interval' => $data['PACS_Interval'],
				'PACS_Interval_TimeType_id' => $data['PACS_Interval_TimeType_id'],
				'PACS_ExcludeTimeFrom' => $data['PACS_ExcludeTimeFrom'],
				'PACS_ExcludeTimeTo' => $data['PACS_ExcludeTimeTo'],
				'LpuPacsCompressionType_id' => $data['LpuPacsCompressionType_id'],
				'PACS_StudyAge' => $data['PACS_StudyAge'],
				'PACS_Age_TimeType_id' => $data['PACS_Age_TimeType_id'],
				'PACS_DeleteFromDb' => ($data['PACS_DeleteFromDb']==='on')?1:2,
				'PACS_DeletePatientsWithoutStudies' => ($data['PACS_DeletePatientsWithoutStudies']==='on')?1:2,
				'pmUser_id' => $data['pmUser_id']
			);
			
			$res = $this->db->query($query, $queryParams);
			
			if ( is_object($res) ) {
				$response = $res->result('array');
			}
			else {
				$this->db->trans_rollback();
				return false;
			}
			
			if (!empty( $data['PACS_CronRequests'] ) && (!is_null($CronRequestArray = json_decode( toUTF($data['PACS_CronRequests']), true )))) {
				if (sizeof($CronRequestArray)==0) {
					$this->db->trans_rollback();
					return array(array('success'=>false,'Error_Msg'=>'Необходимо ввести хотя бы один CRON-запрос'));
				}
				
				
				
				$delResullt = $this->deleteCronRequestByLpuEquipmentPacsId(array(
					'LpuEquipmentPacs_id'=>$response[0]['LpuEquipmentPacs_id']
				));
				
				if (!$delResullt||((!isset($delResullt[0]))||(!isset($delResullt[0]['success']))||(!$delResullt[0]['success']))) {				
					$this->db->trans_rollback();
					return $delResullt;
				}
				foreach ($CronRequestArray as $CronRequest) {
					if (!empty($CronRequest)&&(trim($CronRequest['LpuEquipmentPacsCron_request'])!=='')) {
						$saveResult = $this->saveCronRequest(array(
							'LpuEquipmentPacs_id'=>$response[0]['LpuEquipmentPacs_id'],
							'LpuEquipmentPacsCron_request'=>$CronRequest['LpuEquipmentPacsCron_request'],
							'pmUser_id'=>$data['pmUser_id']
						));
						if (!$saveResult||((!isset($saveResult[0]))||(!isset($saveResult[0]['success']))||(!$saveResult[0]['success']))) {
							$this->db->trans_rollback();
							return $saveResult;
						}
					}
				}
			}
			
			
			
			$this->db->trans_commit();
			return $response;
			
			
		} else {
			if ( !isset($data['LpuEquipment_id']) ) {
				$procedure_action = "ins";
				$out = "output";
			}
			else {
				$procedure_action = "upd";
				$out = "";
			}
			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuEquipment_id;
				exec p_LpuEquipment_" . $procedure_action . "
					@LpuEquipment_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuEquipmentType_id = :LpuEquipmentType_id,
					@LpuEquipment_Name = :LpuEquipment_Name,
					@LpuEquipment_Producer = :LpuEquipment_Producer,
					@LpuEquipment_ReleaseDT = :LpuEquipment_ReleaseDT,
					@LpuEquipment_PurchaseDT = :LpuEquipment_PurchaseDT,
					@LpuEquipment_Model = :LpuEquipment_Model,
					@LpuEquipment_InvNum = :LpuEquipment_InvNum,
					@LpuEquipment_SerNum = :LpuEquipment_SerNum,
					@LpuEquipment_StartUpDT  = :LpuEquipment_StartUpDT,
					@LpuEquipment_WearPersent = :LpuEquipment_WearPersent,
					@LpuEquipment_ConclusionDT  = :LpuEquipment_ConclusionDT,
					@LpuEquipment_PurchaseCost = :LpuEquipment_PurchaseCost,
					@LpuEquipment_ResidualCost = :LpuEquipment_ResidualCost,
					@LpuEquipment_IsNationProj = :LpuEquipment_IsNationProj,
					@LpuEquipment_AmortizationTerm = :LpuEquipment_AmortizationTerm,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuEquipment_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$queryParams = array(
				'Lpu_id' => $data['Lpu_id'],
				'LpuEquipment_id' => $data['LpuEquipment_id'],
				'LpuEquipmentType_id' => $data['LpuEquipmentType_id'],
				'LpuEquipment_Name' => $data['LpuEquipment_Name'],
				'LpuEquipment_Producer' => $data['LpuEquipment_Producer'],
				'LpuEquipment_ReleaseDT' => $data['LpuEquipment_ReleaseDT'],
				'LpuEquipment_PurchaseDT' => $data['LpuEquipment_PurchaseDT'],
				'LpuEquipment_Model' => $data['LpuEquipment_Model'],
				'LpuEquipment_InvNum' => $data['LpuEquipment_InvNum'],
				'LpuEquipment_SerNum' => $data['LpuEquipment_SerNum'],
				'LpuEquipment_StartUpDT' => $data['LpuEquipment_StartUpDT'],
				'LpuEquipment_WearPersent' => $data['LpuEquipment_WearPersent'],
				'LpuEquipment_ConclusionDT' => $data['LpuEquipment_ConclusionDT'],
				'LpuEquipment_PurchaseCost' => $data['LpuEquipment_PurchaseCost'],
				'LpuEquipment_ResidualCost' => $data['LpuEquipment_ResidualCost'],
				'LpuEquipment_IsNationProj' => $data['LpuEquipment_IsNationProj'],
				'LpuEquipment_AmortizationTerm' => $data['LpuEquipment_AmortizationTerm'],
				'pmUser_id' => $data['pmUser_id']
			);
		}
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * Получение справочника типов единиц времени
	 */
	function getTimeTypes() {
		
		$query = "
			select 
				TT.TimeType_id,
				TT.TimeType_Code,
				TT.TimeType_Name
			from 
				v_TimeType TT (nolock)
		";
		$result = $this->db->query($query);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
	
	/**
	 * Получение справочника типов единиц времени
	 */
	function getPacsCompressionTypes() {
		
		$query = "
			select 
				LPCT.LpuPacsCompressionType_id,
				LPCT.LpuPacsCompressionType_Name
			from 
				v_LpuPacsCompressionType LPCT (nolock)
		";
		$result = $this->db->query($query);
		
		if ( is_object($result) ) {
			return $result->result('array');
		}

		return false;
	}
	
	
	
	/**
	* Сохраняет данные формы зданий ЛПУ
	*/
	function saveLpuBuilding($data) {
		
		$procedure_action = '';

		if (empty($data['PropertyType_id']) && getRegionNick() != 'kz') {
			return array(0 => array('Error_Msg' => 'Поле Форма владения обязательно для заполнения.'));
		}

        $queryParams = array(
            'Lpu_id' => $data['Lpu_id'],
            'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
            'LpuBuildingPass_StatPlace' => $data['LpuBuildingPass_StatPlace'],
            'LpuBuildingPass_AmbPlace' => $data['LpuBuildingPass_AmbPlace'],
            'LpuBuildingPass_BuildVol' => $data['LpuBuildingPass_BuildVol'],
            'LpuBuildingPass_EffBuildVol' => $data['LpuBuildingPass_EffBuildVol'],
            'BuildingCurrentState_id' => $data['BuildingCurrentState_id'],
            'DLink_id' => $data['DLink_id'],
            'DHotWater_id' => $data['DHotWater_id'],
            'DHeating_id' => $data['DHeating_id'],
            'DCanalization_id' => $data['DCanalization_id'],
            'LpuBuildingPass_FactVal' => $data['LpuBuildingPass_FactVal'],
            'LpuBuildingPass_ValDT' => $data['LpuBuildingPass_ValDT'],
	        'LpuBuildingPass_IsBalance' => ($data['LpuBuildingPass_IsBalance']),
            'LpuBuildingPass_IsAutoFFSig' => ($data['LpuBuildingPass_IsAutoFFSig']),
            'LpuBuildingPass_IsCallButton' => ($data['LpuBuildingPass_IsCallButton']),
            'LpuBuildingPass_IsSecurAlarm' => ($data['LpuBuildingPass_IsSecurAlarm']),
            'LpuBuildingPass_IsWarningSys' => ($data['LpuBuildingPass_IsWarningSys']),
            'LpuBuildingPass_IsFFWater' => ($data['LpuBuildingPass_IsFFWater']),
            'LpuBuildingPass_IsFFOutSignal' => ($data['LpuBuildingPass_IsFFOutSignal']),
            'LpuBuildingPass_IsConnectFSecure' => ($data['LpuBuildingPass_IsConnectFSecure']),
            'LpuBuildingPass_IsEmergExit' => ($data['LpuBuildingPass_IsEmergExit']),
            'LpuBuildingPass_RespProtect' => ($data['LpuBuildingPass_RespProtect']),
            'LpuBuildingPass_StretProtect' => ($data['LpuBuildingPass_StretProtect']),
            'LpuBuildingPass_CountDist' => $data['LpuBuildingPass_CountDist'],
            'LpuBuildingPass_FSDis' => $data['LpuBuildingPass_FSDis'],
            'LpuBuildingPass_IsBuildEmerg' => $data['LpuBuildingPass_IsBuildEmerg'],
            'LpuBuildingPass_IsNeedRec' => $data['LpuBuildingPass_IsNeedRec'],
            'LpuBuildingPass_IsNeedCap' => $data['LpuBuildingPass_IsNeedCap'],
            'LpuBuildingPass_IsNeedDem' => $data['LpuBuildingPass_IsNeedDem'],
            'LpuBuildingType_id' => $data['LpuBuildingType_id'],
            'LpuBuildingPass_Name' => $data['LpuBuildingPass_Name'],
            'LpuBuildingPass_Number' => $data['LpuBuildingPass_Number'],
            'BuildingAppointmentType_id' => $data['BuildingAppointmentType_id'],
            'LpuBuildingPass_Project' => $data['LpuBuildingPass_Project'],
            'LpuBuildingPass_YearBuilt' => $data['LpuBuildingPass_YearBuilt'],
            'LpuBuildingPass_YearRepair' => $data['LpuBuildingPass_YearRepair'],
            'LpuBuildingPass_PurchaseCost' => $data['LpuBuildingPass_PurchaseCost'],
            'LpuBuildingPass_ResidualCost' => $data['LpuBuildingPass_ResidualCost'],
            'LpuBuildingPass_Floors' => $data['LpuBuildingPass_Floors'],
            'LpuBuildingPass_TotalArea' => $data['LpuBuildingPass_TotalArea'],
            'LpuBuildingPass_WorkArea' => $data['LpuBuildingPass_WorkArea'],
            'LpuBuildingPass_RegionArea' => $data['LpuBuildingPass_RegionArea'],
            'LpuBuildingPass_WorkAreaWardSect' => $data['LpuBuildingPass_WorkAreaWardSect'],
            'LpuBuildingPass_WorkAreaWard' => $data['LpuBuildingPass_WorkAreaWard'],
            'LpuBuildingPass_PowerProjBed' => $data['LpuBuildingPass_PowerProjBed'],
            'LpuBuildingPass_PowerProjViz' => $data['LpuBuildingPass_PowerProjViz'],
            'LpuBuildingPass_OfficeCount' => $data['LpuBuildingPass_OfficeCount'],
            'LpuBuildingPass_OfficeArea' => $data['LpuBuildingPass_OfficeArea'],
            'BuildingType_id' => $data['BuildingType_id'],
            'LpuBuildingPass_NumProj' => $data['LpuBuildingPass_NumProj'],
            'BuildingHoldConstrType_id' => $data['BuildingHoldConstrType_id'],
            'BuildingOverlapType_id' => $data['BuildingOverlapType_id'],
            'LpuBuildingPass_IsAirCond' => $data['LpuBuildingPass_IsAirCond'],
            'LpuBuildingPass_IsVentil' => $data['LpuBuildingPass_IsVentil'],
            'LpuBuildingPass_IsElectric' => $data['LpuBuildingPass_IsElectric'],
            'LpuBuildingPass_IsPhone' => $data['LpuBuildingPass_IsPhone'],
            'LpuBuildingPass_IsHeat' => $data['LpuBuildingPass_IsHeat'],
            'LpuBuildingPass_IsColdWater' => $data['LpuBuildingPass_IsColdWater'],
            'LpuBuildingPass_IsHotWater' => $data['LpuBuildingPass_IsHotWater'],
            'LpuBuildingPass_IsSewerage' => $data['LpuBuildingPass_IsSewerage'],
            'LpuBuildingPass_IsDomesticGas' => $data['LpuBuildingPass_IsDomesticGas'],
            'LpuBuildingPass_IsMedGas' => $data['LpuBuildingPass_IsMedGas'],
            'LpuBuildingPass_HostLift' => $data['LpuBuildingPass_HostLift'],
            'LpuBuildingPass_HostLiftReplace' => $data['LpuBuildingPass_HostLiftReplace'],
            'LpuBuildingPass_PassLift' => $data['LpuBuildingPass_PassLift'],
            'LpuBuildingPass_PassLiftReplace' => $data['LpuBuildingPass_PassLiftReplace'],
            'LpuBuildingPass_TechLift' => $data['LpuBuildingPass_TechLift'],
            'LpuBuildingPass_TechLiftReplace' => $data['LpuBuildingPass_TechLiftReplace'],
            'LpuBuildingPass_WearPersent' => $data['LpuBuildingPass_WearPersent'],
            'PropertyType_id' => $data['PropertyType_id'],
            'LpuBuildingPass_IsInsulFacade' => $data['LpuBuildingPass_IsInsulFacade'],
            'LpuBuildingPass_IsFireAlarm' => $data['LpuBuildingPass_IsFireAlarm'],
            'LpuBuildingPass_IsHeatMeters' => $data['LpuBuildingPass_IsHeatMeters'],
            'LpuBuildingPass_IsWaterMeters' => $data['LpuBuildingPass_IsWaterMeters'],
            'LpuBuildingPass_IsRequirImprovement' => $data['LpuBuildingPass_IsRequirImprovement'],
            'LpuBuildingPass_IsDetached' => $data['LpuBuildingPass_IsDetached'],
            'LpuBuildingPass_YearProjDoc' => $data['LpuBuildingPass_YearProjDoc'],

            'MOArea_id' => $data['MOArea_id'],
            'LpuBuildingPass_BuildingIdent' => $data['LpuBuildingPass_BuildingIdent'],
            'BuildingTechnology_id' => $data['BuildingTechnology_id'],
            'LpuBuildingPass_MedWorkCabinet' => $data['LpuBuildingPass_MedWorkCabinet'],
            'LpuBuildingPass_BedArea' => $data['LpuBuildingPass_BedArea'],
            'LpuBuildingPass_IsFreeEnergy' => $data['LpuBuildingPass_IsFreeEnergy'],
            'FuelType_id' => $data['FuelType_id'],
			
			'LpuBuildingPass_CoordLat' => $data['LpuBuildingPass_CoordLat'],
			'LpuBuildingPass_CoordLong' => $data['LpuBuildingPass_CoordLong'],

            'pmUser_id' => $data['pmUser_id']
        );

		foreach ($queryParams as $key => &$value) {
			if (in_array($key, array('LpuBuildingPass_IsBalance', 'LpuBuildingPass_IsAutoFFSig', 'LpuBuildingPass_IsCallButton', 'LpuBuildingPass_IsSecurAlarm', 'LpuBuildingPass_IsWarningSys', 'LpuBuildingPass_IsFFWater', 'LpuBuildingPass_IsFFOutSignal', 'LpuBuildingPass_IsConnectFSecure', 'LpuBuildingPass_IsEmergExit', 'LpuBuildingPass_RespProtect', 'LpuBuildingPass_StretProtect', 'LpuBuildingPass_IsFreeEnergy'))) {
				if ($value == 'on' || $value == 1) {
					$value = 2;
				} else {
					$value = 1;
				}
			}
		}

        $query = "
            select
                top 1 LpuBuildingPass_id
            from
                v_LpuBuildingPass (nolock)
            where
                LpuBuildingPass_BuildingIdent = :LpuBuildingPass_BuildingIdent
                and LpuBuildingPass_id != ISNULL(:LpuBuildingPass_id, 0)
                and Lpu_id = :Lpu_id
        ";

        $res = $this->db->query($query, $queryParams);

        $response = $res->result('array');

        if ( is_array($response) && count($response) > 0 && !empty($response[0]['LpuBuildingPass_id'])) {
            return array(array('Error_Msg' => 'Указанный идентификатор уже используется в другом здании МО.'));
        }

		if ( !isset($data['LpuBuildingPass_id']) ) {
			$procedure_action = "ins";
			$data['LpuBuildingPass_id'] = null;
		}
		else {
			$procedure_action = "upd";
		}

		$query = "
			declare
				@lpu bigint,
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuBuildingPass_id;

			if ( @Res is not null )
				set @lpu = (select top 1 Lpu_id from LpuBuildingPass with (nolock) where LpuBuildingPass_id = @Res)
			else
				set @lpu = :Lpu_id;

			exec p_LpuBuildingPass_" . $procedure_action . "
				@LpuBuildingPass_id = @Res output,
				@Lpu_id = @lpu,
				@LpuBuildingPass_StatPlace = :LpuBuildingPass_StatPlace,
				@LpuBuildingPass_AmbPlace = :LpuBuildingPass_AmbPlace,
				@LpuBuildingPass_BuildVol = :LpuBuildingPass_BuildVol,
				@LpuBuildingPass_IsBalance = :LpuBuildingPass_IsBalance,
				@LpuBuildingPass_EffBuildVol = :LpuBuildingPass_EffBuildVol,
				@BuildingCurrentState_id = :BuildingCurrentState_id,
				@DLink_id = :DLink_id,
				@DHotWater_id = :DHotWater_id,
				@DHeating_id = :DHeating_id,
				@DCanalization_id = :DCanalization_id,
				@LpuBuildingPass_FactVal = :LpuBuildingPass_FactVal,
				@LpuBuildingPass_ValDT = :LpuBuildingPass_ValDT,
				@LpuBuildingPass_IsAutoFFSig = :LpuBuildingPass_IsAutoFFSig,
				@LpuBuildingPass_IsCallButton = :LpuBuildingPass_IsCallButton,
				@LpuBuildingPass_IsSecurAlarm = :LpuBuildingPass_IsSecurAlarm,
				@LpuBuildingPass_IsWarningSys = :LpuBuildingPass_IsWarningSys,
				@LpuBuildingPass_IsFFWater = :LpuBuildingPass_IsFFWater,
				@LpuBuildingPass_IsFFOutSignal = :LpuBuildingPass_IsFFOutSignal,
				@LpuBuildingPass_IsConnectFSecure = :LpuBuildingPass_IsConnectFSecure,
				@LpuBuildingPass_CountDist = :LpuBuildingPass_CountDist,
				@LpuBuildingPass_IsEmergExit = :LpuBuildingPass_IsEmergExit,
				@LpuBuildingPass_RespProtect = :LpuBuildingPass_RespProtect,
				@LpuBuildingPass_StretProtect = :LpuBuildingPass_StretProtect,
				@LpuBuildingPass_FSDis = :LpuBuildingPass_FSDis,
				@LpuBuildingPass_IsBuildEmerg = :LpuBuildingPass_IsBuildEmerg,
				@LpuBuildingPass_IsNeedRec = :LpuBuildingPass_IsNeedRec,
				@LpuBuildingPass_IsNeedCap = :LpuBuildingPass_IsNeedCap,
				@LpuBuildingPass_IsNeedDem = :LpuBuildingPass_IsNeedDem,
				@LpuBuildingType_id = :LpuBuildingType_id,
				@LpuBuildingPass_Name = :LpuBuildingPass_Name,
				@LpuBuildingPass_Number = :LpuBuildingPass_Number,
				@BuildingAppointmentType_id = :BuildingAppointmentType_id,
				@LpuBuildingPass_Project = :LpuBuildingPass_Project,
				@LpuBuildingPass_YearBuilt = :LpuBuildingPass_YearBuilt,
				@LpuBuildingPass_YearRepair = :LpuBuildingPass_YearRepair,
				@LpuBuildingPass_PurchaseCost = :LpuBuildingPass_PurchaseCost,
				@LpuBuildingPass_ResidualCost = :LpuBuildingPass_ResidualCost,
				@LpuBuildingPass_Floors = :LpuBuildingPass_Floors,
				@LpuBuildingPass_TotalArea = :LpuBuildingPass_TotalArea,
				@LpuBuildingPass_WorkArea = :LpuBuildingPass_WorkArea,
				@LpuBuildingPass_RegionArea = :LpuBuildingPass_RegionArea,
				@LpuBuildingPass_WorkAreaWardSect = :LpuBuildingPass_WorkAreaWardSect,
				@LpuBuildingPass_WorkAreaWard = :LpuBuildingPass_WorkAreaWard,
				@LpuBuildingPass_PowerProjBed = :LpuBuildingPass_PowerProjBed,
				@LpuBuildingPass_PowerProjViz = :LpuBuildingPass_PowerProjViz,
				@LpuBuildingPass_OfficeCount = :LpuBuildingPass_OfficeCount,
				@LpuBuildingPass_OfficeArea = :LpuBuildingPass_OfficeArea,
				@BuildingType_id = :BuildingType_id,
				@LpuBuildingPass_NumProj = :LpuBuildingPass_NumProj,
				@BuildingHoldConstrType_id = :BuildingHoldConstrType_id,
				@BuildingOverlapType_id = :BuildingOverlapType_id,
				@LpuBuildingPass_IsAirCond = :LpuBuildingPass_IsAirCond,
				@LpuBuildingPass_IsVentil = :LpuBuildingPass_IsVentil,
				@LpuBuildingPass_IsElectric = :LpuBuildingPass_IsElectric,
				@LpuBuildingPass_IsPhone = :LpuBuildingPass_IsPhone,
				@LpuBuildingPass_IsHeat = :LpuBuildingPass_IsHeat,
				@LpuBuildingPass_IsColdWater = :LpuBuildingPass_IsColdWater,
				@LpuBuildingPass_IsHotWater = :LpuBuildingPass_IsHotWater,
				@LpuBuildingPass_IsSewerage = :LpuBuildingPass_IsSewerage,
				@LpuBuildingPass_IsDomesticGas = :LpuBuildingPass_IsDomesticGas,
				@LpuBuildingPass_IsMedGas = :LpuBuildingPass_IsMedGas,
				@LpuBuildingPass_HostLift = :LpuBuildingPass_HostLift,
				@LpuBuildingPass_HostLiftReplace = :LpuBuildingPass_HostLiftReplace,
				@LpuBuildingPass_PassLift = :LpuBuildingPass_PassLift,
				@LpuBuildingPass_PassLiftReplace = :LpuBuildingPass_PassLiftReplace,
				@LpuBuildingPass_TechLift = :LpuBuildingPass_TechLift,
				@LpuBuildingPass_TechLiftReplace = :LpuBuildingPass_TechLiftReplace,
				@LpuBuildingPass_WearPersent = :LpuBuildingPass_WearPersent,
				@PropertyType_id = :PropertyType_id,
				@LpuBuildingPass_IsInsulFacade = :LpuBuildingPass_IsInsulFacade,
				@LpuBuildingPass_IsFireAlarm = :LpuBuildingPass_IsFireAlarm,
				@LpuBuildingPass_IsHeatMeters = :LpuBuildingPass_IsHeatMeters,
				@LpuBuildingPass_IsWaterMeters = :LpuBuildingPass_IsWaterMeters,
				@LpuBuildingPass_IsRequirImprovement = :LpuBuildingPass_IsRequirImprovement,
				@LpuBuildingPass_IsDetached = :LpuBuildingPass_IsDetached,
				@LpuBuildingPass_YearProjDoc = :LpuBuildingPass_YearProjDoc,

				@MOArea_id = :MOArea_id,
				@LpuBuildingPass_BuildingIdent = :LpuBuildingPass_BuildingIdent,
				@BuildingTechnology_id = :BuildingTechnology_id,
				@LpuBuildingPass_MedWorkCabinet = :LpuBuildingPass_MedWorkCabinet,
				@LpuBuildingPass_BedArea = :LpuBuildingPass_BedArea,
				@LpuBuildingPass_IsFreeEnergy = :LpuBuildingPass_IsFreeEnergy,
				@FuelType_id = :FuelType_id,
				
				@LpuBuildingPass_CoordLong = :LpuBuildingPass_CoordLong,
				@LpuBuildingPass_CoordLat = :LpuBuildingPass_CoordLat,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as LpuBuildingPass_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		//echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			$response = $res->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении здания МО [1]');
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				return $response;
			}
			else if ( empty($response[0]['LpuBuildingPass_id']) ) {
				return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении здания МО [2]');
			}

			if (getRegionNick() == 'kz') {

				$data['LpuBuildingPass_id'] = $response[0]['LpuBuildingPass_id'];

				$query = "
					select top 1
						BuildingLpu_id
					from passport101.v_BuildingLpu with (nolock)
					where
						LpuBuildingPass_id = :LpuBuildingPass_id
				";

				$result = $this->queryResult($query, array('LpuBuildingPass_id' => $response[0]['LpuBuildingPass_id']));

				if (!empty($result[0]['BuildingLpu_id'])) {
					$data['BuildingLpu_id'] = $result[0]['BuildingLpu_id'];
					$proc = 'upd';
				} else {
					$proc = 'ins';
					$data['BuildingLpu_id'] = null;
				}

				$queryParams = array(
					'LpuBuildingPass_id' => $data['LpuBuildingPass_id'],
					'BuildingLpu_id' => $data['BuildingLpu_id'],
					'PropertyClass_id' => $data['PropertyClass_id'],
					'BuildingUse_id' => $data['BuildingUse_id'],
					'BuildingClass_id' => $data['BuildingClass_id'],
					'BuildingState_id' => $data['BuildingState_id'],
					'HeatingType_id' => $data['HeatingType_id'],
					'BuildingLpu_RepEndDate' => $data['BuildingLpu_RepEndDate'],
					'BuildingLpu_RepCost' => $data['BuildingLpu_RepCost'],
					'BuildingLpu_RepCapBegDate' => $data['BuildingLpu_RepCapBegDate'],
					'BuildingLpu_RepCapEndDate' => $data['BuildingLpu_RepCapEndDate'],
					'BuildingLpu_RepCapCost' => $data['BuildingLpu_RepCapCost'],
					'ColdWaterType_id' => $data['ColdWaterType_id'],
					'VentilationType_id' => $data['VentilationType_id'],
					'ElectricType_id' => $data['ElectricType_id'],
					'GasType_id' => $data['GasType_id'],
					'BuildingLpu_DeprecCost' => $data['BuildingLpu_DeprecCost'],
					'BuildingLpu_RepBegDate' => $data['BuildingLpu_RepBegDate'],
					'pmUser_id' => $data['pmUser_id']
				);

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :BuildingLpu_id;
					exec passport101.p_BuildingLpu_" . $proc . "
						@BuildingLpu_id = @Res output,
						@PropertyClass_id = :PropertyClass_id,
						@BuildingUse_id = :BuildingUse_id,
						@BuildingClass_id = :BuildingClass_id,
						@BuildingState_id = :BuildingState_id,
						@HeatingType_id = :HeatingType_id,
						@BuildingLpu_RepEndDate = :BuildingLpu_RepEndDate,
						@BuildingLpu_RepCost = :BuildingLpu_RepCost,
						@BuildingLpu_RepCapBegDate = :BuildingLpu_RepCapBegDate,
						@BuildingLpu_RepCapEndDate = :BuildingLpu_RepCapEndDate,
						@BuildingLpu_RepCapCost = :BuildingLpu_RepCapCost,
						@LpuBuildingPass_id = :LpuBuildingPass_id,
						@ColdWaterType_id = :ColdWaterType_id,
						@VentilationType_id = :VentilationType_id,
						@ElectricType_id = :ElectricType_id,
						@GasType_id = :GasType_id,
						@BuildingLpu_DeprecCost = :BuildingLpu_DeprecCost,
						@BuildingLpu_RepBegDate = :BuildingLpu_RepBegDate,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output;
					select @Res as BuildingLpu_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
				";

				//echo getDebugSQL($query, $queryParams);die;
				$result = $this->db->query($query, $queryParams);

				if ( is_object($res) ) {
					$response_kz = $result->result('array');

					if (is_array($response_kz) && count($response_kz) == 1 && !empty($response_kz[0]['BuildingLpu_id'])){
						$response_kz[0]['LpuBuildingPass_id'] = $data['LpuBuildingPass_id'];
						return $response_kz;
					} else {
						return array('success' => false, 'Error_Msg' => 'Ошибка при сохранении здания МО [0] (Казахстан)');
					}
				}
				else {
					return false;
				}
			}

			//удаляем все связи у текущего здания
			$query = "
				update
					LpuSection with (rowlock)
				set
					LpuBuildingPass_id = null
				where
					LpuBuildingPass_id = :LpuBuildingPass_id
			";

			$result = $this->db->query($query, array('LpuBuildingPass_id' => $response[0]['LpuBuildingPass_id']));

			if (!empty($data['MOSectionsData'])) {
				$data['MOSectionsData'] = json_decode($data['MOSectionsData'], true);
				$MOSections = array();

				foreach ($data['MOSectionsData'] as $key => $value){
					array_push($MOSections, $value['LpuSection_id']);
				}

				if (!empty($MOSections[0])) {
					//добавляем актуальные связи
					$query = "
						update
							LpuSection with (rowlock)
						set
							LpuBuildingPass_id = :LpuBuildingPass_id
						where
							LpuSection_id in (".implode(',',$MOSections).")
					";
				}

				$result = $this->db->query($query, array('LpuBuildingPass_id' => $response[0]['LpuBuildingPass_id']));

			}

			return $response;
		}
		else {
			return false;
		}
	}
	

	/**
	* Сохраняет данные расчётных квот
	*/
	function saveLpuQuote($data) {
		
		$procedure_action = '';

		if ( !isset($data['LpuQuote_id']) ) {
			$procedure_action = "ins";
			$out = "output";
		}
		else {
			$procedure_action = "upd";
			$out = "";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :LpuQuote_id;
			exec p_LpuQuote_" . $procedure_action . "
				@LpuQuote_id = @Res output,
				@Lpu_id = :Lpu_id,
				@PayType_id = :PayType_id,
				@LpuQuote_HospCount = :LpuQuote_HospCount,
				@LpuQuote_BedDaysCount = :LpuQuote_BedDaysCount,
				@LpuQuote_VizitCount = :LpuQuote_VizitCount,
				@LpuQuote_Year = :LpuQuote_Year,
				@LpuQuote_begDate = :LpuQuote_begDate,
				@LpuQuote_endDate = :LpuQuote_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as LpuQuote_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuQuote_id' => $data['LpuQuote_id'],
			'PayType_id' => $data['PayType_id'],
			'LpuQuote_HospCount' => $data['LpuQuote_HospCount'],
			'LpuQuote_BedDaysCount' => $data['LpuQuote_BedDaysCount'],
			'LpuQuote_VizitCount' => $data['LpuQuote_VizitCount'],
			'LpuQuote_begDate' => $data['LpuQuote_begDate'],
			'LpuQuote_endDate' => $data['LpuQuote_endDate'],
			'LpuQuote_Year' => date('Y', strtotime($data['LpuQuote_begDate'])),
			'pmUser_id' => $data['pmUser_id']
		);
		
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $response = $res->result('array');
		}
		else {
			return false;
		}
	}

	
	/**
	* Сохраняет данные формы паспорта ЛПУ
	*/
	
	function saveLpuPassport($data) {
		// Перед сохранением, проверям наличие на совпадения ОГРН из формы с данными из справочника организаций
		$result = $this->isMatchOGRN($data);
		if ($result['state']) {
			return array(array('Error_Msg' => $result['msg']));
		}

		$this->beginTransaction();

		$procedure_action = '';	

		if ( $data['Lpu_id'] > 0 ) 
		{
			$procedure_action = "upd";
			$org_id = "(select Org_id from Lpu with (nolock) where Lpu_id = :Lpu_id)";
		}
		else 
		{
			$procedure_action = "ins";
			$org_id = ":Org_id";
		}

		// Сохраняем или редактируем адрес
		if(empty($data['fromAPI']))
		{
			// PAddress
			if ( !isset($data['PAddress_Address']) ) {
				$data['PAddress_id'] = NULL;
			}
			else {
				if ( !isset($data['PAddress_id']) ) {
					$procedure_action1 = "ins";
				}
				else {
					$procedure_action1 = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :PAddress_id;

					exec p_Address_" . $procedure_action1 . "
						@Server_id = :Server_id,
						@Address_id = @Res output,
						@KLAreaType_id = NULL,
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

					select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
				";

				$queryParams = array(
					'PAddress_id' => $data['PAddress_id'],
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data['PKLCountry_id'],
					'KLRgn_id' => $data['PKLRGN_id'],
					'KLSubRgn_id' => $data['PKLSubRGN_id'],
					'KLCity_id' => $data['PKLCity_id'],
					'KLTown_id' => $data['PKLTown_id'],
					'KLStreet_id' => $data['PKLStreet_id'],
					'Address_Zip' => $data['PAddress_Zip'],
					'Address_House' => $data['PAddress_House'],
					'Address_Corpus' => $data['PAddress_Corpus'],
					'Address_Flat' => $data['PAddress_Flat'],
					'Address_Address' => $data['PAddress_Address'],
					'pmUser_id' => $data['pmUser_id']
				);
				$res = $this->db->query($query, $queryParams);
				
				if ( !is_object($res) ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}

				$response = $res->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					$this->rollbackTransaction();
					return $response;
				}

				$data['PAddress_id'] = $response[0]['Address_id'];
			}
	        //echo "Первый модуль";

			// UAddress
			if ( !isset($data['UAddress_Address']) ) {
				$data['UAddress_id'] = NULL;
			}
			else {
				if ( !isset($data['UAddress_id']) ) {
					$procedure_action2 = "ins";
				}
				else {
					$procedure_action2 = "upd";
				}

				$query = "
					declare
						@Res bigint,
						@ErrCode bigint,
						@ErrMsg varchar(4000);
					set @Res = :UAddress_id;

					exec p_Address_" . $procedure_action2 . "
						@Server_id = :Server_id,
						@Address_id = @Res output,
						@KLAreaType_id = NULL,
						@KLCountry_id = :KLCountry_id,
						@KLRgn_id = :KLRgn_id,
						@KLSubRgn_id = :KLSubRgn_id,
						@KLCity_id = :KLCity_id,
						@KLTown_id = :KLTown_id,
						@KLStreet_id = :KLStreet_id,
						@Address_Zip = :Address_Zip,
						@Address_House = :Address_House,
						@Address_Corpus = :Address_Corpus,
						@Address_Flat = :Address_Flat,
						@Address_Address = :Address_Address,
						@pmUser_id = :pmUser_id,
						@Error_Code = @ErrCode output,
						@Error_Message = @ErrMsg output

					select @Res as Address_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
				";

				$queryParams = array(
					'UAddress_id' => $data['UAddress_id'],
					'Server_id' => $data['Server_id'],
					'KLCountry_id' => $data['UKLCountry_id'],
					'KLRgn_id' => $data['UKLRGN_id'],
					'KLSubRgn_id' => $data['UKLSubRGN_id'],
					'KLCity_id' => $data['UKLCity_id'],
					'KLTown_id' => $data['UKLTown_id'],
					'KLStreet_id' => $data['UKLStreet_id'],
					'Address_Zip' => $data['UAddress_Zip'],
					'Address_House' => $data['UAddress_House'],
					'Address_Corpus' => $data['UAddress_Corpus'],
					'Address_Flat' => $data['UAddress_Flat'],
					'Address_Address' => $data['UAddress_Address'],
					'pmUser_id' => $data['pmUser_id']
				);

				$res = $this->db->query($query, $queryParams);

				if ( !is_object($res) ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}

				$response = $res->result('array');

				if ( !is_array($response) || count($response) == 0 ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}
				else if ( !empty($response[0]['Error_Msg']) ) {
					$this->rollbackTransaction();
					return $response;
				}

				$data['UAddress_id'] = $response[0]['Address_id'];
			}
		}

        //echo "Третий модуль";

		//https://redmine.swan.perm.ru/issues/62755
		if(!empty($data['Lpu_endDate'])){ //Проверяем, является ли ЛПУ фондодержателем
			$queryCheckFondHolder = "
				select LpuPeriodFondHolder_id
				from LpuPeriodFondHolder with (nolock)
				where Lpu_id = :Lpu_id
			";
			$resultCheckFondHolder = $this->db->query($queryCheckFondHolder,array('Lpu_id' => $data['Lpu_id']));
			if(is_object($resultCheckFondHolder))
			{
				$res = $resultCheckFondHolder->result('array');
				if(count($res) > 0) //Если является фондодержателем, значит, апдетим LpuPeriodFondHolder
				{
					$queryUpdateIsNoAuto = "
						update LpuPeriodFondHolder
						set LpuPeriodFondHolder_IsNotAuto=2 where Lpu_id = :Lpu_id
					";
					$queryUpdateIsNoAutoParams = array('Lpu_id'=>$data['Lpu_id']);
					$result = $this->db->query($queryUpdateIsNoAuto,$queryUpdateIsNoAutoParams);
					if(!$result)
					{
						$this->rollbackTransaction();
						return array(array('Error_Msg' => 'Ошибка при обновлении данных в Объекте Период работы в системе Фондодержания '));
					}
				}
			}
		}

		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Org_id' => 0,
			'OrgInfo_id' => 0,
			'Server_id'=>$data['Server_id'], 
			
			'Lpu_Name' => $data['Lpu_Name'],
			'Lpu_Nick' => $data['Lpu_Nick'],
			'Lpu_Ouz' => $data['Lpu_Ouz'],
			'Lpu_f003mcod' => trim($data['Lpu_f003mcod']),
			'Lpu_RegNomN2' => trim($data['Lpu_RegNomN2']),
			'Org_RegName' => trim($data['Org_RegName']),

			'LpuPmuType_id' => $data['LpuPmuType_id'],
			'LpuPmuClass_id' => $data['LpuPmuClass_id'],
			'LpuType_id' => $data['LpuType_id'],
			'LpuAgeType_id' => $data['LpuAgeType_id'],
			'Lpu_begDate' => $data['Lpu_begDate'],
			'Lpu_endDate' => $data['Lpu_endDate'],
			'Lpu_pid' => $data['Lpu_pid'],
			'Lpu_nid' => $data['Lpu_nid'],
			'Lpu_IsLab' => $data['Lpu_IsLab'],

			'Lpu_StickNick' => $data['Lpu_StickNick'],
			'Lpu_StickAddress' => $data['Lpu_StickAddress'],
			'Lpu_DistrictRate' => $data['Lpu_DistrictRate'],

			'Org_lid' => $data['Org_lid'],
            'Lpu_DocReg' => $data['Lpu_DocReg'],
			'Lpu_RegDate' => $data['Lpu_RegDate'],
			'Lpu_PensRegNum' => $data['Lpu_PensRegNum'],
			'Lpu_RegNum' => $data['Lpu_RegNum'],
			'Lpu_FSSRegNum' => $data['Lpu_FSSRegNum'],

			'LpuSubjectionLevel_id' => $data['LpuSubjectionLevel_id'],
			'LpuLevel_id' => $data['LpuLevel_id'],
			'LpuLevel_cid' => $data['LpuLevel_cid'],
			'Lpu_VizitFact' => $data['Lpu_VizitFact'],
			'Lpu_KoikiFact' => $data['Lpu_KoikiFact'],
			'Lpu_AmbulanceCount' => $data['Lpu_AmbulanceCount'],
			'Lpu_FondOsn' => $data['Lpu_FondOsn'],
			'Lpu_FondEquip' => $data['Lpu_FondEquip'],
			
			'Lpu_ErInfo' => $data['Lpu_ErInfo'],
			'Lpu_IsAllowInternetModeration' => $data['Lpu_IsAllowInternetModeration'],
			'Lpu_MedCare' => $data['Lpu_MedCare'].' ',
			
			'Lpu_Phone' => $data['Lpu_Phone'],
			'Lpu_Email' => $data['Lpu_Email'],
			'Lpu_Www' => $data['Lpu_Www'],
			'Lpu_Worktime' => $data['Lpu_Worktime'],
		
			'UAddress_id' => $data['UAddress_id'],
			'PAddress_id' => $data['PAddress_id'],
			
			'Okopf_id' => $data['Okopf_id'],
			'Okved_id' => $data['Okved_id'],
			'Okogu_id' => $data['Okogu_id'],
			'Okfs_id' => $data['Okfs_id'],
			'Org_INN' => $data['Org_INN'],
			'Org_KPN' => $data['Org_KPN'],
			'Org_pid' => $data['Org_pid'],
			'Org_OGRN' => $data['Org_OGRN'],
			'Org_KPP' => $data['Org_KPP'],
			'Org_OKPO' => $data['Org_OKPO'],
			'Oktmo_id' => $data['Oktmo_id'],
			'Org_OKATO' => $data['Lpu_Okato'],
			'Org_OKDP' => $data['Org_OKDP'],
			'Lpu_IsSecret' => $data['Lpu_IsSecret'],
			'TOUZType_id' => $data['TOUZType_id'],
			'Org_tid' => $data['Org_tid'],

			'pmUser_id' => $data['pmUser_id'],
			'LpuOwnership_id' => $data['LpuOwnership_id'],
			'MOAreaFeature_id' => $data['MOAreaFeature_id'],
			'LpuBuildingPass_mid' => $data['LpuBuildingPass_mid'],
			'lpu_founder' => (empty($data['Lpu_Founder'])) ? null : $data['Lpu_Founder']
		);
		
		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000),
				@Org_rid bigint,
				@OrgType_id bigint,
				@Okonh_id bigint,
				@Org_IsEmailFixed bigint,
				@Org_pid bigint,
				@Org_isAccess bigint,
				@Org_Rukovod varchar(100),
				@Org_Buhgalt varchar(100),
				@Org_begDate datetime,
				@Org_endDate datetime,
				@Org_Description varchar(1024),
				@Org_RGN varchar(15),
				@Org_KBK varchar(20),
				@Org_Code int,
				@Org_IsNotForSystem int;
			set @Res = " . $org_id . ";

			select
				@Org_Code		= Org_Code,
				@Org_begDate	= Org_begDate,
				@Org_endDate	= Org_endDate,
				@Org_Description = Org_Description,
				@OrgType_id		= OrgType_id,
				@Okonh_id    	= Okonh_id,
				@Org_Rukovod	= Org_Rukovod,
				@Org_Buhgalt	= Org_Buhgalt,
				@Org_IsEmailFixed = Org_IsEmailFixed,
				@Org_pid = Org_pid,
				@Org_isAccess = ISNULL(Org_isAccess, 1),
				@Org_RGN = Org_RGN,
				@Org_KBK = Org_KBK,
				@Org_rid		= Org_rid,
				@Org_IsNotForSystem	=	Org_IsNotForSystem
            from
                v_org (nolock)
            where
                org_id = " . $org_id . ";

            exec p_Org_" . $procedure_action . "
				@Server_id = :Server_id,
				@Org_id = @Res output,
				@Org_Code = @Org_Code,
				@Org_Nick = :Lpu_Nick,
				@Org_rid = @Org_rid,
				@Org_begDate = @Org_begDate,
				@Org_endDate = :Lpu_endDate,
				@Org_Description = @Org_Description,
				@Org_Name = :Lpu_Name,
				@Org_RegName = :Org_RegName,
				@Okved_id = :Okved_id,
				@Org_KPN = :Org_KPN,
				@Org_INN = :Org_INN,
				@Org_OGRN = :Org_OGRN,
				@Org_Phone = :Lpu_Phone,
				@Org_Email = :Lpu_Email,
				@OrgType_id = ".(($org_id==0)?'11,':'@OrgType_id,')."
				@UAddress_id = :UAddress_id,
				@PAddress_id = :PAddress_id,
				@Okopf_id = :Okopf_id,
				@Okogu_id = :Okogu_id,
				@Okonh_id = @Okonh_id,
                @Org_pid = :Org_pid,
				@Okfs_id = :Okfs_id,
				@Org_KPP = :Org_KPP,
				@Org_OKPO = :Org_OKPO,
				@Org_OKATO = :Org_OKATO,
				@Oktmo_id = :Oktmo_id,
				@Org_OKDP = :Org_OKDP,
				@Org_Rukovod = @Org_Rukovod,
				@Org_Buhgalt = @Org_Buhgalt,
				@Org_StickNick = :Lpu_StickNick,
                @Org_IsEmailFixed = @Org_IsEmailFixed,
                @Org_KBK = @Org_KBK,
				@Org_isAccess = @Org_isAccess,
                @Org_RGN = @Org_RGN,
                @Org_WorkTime = :Lpu_Worktime,
                @Org_Www = :Lpu_Www,
				@Org_IsNotForSystem = @Org_IsNotForSystem,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as Org_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		//echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( !is_object($res) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}

		$response = $res->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $response;
		}

		$queryParams['Org_id'] = $response[0]['Org_id'];

        //echo "Четвертый модуль";
		if(empty($data['fromAPI']))
		{
			if (intval($data['Lpu_isCMP']) == 1) {
				if (!empty($data['OftenCallers_CallTimes'])) {
					$SetDataStorageQueryParams['OftenCallers_CallTimes']['DS_Name'] = 'OftenCallers_CallTimes';
					$SetDataStorageQueryParams['OftenCallers_CallTimes']['DS_Value'] = $data['OftenCallers_CallTimes'];
				}
				if (!empty($data['OftenCallers_SearchDays'])) {
					$SetDataStorageQueryParams['OftenCallers_SearchDays']['DS_Name'] = 'OftenCallers_SearchDays';
					$SetDataStorageQueryParams['OftenCallers_SearchDays']['DS_Value'] = $data['OftenCallers_SearchDays'];
				}
				if (!empty($data['OftenCallers_FreeDays'])) {
					$SetDataStorageQueryParams['OftenCallers_FreeDays']['DS_Name']   = 'OftenCallers_FreeDays';
					$SetDataStorageQueryParams['OftenCallers_FreeDays']['DS_Value']   = $data['OftenCallers_FreeDays'];
				}
			}
			
			$SetDataStorageQueryParams['Lpu_HasLocalPacsServer']['DS_Name'] = 'Lpu_HasLocalPacsServer';
			$SetDataStorageQueryParams['Lpu_HasLocalPacsServer']['DS_Value'] = $data['Lpu_HasLocalPacsServer'];
			$SetDataStorageQueryParams['Lpu_LocalPacsServerIP']['DS_Name'] = 'Lpu_LocalPacsServerIP';
			$SetDataStorageQueryParams['Lpu_LocalPacsServerIP']['DS_Value'] = $data['Lpu_LocalPacsServerIP'];
			$SetDataStorageQueryParams['Lpu_LocalPacsServerAetitle']['DS_Name']   = 'Lpu_LocalPacsServerAetitle';
			$SetDataStorageQueryParams['Lpu_LocalPacsServerAetitle']['DS_Value']   = $data['Lpu_LocalPacsServerAetitle'];
			$SetDataStorageQueryParams['Lpu_LocalPacsServerPort']['DS_Name']   = 'Lpu_LocalPacsServerPort';
			$SetDataStorageQueryParams['Lpu_LocalPacsServerPort']['DS_Value']   = $data['Lpu_LocalPacsServerPort'];
			$SetDataStorageQueryParams['Lpu_LocalPacsServerWadoPort']['DS_Name']   = 'Lpu_LocalPacsServerWadoPort';
			$SetDataStorageQueryParams['Lpu_LocalPacsServerWadoPort']['DS_Value']   = $data['Lpu_LocalPacsServerWadoPort'];
			
			foreach ($SetDataStorageQueryParams as $value) {
				$value['Lpu_id'] = $data['Lpu_id'];
				$value['pmUser_id'] = $data['pmUser_id'];
				$SetValueQuery = "
					Declare @DataStorage_id bigint;
					Declare @Error_Code int;
					Declare @Error_Message varchar(4000);
					Declare @Lpu_id bigint;
					Set @DataStorage_id = Null;
					Set @Error_Code = 0;
					Set @Error_Message = '';
					exec p_DataStorage_set 
						@DataStorage_id=@DataStorage_id output, 
						@Lpu_id = :Lpu_id, 
						@DataStorage_Name = :DS_Name, 
						@DataStorage_Value= :DS_Value, 
						@pmUser_id=:pmUser_id,
						@Error_Code = @Error_Code output, 
						@Error_Message = @Error_Message output;
					select @DataStorage_id as DataStorage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
				";	
				$result = $this->db->query($SetValueQuery,$value);

				if ( !is_object($result) ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}

				$result_arr = $result->result('array');

				if ( !is_array($result_arr) || count($result_arr) == 0 ) {
					$this->rollbackTransaction();
					return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
				}
				else if ( !empty($result_arr[0]['Error_Msg']) ) {
					$this->rollbackTransaction();
					return $result_arr;
				}
			}

			$query = "
				Select COUNT (*) as [count]
				from v_OrgInfo with (nolock)
				where
					[Org_id] = :Org_id and
					[OrgInfoType_id] = 1
			";

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}
			else if ( $response[0]['count'] > 0 ) {
				$info_action = "upd";
				$info_out = "(select OrgInfo_id from v_OrgInfo with (nolock) where [Org_id] = :Org_id and [OrgInfoType_id] = 1)";
			}
			else {
				$info_action = "ins";
				$info_out = ":OrgInfo_id";
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = " . $info_out . ";
				exec p_OrgInfo_" . $info_action . "
					@Server_id = :Server_id,
					@OrgInfo_id = @Res output,
					@Org_id = :Org_id,
					@OrgInfoType_id = 1,

					@OrgInfo_Info = :Lpu_MedCare,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as OrgInfo_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			//@OrgInfo_Info = :Lpu_MedCare,
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}
		}

		$addFields = '';
		$beforeQuery = '
			if ( @Res is not null )
				set @IsTest = (select top 1 Lpu_IsTest from v_Lpu with (nolock) where Lpu_id = @Res);
		';

		if ( in_array(getRegionNick(), array('perm','msk')) && empty($data['fromAPI'])) {
			$addFields = '
				@TOUZType_id = :TOUZType_id,
				@Org_tid = :Org_tid,
				@Lpu_InterCode = @int_code,
			';

			$beforeQuery .= '
				declare @int_code int;

				if ( @Res is not null )
					set @int_code = (select top 1 Lpu_InterCode from v_Lpu (nolock) where Lpu_id = @Res);

				if ( @int_code is null )
					set @int_code = ISNULL((select max(Lpu_InterCode) from v_Lpu (nolock) where Lpu_InterCode is not null), 0) + 1;

				if ( @int_code is null )
					set @int_code = 1;
			';
		}
	
		$query = "
			declare
				@Res bigint,
				@IsTest bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			set @Res = :Lpu_id;

			" . $beforeQuery . "

			exec p_Lpu_" . $procedure_action . "
				@Server_id = :Server_id,
				@Lpu_id = @Res output,
				@Org_id = :Org_id,
				@Lpu_Ouz = :Lpu_Ouz,
				@Lpu_f003mcod = :Lpu_f003mcod,
				@Lpu_RegNomN2 = :Lpu_RegNomN2,
				@LpuPmuType_id = :LpuPmuType_id,
				@LpuPmuClass_id = :LpuPmuClass_id,
				@LpuType_id = :LpuType_id,
				@MesAgeLpuType_id = :LpuAgeType_id,
				@Lpu_begDate = :Lpu_begDate,
				@Lpu_endDate = :Lpu_endDate,
				@Lpu_pid = :Lpu_pid,
				@Lpu_nid = :Lpu_nid,

				@Lpu_StickAddress = :Lpu_StickAddress,
				@Lpu_DistrictRate = :Lpu_DistrictRate,

				@Org_lid = :Org_lid,
				@Lpu_RegDate = :Lpu_RegDate,
				@Lpu_PensRegNum = :Lpu_PensRegNum,
				@Lpu_RegNum = :Lpu_RegNum,
				@Lpu_FSSRegNum = :Lpu_FSSRegNum,
				@Lpu_DocReg = :Lpu_DocReg,

				@LpuSubjectionLevel_id = :LpuSubjectionLevel_id,
				@LpuLevel_id = :LpuLevel_id,
				@LpuLevel_cid = :LpuLevel_cid,
				@Lpu_VizitFact = :Lpu_VizitFact,
				@Lpu_KoikiFact = :Lpu_KoikiFact,
				@Lpu_AmbulanceCount = :Lpu_AmbulanceCount,
				@Lpu_FondOsn = :Lpu_FondOsn,
				@Lpu_FondEquip = :Lpu_FondEquip,
				@Lpu_IsLab = :Lpu_IsLab,
				
				@LpuOwnership_id = :LpuOwnership_id,
				@MOAreaFeature_id = :MOAreaFeature_id,
				@lpu_founder = :lpu_founder,
				@LpuBuildingPass_mid = :LpuBuildingPass_mid,

				@Lpu_ErInfo = :Lpu_ErInfo,
				@Lpu_IsAllowInternetModeration = :Lpu_IsAllowInternetModeration,
				@Lpu_IsSecret = :Lpu_IsSecret,
				@Lpu_IsTest = @IsTest,
				" . $addFields . "
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
			select @Res as Lpu_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		";

		//echo getDebugSQL($query, $queryParams); exit;
        $result = $this->db->query($query, $queryParams);

		if ( !is_object($result) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}

		$response = $result->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $response;
		}

		if (!empty($queryParams['Lpu_IsSecret'])) {
			$query = "
				declare @Error_Code bigint = null
				declare @Error_Message varchar(4000) = ''
				set nocount on
				begin try
					update LpuBuilding with(rowlock)
					set LpuBuilding_IsAIDSCenter = :Lpu_IsSecret
					where Lpu_id = :Lpu_id
				end try
				begin catch
					set @Error_Code = error_number()
					set @Error_Message = error_message()
				end catch
				set nocount off
				select @Error_Code as Error_Code, @Error_Message as Error_Msg
			";
			$resp = $this->queryResult($query, $queryParams);
			if (!is_array($resp)) {
				$this->rollbackTransaction();
				return $this->createError('','Ошибка при проставлении признака "СПИД-центр" подразделениям МО (строка ' . __LINE__ . ')');
			}
			if (!$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$data['Lpu_id'] = $response[0]['Lpu_id'];

        // PasportMO
		$data['PasportMO_id'] = $this->getFirstResultFromQuery('select top 1 PasportMO_id from fed.PasportMO with (nolock) where Lpu_id = :Lpu_id', $data);

		if ( $data['PasportMO_id'] === false ) {
			$procedure_action1 = "ins";
		}
		else {
			$procedure_action1 = "upd";
		}

		$query = "
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
			set @Res = :PasportMO_id;
			exec fed.p_PasportMO_" . $procedure_action1 . "
				@PasportMO_id = @Res output,
				@Lpu_id = :Lpu_id,
				@DLocationLpu_id = :DLocationLpu_id,
				@PasportMO_IsFenceTer = :PasportMO_IsFenceTer,
				@PasportMO_IsNoFRMP = :PasportMO_IsNoFRMP,
				@PasportMO_IsSecur = :PasportMO_IsSecur,
				@PasportMO_IsMetalDoors = :PasportMO_IsMetalDoors,
				@PasportMO_IsVideo = :PasportMO_IsVideo,
				@PasportMO_IsAccompanying = :PasportMO_IsAccompanying,
				@PasportMO_IsAssignNasel = :PasportMO_IsAssignNasel,
				@PasportMO_MaxDistansePoint = :PasportMO_MaxDistansePoint,
				@PasportMO_IsTerLimited = :PasportMO_IsTerLimited,
				
				@PasportMO_KolServ = :PasportMO_KolServ,
				@PasportMO_KolServSel = :PasportMO_KolServSel,
				@PasportMO_KolServDet = :PasportMO_KolServDet,
				@PasportMO_KolCmpMes = :PasportMO_KolCmpMes,
				@PasportMO_KolCmpPay = :PasportMO_KolCmpPay,
				@PasportMO_KolCmpWage = :PasportMO_KolCmpWage,
				@LpuLevel_id = :FedLpuLevel_id,
				
				
				@DepartAffilType_id = :DepartAffilType_id,
				@Lpu_gid = :Lpu_gid,
				@InstitutionLevel_id = :InstitutionLevel_id,

				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output
			select @Res as PasportMO_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg
		";
		/*@PasportMO_Station = :PasportMO_Station,
		@PasportMO_DisStation = :PasportMO_DisStation,
		@PasportMO_Airport = :PasportMO_Airport,
		@PasportMO_DisAirport = :PasportMO_DisAirport,
		@PasportMO_Railway = :PasportMO_Railway,
		@PasportMO_Disrailway = :PasportMO_Disrailway,
		@PasportMO_Heliport = :PasportMO_Heliport,
		@PasportMO_DisHeliport = :PasportMO_DisHeliport,
		@PasportMO_MainRoad = :PasportMO_MainRoad,*/
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id'],
			'Lpu_gid' => $data['Lpu_gid'],
			'InstitutionLevel_id' => $data['InstitutionLevel_id'],
			'PasportMO_id' => (!empty($data['PasportMO_id'])?$data['PasportMO_id']:null),
			'PasportMO_IsTerLimited' => ($data['PasportMO_IsTerLimited'] == 'true')?(2):(1),
			'PasportMO_MaxDistansePoint' => $data['PasportMO_MaxDistansePoint'],//)?($data['PasportMO_MaxDistansePoint']):(null),
			'PasportMO_IsFenceTer' => ($data['PasportMO_IsFenceTer'] == 'true')?(2):(1),
			'PasportMO_IsAssignNasel' => ($data['PasportMO_IsAssignNasel'] == 'true')?(2):(1),
			'PasportMO_IsNoFRMP' => ($data['PasportMO_IsNoFRMP'] == 'true')?(2):(1),
			'PasportMO_IsSecur' => ($data['PasportMO_IsSecur'] == 'true')?(2):(1),
			'PasportMO_IsMetalDoors' => ($data['PasportMO_IsMetalDoors'] == 'true')?(2):(1),
			'PasportMO_IsVideo' => ($data['PasportMO_IsVideo'] == 'true')?(2):(1),
			'PasportMO_IsAccompanying' => ($data['PasportMO_IsAccompanying'] == 'true')?(2):(1),
			'DLocationLpu_id' => $data['DLocationLpu_id'],
			'DepartAffilType_id' => $data['DepartAffilType_id'],
			
			'PasportMO_KolServ' => $data['PasportMO_KolServ'],
			'PasportMO_KolServSel' => $data['PasportMO_KolServSel'],
			'PasportMO_KolServDet' => $data['PasportMO_KolServDet'],
			'PasportMO_KolCmpMes' => $data['PasportMO_KolCmpMes'],
			'PasportMO_KolCmpPay' => $data['PasportMO_KolCmpPay'],
			'PasportMO_KolCmpWage' => $data['PasportMO_KolCmpWage'],
			'FedLpuLevel_id' => $data['FedLpuLevel_id'],
			//'PasportMO_Station' => $data['PasportMO_Station'],
			//'PasportMO_DisStation' => $data['PasportMO_DisStation'],
			//'PasportMO_Airport' => $data['PasportMO_Airport'],
			//'PasportMO_DisAirport' => $data['PasportMO_DisAirport'],
			//'PasportMO_Railway' => $data['PasportMO_Railway'],
			//'PasportMO_Disrailway' => $data['PasportMO_Disrailway'],
			//'PasportMO_Heliport' => $data['PasportMO_Heliport'],
			//'PasportMO_DisHeliport' => $data['PasportMO_DisHeliport'],
			//'PasportMO_MainRoad' => $data['PasportMO_MainRoad'],
			'pmUser_id' => $data['pmUser_id']
		);
		//echo getDebugSQL($query, $queryParams); exit;
		$res = $this->db->query($query, $queryParams);

		if ( !is_object($res) ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}

		$response = $res->result('array');

		if ( !is_array($response) || count($response) == 0 ) {
			$this->rollbackTransaction();
			return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
		}
		else if ( !empty($response[0]['Error_Msg']) ) {
			$this->rollbackTransaction();
			return $response;
		}

		$data['PasportMO_id'] = $response[0]['PasportMO_id'];
		
		/*
		$query = "
			select top 1
				LpuCmpStationCategory_id,
				CmpStationCategory_id
			from v_LpuCmpStationCategory with(nolock)
			where Lpu_id = :Lpu_id
		";
		$queryParams = array('Lpu_id' => $data['Lpu_id']);
		$resp = $this->queryResult($query, $queryParams);
		if ($resp === false) {
			return $this->createError('', 'Ошибка при сохранении категорийности станции');
		}
		$resp = (count($resp)>0)?$resp[0]:array();
		switch(true) {
			case (empty($data['CmpStationCategory_id']) && !empty($resp['CmpStationCategory_id'])):
				$this->deleteLpuCmpStationCategory(array(
					'LpuCmpStationCategory_id' => $resp['LpuCmpStationCategory_id']
				));
				break;
			case (!empty($data['CmpStationCategory_id']) && empty($resp['CmpStationCategory_id'])):
				$this->saveLpuCmpStationCategory(array(
					'LpuCmpStationCategory_id' => null,
					'Lpu_id' => $data['Lpu_id'],
					'CmpStationCategory_id' => $data['CmpStationCategory_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				break;
			case (!empty($data['CmpStationCategory_id']) && !empty($resp['CmpStationCategory_id']) && $data['CmpStationCategory_id']!=$resp['CmpStationCategory_id']):
				$this->saveLpuCmpStationCategory(array(
					'LpuCmpStationCategory_id' => $resp['LpuCmpStationCategory_id'],
					'Lpu_id' => $data['Lpu_id'],
					'CmpStationCategory_id' => $data['CmpStationCategory_id'],
					'pmUser_id' => $data['pmUser_id']
				));
				break;
		}
		*/

		// Сохраняем уровень оказания МП
		if ( getRegionNick() == 'astra' && empty($data['fromAPI']) && (!empty($data['LpuLevelType_id']) || !empty($data['LevelType_id'])) ) {
			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);

				set @Res = :LpuLevelType_id;

				exec p_LpuLevelType_" . (!empty($data['LpuLevelType_id']) ? "upd" : "ins") . "
					@LpuLevelType_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LevelType_id = :LevelType_id,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;

				select @Res as LpuLevelType_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$queryParams = array(
				 'Lpu_id' => $data['Lpu_id']
				,'LpuLevelType_id' => (!empty($data['LpuLevelType_id']) ? $data['LpuLevelType_id'] : NULL)
				,'LevelType_id' => (!empty($data['LevelType_id']) ? $data['LevelType_id'] : NULL)
				,'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}
		}


		//Если добавляем новую организацию и в настройках проставлен флаг - записываем PassportToken_tid = -1
		if ('ins' == $procedure_action && $data['session']['setting']['server']['setOIDForNewLpu'] == 1) {
			$data['PassportToken_id'] = 0;
			$data['PassportToken_tid'] = -1;

			//проверяем существует ли ОИД для данного МО
			$query = "
				select
					PassportToken_id
				from
					fed.PassportToken with(nolock)
				where
					Lpu_id = :Lpu_id
			";

			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}

			if (is_array($response) && count($response) > 1) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'У МО может быть только один ОИД.'));
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :PassportToken_id;
				exec fed.p_PassportToken_ins
					@PassportToken_id = @Res output,
					@Lpu_id = :Lpu_id,
					@PassportToken_tid = :PassportToken_tid,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as PassportToken_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			$result = $this->db->query($query, $data);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			} else if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}
		}

		//Сохранение дополнительных данных для Казахстана
		if (getRegionNick() == 'kz') {

			$query = "
				Select top 1 LpuInfo_id
				from passport101.v_LpuInfo with (nolock)
				where Lpu_id = :Lpu_id
			";

			$queryParams = array(
				 'Lpu_id' => $data['Lpu_id']
				,'LpuInfo_id' => (!empty($data['LpuInfo_id']) ? $data['LpuInfo_id'] : NULL)
				,'LpuNomen_id' => (!empty($data['LpuNomen_id']) ? $data['LpuNomen_id'] : NULL)
				,'LpuInfo_BIN' => (!empty($data['LpuInfo_BIN']) ? $data['LpuInfo_BIN'] : NULL)
				,'PropertyClass_id' => (!empty($data['PropertyClass_id']) ? $data['PropertyClass_id'] : NULL)
				,'LpuInfo_AkkrNum' => (!empty($data['LpuInfo_AkkrNum']) ? $data['LpuInfo_AkkrNum'] : NULL)
				,'LpuInfo_AkkrDate' => (!empty($data['LpuInfo_AkkrDate']) ? $data['LpuInfo_AkkrDate'] : NULL)
				,'SubjectionType_id' => (!empty($data['SubjectionType_id']) ? $data['SubjectionType_id'] : NULL)
				,'LpuInfo_Area' => (!empty($data['LpuInfo_Area']) ? $data['LpuInfo_Area'] : NULL)
				,'LpuInfo_Distance' => (!empty($data['LpuInfo_Distance']) ? $data['LpuInfo_Distance'] : NULL)
				,'pmUser_id' => $data['pmUser_id']
			);

			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['LpuInfo_id']) ) {
				$info_action = "upd";
				$data['LpuInfo_id'] = $response[0]['LpuInfo_id'];
				$queryParams['LpuInfo_id'] = $response[0]['LpuInfo_id'];
			} else {
				$info_action = "ins";
				$data['LpuInfo_id'] = null;
				$queryParams['LpuInfo_id'] = null;
			}

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuInfo_id ;
				exec passport101.p_LpuInfo_" . $info_action . "
					@LpuInfo_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuNomen_id = :LpuNomen_id,
					@LpuInfo_BIN = :LpuInfo_BIN,
					@PropertyClass_id = :PropertyClass_id,
					@LpuInfo_AkkrNum = :LpuInfo_AkkrNum,
					@LpuInfo_AkkrDate = :LpuInfo_AkkrDate,
					@SubjectionType_id = :SubjectionType_id,
					@LpuInfo_Area = :LpuInfo_Area,
					@LpuInfo_Distance = :LpuInfo_Distance,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuInfo_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";

			//echo getDebugSQL($query, $queryParams);die;
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}

			$this->load->model('ServiceSUR_model');
			$this->ServiceSUR_model->saveLpuLink($data['Lpu_id'], null);
			$this->ServiceSUR_model->saveLpuLink($data['Lpu_id'], $data['LpuSUR_id']);
		}

		//Сохранение дополнительных данных для Беларуси
		if ( getRegionNick() == 'by' ) {
			$query = "
				select top 1
					 LpuSpec_id
					,convert(varchar(10), LpuSpec_begDate, 120) as LpuSpec_begDate
					,convert(varchar(10), LpuSpec_endDate, 120) as LpuSpec_endDate
				from passport201.v_LpuSpec with (nolock)
				where Lpu_id = :Lpu_id
				order by LpuSpec_id desc
			";
			$queryParams = array('Lpu_id' => $data['Lpu_id']);
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( is_array($response) && count($response) > 0 && !empty($response[0]['LpuSpec_id']) ) {
				$ls_action = "upd";
				$data['LpuSpec_id'] = $response[0]['LpuSpec_id'];
				$data['LpuSpec_begDate'] = $response[0]['LpuSpec_begDate'];
				$data['LpuSpec_endDate'] = $response[0]['LpuSpec_endDate'];
			}
			else {
				$ls_action = "ins";
			}

			$queryParams = array(
				 'Lpu_id' => $data['Lpu_id']
				,'LpuSpec_id' => (!empty($data['LpuSpec_id']) ? $data['LpuSpec_id'] : NULL)
				,'LpuSpecType_id' => (!empty($data['LpuSpecType_id']) ? $data['LpuSpecType_id'] : NULL)
				,'LpuSpec_begDate' => (!empty($data['LpuSpec_begDate']) ? $data['LpuSpec_begDate'] : date('Y-m-d'))
				,'LpuSpec_endDate' => (!empty($data['LpuSpec_endDate']) ? $data['LpuSpec_endDate'] : NULL)
				,'pmUser_id' => $data['pmUser_id']
			);

			$query = "
				declare
					@Res bigint,
					@ErrCode bigint,
					@ErrMsg varchar(4000);
				set @Res = :LpuSpec_id;
				exec passport201.p_LpuSpec_" . $ls_action . "
					@LpuSpec_id = @Res output,
					@Lpu_id = :Lpu_id,
					@LpuSpecType_id = :LpuSpecType_id,
					@LpuSpec_begDate = :LpuSpec_begDate,
					@LpuSpec_endDate = :LpuSpec_endDate,
					@pmUser_id = :pmUser_id,
					@Error_Code = @ErrCode output,
					@Error_Message = @ErrMsg output;
				select @Res as LpuSpec_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
			";
			//echo getDebugSQL($query, $queryParams);die;
			$result = $this->db->query($query, $queryParams);

			if ( !is_object($result) ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}

			$response = $result->result('array');

			if ( !is_array($response) || count($response) == 0 ) {
				$this->rollbackTransaction();
				return array(array('Error_Msg' => 'Ошибка при выполнении запроса к базе данных (строка ' . __LINE__ . ')'));
			}
			else if ( !empty($response[0]['Error_Msg']) ) {
				$this->rollbackTransaction();
				return $response;
			}
		}

		$this->commitTransaction();

		return array(array('Error_Msg' => ''));
        //echo "Пятый, самый главный модуль";
	}

	/**
	 * Удаление категорийности станции СМП
	 */
	function deleteLpuCmpStationCategory($data) {
		$params = array('LpuCmpStationCategory_id' => $data['LpuCmpStationCategory_id']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_LpuCmpStationCategory_del
				@LpuCmpStationCategory_id = :LpuCmpStationCategory_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранения категорийности станции СМП
	 */
	function saveLpuCmpStationCategory($data) {
		$params = array(
			'LpuCmpStationCategory_id' => (!empty($data['LpuCmpStationCategory_id']))?$data['LpuCmpStationCategory_id']:null,
			'Lpu_id' => $data['Lpu_id'],
			'CmpStationCategory_id' => $data['CmpStationCategory_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		if (!empty($data['LpuCmpStationCategory_id'])) {
			$procedure = 'p_LpuCmpStationCategory_upd';
		} else {
			$procedure = 'p_LpuCmpStationCategory_ins';
		}

		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :LpuCmpStationCategory_id;
			exec {$procedure}
				@LpuCmpStationCategory_id = @Res output,
				@Lpu_id = :Lpu_id,
				@CmpStationCategory_id = :CmpStationCategory_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as LpuCmpStationCategory_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}
	
	/**
	 *	Получение данных для таблицы с периодами ОМС
	 */
	function loadLpuPeriodOMSGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuDispContract_id']))
		{
			$filter .= ' and LDC.LpuDispContract_id = :LpuDispContract_id';
			$params['LpuDispContract_id'] = $data['LpuDispContract_id'];
		}
		$query = "
		Select
			Lpu_id,
			LpuPeriodOMS_id,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_begDate as datetime),104),'')) as LpuPeriodOMS_begDate,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_endDate as datetime),104),'')) as LpuPeriodOMS_endDate,
			LpuPeriodOMS_DogNum,
			LpuPeriodOMS_RegNumC,
			LpuPeriodOMS_RegNumN
		from v_LpuPeriodOMS (nolock)
		where
			Lpu_id = :Lpu_id and LpuPeriodOMS_pid is null and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *	Получение данных для таблицы с периодами ОМС
	 */
	function loadLpuOMSGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('LpuPeriodOMS_pid' => $data['LpuPeriodOMS_pid']);
		
		$query = "
		Select
			Lpu_id,
			LpuPeriodOMS_id,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_begDate as datetime),104),'')) as LpuPeriodOMS_begDate,
			Org.Org_Nick,
			LpuPeriodOMS_DogNum,
			LpuPeriodOMS_RegNumC,
			LpuPeriodOMS_RegNumN,
			LpuPeriodOMS_Descr
		from v_LpuPeriodOMS LPOMS with (nolock)
		left join v_Org Org with (nolock) on LPOMS.Org_id = Org.Org_id
		where
			LpuPeriodOMS_pid = :LpuPeriodOMS_pid and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *	Получение данных о периоде ОМС
	 */
	function loadLpuPeriodOMS($data) {
		$filter = "";
		$queryParams = array(
			'LpuPeriodOMS_id' => $data['LpuPeriodOMS_id']
		);

		if (isset($data['Lpu_id']) && !empty($data['filterByMO'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		$query = "
			SELECT TOP 1
				Lpu_id,
				LpuPeriodOMS_id,
				RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_begDate as datetime),104),'')) as LpuPeriodOMS_begDate,
				RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_endDate as datetime),104),'')) as LpuPeriodOMS_endDate,
				LpuPeriodOMS_DogNum,
				LpuPeriodOMS_RegNumC,
				LpuPeriodOMS_RegNumN
			FROM
				v_LpuPeriodOMS (nolock)
			WHERE
				LpuPeriodOMS_id = :LpuPeriodOMS_id
				{$filter}
		";
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}
	/**
	 *  Возвращает флаг о наличии на МО периода ОМС
	 */
	function hasLpuPeriodOMS($data) {
		$params = array(
			'Org_id' => $data['Org_oid'],
			'Date' => !empty($data['Date'])?$data['Date']:null
		);

		$query = "
			declare @Date date = :Date
			if @Date is null set @Date = (select dbo.tzGetDate())
			select top 1 count(*) as cnt
			from v_LpuPeriodOMS LPOMS
			inner join v_Lpu L with(nolock) on L.Lpu_id = LPOMS.Lpu_id
			where
				L.Org_id = :Org_id
				and LPOMS.LpuPeriodOMS_begDate <= @Date
				and (LPOMS.LpuPeriodOMS_endDate is null or LPOMS.LpuPeriodOMS_endDate > @Date)
		";

		$count = $this->getFirstResultFromQuery($query, $params);
		if ($count === false) {
			return $this->createError('Ошибка при поиске периодов ОМС');
		}
		$hasLpuPeriodOMS = ($count > 0);

		return array(array('success' => true, 'hasLpuPeriodOMS' => $hasLpuPeriodOMS));
	}
	/**
	 *	Получение данных о периоде ОМС
	 */
	function loadLpuOMS($data) {
		$query = "
			SELECT TOP 1
				Lpu_id,
				LpuPeriodOMS_id,
				LpuPeriodOMS_pid,
				RTrim(IsNull(convert(varchar,cast(LpuPeriodOMS_begDate as datetime),104),'')) as LpuPeriodOMS_begDate,
				LpuPeriodOMS_DogNum,
				Org_id,
				LpuPeriodOMS_RegNumC,
				LpuPeriodOMS_RegNumN,
				LpuPeriodOMS_Descr
			FROM
				v_LpuPeriodOMS (nolock)
			WHERE
				LpuPeriodOMS_id = ?
		";
		$res = $this->db->query($query, array($data['LpuPeriodOMS_id']));

		if ( is_object($res) )
			return $res->result('array');
		else
			return false;
	}	
	/**
	 *	Получение данных для грида ЛЛО
	 */
	function loadLpuPeriodDLOGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		$addSelect = "";
		if (in_array(getRegionNick(), array('ufa','msk'))) {
			$addSelect .= ',LP.LpuPeriodDLO_Code';
		}
		$addFrom = '';
		if (getRegionNick()=='msk') {
			$addSelect .= ',ISNULL(LU.LpuUnit_Name,L.Lpu_Name) as LpuPeriodDLO_Name';
			$addFrom = 'left join v_LpuUnit LU (nolock) on LU.LpuUnit_id = LP.LpuUnit_id';
			$addFrom .= ' left join v_Lpu L (nolock) on L.Lpu_id = LP.Lpu_id';
		}

		
		if (isset($data['LpuDispContract_id']))
		{
			$filter .= ' and LDC.LpuDispContract_id = :LpuDispContract_id';
			$params['LpuDispContract_id'] = $data['LpuDispContract_id'];
		}
		$query = "
		Select
			LP.Lpu_id,
			LP.LpuPeriodDLO_id,
			RTrim(IsNull(convert(varchar,cast(LP.LpuPeriodDLO_begDate as datetime),104),'')) as LpuPeriodDLO_begDate,
			RTrim(IsNull(convert(varchar,cast(LP.LpuPeriodDLO_endDate as datetime),104),'')) as LpuPeriodDLO_endDate
			{$addSelect}
		from v_LpuPeriodDLO LP (nolock)
			{$addFrom}
		where
			LP.Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *	Получение данных для грида работы в системе Промед
	 */
	function loadOrgWorkPeriodGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Org_id' => $data['Org_id']);
		$query = "
		Select
			Org_id,
			OrgWorkPeriod_id,
			RTrim(IsNull(convert(varchar,cast(OrgWorkPeriod_begDate as datetime),104),'')) as OrgWorkPeriod_begDate,
			RTrim(IsNull(convert(varchar,cast(OrgWorkPeriod_endDate as datetime),104),'')) as OrgWorkPeriod_endDate
		from v_OrgWorkPeriod (nolock)
		where
			Org_id = :Org_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	/**
	 *	Получение периода работы в системе Промед
	 */
	function loadOrgWorkPeriod($data) 
	{
		$query = "
		Select top 1
			Org_id,
			OrgWorkPeriod_id,
			RTrim(IsNull(convert(varchar,cast(OrgWorkPeriod_begDate as datetime),104),'')) as OrgWorkPeriod_begDate,
			RTrim(IsNull(convert(varchar,cast(OrgWorkPeriod_endDate as datetime),104),'')) as OrgWorkPeriod_endDate
		from v_OrgWorkPeriod (nolock)
		where
			OrgWorkPeriod_id = ?";
		$result = $this->db->query($query, array($data['OrgWorkPeriod_id']));

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение периода ЛЛО
	 */
	function loadLpuPeriodDLO($data) 
	{
		$filter = "";
		$queryParams = array(
			'LpuPeriodDLO_id' => $data['LpuPeriodDLO_id']
		);

		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}

		if (empty($data['LpuPeriodDLO_id'])) {
			$query = "
				Select
					:Lpu_id as Lpu_id,
					case when COUNT(Lpu_id)>0 then 'groups' else 'mo' end as LpuPeriodTypeValue
				from v_LpuPeriodDLO (nolock)
				WHERE
					LpuPeriodDLO_endDate is null
					and LpuUnit_id is not null
					{$filter}
			";
		}
		else {
			$query = "
				Select TOP 1
					Lpu_id,
					LpuUnit_id,
					LpuPeriodDLO_id,
					RTrim(IsNull(convert(varchar,cast(LpuPeriodDLO_begDate as datetime),104),'')) as LpuPeriodDLO_begDate,
					RTrim(IsNull(convert(varchar,cast(LpuPeriodDLO_endDate as datetime),104),'')) as LpuPeriodDLO_endDate,
					LpuPeriodDLO_Code
				from v_LpuPeriodDLO (nolock)
				WHERE
					LpuPeriodDLO_id = :LpuPeriodDLO_id
					{$filter}
			";
		}
		$result = $this->db->query($query, $queryParams);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuPeriodDMSGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuDispContract_id']))
		{
			$filter .= ' and LDC.LpuDispContract_id = :LpuDispContract_id';
			$params['LpuDispContract_id'] = $data['LpuDispContract_id'];
		}
		$query = "
		Select
			Lpu_id,
			LpuPeriodDMS_id,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodDMS_begDate as datetime),104),'')) as LpuPeriodDMS_begDate,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodDMS_endDate as datetime),104),'')) as LpuPeriodDMS_endDate,
			LpuPeriodDMS_DogNum
		from v_LpuPeriodDMS (nolock)
		where
			Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuPeriodDMS($data) 
	{
		$query = "
		Select TOP 1
			Lpu_id,
			LpuPeriodDMS_id,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodDMS_begDate as datetime),104),'')) as LpuPeriodDMS_begDate,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodDMS_endDate as datetime),104),'')) as LpuPeriodDMS_endDate,
			LpuPeriodDMS_DogNum
		from v_LpuPeriodDMS (nolock)
		WHERE
			LpuPeriodDMS_id = ?";
		$result = $this->db->query($query, array($data['LpuPeriodDMS_id']));

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuPeriodFondHolderGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuDispContract_id']))
		{
			$filter .= ' and LDC.LpuDispContract_id = :LpuDispContract_id';
			$params['LpuDispContract_id'] = $data['LpuDispContract_id'];
		}
		if(isset($data['HolderDate'])){
			$filter .= ' and (LpuPeriodFondHolder_begDate <= :HolderDate and (LpuPeriodFondHolder_endDate is null or LpuPeriodFondHolder_endDate >= :HolderDate))';
			$params['HolderDate'] = $data['HolderDate'];
		}
		$query = "
		Select
			LPFH.Lpu_id,
			LPFH.LpuPeriodFondHolder_id,
			LPFH.LpuRegionType_id,
			LRT.LpuRegionType_Name,
			LRT.LpuRegionType_SysNick,
			RTrim(IsNull(convert(varchar,cast(LPFH.LpuPeriodFondHolder_begDate as datetime),104),'')) as LpuPeriodFondHolder_begDate,
			RTrim(IsNull(convert(varchar,cast(LPFH.LpuPeriodFondHolder_endDate as datetime),104),'')) as LpuPeriodFondHolder_endDate
		from v_LpuPeriodFondHolder LPFH (nolock)
			left join v_LpuRegionType LRT (nolock) on LPFH.LpuRegionType_id = LRT.LpuRegionType_id
		where
			LPFH.Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuPeriodFondHolder($data) 
	{
		$query = "
		Select TOP 1
			Lpu_id,
			LpuPeriodFondHolder_id,
			LpuRegionType_id,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodFondHolder_begDate as datetime),104),'')) as LpuPeriodFondHolder_begDate,
			RTrim(IsNull(convert(varchar,cast(LpuPeriodFondHolder_endDate as datetime),104),'')) as LpuPeriodFondHolder_endDate
		from v_LpuPeriodFondHolder (nolock)
		WHERE
			LpuPeriodFondHolder_id = ?";
		$result = $this->db->query($query, array($data['LpuPeriodFondHolder_id']));

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
		
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuLicenceGrid($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuLicence_id']))
		{
			$filter .= ' and LpuLicence_id = :LpuLicence_id';
			$params['LpuLicence_id'] = $data['LpuLicence_id'];
		}
		if (!empty($data['LpuLicence_Num']))
		{
			$filter .= ' and LpuLicence_Num = :LpuLicence_Num';
			$params['LpuLicence_Num'] = $data['LpuLicence_Num'];
		}
		if (!empty($data['Org_id']))
		{
			$filter .= ' and Org_id = :Org_id';
			$params['Org_id'] = $data['Org_id'];
		}
		if (!empty($data['LpuLicence_setDate']))
		{
			$filter .= ' and convert(varchar,cast(LpuLicence_setDate as datetime),109) = cast(:LpuLicence_setDate as datetime)';
			$params['LpuLicence_setDate'] = $data['LpuLicence_setDate'];
		}

		$query = "
		Select
			Lpu_id,
			LpuLicence_id,
			LpuLicence_Ser,
			LpuLicence_Num,
			Org_id,
			RTrim(IsNull(convert(varchar,cast(LpuLicence_setDate as datetime),104),'')) as LpuLicence_setDate,
			LpuLicence_RegNum,
			VidDeat_id,
			RTrim(IsNull(convert(varchar,cast(LpuLicence_begDate as datetime),104),'')) as LpuLicence_begDate,
			RTrim(IsNull(convert(varchar,cast(LpuLicence_endDate as datetime),104),'')) as LpuLicence_endDate,
			KLCountry_id,
			KLRgn_id,
			KLSubRgn_id,
			KLCity_id,
			KLTown_id, 
			KLAreaStat.KLAreaStat_id
		from v_LpuLicence LpuLicence (nolock)
		outer apply (
			Select top 1 KLAreaStat_id 
			from v_KLAreaStat KLAreaStat (nolock)
			where (LpuLicence.KLCountry_id = KLAreaStat.KLCountry_id and (ISNULL(LpuLicence.KLCountry_id, KLAreaStat.KLCountry_id) is not null)) and 
			(LpuLicence.KLRgn_id = KLAreaStat.KLRgn_id and (ISNULL(LpuLicence.KLRgn_id, KLAreaStat.KLRgn_id) is not null)) and 
			(LpuLicence.KLSubRgn_id = KLAreaStat.KLSubRgn_id or (KLAreaStat.KLSubRgn_id is null)) and 
			(LpuLicence.KLCity_id = KLAreaStat.KLCity_id or (KLAreaStat.KLCity_id is null)) and 
			(LpuLicence.KLTown_id = KLAreaStat.KLTown_id or (KLAreaStat.KLTown_id is null))
		) KLAreaStat
		where
			Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных лицензии МО. Метод для API.
	 */
	function loadLpuLicenceById($data) 
	{
		$filter = "";
		$query = "
			select
				Lpu_id,
				LpuLicence_id,
				LpuLicence_Ser,
				LpuLicence_Num,
				Org_id,
				convert(varchar(10), LpuLicence_setDate, 120) as LpuLicence_setDate,
				LpuLicence_RegNum,
				VidDeat_id,
				convert(varchar(10), LpuLicence_begDate, 120) as LpuLicence_begDate,
				convert(varchar(10), LpuLicence_endDate, 120) as LpuLicence_endDate,
				KLCountry_id,
				KLRgn_id,
				KLSubRgn_id,
				KLCity_id,
				KLTown_id
			from v_LpuLicence (nolock)
			where
				LpuLicence_id = :LpuLicence_id 
				and Lpu_id = :Lpu_id
				{$filter}
		";
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Удаление лицензии МО
	 */
	function deleteLpuLicence($data) 
	{
		$this->load->model('Utils_model', 'umodel');

		$resp = $this->loadLpuLicenceProfile($data);
		if(is_array($resp) && count($resp)>0){
			foreach ($resp as $value) {
				$res = $this->umodel->ObjectRecordsDelete(false, 'LpuLicenceProfile', false, array($value['LpuLicenceProfile_id']), 'fed', false);
				if(!empty($res[0]['Error_Message'])){
					return $res;
				}
			}
		}
		$resp = $this->loadLpuLicenceOperationLink($data);
		if(is_array($resp) && count($resp)>0){
			foreach ($resp as $value) {
				$res = $this->umodel->ObjectRecordsDelete(false, 'LpuLicenceOperationLink', false, array($value['LpuLicenceOperationLink_id']), 'fed', false);
				if(!empty($res[0]['Error_Message'])){
					return $res;
				}
			}
		}
		$resp = $this->loadLpuLicenceLink($data);
		if(is_array($resp) && count($resp)>0){
			foreach ($resp as $value) {
				$res = $this->umodel->ObjectRecordsDelete(false, 'LpuLicenceLink', false, array($value['LpuLicenceLink_id']), 'dbo', false);
				if(!empty($res[0]['Error_Message'])){
					return $res;
				}
			}
		}

		$resp = $this->umodel->ObjectRecordsDelete(false, 'LpuLicence', false, array($data['LpuLicence_id']), 'dbo', false);
		return $resp;
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuMobileTeamGrid($data) 
	{
		$filter = "(1=1)";
		$params = array();
		
		$filter .= ' and Lpu_id = :Lpu_id';
		$params['Lpu_id'] = $data['Lpu_id'];
		
		$query = "
			Select
				LMT.LpuMobileTeam_id,
				convert(varchar, LMT.LpuMobileTeam_begDate,104) as LpuMobileTeam_begDate,
				convert(varchar, LMT.LpuMobileTeam_endDate,104) as LpuMobileTeam_endDate,
				LMT.LpuMobileTeam_Count
			from v_LpuMobileTeam LMT (nolock)
			where
				{$filter}
		";
			
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$resp = $result->result('array');
			foreach($resp as &$respone) {
				$respone['DispClass_Name'] = '';
				$query = "
					Select
						DC.DispClass_Name
					from v_LpuMobileTeamLink LMTL (nolock)
						inner join v_DispClass DC (nolock) on DC.DispClass_id = LMTL.DispClass_id
					where
						LMTL.LpuMobileTeam_id = :LpuMobileTeam_id
				";
				$result = $this->db->query($query, array('LpuMobileTeam_id' => $respone['LpuMobileTeam_id']));
				if ( is_object($result) ) 
				{
					$respdc = $result->result('array');
					$first = true;
					foreach ($respdc as $respdcone) {
						$respone['DispClass_Name'] .= ($first?'':', ').$respdcone['DispClass_Name'];
						$first = false;
					}
				}
			}
			return $resp;
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuMobileTeam($data) 
	{
		$filter = "(1=1)";
		$params = array();
		
		$filter .= ' and LpuMobileTeam_id = :LpuMobileTeam_id';
		$params['LpuMobileTeam_id'] = $data['LpuMobileTeam_id'];
	
		$DispClassIds = array(-1);
		$query = "select LMTL.DispClass_id from v_LpuMobileTeamLink LMTL (nolock) where LMTL.LpuMobileTeam_id = :LpuMobileTeam_id";
		$result = $this->db->query($query, $params);
		if ( is_object($result) ) 
		{
			$resp = $result->result('array');
			foreach($resp as $respone) {
				$DispClassIds[] = $respone['DispClass_id'];
			}
		}
		
		$DispClassIds = implode(',', $DispClassIds);
		
		$query = "
			Select
				LMT.LpuMobileTeam_id,
				convert(varchar, LMT.LpuMobileTeam_begDate,104) as LpuMobileTeam_begDate,
				convert(varchar, LMT.LpuMobileTeam_endDate,104) as LpuMobileTeam_endDate,
				LMT.LpuMobileTeam_Count,
				case when 1 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig1,
				case when 2 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig2,
				case when 3 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig3,
				case when 4 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig4,
				case when 5 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig5,
				case when 6 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig6,
				case when 7 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig7,
				case when 8 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig8,
				case when 9 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig9,
				case when 10 IN ({$DispClassIds}) then 1 else 0 end as TypeBrig10
			from v_LpuMobileTeam LMT (nolock)
			where
				{$filter}
		";
			
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	
	/**
	 * @desc Возвращает список тарифов ЛПУ по СМП
	 * @param array $data Массив входящих данных
	 * @return array|false
	 */
	function loadSmpTariffGrid( $data ){
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] ) {
			return false;
		}
		
		$filter = "Lpu_id = :Lpu_id";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (CmpProfileTariff_endDT is null or CmpProfileTariff_endDT > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and CmpProfileTariff_endDT <= dbo.tzGetDate()";
		}

		$query = "
			SELECT
				CmpProfileTariff_id,
				RTrim(IsNull(convert(varchar,cast(CmpProfileTariff_begDT as datetime),104),'')) as CmpProfileTariff_begDT,
				RTrim(IsNull(convert(varchar,cast(CmpProfileTariff_endDT as datetime),104),'')) as CmpProfileTariff_endDT,
				CmpProfileTariff__Value,
				
				LpuSectionProfile_Name,
				
				TariffClass_Name
			FROM
				v_CmpProfileTariff cptf (nolock)
				LEFT JOIN v_LpuSectionProfile lsp (nolock) ON( lsp.LpuSectionProfile_id=cptf.LpuSectionProfile_id )
				LEFT JOIN v_TariffClass tc (nolock) ON( tc.TariffClass_id=cptf.TariffClass_id )
			WHERE
				".$filter."
		";
		$result = $this->db->query( $query, $params );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}	
	
	/**
	 * @desc Возвращает список тарифов ЛПУ по ДД
	 * @param array $data Массив входящих данных
	 * @return array|false
	 */
	function loadTariffDispGrid( $data )
	{
		$filter = "TD.Lpu_id = :Lpu_id";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (TD.TariffDisp_endDT is null or TD.TariffDisp_endDT > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and TD.TariffDisp_endDT <= dbo.tzGetDate()";
		}

		$query = "
			SELECT
				TD.TariffDisp_id,
				S.Sex_Name,
				TD.TariffDisp_Tariff,
				TD.TariffDisp_TariffDayOff,
				TC.TariffClass_Name,
				cast(AGD.AgeGroupDisp_From as varchar) + ' - ' + cast(AGD.AgeGroupDisp_To as varchar) as AgeGroupDisp_Name,
				LSP.LpuSectionProfile_Name,
				convert(varchar,TD.TariffDisp_begDT,104) as TariffDisp_begDT,
				convert(varchar,TD.TariffDisp_endDT,104) as TariffDisp_endDT
			FROM
				v_TariffDisp TD (nolock)
				left join v_TariffClass TC (nolock) on TC.TariffClass_id = TD.TariffClass_id
				left join v_AgeGroupDisp AGD (nolock) on AGD.AgeGroupDisp_id = TD.AgeGroupDisp_id
				left join v_Sex S (nolock) on S.Sex_id = TD.Sex_id
				left join v_LpuSectionProfile LSP (nolock) on LSP.LpuSectionProfile_id = TD.LpuSectionProfile_id
			WHERE
				".$filter."
		";
		$result = $this->db->query( $query, $params );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * Удаление тарифа по бюджету
	 */
	function deleteMedicalCareBudgTypeTariff($data)
	{
		$queryParams = array('MedicalCareBudgTypeTariff_id' => $data['id']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_MedicalCareBudgTypeTariff_del
				@MedicalCareBudgTypeTariff_id = :MedicalCareBudgTypeTariff_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Сохранение тарифа по бюджету
	 */
	function saveMedicalCareBudgTypeTariff($data)
	{
		$params = array(
			'MedicalCareBudgTypeTariff_id' => (!empty($data['MedicalCareBudgTypeTariff_id']))?$data['MedicalCareBudgTypeTariff_id']:null,
			'MedicalCareBudgType_id' => $data['MedicalCareBudgType_id'],
			'Lpu_id' => $data['Lpu_id'],
			'PayType_id' => $data['PayType_id'],
			'QuoteUnitType_id' => $data['QuoteUnitType_id'],
			'MedicalCareBudgTypeTariff_Value' => $data['MedicalCareBudgTypeTariff_Value'],
			'MedicalCareBudgTypeTariff_begDT' => $data['MedicalCareBudgTypeTariff_begDT'],
			'MedicalCareBudgTypeTariff_endDT' => $data['MedicalCareBudgTypeTariff_endDT'],
			'pmUser_id' => $data['pmUser_id']
		);

		$filter_check = "";
		if (!empty($params['MedicalCareBudgTypeTariff_id'])) {
			$filter_check .= " and MedicalCareBudgTypeTariff_id <> :MedicalCareBudgTypeTariff_id";
		}
		if (!empty($params['Lpu_id'])) {
			$filter_check .= " and Lpu_id = :Lpu_id";
		} else {
			$filter_check .= " and Lpu_id IS NULL";
		}

		// проверка
		$resp_check = $this->queryResult("
			select top 1
				MedicalCareBudgTypeTariff_id
			from
				v_MedicalCareBudgTypeTariff (nolock)
			where
				MedicalCareBudgType_id = :MedicalCareBudgType_id
				and PayType_id = :PayType_id
				and (
					(MedicalCareBudgTypeTariff_begDT >= :MedicalCareBudgTypeTariff_begDT and MedicalCareBudgTypeTariff_begDT <= :MedicalCareBudgTypeTariff_endDT)
					or (MedicalCareBudgTypeTariff_endDT >= :MedicalCareBudgTypeTariff_begDT and MedicalCareBudgTypeTariff_endDT <= :MedicalCareBudgTypeTariff_endDT)
				)
				{$filter_check}
		", $params);

		if (!empty($resp_check[0]['MedicalCareBudgTypeTariff_id'])) {
			return array('Error_Msg' => 'Сохранение невозможно: уже существует тариф с такими параметрами.');
		}

		if (!empty($data['MedicalCareBudgTypeTariff_id'])) {
			$procedure = 'p_MedicalCareBudgTypeTariff_upd';
		} else {
			$procedure = 'p_MedicalCareBudgTypeTariff_ins';
		}

		$query = "
			declare
                @MedicalCareBudgTypeTariff bigint,
				@Error_Code int,
				@Error_Msg varchar(4000);
            set @MedicalCareBudgTypeTariff = :MedicalCareBudgTypeTariff_id;
			exec {$procedure}
				@MedicalCareBudgTypeTariff_id = @MedicalCareBudgTypeTariff output,
				@MedicalCareBudgType_id = :MedicalCareBudgType_id,
				@Lpu_id = :Lpu_id,
				@PayType_id = :PayType_id,
				@QuoteUnitType_id = :QuoteUnitType_id,
				@MedicalCareBudgTypeTariff_Value = :MedicalCareBudgTypeTariff_Value,
				@MedicalCareBudgTypeTariff_begDT = :MedicalCareBudgTypeTariff_begDT,
				@MedicalCareBudgTypeTariff_endDT = :MedicalCareBudgTypeTariff_endDT,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Msg output;
			select @MedicalCareBudgTypeTariff as MedicalCareBudgTypeTariff_id, @Error_Code as Error_Code, @Error_Msg as Error_Msg;
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Загрузка тарифа по бюджету на редактирование
	 */
	function loadMedicalCareBudgTypeTariffEditWindow($data) {
		return $this->queryResult("
			select
				MedicalCareBudgTypeTariff_id,
				MedicalCareBudgType_id,
				Lpu_id,
				PayType_id,
				QuoteUnitType_id,
				convert(varchar(10), MedicalCareBudgTypeTariff_begDT, 104) as MedicalCareBudgTypeTariff_begDT,
				convert(varchar(10), MedicalCareBudgTypeTariff_endDT, 104) as MedicalCareBudgTypeTariff_endDT,
				MedicalCareBudgTypeTariff_Value
			from
				v_MedicalCareBudgTypeTariff (nolock)
			where
				MedicalCareBudgTypeTariff_id = :MedicalCareBudgTypeTariff_id
		", array(
			'MedicalCareBudgTypeTariff_id' => $data['MedicalCareBudgTypeTariff_id']
		));
	}

	/**
	 * Загрузка списка тарифов по бюджету
	 */
	function loadMedicalCareBudgTypeTariffGrid($data)
	{
		$filter = "1=1";
		$params = array();

		if (!empty($data['Lpu_id'])) {
			if (!empty($data['addWithoutLpu'])) {
				$filter .= " and ISNULL(MCBTT.Lpu_id, :Lpu_id) = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			} else {
				$filter .= " and MCBTT.Lpu_id = :Lpu_id";
				$params['Lpu_id'] = $data['Lpu_id'];
			}
		}

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (MCBTT.MedicalCareBudgTypeTariff_endDT is null or MCBTT.MedicalCareBudgTypeTariff_endDT > @curDate)";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and MCBTT.MedicalCareBudgTypeTariff_endDT <= @curDate";
		}

		if (!empty($data['MedicalCareBudgType_id'])) {
			$filter .= " and MCBTT.MedicalCareBudgType_id = :MedicalCareBudgType_id";
			$params['MedicalCareBudgType_id'] = $data['MedicalCareBudgType_id'];
		}

		if (!empty($data['PayType_id'])) {
			$filter .= " and MCBTT.PayType_id = :PayType_id";
			$params['PayType_id'] = $data['PayType_id'];
		}

		if (!empty($data['QuoteUnitType_id'])) {
			$filter .= " and MCBTT.QuoteUnitType_id = :QuoteUnitType_id";
			$params['QuoteUnitType_id'] = $data['QuoteUnitType_id'];
		}

		$query = "
			-- variables
			declare @curDate date = dbo.tzGetDate();
			-- end variables
			
			SELECT
			-- select
				MCBTT.MedicalCareBudgTypeTariff_id,
				MCBT.MedicalCareBudgType_Name,
				PT.PayType_Name,
				QUT.QuoteUnitType_Name,
				L.Lpu_Nick,
				MCBTT.MedicalCareBudgTypeTariff_Value,
				convert(varchar(10), MCBTT.MedicalCareBudgTypeTariff_begDT, 104) as MedicalCareBudgTypeTariff_begDT,
				convert(varchar(10), MCBTT.MedicalCareBudgTypeTariff_endDT, 104) as MedicalCareBudgTypeTariff_endDT
			-- end select
			FROM
			-- from
				v_MedicalCareBudgTypeTariff MCBTT (nolock)
				left join v_MedicalCareBudgType MCBT (nolock) on MCBT.MedicalCareBudgType_id = MCBTT.MedicalCareBudgType_id
				left join v_PayType PT (nolock) on PT.PayType_id = MCBTT.PayType_id
				left join v_QuoteUnitType QUT (nolock) on QUT.QuoteUnitType_id = MCBTT.QuoteUnitType_id
				left join v_Lpu L (nolock) on L.Lpu_id = MCBTT.Lpu_id
			-- end from
			WHERE
			-- where
				".$filter."
			-- end where
			ORDER BY
			-- order by
				MCBTT.MedicalCareBudgTypeTariff_id
			-- end order by
		";

		return $this->getPagingResponse($query, $params, $data['start'], $data['limit'], true);
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadTariffLpuGrid( $data )
	{
		$filter = "LT.Lpu_id = :Lpu_id";
		$params = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['isClose']) && $data['isClose'] == 1) {
			$filter .= " and (LT.LpuTariff_disDate is null or LT.LpuTariff_disDate > dbo.tzGetDate())";
		} elseif (!empty($data['isClose']) && $data['isClose'] == 2) {
			$filter .= " and LT.LpuTariff_disDate <= dbo.tzGetDate()";
		}

		$query = "
			SELECT
				LT.LpuTariff_id,
				LT.LpuTariff_Tariff,
				TC.TariffClass_Name,
				convert(varchar,LT.LpuTariff_setDate,104) as LpuTariff_setDate,
				convert(varchar,LT.LpuTariff_disDate,104) as LpuTariff_disDate
			FROM
				v_LpuTariff LT (nolock)
				left join v_TariffClass TC (nolock) on TC.TariffClass_id = LT.TariffClass_id
			WHERE
				".$filter."
		";
		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}
	
	/**
	 * @desc Возвращает укзанный тариф ЛПУ по СМП
	 * @param array $data Массив входящих данных
	 * @return array|false
	 */
	function loadSmpTariff( $data ){
		
		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] || !array_key_exists( 'CmpProfileTariff_id', $data ) || !$data['CmpProfileTariff_id'] ) {
			return false;
		}
		
		$filter = "	
			Lpu_id = :Lpu_id
			AND CmpProfileTariff_id = :CmpProfileTariff_id
		";
		$params = array(
			'Lpu_id' => (int)$data['Lpu_id'],
			'CmpProfileTariff_id' => (int)$data['CmpProfileTariff_id'],
		);

		$query = "
			SELECT
				CmpProfileTariff_id,
				RTrim(IsNull(convert(varchar,cast(CmpProfileTariff_begDT as datetime),104),'')) as CmpProfileTariff_begDT,
				RTrim(IsNull(convert(varchar,cast(CmpProfileTariff_endDT as datetime),104),'')) as CmpProfileTariff_endDT,
				CmpProfileTariff__Value,
				
				LpuSectionProfile_id,
				
				TariffClass_id
			FROM
				v_CmpProfileTariff (nolock)
			WHERE
				".$filter."
		";
		$result = $this->db->query( $query, $params );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function loadTariffLpu( $data ){

		if ( !array_key_exists( 'Lpu_id', $data ) || !$data['Lpu_id'] || !array_key_exists( 'LpuTariff_id', $data ) || !$data['LpuTariff_id'] ) {
			return false;
		}

		$filter = "
			LT.Lpu_id = :Lpu_id
			AND LT.LpuTariff_id = :LpuTariff_id
		";
		$params = array(
			'Lpu_id' => (int)$data['Lpu_id'],
			'LpuTariff_id' => (int)$data['LpuTariff_id'],
		);

		$query = "
			SELECT
				LT.LpuTariff_id,
				LT.LpuTariff_Tariff,
				convert(varchar,LT.LpuTariff_setDate,104) as LpuTariff_setDate,
				convert(varchar,LT.LpuTariff_disDate,104) as LpuTariff_disDate,
				LT.TariffClass_id
			FROM
				v_LpuTariff LT (nolock)
			WHERE
				".$filter."
		";
		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query( $query, $params );

		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}

	/**
	 * @desc Возвращает укзанный тариф ЛПУ по ДД
	 * @param array $data Массив входящих данных
	 * @return array|false
	 */
	function loadTariffDisp( $data )
	{
		$filter = "	
			TariffDisp_id = :TariffDisp_id
		";
		$params = array(
			'TariffDisp_id' => $data['TariffDisp_id'],
		);

		$query = "
			SELECT
				TD.TariffDisp_id,
				convert(varchar,TD.TariffDisp_begDT,104) as TariffDisp_begDT,
				convert(varchar,TD.TariffDisp_endDT,104) as TariffDisp_endDT,
				TD.TariffDisp_Tariff,
				TD.TariffDisp_TariffDayOff,
				TD.Sex_id,
				TD.AgeGroupDisp_id,				
				TD.LpuSectionProfile_id,
				TD.TariffClass_id
			FROM
				v_TariffDisp TD (nolock)
				left join v_AgeGroupDisp AGD (nolock) on TD.AgeGroupDisp_id = AGD.AgeGroupDisp_id
			WHERE
				".$filter."
		";
		$result = $this->db->query( $query, $params );
		
		if ( is_object( $result ) ) {
			return $result->result('array');
		}
		return false;
	}
	

	/**
	 *	Получение данных о здании МО
	 */
	function loadLpuBuilding($data) 
	{
		$filter = "";
		$select_add = "";
		$from_add = "";
		$params = array();
		
		if (isset($data['LpuBuildingPass_id']))
		{
			$filter .= ' and lbp.LpuBuildingPass_id = :LpuBuildingPass_id';
			$params['LpuBuildingPass_id'] = $data['LpuBuildingPass_id'];
		}

		if (isset($data['Lpu_id']))
		{
			$filter .= ' and lbp.Lpu_id = :Lpu_id';
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (empty($filter)) {
			return array();
		}

		if (getRegionNick() == 'kz') {
			$select_add .= "
				, bl.BuildingLpu_id
				, bl.PropertyClass_id
				, bl.BuildingUse_id
				, bl.BuildingClass_id
				, bl.BuildingState_id
				, bl.HeatingType_id
				, bl.BuildingLpu_updDT
				, bl.pmUser_updID
				, bl.BuildingLpu_insDT
				, bl.pmUser_insID
				, RTrim(IsNull(convert(varchar,cast(bl.BuildingLpu_RepEndDate as datetime),104),'')) as BuildingLpu_RepEndDate
				, RTrim(IsNull(convert(varchar,cast(bl.BuildingLpu_RepBegDate as datetime),104),'')) as BuildingLpu_RepBegDate
				, RTrim(IsNull(convert(varchar,cast(bl.BuildingLpu_RepCapBegDate as datetime),104),'')) as BuildingLpu_RepCapBegDate
				, RTrim(IsNull(convert(varchar,cast(bl.BuildingLpu_RepCapEndDate as datetime),104),'')) as BuildingLpu_RepCapEndDate
				, bl.BuildingLpu_RepCost
				, bl.BuildingLpu_RepCapCost
				, bl.ColdWaterType_id
				, bl.VentilationType_id
				, bl.ElectricType_id
				, bl.GasType_id
				, bl.BuildingLpu_DeprecCost
				,bu.BuildingUse_Name
				,pc.PropertyClass_Name
			";
			$from_add .= "
				left join passport101.v_BuildingLpu bl (nolock) on lbp.LpuBuildingPass_id = bl.LpuBuildingPass_id
				left join passport101.v_PropertyClass pc (nolock) on pc.PropertyClass_id = bl.PropertyClass_id
				left join passport101.v_BuildingUse bu (nolock) on bu.BuildingUse_id = bl.BuildingUse_id
			";
		}

		$query = "
		Select
			lbp.*,
			lbt.LpuBuildingType_Name,
			RTrim(IsNull(convert(varchar,cast(lbp.LpuBuildingPass_ValDT as datetime),104),'')) as LpuBuildingPass_ValDT,
			RTrim(IsNull(convert(varchar,cast(lbp.LpuBuildingPass_YearProjDoc as datetime),104),'')) as LpuBuildingPass_YearProjDoc,
			RTrim(IsNull(convert(varchar,cast(lbp.LpuBuildingPass_YearRepair as datetime),104),'')) as LpuBuildingPass_YearRepair,
			RTrim(IsNull(convert(varchar,cast(lbp.LpuBuildingPass_YearBuilt as datetime),104),'')) as LpuBuildingPass_YearBuilt,
			bat.BuildingAppointmentType_Name,
			BOT.BuildingOverlapType_Name,
			BHCT.BuildingHoldConstrType_Name
			{$select_add}
		from v_LpuBuildingPass lbp (nolock)
			left join v_LpuBuildingType lbt (nolock) on lbt.LpuBuildingType_id = lbp.LpuBuildingType_id
			left join v_BuildingOverlapType BOT (nolock) on BOT.BuildingOverlapType_id = lbp.BuildingOverlapType_id
			left join v_BuildingHoldConstrType BHCT (nolock) on BHCT.BuildingHoldConstrType_id = lbp.BuildingHoldConstrType_id
			left join v_BuildingAppointmentType bat (nolock) on bat.BuildingAppointmentType_id = lbp.BuildingAppointmentType_id
			{$from_add}
		where
			(1=1)
			{$filter}
		";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			$responce = $result->result('array');

			//колхозная обработка чекбоксов
			if (is_array($responce) && !empty($responce[0]) && is_array($responce[0])){
				foreach ($responce[0] as $key => &$value) {
					if (in_array($key, array('LpuBuildingPass_IsBalance', 'LpuBuildingPass_IsAutoFFSig', 'LpuBuildingPass_IsCallButton', 'LpuBuildingPass_IsSecurAlarm', 'LpuBuildingPass_IsWarningSys', 'LpuBuildingPass_IsFFWater', 'LpuBuildingPass_IsFFOutSignal', 'LpuBuildingPass_IsConnectFSecure', 'LpuBuildingPass_IsEmergExit', 'LpuBuildingPass_RespProtect', 'LpuBuildingPass_StretProtect', 'LpuBuildingPass_IsFreeEnergy'))) {
						$value == 2?$value = 1:$value = 0;
					}
				}
			}

			return $responce;
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение данных об отделениях здания МО
	 */
	function loadMOSections($data){

		$params = array('LpuBuildingPass_id' => $data['LpuBuildingPass_id']);

		$query = "
			Select
				LS.LpuSection_id,
				LS.LpuSection_Name,
				LU.LpuUnit_Name,
				LB.LpuBuilding_Name,
				1 as MOSectionBase_id
			from
				V_LpuSection LS (nolock)
				left join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
				left join v_LpuBuilding LB with (nolock) on LU.LpuBuilding_id = LB.LpuBuilding_id
			where
				LS.LpuBuildingPass_id = :LpuBuildingPass_id
		";

		$result = $this->db->query($query, $params);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Получение данных об отделениях здания МО
	 */
	function calcWorkAreaWard($data){

		$data['deniedSectionsList']  = explode(",", $data['deniedSectionsList']);

		foreach ($data['deniedSectionsList'] as &$el) {
			$el = floatval($el);
		}

		$query = "
			select
				ISNULL(SUM(LpuSectionWard_Square), '0') as square
			from
				LpuSectionWard with(nolock)
			where
				LpuSection_id in (".implode(",", $data['deniedSectionsList']).")
		";

		$result = $this->db->query($query);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 *	Загрузка всех подразделений выбранной ноды для отображения на форме добавления здания МО
	 */
	function getMOSectionsForList($data){

		$filter = "";
		if (!empty($data['deniedSectionsList'])) {
			$data['deniedSectionsList']  = explode(",", $data['deniedSectionsList']);

			foreach ($data['deniedSectionsList'] as &$el) {
				$el = floatval($el);
			}

			$filter = "and LS.LpuSection_id not in (".implode(',',$data['deniedSectionsList']).") and LS.LpuBuildingPass_id is null";
		}

		switch ($data['type']){
			case 'Lpu':
				$query = "
					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					where
						LB.Lpu_id = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}
				";
			break;
			case 'LpuBuilding':
				$query = "
					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					where
						LB.LpuBuilding_id = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}
				";
			break;
			case 'LpuUnitType':
				$query = "
					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuUnitType LUT with (nolock) on LU.LpuUnitType_id = LUT.LpuUnitType_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					where
						LU.LpuUnitType_id = :unitType
						and LB.LpuBuilding_id = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}
				";
			break;
			case 'LpuUnit':
				$query = "
					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
					where
						LU.LpuUnit_id = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}
				";
			break;
			case 'LpuSection':
				$query = "
					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join v_LpuSection vLS with (nolock) on vLS.LpuSection_pid = LS.LpuSection_id
					where
						LS.LpuSection_id = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}

					union all

					select
						LS.LpuSection_id,
						LS.LpuSection_Name,
						LU.LpuUnit_Name,
						LB.LpuBuilding_Name
					from
						LpuSection LS (nolock)
						inner join v_LpuUnit LU with (nolock) on LU.LpuUnit_id = LS.LpuUnit_id
						inner join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = LU.LpuBuilding_id
						left join v_LpuSection vLS with (nolock) on vLS.LpuSection_pid = LS.LpuSection_id
					where
						LS.LpuSection_pid = :id
						and LS.LpuSection_pid is null
						and (LS.LpuSection_deleted = 1 or LS.LpuSection_deleted is null )
						{$filter}
				";
			break;
			default:
		        return array( 'Error_Msg' => 'Передан не верный тип ноды.' );
			break;
		}

		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	 * @desc Удаляет связь отделения МО и здания
	 * @param array $data Данные
	 * @return type
	 */
	function deleteMOSectionBuildingPass( $data ) {

		$query = "
			update
				LpuSection
			set
				LpuBuildingPass_id = null
			where
				LpuSection_id = :LpuSection_id
		";

		$result = $this->db->query($query, array('LpuSection_id' => $data['LpuSection_id']));

		return array(array('success' => true));

	}

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuEquipment($data) 
	{
		
	
		// Весь список
		if (!isset($data['LpuEquipment_id']) && !isset($data['LpuEquipmentPacs_id'])) {
			$params = array('Lpu_id' => $data['Lpu_id']);
			$query = "
			Select
				'' as LpuEquipment_id, 
				LpuEquipmentPacs_id, 			
				'4' as LpuEquipmentType_id, 
				PACS_name as LpuEquipment_Name, 
				'' as LpuEquipment_Producer,
				'' as LpuEquipment_ReleaseDT,
				'' as LpuEquipment_PurchaseDT,
				PACS_aet as LpuEquipment_Model, 
				PACS_ip_vip as LpuEquipment_InvNum,
				'' as LpuEquipment_SerNum, 
				'' as LpuEquipment_StartUpDT,	
				'' as LpuEquipment_WearPersent, 
				'' as LpuEquipment_ConclusionDT,
				'' as LpuEquipment_PurchaseCost, 
				'' as LpuEquipment_ResidualCost, 
				'' as LpuEquipment_AmortizationTerm,
				0 as LpuEquipment_IsNationProj
			from v_LpuEquipmentPacs (nolock)
			where
				Lpu_id = :Lpu_id		
			/*UNION -- закоменчено в рамках задачи https://redmine.swan.perm.ru/issues/38218 т.к. теперь для не ПАКС оборудования свой функционал редактирования, и это здесь больше ненужно
			Select
				LpuEquipment_id, 
				'' as LpuEquipmentPacs_id, 
				LpuEquipmentType_id, 
				LpuEquipment_Name, 
				LpuEquipment_Producer,
				RTrim(IsNull(convert(varchar,cast(LpuEquipment_ReleaseDT as datetime),104),'')) as LpuEquipment_ReleaseDT,
				RTrim(IsNull(convert(varchar,cast(LpuEquipment_PurchaseDT as datetime),104),'')) as LpuEquipment_PurchaseDT,			
				LpuEquipment_Model, 
				LpuEquipment_InvNum, 
				LpuEquipment_SerNum, 
				RTrim(IsNull(convert(varchar,cast(LpuEquipment_StartUpDT as datetime),104),'')) as LpuEquipment_StartUpDT,	
				LpuEquipment_WearPersent, 
				RTrim(IsNull(convert(varchar,cast(LpuEquipment_ConclusionDT as datetime),104),'')) as LpuEquipment_ConclusionDT,
				LpuEquipment_PurchaseCost, 
				LpuEquipment_ResidualCost, 
				LpuEquipment_AmortizationTerm,
				LpuEquipment_IsNationProj
			from v_LpuEquipment (nolock)
			where
				Lpu_id = :Lpu_id*/";
		} else {
			// Только оборудование
			if (isset($data['LpuEquipment_id'])) {
				$params = array('Lpu_id' => $data['Lpu_id']);
				$params['LpuEquipment_id'] = $data['LpuEquipment_id'];		
				$query = "
					Select					
						LpuEquipment_id, 						
						LpuEquipmentType_id, 
						LpuEquipment_Name, 
						LpuEquipment_Producer,
						RTrim(IsNull(convert(varchar,cast(LpuEquipment_ReleaseDT as datetime),104),'')) as LpuEquipment_ReleaseDT,
						RTrim(IsNull(convert(varchar,cast(LpuEquipment_PurchaseDT as datetime),104),'')) as LpuEquipment_PurchaseDT,			
						LpuEquipment_Model, 
						LpuEquipment_InvNum, 
						LpuEquipment_SerNum, 
						RTrim(IsNull(convert(varchar,cast(LpuEquipment_StartUpDT as datetime),104),'')) as LpuEquipment_StartUpDT,	
						LpuEquipment_WearPersent, 
						RTrim(IsNull(convert(varchar,cast(LpuEquipment_ConclusionDT as datetime),104),'')) as LpuEquipment_ConclusionDT,
						LpuEquipment_PurchaseCost, 
						LpuEquipment_ResidualCost, 
						LpuEquipment_AmortizationTerm,
						LpuEquipment_IsNationProj
					from v_LpuEquipment (nolock)
					where
						Lpu_id = :Lpu_id and
						LpuEquipment_id = :LpuEquipment_id
				";
			}
			// Только PACS
			if (isset($data['LpuEquipmentPacs_id'])) {
				$params['LpuEquipmentPacs_id'] = $data['LpuEquipmentPacs_id'];
				$query = "
					SELECT
						LpuEquipmentPacs_id,						
						'4' as LpuEquipmentType_id, 
						LEP.PACS_name, 
						LEP.PACS_aet, 
						LEP.PACS_ip_vip,
						LEP.PACS_ip_local,
						LEP.PACS_wado,
						LEP.PACS_port,
						LEP.PACS_Interval,
						LEP.PACS_Interval_TimeType_id,
						LEP.PACS_ExcludeTimeFrom,
						LEP.PACS_ExcludeTimeTo,
						LEP.LpuPacsCompressionType_id,
						LEP.PACS_StudyAge,
						LEP.PACS_Age_TimeType_id,
						CASE WHEN LEP.PACS_DeleteFromDb = 1 THEN 'on'
							ELSE NULL
						END AS PACS_DeleteFromDb,
						CASE WHEN LEP.PACS_DeletePatientsWithoutStudies = 1 THEN 'on'
							ELSE NULL
						END AS PACS_DeletePatientsWithoutStudies
					FROM v_LpuEquipmentPacs LEP (nolock)
					WHERE
						LEP.LpuEquipmentPacs_id = :LpuEquipmentPacs_id
				";		
			}
		}
		
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение списка медицинского оборудования
	 */
	function loadMedProductCard($data)
	{
		$filter = "(1=1)";

		$filter.= " and LB.Lpu_id = :Lpu_id";
		
		if (!empty($data['MedProductCard_id'])) {
			$filter.= " and MedProductCard_id = :MedProductCard_id";
			$queryParams['MedProductCard_id'] = $data['MedProductCard_id'];
		}
		
		if (!empty($data['LpuSection_id'])) {
			$filter.= " and MS.LpuSection_id = :LpuSection_id";
			$queryParams['LpuSection_id'] = $data['LpuSection_id'];
		}

		if (!empty($data['MedService_id'])) {
			$filter.= " and MS.MedService_id = :MedService_id";
			$queryParams['MedService_id'] = $data['MedService_id'];
		}

		if (!isSuperadmin() && $this->regionNick == 'perm') {
			$filter.= " and ISNULL(MPC.MedProductCard_IsNoAvailLpu, 1) = 1";
		}

        $query = "
			Select
				MPC.MedProductCard_id,
				MPC.MedProductCard_SerialNumber,
				MPC.MedProductCard_BoardNumber,
				MPC.MedProductCard_Phone,
				MPC.MedProductCard_Glonass,
				RTrim(IsNull(convert(varchar,cast(MPC.MedProductCard_begDate as datetime),104),'')) as MedProductCard_begDate,
				MPCl.MedProductClass_Name,
				MPCl.MedProductClass_Model,
                RTrim(IsNull(convert(varchar,cast(AD.AccountingData_setDate as datetime),104),'')) as AccountingData_setDate,
				AD.AccountingData_BuyCost,
				AD.AccountingData_InventNumber,
                RTrim(IsNull(convert(varchar,cast(GC.GosContract_setDate as datetime),104),'')) as GosContract_setDate,
				GC.GosContract_Number,
				CT.CardType_id,
				CT.CardType_Name,
				LB.LpuBuilding_Name,
				LB.LpuBuilding_id,
				O.Org_Nick,
				FT.FinancingType_Name,
				CT.CardType_Name,
				CRT.ClassRiskType_Name,
				FPT.FuncPurpType_Name,
				UAT.UseAreaType_Name,
				UST.UseSphereType_Name,
				MS.LpuSection_id,
				R.Resource_id,
				PWT.PrincipleWorkType_id,
				PWT.PrincipleWorkType_Name,
				AD.AccountingData_RegNumber,
				MPC.MedProductCard_BoardNumber,
				MPCT.MedProductCauseType_Name,
				MPC.MedProductCard_Cause,
				RTrim(IsNull(convert(varchar,cast(MPC.MedProductCard_SendFRMODT as datetime),104),'')) as MedProductCard_SendFRMODT,
				FRMOE.FRMOEquipment_Name,
				case when MPC.MedProductCard_IsNotFRMO = 2 then 2 else 1 end as MedProductCard_IsNotFRMO,

				FT.FinancingType_id,
				CT.CardType_id,
				CRT.ClassRiskType_id,
				FPT.FuncPurpType_id,
				UAT.UseAreaType_id,
				UST.UseSphereType_id,
				MPC.MedProductClass_id,
				MPCT.MedProductCauseType_id,
				MPCl.FRMOEquipment_id,
				FRMOE.FRMOEquipment_id,
				YesNo.YesNo_id
			from
				passport.v_MedProductCard MPC with (nolock)
				left join passport.v_AccountingData AD with (nolock) on AD.MedProductCard_id = MPC.MedProductCard_id
				left join passport.v_RegCertificate RC with (nolock) on RC.MedProductCard_id = MPC.MedProductCard_id
				left join passport.v_GosContract GC with (nolock) on GC.MedProductCard_id = MPC.MedProductCard_id
				left join passport.v_FinancingType FT with (nolock) on GC.FinancingType_id = FT.FinancingType_id
				left join passport.v_MedProductClass MPCl with (nolock) on MPCl.MedProductClass_id = MPC.MedProductClass_id
				left join v_LpuBuilding LB with (nolock) on LB.LpuBuilding_id = MPC.LpuBuilding_id
				left join v_Org O with (nolock) on O.Org_id = RC.Org_prid
				left join passport.v_CardType CT with (nolock) on CT.CardType_id = MPCl.CardType_id
				left join passport.v_ClassRiskType CRT with (nolock) on MPCl.ClassRiskType_id = CRT.ClassRiskType_id
				left join passport.v_FuncPurpType FPT with (nolock) on MPCl.FuncPurpType_id = FPT.FuncPurpType_id
				left join passport.v_UseAreaType UAT with (nolock) on MPCl.UseAreaType_id = UAT.UseAreaType_id
				left join passport.v_UseSphereType UST with (nolock) on MPCl.UseSphereType_id = UST.UseSphereType_id
				left join passport.v_MedProductCardResource MPCR with (nolock) on MPC.MedProductCard_id = MPCR.MedProductCard_id
				left join v_Resource R with (nolock) on R.Resource_id = MPCR.Resource_id
				left join v_MedService MS with (nolock) on MS.MedService_id = R.MedService_id
				left join passport.PrincipleWorkType PWT with (nolock) on PWT.PrincipleWorkType_id = MPC.PrincipleWorkType_id
				left join passport.v_MedProductCauseType MPCT with (nolock) on MPCT.MedProductCauseType_id = MPC.MedProductCauseType_id
				left join passport.v_FRMOEquipment FRMOE with (nolock) on FRMOE.FRMOEquipment_id = MPCl.FRMOEquipment_id
				left join v_YesNo YesNo with (nolock) on YesNo.YesNo_Code = MPC.MedProductCard_IsNotFRMO
			where
				{$filter}
		";

        //echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if ( is_object($result) )
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuQuote($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuQuote_id']))
		{
			$filter .= ' and LpuQuote_id = :LpuQuote_id';
			$params['LpuQuote_id'] = $data['LpuQuote_id'];
		}
		$query = "
		Select
			LQ.LpuQuote_id,
			LQ.LpuQuote_VizitCount,
			LQ.LpuQuote_BedDaysCount,
			RTrim(IsNull(convert(varchar,cast(LQ.LpuQuote_begDate as datetime),104),'')) as LpuQuote_begDate,
			RTrim(IsNull(convert(varchar,cast(LQ.LpuQuote_endDate as datetime),104),'')) as LpuQuote_endDate,
			LQ.PayType_id,
			PT.PayType_Name,
			LQ.LpuQuote_HospCount
		from v_LpuQuote as LQ (nolock)
		left join v_PayType as PT (nolock) on PT.PayType_id = LQ.PayType_id
		where
			Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuTransport($data) 
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuTransport_id']))
		{
			$filter .= ' and LpuTransport_id = :LpuTransport_id';
			$params['LpuTransport_id'] = $data['LpuTransport_id'];
		}
		$query = "
		Select
			LpuTransport_id, 
			LpuTransport_Name, 
			LpuTransport_Producer,
			LpuTransport_Model, 
			RTrim(IsNull(convert(varchar,cast(LpuTransport_ReleaseDT as datetime),104),'')) as LpuTransport_ReleaseDT,
			RTrim(IsNull(convert(varchar,cast(LpuTransport_PurchaseDT as datetime),104),'')) as LpuTransport_PurchaseDT, 
			LpuTransport_Supplier, 
			LpuTransport_RegNum, 
			LpuTransport_EngineNum, 
			LpuTransport_BodyNum, 
			LpuTransport_ChassiNum, 
			RTrim(IsNull(convert(varchar,cast(LpuTransport_StartUpDT as datetime),104),'')) as LpuTransport_StartUpDT, 
			LpuTransport_WearPersent, 
			LpuTransport_PurchaseCost, 
			LpuTransport_ResidualCost,
			RTrim(IsNull(convert(varchar,cast(LpuTransport_ValuationDT as datetime),104),'')) as LpuTransport_ValuationDT,  
			LpuTransport_IsNationProj
		from v_LpuTransport (nolock)
		where
			Lpu_id = :Lpu_id and
			{$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *  Загрузка комбо "По договору"
	 */
	function loadLpuDispContractCombo($data)
	{
		$filters = "";
		$queryParams = array(
			'Lpu_id' => $data['Lpu_id']
		);

		if (!empty($data['LpuDispContract_id'])) {
			$filters .= " and ldc.LpuDispContract_id = :LpuDispContract_id";
			$queryParams['LpuDispContract_id'] = $data['LpuDispContract_id'];
		} else {
			if (!empty($data['LpuSectionProfile_id'])) {
				$filters .= " and ldc.LpuSectionProfile_id = :LpuSectionProfile_id";
				$queryParams['LpuSectionProfile_id'] = $data['LpuSectionProfile_id'];
			}

			if (!empty($data['onDate'])) {
				$filters .= " and ldc.LpuDispContract_setDate <= :onDate";
				$filters .= " and ISNULL(ldc.LpuDispContract_disDate, :onDate) >= :onDate";
				$queryParams['onDate'] = $data['onDate'];
			}
		}

		$query = "
			select
				ldc.LpuDispContract_id,
				convert(varchar(10), ldc.LpuDispContract_setDate, 104) as LpuDispContract_setDate,
				convert(varchar(10), ldc.LpuDispContract_disDate, 104) as LpuDispContract_disDate,
				sct.SideContractType_Name,
				ldc.LpuDispContract_Num,
				l.Lpu_Nick,
				ls.LpuSection_Name,
				ldc.LpuSectionProfile_id
			from
				v_LpuDispContract ldc (nolock)
				left join v_Lpu l (nolock) on l.Lpu_id = LDC.Lpu_oid
				left join v_LpuSection LS (nolock) on LS.LpuSection_id = LDC.LpuSection_id
				left join v_SideContractType sct (nolock) on sct.SideContractType_id = LDC.SideContractType_id
			where
				ldc.Lpu_id = :Lpu_id
				{$filters}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function loadLpuDispContract($data)
	{
		$filter = "(1=1)";
		$params = array('Lpu_id' => $data['Lpu_id']);
		
		if (isset($data['LpuDispContract_id']))
		{
			$filter .= ' and LDC.LpuDispContract_id = :LpuDispContract_id';
			$params['LpuDispContract_id'] = $data['LpuDispContract_id'];
		}
		$query = "
		Select
			LDC.LpuDispContract_id,
			IsNull(convert(varchar,cast(LDC.LpuDispContract_setDate as datetime),104),'') as LpuDispContract_setDate,
			IsNull(convert(varchar,cast(LDC.LpuDispContract_disDate as datetime),104),'') as LpuDispContract_disDate,
			LDC.SideContractType_id,
			LDC.Lpu_id,
			LDC.ContractType_id,
			sct.SideContractType_Name,
			RTRIM(LDC.LpuDispContract_Num) as LpuDispContract_Num,
			LDC.Lpu_oid,
			RTRIM(Lpu.Lpu_Nick) as Lpu_Nick,
			LDC.LpuSectionProfile_id, 
			LSP.LpuSectionProfile_Code, 
			LDC.LpuSection_id, 
			LS.LpuSection_Name
			from v_LpuDispContract LDC (nolock)
			 left join v_Lpu Lpu (nolock) on Lpu.Lpu_id = LDC.Lpu_oid
			 left join v_LpuSectionProfile LSP (nolock) on LSP.LpuSectionProfile_id = LDC.LpuSectionProfile_id
			 left join v_LpuSection LS (nolock) on LS.LpuSection_id = LDC.LpuSection_id
			 left join v_SideContractType sct (nolock) on sct.SideContractType_id = LDC.SideContractType_id
			where 
			 LDC.Lpu_id = :Lpu_id and 
			 {$filter}";
		$result = $this->db->query($query, $params);

		if ( is_object($result) ) 
		{
			return $result->result('array');
		}
		else {
			return false;
		}
	}
	
	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function saveLpuDispContract($data)
	{
		$proc = '';
		$serviceContract = array();
		if (!isset($data['LpuDispContract_id']))
		{
			$proc = 'p_LpuDispContract_ins';
		}
		else
		{
			$proc = 'p_LpuDispContract_upd';
		}
		
		if($data['serviceContractList']){
			$serviceContract = json_decode($data['serviceContractList'], true);
		}
		
		$query = "
			declare
				@LpuDispContract_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @LpuDispContract_id = :LpuDispContract_id;
			exec " . $proc . " 
				@LpuDispContract_id = @LpuDispContract_id output, 
				@LpuDispContract_setDate = :LpuDispContract_setDate, 
				@LpuDispContract_disDate = :LpuDispContract_disDate, 
				@SideContractType_id = :SideContractType_id, 
				@ContractType_id = :ContractType_id, 
				@LpuDispContract_Num = :LpuDispContract_Num, 
				@Lpu_id = :Lpu_id,
				@Server_id = :Server_id,
				@Lpu_oid = :Lpu_oid,
				@LpuSectionProfile_id = :LpuSectionProfile_id,
				@LpuSection_id = :LpuSection_id,
				@pmUser_id = :pmUser_id, 
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMessage output;
			select @LpuDispContract_id as LpuDispContract_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$params = array(
			'LpuDispContract_id'=>$data['LpuDispContract_id'], 
			'LpuDispContract_setDate'=>$data['LpuDispContract_setDate'], 
			'LpuDispContract_disDate'=>$data['LpuDispContract_disDate'],
			'SideContractType_id'=>$data['SideContractType_id'],
			'ContractType_id'=>$data['ContractType_id'],
			'LpuDispContract_Num'=>$data['LpuDispContract_Num'],
			'Lpu_id'=>$data['Lpu_id'], 
			'Server_id'=>$data['Server_id'], 
			'Lpu_oid'=>$data['Lpu_oid'], 
			'LpuSectionProfile_id'=>$data['LpuSectionProfile_id'], 
			'LpuSection_id'=>$data['LpuSection_id'], 
			'pmUser_id'=>$data['pmUser_id']
		);
		/*
		echo getDebugSql($query, $params);
		exit;
		*/
		$res = $this->db->query($query, $params);
		$result = $res->result('array');
		if($proc == 'p_LpuDispContract_upd' && !empty($result[0]['LpuDispContract_id'])){
			// удалаяем все прежние записи услуги договора
			$queryFields = $this->db->query("
			select
				LpuDispContractUslugaComplexLink_id
			from
				dbo.LpuDispContractUslugaComplexLink 
			WHERE LpuDispContract_id = :LpuDispContract_id", $data);
			$allFields = $queryFields->result_array();
			foreach ($allFields as $fieldVal)
			{
				$fieldVal['pmUser_id'] = $data['pmUser_id'];
				$resDel = $this->deleteServiceContract($fieldVal);
			}
		}
		
		if(!empty($result[0]['LpuDispContract_id'])){
			// добавляем новые записи услуги договора
			foreach ($serviceContract as $value) {
				$value['LpuDispContract_id'] = $result[0]['LpuDispContract_id'];
				$value['pmUser_id'] = $data['pmUser_id'];
				$res_add_sc = $this->saveServiceContract($value);
			}
		}
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

	/**
	 *	Получение признака возможности интернет-модерации
	 */
	function getIsAllowInternetModeration($data)
	{
		$params = array('Lpu_id' => $data['Lpu_id']);
		$query = '
			select Lpu_IsAllowInternetModeration
			from v_Lpu (nolock)
			where Lpu_id = :Lpu_id
		';
		$result = $this->db->query($query, $params);
		return $result->result('array');
	}


    /**
     *	Получение лицензий текущей МО
     */
    function loadLpuLicenceSpecializationMO($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);

        /*if (isset($data['LpuLicence_id']))
        {
            $filter .= ' and LpuLicence_id = :LpuLicence_id';
            $params['LpuLicence_id'] = $data['LpuLicence_id'];
        }*/
        $query = "
		Select
			Lpu_id,
			LpuLicence_id,
			LpuLicence_Ser,
			LpuLicence_Num
		from v_LpuLicence with (nolock)
		where
			Lpu_id = :Lpu_id";

        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     *	Получение идентификаторов зданий текущей МО
     */
    function loadLpuBuildingMedTechnology($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);

        $query = "
		Select
			LpuBuildingPass_Name,
			LpuBuildingPass_id
		from v_LpuBuildingPass with (nolock)
		where
			Lpu_id = :Lpu_id";

        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 * @desc Загрузка списка Cron-запросов 
	 * @param type $data
	 * @return boolean
	 */
	public function loadCronRequests($data) {
		
		$query = "
			SELECT
				LEPC.LpuEquipmentPacsCron_id,
				LEPC.LpuEquipmentPacsCron_request
			FROM
				v_LpuEquipmentPacsCron LEPC (nolock)
			WHERE
				LEPC.LpuEquipmentPacs_id = :LpuEquipmentPacs_id
			";
		
		$queryParams = array(
			'LpuEquipmentPacs_id' =>isset($data['LpuEquipmentPacs_id'])?$data['LpuEquipmentPacs_id']:null
		);
		
		$result = $this->db->query($query, $queryParams);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }		
	}

	/**
	 * @desc Удаление всех Cron-заросов по идентификатору настроек PACS
	 * @param type $data
	 * @return boolean
	 */
	public function deleteCronRequestByLpuEquipmentPacsId($data) {
		if (!isset($data['LpuEquipmentPacs_id'])||empty($data['LpuEquipmentPacs_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Отсутствует обязательный параметр: идентификатор настроек устройства PACS'));
		}
		
		$query = "
			DECLARE
				@ErrCode int,
				@ErrMessage varchar(4000);
			EXEC p_LpuEquipmentPacsCron_delByLpuEquipmentPacsId
				@LpuEquipmentPacs_id = :LpuEquipmentPacs_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			SELECT @ErrCode as Error_Code, @ErrMessage as Error_Msg;
			";
		
		$queryParams = array(
			'LpuEquipmentPacs_id' =>isset($data['LpuEquipmentPacs_id'])?$data['LpuEquipmentPacs_id']:null
		);
		
		$res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
			$result = $res->result_array();
			
			$result[0]['success']=empty($result[0]['Error_Msg']);
			
			return $result;	
        } else {
            return false;
        }	
		
	}

	/**
	 * Сохранение Cron-запроса 
	 * @param null $data
	 * @return boolean
	 */
	public function saveCronRequest($data) {
		
		if (!isset($data['LpuEquipmentPacsCron_request'])||empty($data['LpuEquipmentPacsCron_request'])) {
			return array(array('success'=>false,'Error_Msg'=>'Пустой CRON-запрос'));
		}
		if (!isset($data['LpuEquipmentPacs_id'])||empty($data['LpuEquipmentPacs_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор настроек PACS для ЛПУ'));
		}
		if (!isset($data['pmUser_id'])||empty($data['pmUser_id'])) {
			return array(array('success'=>false,'Error_Msg'=>'Не задан обязательный параметр: идентификатор пользователя'));
		}
		
		if (isset($data['LpuEquipmentPacsCron_id'])) {
			$proc_postfix = 'upd';
		} else {
			$data['LpuEquipmentPacsCron_id']=null;
			$proc_postfix = 'ins';
		}
		
		$query = "
			declare
				@Res bigint,
				@Error_Code int,
				@Error_Message varchar(4000)
			set @Res = :LpuEquipmentPacsCron_id;
			exec p_LpuEquipmentPacsCron_{$proc_postfix}
				@LpuEquipmentPacsCron_id = @Res output,
				@LpuEquipmentPacsCron_request = :LpuEquipmentPacsCron_request,
				@LpuEquipmentPacs_id = :LpuEquipmentPacs_id,
				@pmUser_id = :pmUser_id;
			select @Res as LpuEquipmentPacsCron_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
			";
		
		$queryParams = array(
			'LpuEquipmentPacsCron_id' =>isset($data['LpuEquipmentPacsCron_id'])?$data['LpuEquipmentPacsCron_id']:null,
			'LpuEquipmentPacsCron_request'=>$data['LpuEquipmentPacsCron_request'],
			'LpuEquipmentPacs_id'=>$data['LpuEquipmentPacs_id'],
			'pmUser_id'=>$data['pmUser_id'],
		);
		
		$res = $this->db->query($query, $queryParams);

        if ( is_object($res) ) {
            $result = $res->result('array');
			$result[0]['success']=empty($result[0]['Error_Msg']);
			return $result;
        } else {
            return false;
        }
			
	}


	/**
	 * @desc Загрузка OkeiLink combo
	 * @param type $data
	 * @return boolean
	 */
	function  loadOkeiLinkCombo($data) {

		$query = "
			SELECT
                OL.OkeiLink_id,
                Ok.Okei_NationSymbol
			FROM
				v_Okei Ok (nolock)
				inner join passport.OkeiLink OL with (nolock) on Ok.Okei_id = OL.Okei_id
			";

		$result = $this->db->query($query, array());

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
	}


	/**
	* Возвращает список видом ведицинских изделий для комбобокса
	*/
	function getMedProductClassList($data) {
		$filter = "";
		$queryParams = array();

		$queryParams['Lpu_id'] = !empty($data['Lpu_id'])?$data['Lpu_id']:$data['session']['Lpu_id'];

		if ( isset($data['MedProductClass_id'])) {
			$filter .= ' and MPC.MedProductClass_id = :MedProductClass_id';
			$queryParams['MedProductClass_id'] = $data['MedProductClass_id'];
		} else {
            if( isset($data['query'])){
                $filter .= ' and MedProductClass_Name LIKE :query';
                $queryParams['query'] = '%'.$data['query'].'%';
            };

            if( isset($data['MedProductClass_Name'])){
                $filter .= ' and MedProductClass_Name LIKE :MedProductClass_Name';
                $queryParams['MedProductClass_Name'] = '%'.$data['MedProductClass_Name'].'%';
            };

            if( isset($data['MedProductClass_Model'])){
                $filter .= ' and MedProductClass_Model LIKE :MedProductClass_Model';
                $queryParams['MedProductClass_Model'] = '%'.$data['MedProductClass_Model'].'%';
            };

            if( isset($data['UseSphereType_id'])){
                $filter .= ' and MPC.UseSphereType_id = :UseSphereType_id';
                $queryParams['UseSphereType_id'] = $data['UseSphereType_id'];
            };

            if( isset($data['UseAreaType_id'])){
                $filter .= ' and MPC.UseAreaType_id = :UseAreaType_id';
                $queryParams['UseAreaType_id'] = $data['UseAreaType_id'];
            };

            if( isset($data['FuncPurpType_id'])){
                $filter .= ' and MPC.FuncPurpType_id = :FuncPurpType_id';
                $queryParams['FuncPurpType_id'] = $data['FuncPurpType_id'];
            };

            if( isset($data['ClassRiskType_id'])){
                $filter .= ' and MPC.ClassRiskType_id = :ClassRiskType_id';
                $queryParams['ClassRiskType_id'] = $data['ClassRiskType_id'];
            };

            if( isset($data['CardType_id'])){
                $filter .= ' and MPC.CardType_id = :CardType_id';
                $queryParams['CardType_id'] = $data['CardType_id'];
            };

            if( isset($data['MedProductType_id'])){
                $filter .= ' and MPC.MedProductType_id = :MedProductType_id';
                $queryParams['MedProductType_id'] = $data['MedProductType_id'];
			};
			
			if( isset($data['FRMOEquipment_id'])){
                $filter .= ' and MPC.FRMOEquipment_id = :FRMOEquipment_id';
                $queryParams['FRMOEquipment_id'] = $data['FRMOEquipment_id'];
			};
        }

		$query = "
			SELECT
				MedProductClass_id,
				RTRIM(MPC.MedProductClass_Model) as MedProductClass_Model,
				RTRIM(MPC.MedProductClass_Name) as MedProductClass_Name,
				RTRIM(MPC.CardType_id) as CardType_id,
				RTRIM(MPC.ClassRiskType_id) as ClassRiskType_id,
				RTRIM(MPC.FuncPurpType_id) as FuncPurpType_id,
				RTRIM(MPC.FZ30Type_id) as FZ30Type_id,
				RTRIM(MPC.GMDNType_id) as GMDNType_id,
				RTRIM(MPC.MT97Type_id) as MT97Type_id,
				RTRIM(MPC.OKOFType_id) as OKOFType_id,
				RTRIM(MPC.OKPType_id) as OKPType_id,
				RTRIM(MPC.OKPDType_id) as OKPDType_id,
				RTRIM(MPC.TNDEDType_id) as TNDEDType_id,
				RTRIM(MPC.UseAreaType_id) as UseAreaType_id,
				RTRIM(MPC.UseSphereType_id) as UseSphereType_id,
				RTRIM(MPC.MedProductType_id) as MedProductType_id,
				RTRIM(MPC.MedProductType_id) as MedProductType_id,
				RTRIM(MPC.FRMOEquipment_id) as FRMOEquipment_id,
				
				RTRIM(MPC.MedProductClass_IsAmbulNovor) as MedProductClass_IsAmbulNovor,
				RTRIM(MPC.MedProductClass_IsAmbulTerr) as MedProductClass_IsAmbulTerr,
				
				RTRIM(CT.CardType_Name) as CardType_Name,
				RTRIM(CRT.ClassRiskType_Name) as ClassRiskType_Name,
				RTRIM(FPT.FuncPurpType_Name) as FuncPurpType_Name,
				RTRIM(FZ30.FZ30Type_Name) as FZ30Type_Name,
				RTRIM(GMDN.GMDNType_Name) as GMDNType_Name,
				RTRIM(MT97.MT97Type_Name) as MT97Type_Name,
				RTRIM(OKOF.OKOFType_Name) as OKOFType_Name,
				RTRIM(OKP.OKPType_Name) as OKPType_Name,
				RTRIM(OKPD.OKPDType_Name) as OKPDType_Name,
				RTRIM(TNDE.TNDEDType_Name) as TNDEDType_Name,
				RTRIM(UAT.UseAreaType_Name) as UseAreaType_Name,
				RTRIM(UST.UseSphereType_Name) as UseSphereType_Name,
				RTRIM(MPT.MedProductType_Name) as MedProductType_Name,
				RTRIM(FRMOE.FRMOEquipment_Name) as FRMOEquipment_Name,
				MPT.MedProductType_Code
			FROM
				passport.v_MedProductClass MPC with(nolock)
				left join passport.v_CardType CT with (nolock) on MPC.CardType_id = CT.CardType_id
				left join passport.v_ClassRiskType CRT with (nolock) on MPC.ClassRiskType_id = CRT.ClassRiskType_id
				left join passport.v_FuncPurpType FPT with (nolock) on MPC.FuncPurpType_id = FPT.FuncPurpType_id
				left join passport.v_FZ30Type FZ30 with (nolock) on MPC.FZ30Type_id = FZ30.FZ30Type_id
				left join passport.v_GMDNType GMDN with (nolock) on MPC.GMDNType_id = GMDN.GMDNType_id
				left join passport.v_MT97Type MT97 with (nolock) on MPC.MT97Type_id = MT97.MT97Type_id
				left join passport.v_OKOFType OKOF with (nolock) on MPC.OKOFType_id = OKOF.OKOFType_id
				left join passport.v_OKPType OKP with (nolock) on MPC.OKPType_id = OKP.OKPType_id
				left join passport.v_OKPDType OKPD with (nolock) on MPC.OKPDType_id = OKPD.OKPDType_id
				left join passport.v_TNDEDType TNDE with (nolock) on MPC.TNDEDType_id = TNDE.TNDEDType_id
				left join passport.v_UseAreaType UAT with (nolock) on MPC.UseAreaType_id = UAT.UseAreaType_id
				left join passport.v_UseSphereType UST with (nolock) on MPC.UseSphereType_id = UST.UseSphereType_id
				left join passport.v_MedProductType MPT with (nolock) on MPC.MedProductType_id = MPT.MedProductType_id
				left join passport.v_MedProductCauseType MPCT with (nolock) on MPCT.MedProductCauseType_id = MPT.MedProductType_id
				left join passport.v_FRMOEquipment FRMOE with (nolock) on FRMOE.FRMOEquipment_id = MPC.FRMOEquipment_id
			WHERE
				(1 = 1)
				and MPC.Lpu_id = :Lpu_id
				" . $filter . "
		";
        //echo getDebugSQL($query, $queryParams);die;
		$res = $this->db->query($query, $queryParams);

		if ( is_object($res) ) {
			return $res->result('array');
		}
		else {
			return false;
		}
	}

	/**
	 * Получение данных классификатора мед. изделий. Метод для API.
	 */
	function loadMedProductClassForAPI($data) {
		$filter = "";
		$queryParams = array();

		if (!empty($data['MedProductClass_id'])) {
			$filter .= " and MedProductClass_id = :MedProductClass_id";
			$queryParams['MedProductClass_id'] = $data['MedProductClass_id'];
		} else if (!empty($data['MedProductClass_Name']) && !empty($data['MedProductClass_Model'])) {
			$filter .= " and MedProductClass_Name = :MedProductClass_Name";
			$queryParams['MedProductClass_Name'] = $data['MedProductClass_Name'];
			$filter .= " and MedProductClass_Model = :MedProductClass_Model";
			$queryParams['MedProductClass_Model'] = $data['MedProductClass_Model'];
		} else {
			return array();
		}

		if (!empty($data['CardType_id'])) {
			$filter .= " and CardType_id = :CardType_id";
			$queryParams['CardType_id'] = $data['CardType_id'];
		}

		if (!empty($data['ClassRiskType_id'])) {
			$filter .= " and ClassRiskType_id = :ClassRiskType_id";
			$queryParams['ClassRiskType_id'] = $data['ClassRiskType_id'];
		}

		if (!empty($data['FuncPurpType_id'])) {
			$filter .= " and FuncPurpType_id = :FuncPurpType_id";
			$queryParams['FuncPurpType_id'] = $data['FuncPurpType_id'];
		}

		if (!empty($data['UseAreaType_id'])) {
			$filter .= " and UseAreaType_id = :UseAreaType_id";
			$queryParams['UseAreaType_id'] = $data['UseAreaType_id'];
		}

		if (!empty($data['UseSphereType_id'])) {
			$filter .= " and UseSphereType_id = :UseSphereType_id";
			$queryParams['UseSphereType_id'] = $data['UseSphereType_id'];
		}

		if (!empty($data['Lpu_id'])) {
			$filter .= " and Lpu_id = :Lpu_id";
			$queryParams['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				MedProductClass_id,
				MedProductClass_Name,
				MedProductClass_Model,
				MedProductType_id,
				CardType_id,
				ClassRiskType_id,
				FuncPurpType_id,
				UseAreaType_id,
				UseSphereType_id,
				FZ30Type_id,
				TNDEDType_id,
				GMDNType_id,
				MT97Type_id,
				OKOFType_id,
				OKPType_id,
				OKPDType_id,
				case when MedProductClass_IsAmbulNovor = 2 then 1 else 0 end as MedProductClass_IsAmbulNovor,
				case when MedProductClass_IsAmbulTerr = 2 then 1 else 0 end as MedProductClass_IsAmbulTerr,
				Lpu_id
			from
				passport.v_MedProductClass (nolock)
			where
				(1=1)
				{$filter}
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 *	Получение каких-то данных (выкуси, парсер)
	 */
	function saveMedProductClass($data)
	{
		// Если для выбранного классификатора МИ имеются созданные экземпляры МИ (карточки МИ), то вернуть ошибку «Редактирование класса МИ не доступно».
		if ($this->getRegionNick() != 'perm' && !empty($data['MedProductClass_id'])) {
			$resp_mpc = $this->queryResult("
				select top 1
					MedProductCard_id
				from
					passport.v_MedProductCard (nolock)
				where
					MedProductClass_id = :MedProductClass_id
			", array(
				'MedProductClass_id' => $data['MedProductClass_id']
			));

			if (!empty($resp_mpc[0]['MedProductCard_id'])) {
				return array(array('Error_Msg' => 'Редактирование класса МИ не доступно'));
			}
		}

		if (!isset($data['MedProductClass_id'])) {
			$proc = 'passport.p_MedProductClass_ins';
		} else {
			$proc = 'passport.p_MedProductClass_upd';
		}
		
		if( isset($data['MedProductClass_IsAmbulNovor'])){$data['MedProductClass_IsAmbulNovor'] = 2;}
		else {$data['MedProductClass_IsAmbulNovor'] = 1;}
		
		if( isset($data['MedProductClass_IsAmbulTerr'])){$data['MedProductClass_IsAmbulTerr'] = 2;}
		else {$data['MedProductClass_IsAmbulTerr'] = 1;}

		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :MedProductClass_id;
			exec " . $proc . "
				@MedProductClass_id = @Res output,
				@MedProductClass_Name = :MedProductClass_Name,
				@MedProductClass_Model = :MedProductClass_Model,
				@CardType_id = :CardType_id,
				@FRMOEquipment_id = :FRMOEquipment_id,
				@ClassRiskType_id = :ClassRiskType_id,
				@FuncPurpType_id = :FuncPurpType_id,
				@FZ30Type_id = :FZ30Type_id,
				@GMDNType_id = :GMDNType_id,
				@MT97Type_id = :MT97Type_id,
				@OKOFType_id = :OKOFType_id,
				@OKPType_id = :OKPType_id,
				@OKPDType_id = :OKPDType_id,
				@TNDEDType_id = :TNDEDType_id,
				@UseAreaType_id = :UseAreaType_id,
				@UseSphereType_id = :UseSphereType_id,
				@MedProductType_id = :MedProductType_id,
				@MedProductClass_IsAmbulNovor = :MedProductClass_IsAmbulNovor,
				@MedProductClass_IsAmbulTerr = :MedProductClass_IsAmbulTerr,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as MedProductClass_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSql($query, $data); die;
		$res = $this->db->query($query, $data);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}


	/**
	 *	Сохранение связи с транспортной площадкой
	 */
	function saveTransportConnect($data)
	{
		if (!isset($data['TransportConnect_id'])) {
			$proc = 'passport.p_TransportConnect_ins';
			$data['TransportConnect_id'] = 0;
		} else {
			$proc = 'passport.p_TransportConnect_upd';
		}

		//для одной площадки может быть только одна запись
		$query = "
			select
				count(*) as cnt
			from
				passport.v_TransportConnect with(nolock)
			where
				MOArea_id = :MOArea_id
				and TransportConnect_id <> :TransportConnect_id
		";

		//echo getDebugSql($query, $data); die;
		$result = $this->db->query($query, $data);

		if (is_object($result)) {
			$response = $result->result('array');

			if (is_array($response) && !empty($response[0]['cnt']) && $response[0]['cnt'] > 0) {
				return array(0 => array('success' => false, 'Error_Msg' => 'Для данной площадки уже указан набор транспортных узлов.'));
			}
		} else {
			return false;
		}

		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :TransportConnect_id;
			exec " . $proc . "
				@TransportConnect_id = @Res output,
				@MOArea_id = :MOArea_id,
				@TransportConnect_AreaIdent = :TransportConnect_AreaIdent,
				@TransportConnect_Station = :TransportConnect_Station,
				@TransportConnect_DisStation = :TransportConnect_DisStation,
				@TransportConnect_Airport = :TransportConnect_Airport,
				@TransportConnect_DisAirport = :TransportConnect_DisAirport,
				@TransportConnect_Railway = :TransportConnect_Railway,
				@TransportConnect_DisRailway = :TransportConnect_DisRailway,
				@TransportConnect_Heliport = :TransportConnect_Heliport,
				@TransportConnect_DisHeliport = :TransportConnect_DisHeliport,
				@TransportConnect_MainRoad = :TransportConnect_MainRoad,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as TransportConnect_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSql($query, $data); die;
		$res = $this->db->query($query, $data);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

    /**
     * Загружает список площадок занимаемых организацией
     */
    function loadTransportConnect($data)
    {
        $filter = "(1=1)";

        if (isset($data['TransportConnect_id']))
        {
            $filter .= ' and TC.TransportConnect_id = :TransportConnect_id';
        }

		if (isset($data['Lpu_id']))
		{
			$filter .= ' and MO.Lpu_id = :Lpu_id';
		}

        $query = "
            Select
                TC.TransportConnect_id,
                TC.MOArea_id,
                MO.MOArea_Name,
                TC.TransportConnect_AreaIdent,
                TC.TransportConnect_Station,
                TC.TransportConnect_DisStation,
                TC.TransportConnect_Airport,
                TC.TransportConnect_DisAirport,
                TC.TransportConnect_Railway,
                TC.TransportConnect_DisRailway,
                TC.TransportConnect_Heliport,
                TC.TransportConnect_DisHeliport,
                TC.TransportConnect_MainRoad
            from passport.v_TransportConnect TC with (nolock)
            	left join fed.v_MOArea MO with (nolock) on TC.MOArea_id = MO.MOArea_id
            where
                {$filter}
        ";

        //echo getDebugSQL($query, $data);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }


	/**
	 *	Сохранение периода функционирования
	 */
	function saveFunctionTime($data)
	{
		if (!isset($data['FunctionTime_id'])) {
			$proc = 'passport.p_FunctionTime_ins';
		} else {
			$proc = 'passport.p_FunctionTime_upd';
		}

		//проверяем на дублирующие значения и пересечения периодов
		$query = "
			Select
				COUNT (*) as [count]
			from
				passport.FunctionTime (nolock)
			where
				FunctionTime_id <> isnull(:FunctionTime_id, 0) and
				Lpu_id = :Lpu_id and
				(FunctionTime_begDate <= :FunctionTime_endDate or :FunctionTime_endDate is null) and
				(FunctionTime_endDate >= :FunctionTime_begDate or FunctionTime_endDate is null)
		";

		//echo getDebugSQL($query, $data); die;
		$result = $this->db->query($query, $data);

		if ( !is_object($result) ) {
			return array(0 => array('Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}

		$response = $result->result('array');

		if ( $response[0]['count'] > 0 ) {
			return array(0 => array('Error_Msg' => 'Периоды функционирования не могут пересекатся.'));
		}

		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :FunctionTime_id;
			exec " . $proc . "
				@FunctionTime_id = @Res output,
				@Lpu_id = :Lpu_id,
				@InstitutionFunction_id = :InstitutionFunction_id,
				@FunctionTime_begDate = :FunctionTime_begDate,
				@FunctionTime_endDate = :FunctionTime_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as FunctionTime_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		//echo getDebugSql($query, $data); die;
		$res = $this->db->query($query, $data);

		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}

    /**
     * Загружает список площадок занимаемых организацией
     */
    function loadFunctionTime($data)
    {
        $filter = "(1=1)";

        if (isset($data['FunctionTime_id']))
        {
            $filter .= ' and FC.FunctionTime_id = :FunctionTime_id';
        }

        $query = "
            Select
                FC.FunctionTime_id,
                FC.InstitutionFunction_id,
				rtrim(isNull(convert(varchar,cast(FC.FunctionTime_begDate as datetime),104),'')) as FunctionTime_begDate,
				rtrim(isNull(convert(varchar,cast(FC.FunctionTime_endDate as datetime),104),'')) as FunctionTime_endDate,
                IFunc.InstitutionFunction_Name,
                FC.Lpu_id
            from passport.FunctionTime FC with (nolock)
            	left join passport.InstitutionFunction IFunc with (nolock) on IFunc.InstitutionFunction_id = FC.InstitutionFunction_id
            where
            	Lpu_id = :Lpu_id and
                {$filter}
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Загружает период функционирования МО. Метод для API.
     */
    function loadFunctionTimeById($data)
    {
        $query = "
            Select
                FC.FunctionTime_id,
                FC.InstitutionFunction_id,
				convert(varchar(10), FC.FunctionTime_begDate, 120) as FunctionTime_begDate,
				convert(varchar(10), FC.FunctionTime_endDate, 120) as FunctionTime_endDate,
				FC.Lpu_id
            from passport.FunctionTime FC with (nolock)
            where
            	FC.FunctionTime_id = :FunctionTime_id
            	and FC.Lpu_id = :Lpu_id
        ";

        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

	/**
	 * Получение списка МО
	 */
	function loadLpuListForReport($data=array()) {
		$params = array();
		$filters = '(1=1)';

		if (!empty($data['Lpu_id']) && $data['Lpu_id'] > 0) {
			$filters .= " and Lpu_id = :Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		}
		$query = "
			select
				Lpu_id,
				Lpu_f003mcod,
				Lpu_Nick
			from v_Lpu with(nolock)
			where {$filters}
		";
		return $this->queryResult($query, $params);
	}

	/**
	 * Сохранения подстанции
	 */
	function saveCmpSubstation($data) {
		$this->beginTransaction();

		$query = "
			select top 1 count(CmpSubstation_id) as Count
			from v_CmpSubstation with(nolock)
			where Lpu_id = :Lpu_id and CmpSubstation_Code = :CmpSubstation_Code
			and CmpSubstation_id <> isnull(:CmpSubstation_id,0)
		";
		$count = $this->getFirstResultFromQuery($query, array(
			'Lpu_id' => $data['Lpu_uid'],
			'CmpSubstation_Code' => $data['CmpSubstation_Code'],
			'CmpSubstation_id' => !empty($data['CmpSubstation_id'])?$data['CmpSubstation_id']:null,
		));
		if ($count === false) {
			$this->rollbackTransaction();
			return $this->createError('', 'Ошибка при проверке подстанции');
		}
		if ($count > 0) {
			return $this->createError('', 'Подстанция с указанным кодом уже существует');
		}

		if (!empty($data['CmpSubstation_id'])) {
			$procedure = 'p_CmpSubstation_upd';
		} else {
			$procedure = 'p_CmpSubstation_ins';
		}
			
		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :CmpSubstation_id;
			exec {$procedure}
				@CmpSubstation_id = @Res output,
				@CmpSubstation_Code = :CmpSubstation_Code,
				@CmpSubstation_Name = :CmpSubstation_Name,
				@CmpStationCategory_id =:CmpStationCategory_id,
				@CMPSubstation_IsACS =:CMPSubstation_IsACS,
				@Lpu_id = :Lpu_id,
				@LpuBuilding_id = :LpuBuilding_id,
				@LpuUnit_id = :LpuUnit_id,
				@LpuSection_id = :LpuSection_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpSubstation_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		$queryParams = array(
			'CmpSubstation_id' => !empty($data['CmpSubstation_id'])?$data['CmpSubstation_id']:null,
			'CmpSubstation_Code' => $data['CmpSubstation_Code'],
			'CmpSubstation_Name' => $data['CmpSubstation_Name'],
			'CmpStationCategory_id' => $data['CmpStationCategory_id'],
			'Lpu_id' => $data['Lpu_uid'],
			'LpuBuilding_id' => !empty($data['LpuBuilding_id'])?$data['LpuBuilding_id']:null,
			'CMPSubstation_IsACS' => !empty($data['CMPSubstation_IsACS'])?2:1,
			'LpuUnit_id' => !empty($data['LpuUnit_id'])?$data['LpuUnit_id']:null,
			'LpuSection_id' => !empty($data['LpuSection_id'])?$data['LpuSection_id']:null,
			'pmUser_id' => $data['pmUser_id']
		);
		
		//var_dump(getDebugSQL($query, $queryParams)); exit;

		$response = $this->queryResult($query, $queryParams);

		if (!$this->isSuccessful($response)) {
			$this->rollbackTransaction();
			return $response;
		}

		$CmpEmergencyTeamData = array();
		if (!empty($data['CmpEmergencyTeamData'])) {
			$CmpEmergencyTeamData = json_decode($data['CmpEmergencyTeamData'], true);
		}

		foreach($CmpEmergencyTeamData as $item) {
			$item['pmUser_id'] = $data['pmUser_id'];
			$item['CmpSubstation_id'] = $response[0]['CmpSubstation_id'];

			switch($item['RecordStatus_Code']) {
				case 0:
				case 2:
					$resp = $this->saveCmpEmergencyTeam($item);
					break;

				case 3:
					$resp = $this->deleteCmpEmergencyTeam($item);
					break;
			}
			if (!empty($resp) && !$this->isSuccessful($resp)) {
				$this->rollbackTransaction();
				return $resp;
			}
		}

		$this->commitTransaction();
		return array(array('success' => true, 'CmpSubstation_id' => $response[0]['CmpSubstation_id'], 'Error_Msg' => ''));
	}

	/**
	 * Сохранения бригады
	 */
	function saveCmpEmergencyTeam($data) {
		$queryParams = array(
			'CmpEmergencyTeam_id' => (!empty($data['CmpEmergencyTeam_id']) && $data['CmpEmergencyTeam_id'] > 0)?$data['CmpEmergencyTeam_id']:null,
			'CmpSubstation_id' => $data['CmpSubstation_id'],
			'CmpProfile_id' => $data['CmpProfile_id'],
			'CmpProfileTFOMS_id' => $data['CmpProfileTFOMS_id'],
			'CmpEmergencyTeam_Count' => $data['CmpEmergencyTeam_Count'],
			'pmUser_id' => $data['pmUser_id']
		);

		$query = "
			select top 1 count(CmpEmergencyTeam_id) as Count
			from v_CmpEmergencyTeam with(nolock)
			where CmpSubstation_id = :CmpSubstation_id and CmpProfile_id = :CmpProfile_id
			and CmpEmergencyTeam_id <> isnull(:CmpEmergencyTeam_id, 0)
		";
		$count = $this->getFirstResultFromQuery($query, $queryParams);
		if ($count === false) {
			return $this->createError('', 'Ошибка при проверке бригады');
		}
		if ($count > 0) {
			return $this->createError('', 'На подстанции уже существет бригада с указанным профилем');
		}

		if (!empty($data['CmpEmergencyTeam_id']) && $data['CmpEmergencyTeam_id'] > 0) {
			$procedure = 'p_CmpEmergencyTeam_upd';
		} else {
			$procedure = 'p_CmpEmergencyTeam_ins';
		}

		$query = "
			declare
                @Res bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @Res = :CmpEmergencyTeam_id;
			exec {$procedure}
				@CmpEmergencyTeam_id = @Res output,
				@CmpSubstation_id = :CmpSubstation_id,
				@CmpProfile_id = :CmpProfile_id,
				@CmpProfileTFOMS_id = :CmpProfileTFOMS_id,
				@CmpEmergencyTeam_Count = :CmpEmergencyTeam_Count,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @Res as CmpEmergencyTeam_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Удаление бригады
	 */
	function deleteCmpEmergencyTeam($data) {
		$queryParams = array('CmpEmergencyTeam_id' => $data['CmpEmergencyTeam_id']);

		$query = "
			declare
				@ErrCode int,
				@ErrMessage varchar(4000);
			exec p_CmpEmergencyTeam_del
				@CmpEmergencyTeam_id = :CmpEmergencyTeam_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		return $this->queryResult($query, $queryParams);
	}

	/**
	 * Получения списка подстанций
	 */
	function loadCmpSubstationGrid($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);
		$query = "
			select
				CS.CmpSubstation_id,
				CS.CmpSubstation_Code,
				CS.CmpSubstation_Name,
				coalesce(LS.LpuSection_Name,LU.LpuUnit_Name,LB.LpuBuilding_Name,L.Lpu_Nick) as LpuStructure_Name
			from
				v_CmpSubstation CS with(nolock)
				left join v_Lpu L with(nolock) on L.Lpu_id = CS.Lpu_id
				left join v_LpuBuilding LB with(nolock) on LB.LpuBuilding_id = CS.LpuBuilding_id
				left join v_LpuUnit LU with(nolock) on LU.LpuUnit_id = CS.LpuUnit_id
				left join v_LpuSection LS with(nolock) on LS.LpuSection_id = CS.LpuSection_id
			where CS.Lpu_id = :Lpu_id
		";

		$response = $this->queryResult($query, $params);
		return array('data' => $response);
	}

	/**
	 * Получения подстанции для редактирования
	 */
	function loadCmpSubstationForm($data) {
		$params = array('CmpSubstation_id' => $data['CmpSubstation_id']);
		$query = "
			select top 1
				CS.CmpSubstation_id,
				CS.CmpSubstation_Code,
				CS.CmpSubstation_Name,
				CS.Lpu_id,
				CS.CmpStationCategory_id,
				CS.CMPSubstation_IsACS,
				case
					when CS.LpuSection_id is not null then 'LpuSection_'+cast(CS.LpuSection_id as varchar)
					when CS.LpuUnit_id is not null then 'LpuUnit_'+cast(CS.LpuUnit_id as varchar)
					when CS.LpuBuilding_id is not null then 'LpuBuilding_'+cast(CS.LpuBuilding_id as varchar)
					when CS.Lpu_id is not null then 'Lpu_'+cast(CS.Lpu_id as varchar)
				end as LpuStructure_id
			from v_CmpSubstation CS with(nolock)
			where CS.CmpSubstation_id = :CmpSubstation_id
		";

		return $this->queryResult($query, $params);
	}

	/**
	 * Получение списка бригад
	 */
	function loadCmpEmergencyTeamGrid($data) {
		$params = array('CmpSubstation_id' => $data['CmpSubstation_id']);
		$query = "
			select
				CET.CmpEmergencyTeam_id,
				CET.CmpSubstation_id,
				CP.CmpProfile_id,
				CP.CmpProfile_Name,
				CPT.CmpProfileTFOMS_id,
				CPT.CmpProfileTFOMS_Name,
				CET.CmpEmergencyTeam_Count,
				1 as RecordStatus_Code
			from
				v_CmpEmergencyTeam CET with(nolock)
				left join v_CmpProfile CP with(nolock) on CP.CmpProfile_id = CET.CmpProfile_id
				left join v_CmpProfileTFOMS CPT with(nolock) on CPT.CmpProfileTFOMS_id = CET.CmpProfileTFOMS_id
			where CET.CmpSubstation_id = :CmpSubstation_id
		";

		$response = $this->queryResult($query, $params);
		return array('data' => $response);
	}

	/**
	 * Получение списка ОКАТО
	 */
	function loadOKATOList($data) {

		$filter = '(1=1)';
		if (!empty($data['OKATO_id'])) {
			$filter .= " and OKATO_id = :OKATO_id";
		} else if (( !empty($data['query']) ) && ( strlen($data['query'])>=3 )) {
			$filter .= " and (OKATO_Name like :query + '%' or OKATO_Code like :query + '%')";
		} else {

			if (!empty($data['OKATO_Name'])) {
				$filter .= " and (OKATO_Name like :OKATO_Name + '%')";
			}

			if (!empty($data['OKATO_Code'])) {
				$filter .= " and (OKATO_Code like :OKATO_Code + '%')";
			}
		}

		$top = '';
		if($filter === '(1=1)'){
			$top .= " top (500) ";
		}

		$query = "
			select {$top}
				OKATO_id,
				OKATO_Code,
				OKATO_Name + case when OKATO_CentrName is not null then ' (' + OKATO_CentrName + ')' else '' end as OKATO_Name
			from
				nsi.OKATO with(nolock)
			where
				{$filter}
		";

		//echo getDebugSQL($query, $data);die;
		$response = $this->queryResult($query, $data);
		return $response;
	}

	/**
	 * Получение списка МО для выгрузки паспортов МО
	 */
	function getExportLpuPassportXmlData($data) {
		$query = "
			select Lpu_id, Lpu_Nick
			from v_Lpu with(nolock)
			where (:Lpu_id is null or Lpu_id = :Lpu_id)
		";
		$params = array(
			'Lpu_id' => !empty($data['Lpu_id'])?$data['Lpu_id']:null
		);
		$resp = $this->queryResult($query, $params);

		return $resp;
	}

	/**
     * Загружает список периодов, в которых МО может производить обслуживание населения на дому по стоматологическим профилям
     */
    function loadLpuPeriodStom($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);
        $filter = "(1=1)";

        if (isset($data['LpuPeriodStom_id']))
        {
            $filter .= ' and LpuPeriodStom_id = :LpuPeriodStom_id';
            $params['LpuPeriodStom_id'] = $data['LpuPeriodStom_id'];
        }

        $query = "
            Select
                LPS.LpuPeriodStom_id,
                LPS.Lpu_id,
                RTrim(IsNull(convert(varchar,cast(LPS.LpuPeriodStom_begDate as datetime),104),'')) as LpuPeriodStom_begDate,
                RTrim(IsNull(convert(varchar,cast(LPS.LpuPeriodStom_endDate as datetime),104),'')) as LpuPeriodStom_endDate
            from v_LpuPeriodStom LPS with (nolock)
            where
                LPS.Lpu_id = :Lpu_id and
                {$filter}
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Сохраняет период, в котором МО может производить обслуживание населения на дому по стоматологическим профилям
     */
    function saveLpuPeriodStom($data)
    {
    	$params = array('Lpu_id' => $data['Lpu_id']);

        $query = "
            Select
                RTrim(IsNull(convert(varchar,cast(LL.LpuLicence_begDate as datetime),120),'')) as LpuLicence_begDate,
				RTrim(IsNull(convert(varchar,cast(LL.LpuLicence_endDate as datetime),120),'')) as LpuLicence_endDate
            from v_LpuLicence LL with (nolock)
            left join fed.v_LpuLicenceProfile LLP with (nolock) on LLP.LpuLicence_id = LL.LpuLicence_id
            left join fed.v_LpuLicenceProfileType LLPT with (nolock) on LLP.LpuLicenceProfileType_id = LLPT.LpuLicenceProfileType_id
            where
                LL.Lpu_id = :Lpu_id 
                and LLPT.LpuLicenceProfileType_Name like 'стоматология%'
                and LL.LpuLicence_begDate <= dbo.tzGetDate()
                and (LL.LpuLicence_endDate is null or LL.LpuLicence_endDate > dbo.tzGetDate())
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
        	$res = $result->result('array');
        	if(count($res) == 0){
        		return array(array('Error_Code'=>500,'Error_Msg'=>'Нет действующих лицензий по стоматологическим профилям. Добавление периода невозможно.'));
        	}
        	$periods = '';
    		$beg = strtotime($data['LpuPeriodStom_begDate']);
    		$end = ((!empty($data['LpuPeriodStom_endDate'])) ? strtotime($data['LpuPeriodStom_endDate']) : 0);
    		$out = 0;
    		foreach ($res as $key => $value) {
    			foreach ($value as $k => $v) {
        			$ress[0][$key][$k.'M'] = (!empty($v)) ? strtotime($v) : 0 ;
        			$periods .= (($k=='LpuLicence_begDate') ? ' начало ' : ' окончание ' );
        			if(!empty($v)){
    					$date = new DateTime($v);
						$periods .= $date->format('d.m.Y');
    				} else {
    					$periods .= 'не определено';
    				}
        		}
        		if($key<(count($res)-1))
        			$periods .= ',<br />';
    			if($beg<strtotime($value['LpuLicence_begDate'])){
    				$out++;
    				continue;
    			}
    			if(!empty($value['LpuLicence_endDate'])){
    				if($beg>strtotime($value['LpuLicence_endDate'])){
        				$out++;
        				continue;
        			}
    			}
        		if(!empty($data['LpuPeriodStom_endDate'])){
        			if($end<strtotime($value['LpuLicence_begDate'])){
        				$out++;
        				continue;
        			}
        			if(!empty($value['LpuLicence_endDate'])){
        				if($end>strtotime($value['LpuLicence_endDate'])){
	        				$out++;
	        				continue;
	        			}
        			}
        		}
        	}
        	if($out == count($res))
        		return array(array('Error_Code'=>5555,'Error_Msg'=>'Период обслуживания должен входить в один из периодов дествия лицензий по стоматологическим профилям:<br />'.$periods));
        }
        else {
            return false;
        }

        $proc = "p_LpuPeriodStom_" . ( empty($data['LpuPeriodStom_id']) ? 'ins' : 'upd' );

        $query = "
			declare
                @LpuPeriodStom_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
            set @LpuPeriodStom_id = :LpuPeriodStom_id;
			exec " . $proc . "
				@LpuPeriodStom_id = @LpuPeriodStom_id output,
				@Lpu_id = :Lpu_id,
				@LpuPeriodStom_begDate = :LpuPeriodStom_begDate,
				@LpuPeriodStom_endDate = :LpuPeriodStom_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @LpuPeriodStom_id as LpuPeriodStom_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

        //echo getDebugSQL($query, $data);
        $result = $this->db->query($query, $data);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
     * Проверяет даты стом. лицензии МО
     */
    function checkLpuStomLicenceDates($data)
    {
        $params = array('Lpu_id' => $data['Lpu_id']);

        $query = "
            Select
                RTrim(IsNull(convert(varchar,cast(LL.LpuLicence_begDate as datetime),120),'')) as LpuLicence_begDate,
				RTrim(IsNull(convert(varchar,cast(LL.LpuLicence_endDate as datetime),120),'')) as LpuLicence_endDate
            from v_LpuLicence LL with (nolock)
            left join fed.v_LpuLicenceProfile LLP with (nolock) on LLP.LpuLicence_id = LL.LpuLicence_id
            left join fed.v_LpuLicenceProfileType LLPT with (nolock) on LLP.LpuLicenceProfileType_id = LLPT.LpuLicenceProfileType_id
            where
                LL.Lpu_id = :Lpu_id 
                and LLPT.LpuLicenceProfileType_Name like 'стоматология%'
                and LL.LpuLicence_begDate <= dbo.tzGetDate()
                and (LL.LpuLicence_endDate is null or LL.LpuLicence_endDate > dbo.tzGetDate())
        ";
        //echo getDebugSQL($query, $params);
        $result = $this->db->query($query, $params);

        if ( is_object($result) )
        {
            return $result->result('array');
        }
        else {
            return false;
        }
    }

    /**
	 *	Функция проверки класса медицинского изделия на наличие созданных медицинских изделий
	 */
	function checkMedProductCardHasClass($data)
	{
		$filter = "MPC.MedProductClass_id = :MedProductClass_id";
		$params = array('MedProductClass_id' => $data['MedProductClass_id']);

		$query = "
			SELECT
				COUNT (*) as count
			FROM
				passport.v_MedProductCard MPC with (nolock)
			WHERE
				{$filter}
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $params);

		if ( !is_object($result) ) {
			return array(0 => array('success' => false, 'Error_Msg' => 'При выполнении проверки сервер базы данных вернул ошибку.'));
		}

		$response = $result->result('array');
		if ( $response[0]['count'] > 0 ) {
			return array(0 => array('success' => false, 'Error_Msg' => 'Внимание! Для выбранного класса имеются созданные медицинские изделия. Редактирование класса запрещено.'));
		} else {
			return false;
		}
	}

	/**
	 *	Функция проверки наличия ЛПУ
	 */
	function checkLpu($data)
	{
		$query = "
			SELECT
				Lpu_id
			FROM
				v_Lpu with (nolock)
			WHERE
				Org_id = :org_id
		";

		//echo getDebugSQL($query, $data);die;
		$result = $this->db->query($query, $data);

		if ( is_object($result) ) {
			return $result->result('array');
		} else {
			return false;
		}
	}

	/**
	* Проверка адреса пациента на соответствие зонам обслуживания МО
	*/
	function checkOrgServiceTerr($data)
	{
		$params = array(
			'person_id' => $data['person_id'],
			'org_id'	=> $data['org_id']
		);
		$query = "
			select top 1 OST.OrgServiceTerr_id
			from v_PersonState PS (nolock)
			left join v_Address A (nolock) on A.Address_id = PS.UAddress_id
			left join v_Address AU (nolock) on AU.Address_id = PS.PAddress_id --учитываем адресс проживания
			cross apply (
				select top 1 O.OrgServiceTerr_id
				from v_OrgServiceTerr O (nolock)
				where O.Org_id = :org_id
				and ((KLCountry_id is null or KLCountry_id = A.KLCountry_id) or (KLCountry_id is null or KLCountry_id = AU.KLCountry_id))
				and ((KLRGN_id is null or KLRGN_id = A.KLRGN_id) or (KLRGN_id is null or KLRGN_id = AU.KLRGN_id))
				and ((KLSubRGN_id is null or KLSubRGN_id = A.KLSubRGN_id) or (KLSubRGN_id is null or KLSubRGN_id = AU.KLSubRGN_id))
				and ((KLCity_id is null or KLCity_id = A.KLCity_id) or (KLCity_id is null or KLCity_id = AU.KLCity_id))
				and ((KLTown_id is null or KLTown_id = A.KLTown_id) or (KLTown_id is null or KLTown_id = AU.KLTown_id))
			) as OST
			where PS.Person_id = :person_id
		";
		//echo getDebugSQL($query, $params);die;
		$result = $this->db->query($query, $params);
		if(is_object($result)){
			$result = $result->result('array');
			if(count($result) > 0)
				return $result;
			else
				return false;
		}
		else
			return false;
	}
	
	/**
	 * Функция получения списка зданий МО
	 */
	function LpuBuildingPassList($data){
		if( !isset($data['Lpu_id']) ) return false;
		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
		SELECT
			LpuBuildingPass_id,
			LpuBuildingPass_Name
		FROM v_LpuBuildingPass
		where
			Lpu_id = :Lpu_id";

		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
		    $resultWithEmptyValue = $result->result('array');
		    $resultWithEmptyValue[] = "&nbsp;";
			return $resultWithEmptyValue;
		}
		else {
			return false;
		}
	}	
	
	/**
	 * Функция получения Lpu_id по MedProductCard_id (Идентификатор мед. изделия)
	 */
	function getLpuByMedProductCard($data){
		if( !isset($data['MedProductCard_id']) ) return false;
		
		$result = $this->getFirstRowFromQuery("
			select
				LB.Lpu_id
			from
				passport.v_MedProductCard MPC
				left join LpuBuilding LB with (nolock) on MPC.LpuBuilding_id = LB.LpuBuilding_id
			where
				MPC.MedProductCard_id = :MedProductCard_id
		", $data);
		if(!empty($result)){
			return $result['Lpu_id'];
		}else{
			return false;
		}
	}

	/**
	 * Метод для получения данных обо всех филиалах МО по Lpu_id или некоторых с учетом фильтров. Для отображения в гриде
	 *
	 *
	 * @param $data [Lpu_id; optional: LpuFilial_begDate, LpuFilial_endDate, LpuFilial_Name, LpuFilial_Nick]
	 * @return array
	 * @throws Exception
	 */
	function getLpuFilialGrid($data)
	{
		// Проверка наличия главного идентификатора для поиска филиалов
		if ( ! isset($data['Lpu_id']) || ! (int) $data['Lpu_id'] > 0 )
		{
			return false; //throw new Exception('Не удалось загрузить. Не известен id МО');
		}

		// Список возможных фильтров
		$filters = array('LpuFilial_begDate', 'LpuFilial_endDate', 'LpuFilial_Name', 'LpuFilial_Nick');

		$filterQuery = '';

		// Формируем дополнительные условия для фильтрации, если есть фильтры в запросе
		foreach ($data as $key => $value)
		{

			if ( $value === null || ! in_array($key, $filters) )
			{
				continue;
			}

			// Определяем, поле с датой или просто текст
			if (strpos($key, 'Date') === false)
			{
				$filterQuery .= "AND $key LIKE :$key ";
				$data[$key] = "%$value%";
			} else
			{
				// Ставим разный оператор сравнения в зависимости от того, begDate или endDate
				$dateBeg = (strpos($key, 'beg') !== false) ? true : false;

				$operator = $dateBeg ? ">=" : "<=";
				$filterQuery .= "AND $key $operator :$key ";

			}

		}


		// Выбираем дату сразу в нужном формате d.m.Y, иначе она приходит в неподходящем для поля формате и не отображается
		$query =
			"SELECT
				LpuFilial_id,
				LpuFilial_Name,
				LpuFilial_Nick,
				Oktmo_Code as Oktmo_Name,
				convert(varchar, LpuFilial_begDate, 104) as LpuFilial_begDate,
				convert(varchar, LpuFilial_endDate, 104) as LpuFilial_endDate
			FROM 
				v_LpuFilial LF with (nolock)
			  JOIN
				v_Oktmo O with (nolock) ON LF.Oktmo_id = O.Oktmo_id
			WHERE
				Lpu_id = :Lpu_id
				$filterQuery";

		$results = $this->queryResult($query, $data);

		return $results;
	}

	/**
	 * Метод достает одну запись из таблицы филиалов по LpuFilial_id
	 *
	 * @param $data [LpuFilial_id]
	 * @return array | bool
	 */
	function getLpuFilialRecord($data)
	{
		if ( ! isset($data['LpuFilial_id']) || ! (int) $data['LpuFilial_id'] > 0 )
		{
			return false;
		}

		$query =
			"SELECT TOP 1
				LF.LpuFilial_id,
				LF.LpuFilial_Name,
				LF.LpuFilial_Nick,
				LF.LpuFilial_Code,
				convert(varchar(10), LF.LpuFilial_begDate, 104) as LpuFilial_begDate,
				convert(varchar(10), LF.LpuFilial_endDate, 104) as LpuFilial_endDate,
				O.Oktmo_id,
				O.Oktmo_Code as Oktmo_Name,
				LF.RegisterMO_id
			FROM 
				v_LpuFilial LF with (nolock)
			JOIN
				v_Oktmo O with (nolock) ON LF.Oktmo_id = O.Oktmo_id
			WHERE
				LF.LpuFilial_id = :LpuFilial_id";

		$result = $this->getFirstRowFromQuery($query, $data);


		// Вернем ошибку если запись не найдена
		if ($result === false)
		{
			return false; //return array(array('success' => false, 'Error_Msg' => 'Не удалось найти указанный филиал'));
		}

		// Оборачиваем результат в массив, чтобы внутри получился массив с индексом 0, иначе дальше он некорректно конвертится в Json и не отображается в форме
		return array($result);

	}

	/**
	 * Метод для сохранения (вставки/обновления) записи в таблице Филиалы
	 *
	 * @param $data [Lpu_id, LpuFilial_Name, LpuFilial_Code, pmUser_id, Oktmo_id, LpuFilial_Nick, LpuFilial_id, LpuFilial_begDate, LpuFilial_endDate]
	 * @return array - массив с описание ошибок или успешного завершения
	 */
	function saveLpuFilialRecord($data)
	{
		// Перечисляем все необходимые поля, чтобы достать только их из массива данных $data. Там много лишних сессионных переменных, но если кинуть ее напрямую в запрос, то ничего не изменится
		$requiredFields = array ('Lpu_id' => null, 'LpuFilial_Name' => null, 'LpuFilial_Code' => null, 'pmUser_id' => null, 'Oktmo_id' => null,
			'LpuFilial_Nick' => null, 'LpuFilial_id' => null, 'RegisterMO_id' => null, 'LpuFilial_begDate' => null, 'LpuFilial_endDate' => null);

		$params = array_intersect_key($data, $requiredFields);

		// Проверка все ли данные пришли
		if ( count($params) !== count($requiredFields) )
		{
			return false; //return array('success' => false, 'Error_Msg' => 'Недостаточно данных');
		}


		// Если дата начала совпадает или больше даты завершения, то выкинем сообщение с ошибкой
		if ( (int) strtotime($params['LpuFilial_begDate']) >= (int) strtotime($params['LpuFilial_endDate']) && (int) strtotime($params['LpuFilial_endDate']) !== 0 )
		{
			return false; //return array('success' => false, 'Error_Msg' => "Уточните период действия филиала");
		}


		// Проверка на уникальность по наименованию в определенном временном периоде (не могут существовать два филиала с одним названием, если их периоды действия пересекаются)
		$query =
			"SELECT TOP 1
				LpuFilial_id
			FROM
				v_LpuFilial with (nolock)
			WHERE 
				LpuFilial_Name = :LpuFilial_Name AND
				(LpuFilial_id != :LpuFilial_id OR :LpuFilial_id IS NULL) AND 
					(
						(LpuFilial_begDate <= :LpuFilial_endDate OR :LpuFilial_endDate IS NULL ) AND
						(:LpuFilial_begDate <= LpuFilial_endDate OR LpuFilial_endDate IS NULL )
					)";

		$result = $this->getFirstResultFromQuery($query, $params);


		// Сообщение по ТЗ
		if ($result !== false)
		{
			return array('success' => false, 'Error_Msg' => "Филиал с наименованием <b>{$params['LpuFilial_Name']}</b> уже существует в системе. Измените наименование или уточните период действия филиала");
		}

		// Определяем вставка/обновление исходя из наличия идентификатора филиала
		$action = ( (int) $params['LpuFilial_id'] > 0 ) ? 'upd' : 'ins';

		$query =
			"DECLARE
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);
				
			SET @Res = :LpuFilial_id;
			
			EXEC p_LpuFilial_$action
				@LpuFilial_id = @Res output,
				@LpuFilial_Code = :LpuFilial_Code,
				@LpuFilial_Name = :LpuFilial_Name,
				@LpuFilial_Nick = :LpuFilial_Nick,
				@Lpu_id = :Lpu_id,
				@Oktmo_id = :Oktmo_id,
				@RegisterMO_id = :RegisterMO_id,
				@LpuFilial_begDate = :LpuFilial_begDate,
				@LpuFilial_endDate = :LpuFilial_endDate,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
				
			SELECT 
				@Res as LpuFilial_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$result = $this->queryResult($query, $params);

		return $result;
	}

	/**
	 * Метод для удаления записи из таблицы филиалов
	 *
	 * @param $data [id, object, linkedTables]
	 * @return array | bool
	 */
	function deleteLpuFilialRecord($data)
	{
		if ( ! is_numeric($data['id']) || ! (int) $data['id'] > 0 )
		{
			return false;
		}

		$query =
			"
			SELECT TOP 1
				LpuBuilding_id
			FROM 
				v_LpuBuilding with (nolock)
			WHERE
				LpuFilial_id = :id";

		$result = $this->getFirstResultFromQuery($query, $data);


		// если в результате запроса была обнаружена связанная запись, то удаление запрещено
		if ($result !== false)
		{
			return array('success' => false, 'Error_Msg' => 'В состав выбранного филиала входят подразделения. Удаление невозможно');
		}


		$query =
			"
			DECLARE
				@ErrCode bigint,
				@ErrMsg varchar(4000);
				
			EXEC p_LpuFilial_del
				@LpuFilial_id = :id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
				
			SELECT 
				@ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$result = $this->queryResult($query, $data);

		return $result;

	}
	
	/**
	 * Загрузка грида "Услуга договора" формы "Договор по сторонним специалистам" (Паспорт МО)
	 */
	function loadServiceContract($data){
		if(empty($data['LpuDispContract_id'])) return false;
		
		$query = "
			--declare @Region_id bigint;
			--set @Region_id = dbo.GetRegion();

			select
				LpuDispContractUslugaComplexLink_id,
				uc.UslugaComplex_id,
				uc.UslugaComplex_Code,
				uc.UslugaComplex_Name,
				ucat.UslugaCategory_Name,
				ucat.UslugaCategory_id,
				LDCUC.LpuDispContractUslugaComplexLink_Kolvo
			from
				LpuDispContractUslugaComplexLink LDCUC with (nolock)
				left join UslugaComplex uc with (nolock) on LDCUC.UslugaComplex_id = uc.UslugaComplex_id
				left join UslugaCategory ucat with (nolock) on ucat.UslugaCategory_id = uc.UslugaCategory_id
			where (1=1)
				and LDCUC.LpuDispContract_id = :LpuDispContract_id
		";
		
		//echo getDebugSql($query, $data);exit;
		$result = $this->db->query($query, $data);
		if(is_object($result)){
			return $result->result('array');
		}else{
			return false;
		}
	}
	
	/**
	 * сохранение "Услуга договора" формы "Договор по сторонним специалистам" (Паспорт МО)
	 */
	function saveServiceContract($data){
		if(empty($data['LpuDispContract_id'])) return false;
		$query = "
			declare
				@LpuDispContractUslugaComplexLink_id bigint,
				@ErrCode int,
				@ErrMessage varchar(4000);
			set @LpuDispContractUslugaComplexLink_id = null;
			exec p_LpuDispContractUslugaComplexLink_ins 
				@LpuDispContractUslugaComplexLink_id = @LpuDispContractUslugaComplexLink_id output, 
				@LpuDispContract_id = :LpuDispContract_id, 
				@UslugaComplex_id = :UslugaComplex_id, 
				@LpuDispContractUslugaComplexLink_Kolvo = :LpuDispContractUslugaComplexLink_Kolvo,
				
				@pmUser_id = :pmUser_id, 
				@Error_Code = @ErrCode output, 
				@Error_Message = @ErrMessage output;
			select @LpuDispContractUslugaComplexLink_id as LpuDispContractUslugaComplexLink_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";
		
		$params = array(
			'LpuDispContract_id'=>$data['LpuDispContract_id'], 
			'UslugaComplex_id'=>$data['UslugaComplex_id'], 
			'LpuDispContractUslugaComplexLink_Kolvo'=>$data['LpuDispContractUslugaComplexLink_Kolvo'],
			'pmUser_id'=>$data['pmUser_id']
		);
		//return getDebugSQL($query, $params); 
		$res = $this->db->query($query, $params);
		
		if (is_object($res))
			return $res->result('array');
		else
			return false;
	}
	
	/**
	 * удаление записей "Услуга договора"
	 */
	function deleteServiceContracts($data)
	{
		if(empty($data['LpuDispContract_id']) || empty($data['pmUser_id'])) {
			return false;
		}
		$queryFields = $this->db->query("
		select
			LpuDispContractUslugaComplexLink_id
		from
			dbo.LpuDispContractUslugaComplexLink 
		WHERE LpuDispContract_id = :LpuDispContract_id", $data);
		$allFields = $queryFields->result_array();
		foreach ($allFields as $fieldVal)
		{
			$fieldVal['pmUser_id'] = $data['pmUser_id'];
			$resDel = $this->deleteServiceContract($fieldVal);
		}
		return true;
	}
	
	/**
	 * удаление записи "Услуга договора"
	 */
	function deleteServiceContract($data){
		if(empty($data['LpuDispContractUslugaComplexLink_id'])) return false;
		$query =
			"
			DECLARE
				@ErrCode bigint,
				@ErrMsg varchar(4000);
				
			EXEC p_LpuDispContractUslugaComplexLink_del
				@LpuDispContractUslugaComplexLink_id = :LpuDispContractUslugaComplexLink_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;
				
			SELECT 
				@ErrCode as Error_Code, @ErrMsg as Error_Msg;";

		$result = $this->queryResult($query, $data);
		return $result;
	}
	
	/**
	 * Добавление домового хозяйства
	 */
	public function saveLpuHouseholdAPI($data) {
		if ( !empty($data['LpuHousehold_id']) ) {
			$action = 'upd';

			$data['Lpu_id'] = $this->getFirstResultFromQuery("select top 1 Lpu_id from fed.v_LpuHousehold with (nolock) where LpuHousehold_id = :LpuHousehold_id", $data, true);

			if ( $data['Lpu_id'] === false ) {
				return array(array('Error_Msg' => 'Ошибка при получении идентификатора МО'));
			}
			else if ( empty($data['Lpu_id']) ) {
				return array(array('Error_Msg' => 'Изменяемая запись отсутствует в БД'));
			}
		}
		else {
			if ( empty($data['Lpu_id']) ) {
				return false;
			}

			$action = 'ins';

			$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_Lpu with (nolock) where Lpu_id = :Lpu_id", $data, true);

			if ( $Lpu_id === false ) {
				return array(array('Error_Msg' => 'Ошибка при проверке идентификатора МО'));
			}
			else if ( empty($Lpu_id) ) {
				return array(array('Error_Msg' => 'Не найден идентификатор МО'));
			}
		}

		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'LpuHousehold_id' => (!empty($data['LpuHousehold_id']) ? $data['LpuHousehold_id'] : null),
			'LpuHousehold_Name' => $data['LpuHousehold_Name'],
			'LpuHousehold_ContactPerson' => $data['LpuHousehold_ContactPerson'],
			'LpuHousehold_ContactPhone' => $data['LpuHousehold_ContactPhone'],
			'LpuHousehold_CadNumber' => $data['LpuHousehold_CadNumber'],
			'LpuHousehold_CoordLat' => $data['LpuHousehold_CoordLat'],
			'LpuHousehold_CoordLon' => $data['LpuHousehold_CoordLon'],
			'PAddress_id' => $data['PAddress_id'],
			'pmUser_id' => $data['pmUser_id']
		);

		return $this->queryResult("
			declare
				@Res bigint,
				@ErrCode bigint,
				@ErrMsg varchar(4000);

			set @Res = :LpuHousehold_id;

			exec fed.p_LpuHousehold_{$action}
				@LpuHousehold_id = @Res output,
				@Lpu_id = :Lpu_id,
				@LpuHousehold_Name = :LpuHousehold_Name,
				@LpuHousehold_ContactPerson = :LpuHousehold_ContactPerson,
				@LpuHousehold_ContactPhone = :LpuHousehold_ContactPhone,
				@LpuHousehold_CadNumber = :LpuHousehold_CadNumber,
				@LpuHousehold_CoordLat = :LpuHousehold_CoordLat,
				@LpuHousehold_CoordLon = :LpuHousehold_CoordLon,
				@PAddress_id = :PAddress_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMsg output;

			select @Res as LpuHousehold_id, @ErrCode as Error_Code, @ErrMsg as Error_Msg;
		", $params);
	}

    /**
     * Удаление домового хозяйства
     */
	public function deleteLpuHouseholdAPI($data) {
		$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from fed.v_LpuHousehold with (nolock) where LpuHousehold_id = :LpuHousehold_id", $data, true);

		if ( $Lpu_id === false ) {
			return array(array('Error_Msg' => 'Ошибка при поиске записи в БД'));
		}
		else if ( empty($Lpu_id) ) {
			return array(array('Error_Msg' => 'Удаляемая запись отсутствует в БД'));
		}
		
		return $this->queryResult("
			declare
				@Error_Code bigint = 0,
				@Error_Message varchar(4000) = '';
			exec fed.p_LpuHousehold_del
				@LpuHousehold_id = :LpuHousehold_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @Error_Code output,
				@Error_Message = @Error_Message output;
			select @Error_Message as Error_Msg;
        ", $data);
	}

    /**
     * Получение списка домовых хозяйств МО
     */
	public function getLpuHouseHoldByMOAPI($data) {
		$Lpu_id = $this->getFirstResultFromQuery("select top 1 Lpu_id from v_Lpu with (nolock) where Lpu_id = :Lpu_id", $data, true);

		if ( $Lpu_id === false ) {
			return array(array('Error_Msg' => 'Ошибка при проверке идентификатора МО'));
		}
		else if ( empty($Lpu_id) ) {
			return array(array('Error_Msg' => 'Не найден идентификатор МО'));
		}

		return $this->queryResult("
			select
				LpuHousehold_id,
				LpuHousehold_Name,
				LpuHousehold_ContactPerson,
				LpuHousehold_ContactPhone,
				LpuHousehold_CadNumber,
				LpuHousehold_CoordLat,
				LpuHousehold_CoordLon,
				PAddress_id
			from fed.v_LpuHousehold with (nolock)
			where Lpu_id = :Lpu_id
        ", $data);
	}
	
	/**
	 * Функция получения данных классификации МИ (форма 30)
	 */
	function loadMedProductClassForm($data){
		$filter = '';
		$order_by = '';
		$params = array();
		$codeArr = array("5117", "5118", "5126", "5302", "5404", "5450", "5460", "5600", "7000");
		if( !empty($data['MedProductClassForm_pid']) ) {
			$filter .= ' and MPCF.MedProductClassForm_pid = :MedProductClassForm_pid';
			$params['MedProductClassForm_pid'] = $data['MedProductClassForm_pid'];
			$order_by = ' ORDER BY MPCF.MedProductClassForm_Code';
		}else{
			$order_by = ' ORDER BY MPCF.MedProductClassForm_Name';
			$filter .= " AND MPCF.MedProductClassForm_Code in ('". implode("', '", $codeArr)."')";
		}

		$query = "
			declare @curdate datetime = dbo.tzGetDate();
			SELECT
				MPCF.MedProductClassForm_id,
				MPCF.MedProductClassForm_Name,
				MPCF.MedProductClassForm_Code
			FROM passport.v_MedProductClassForm MPCF
			WHERE 
				MPCF.MedProductClassForm_begDT <= @curdate AND ISNULL(MPCF.MedProductClassForm_endDT, @curdate) >= @curdate
				{$filter}
				{$order_by}
			";
		//echo getDebugSQL($query, $params); die();
		$result = $this->db->query($query, $params);
		if ( is_object($result) )
		{
		    $medProductClassFormList = $result->result('array');
		    //$medProductClassFormList[] = "&nbsp;";
			return $medProductClassFormList;
		}
		else {
			return false;
		}
	}

	/**
	 * Получение OKATO региона
	 */
	public function getOKATO() {
		return $this->queryResult("select left(KLAdr_Ocatd, 2) as \"OKATO\" from v_KLArea where KLArea_id = :Region_id", array('Region_id' => getRegionNumber()));
	}

	/**
	 * Функция проверяет наличие совпадения ОГРН с ОГРН организаций из справочника организаций
	 *
	 * @param array $data входные данные
	 * @return mixed признак валидаций
	 */
	public function isMatchOGRN($data) {
		$arr['state'] = false;

		$this->load->model('Org_model');
		$org_id = $this->Org_model->getOrgOnLpu($data);

		$Org_Name = $this->dbmodel->getFirstResultFromQuery('
			select top 1 
				Org_Name
			from 
				Org with (nolock)
			where
				Org_OGRN = :Org_OGRN
			and
				Org_id != ISNULL(:Org_id, 0)
		', ['Org_OGRN' => $data['Org_OGRN'], 'Org_id' => $org_id]);
		if (!empty($Org_Name)) {
			$arr['state'] = true;
			$arr['msg'] = 'Указанный ОГРН уже используется для другой организации "' . $Org_Name . '". Укажите корректный ОГРН.';
		}
		return $arr;
	}

	/**
	 *  Функция получения данных о типах медицинских изделий для формы добавления класса медицинского изделия .
	 */
	function loadCardTypeList()
	{
		return $this->queryResult("select CardType_id, CardType_pid from passport.v_CardType with (nolock) where (1=1)");
	}
}

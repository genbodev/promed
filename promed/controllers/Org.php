<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
 * Org - контроллер для получения списка организаций, данных по организации, сохранения, удаления организаций
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      DLO
 * @access       public
 * @copyright    Copyright (c) 2009 Swan Ltd.
 * @author       Petukhov Ivan aka Lich (megatherion@list.ru)
 * @version      12.05.2009
 */

/**
 * @property Org_model orgmodel
 */
class Org extends swController {

	private $moduleMethods = [
		'getOrgList'
	];
	/**
	 *  Конструктор
	 */
    function __construct()  {
        parent::__construct();
        $this->inputRules = array(
			'giveOrgAccess' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'grant',
					'label' => 'Признак доступа в систему',
					'rules' => 'required',
					'type' => 'int'
				)
			),
			'getOrgView' => array(
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор',
					'rules' => '',
					'type' => 'id'
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
				),
				array(
					'field' => 'Name',
					'label' => 'Фильтр по полному наименованию',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Nick',
					'label' => 'Фильтр по краткому наименованию',
					'rules' => '',
					'type' => 'string'
				),
				array(
					'field' => 'Type',
					'label' => 'Фильтр по типу',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'OnlyOrgStac',
					'label' => 'Фильтр по стационарным учреждениям',
					'rules' => '',
					'type' => 'int'
				),
				array(
					'field' => 'mode',
					'label' => 'Режим',
					'rules' => 'trim',
					'type' => 'string'
				),
                array(
                    'field' => 'LpuArr',
                    'label' => 'Доступные МО',
                    'rules' => '',
                    'type'  => 'string'
                )
				
			),
			'loadLpuSetOIDGrid' => array(
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
			'loadLpuWithoutOID' => array(
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
			'setLpuIsFRMO' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'Lpu_isFRMO',
					'label' => 'Флаг ФРМО',
					'rules' => 'required',
					'type' => 'id'
				)
			),
			'setLpuOID' => array(
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => 'required',
					'type' => 'id'
				),
				array(
					'field' => 'PassportToken_tid',
					'label' => 'OID',
					'rules' => '',
					'type' => 'string'
				)
			),
			'setOID' => array(
				array(
					'field' => 'lpus',
					'label' => 'МО в формате JSON',
					'rules' => 'required',
					'type' => 'string'
				)
			),
            'saveLpuEmail' => array(
                array(
                    'field' => 'Lpu_Email',
                    'label' => 'Мыло',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_AmbulanceCount',
                    'label' => 'Число выездных бригад',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'loadOrgHeadGrid' => array(
                array(
                    'field' => 'LpuUnit_id',
                    'label' => 'Идетификатор группы отделений',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field'	=> 'fromMZ',
					'label'	=> 'Запуск из АРМ Минздрава',
					'rules'	=>'',
					'type'	=> 'string',
					'defauld' => '1'
				),
				array(
					'field'	=> 'OrgHead_id',
					'label'	=> 'ИД руководителя',
					'rules'	=>'',
					'type'	=> 'id'
				)
            ),
            'getOrgColoredList' => array(
                array(
                    'field' => 'query',
                    'label' => 'Запрос от комбобокса',
                    'rules' => 'trim',
                    'type'  => 'string'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'trim',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'OrgType',
                    'label' => 'Идентификатор типа организации',
                    'rules' => 'trim',
                    'type'  => 'id'
                ),
                array(
                    'field' => 'needOrgType',
                    'label' => 'Флаг загрузки типов организаций',
                    'rules' => '',
                    'type'  => 'string'
                )
            ),
            'getOrgList' => array(
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
                    'field' => 'OrgType_id',
                    'label' => 'Идентификатор типа организации',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_oid',
                    'label' => 'Идентификатор лпу',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_sid',
                    'label' => 'Идентификатор лпу',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DepartAffilType_id',
                    'label' => 'Ведомственная принадлежность',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'DispClass_id',
                    'label' => 'Тип дд',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Disp_consDate',
                    'label' => 'Дата согласия дд',
                    'rules' => '',
                    'type' => 'date'
                ),
				array(
					'field' => 'activeInDate',
					'label' => 'Дата активности организации (ЛПУ)',
					'rules' => '',
					'type' => 'date'
				),
                array(
                    'field' => 'Org_Name',
                    'label' => 'Наименование организации',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Nick',
                    'label' => 'Краткое наименование организации',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'default' => 'org',
                    'field' => 'OrgType',
                    'label' => 'Тип организации',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
				array(
					'field' => 'OrgServed_Type',
					'label' => 'Тип организации (для обслуживающих)',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'OrgType_Code',
					'label' => 'Код типа организации',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'Org_pid',
					'label' => 'Идентификатор родительской организации',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'Org_IsAccess',
					'label' => 'Признак наличия доступа в систему',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'needOrgType',
					'label' => 'Флаг загрузки типов организаций',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'needOrgOGRN',
					'label' => 'Флаг загрузки кодов ОГРН организаций',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'query',
					'label' => 'Строка контекстного поиска',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'WithOrgStacCode',
					'label' => 'признак поиска организаций с федеральным кодом',
					'rules' => '',
					'type'  => 'string'
				),
				array(
					'field' => 'onlyFromDictionary',
					'label' => 'признак поиска только по справочнику типа организации',
					'rules' => '',
					'type'  => 'checkbox'
				),
				array(
					'field' => 'WithoutOrgEndDate',
					'label' => 'признак поиска организаций с пустой датой окончания',
					'rules' => '',
					'type'  => 'string'
				),
                array(
                    'field' => 'isNotForSystem',
                    'label' => 'Не работает в данной Системе',
                    'rules' => '',
                    'type' => 'id'
                ),
				array(
					'field' => 'AdditionalFeature_id',
					'label' => 'Дополнительный признак',
					'rules' => '',
					'type' => 'id'
				)
            ),
			'getOrgTypeList' => array(
				array(
					'field' => 'Org_IsAccess',
					'label' => 'Признак наличия доступа в систему',
					'rules' => '',
					'type'  => 'id'
				),
				array(
					'field' => 'query',
					'label' => 'Строка контекстного поиска',
					'rules' => '',
					'type'  => 'string'
				)
            ),
            'getOrgFarmacyList' => array(
                array(
                    'field' => 'OrgFarmacy_id',
                    'label' => 'Идентификатор аптеки',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgFarmacy_Name',
                    'label' => 'Наименование аптеки',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgFarmacy_Nick',
                    'label' => 'Краткое наименование аптеки',
                    'rules' => 'trim',
                    'type' => 'string'
                )
            ),
			'getOrgSmoList' => array(
				array(
					'field' => 'KLRgn_id',
					'label' => 'Регион',
					'rules' => 'trim',
					'type' => 'id'
				),
                array(
                    'field' => 'OMSSprTerr_Code',
                    'label' => 'Код территории страхования',
                    'rules' => 'trim',
                    'type' => 'int'
                )
            ),
            'getOrgData' => array (
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'getLpuData' => array (
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'getLpuPassport' => array (
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'addOrgHead' => array(
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Person_id',
                    'label' => 'Идентификатор человека',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgHeadPost_id',
                    'label' => 'Должность',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgHead_Phone',
                    'label' => 'Телефон(ы)',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgHead_Fax',
                    'label' => 'Факс(ы)',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgHead_Email',
                    'label' => 'Мыло',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgHead_Mobile',
                    'label' => 'Номер мобильного телефона',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgHead_CommissNum',
                    'label' => 'Номер приказа о назначении',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgHead_CommissDate',
                    'label' => 'Дата приказа о назначении',
                    'rules' => 'trim',
                    'type' => 'date'
                ),
                array(
                    'field' => 'OrgHead_Address',
                    'label' => 'Адрес',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'default' => 'add',
                    'field' => 'action',
                    'label' => 'Действие',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuUnit_id',
                    'label' => 'Группа отделений',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'editOrgHead' => array(
                array(
                    'field' => 'OrgHead_id',
                    'label' => 'Идентификатор руководства',
                    'rules' => 'required',
                    'type' => 'id'
                )
            ),
            'saveOrgRSchet' => array(
                array(
                    'field' => 'OrgRSchet_id',
                    'label' => 'Идентификатор счета',
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
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgRSchet_Name',
                    'label' => 'Наименование',
                    'rules' => 'trim|required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgBank_id',
                    'label' => 'Банк',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgRSchetType_id',
                    'label' => 'Тип счёта',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Okv_id',
                    'label' => 'Валюта',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgRSchet_RSchet',
                    'label' => 'Номер счета',
                    'rules' => 'trim|required',
                    'type' => 'string'
                ),
                array(
                    'default' => 'add',
                    'field' => 'action',
                    'label' => 'Действие',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
				array(
					'field' => 'OrgRSchet_begDate',
					'label' => 'Дата открытия',
					'rules' => 'required',
					'type' => 'date'
				),
				array(
					'field' => 'OrgRSchet_endDate',
					'label' => 'Дата закрытия',
					'rules' => '',
					'type' => 'date'
				)
            ),
            'saveOrgRSchetKBK' => array(
                array(
                    'field' => 'OrgRSchetKBK_id',
                    'label' => 'Идентификатор КБК счета',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgRSchet_id',
                    'label' => 'Идентификатор счета',
                    'rules' => 'required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgRSchet_KBK',
                    'label' => 'КБК',
                    'rules' => 'required',
                    'type' => 'int'
                )
            ),
            'saveOrg' => array(
                array(
                    'field' => 'Okved_id',
                    'label' => 'Идентификатор ОКВЭД',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Oktmo_id',
                    'label' => 'Идентификатор ОКТМО',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Okopf_id',
                    'label' => 'Идентификатор ОКОПФ',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Okfs_id',
                    'label' => 'Идентификатор ОКФС',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_Code',
                    'label' => 'Код организации',
                    'rules' => 'trim|required',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_INN',
                    'label' => 'ИНН организации',
                    'rules' => 'trim|is_numeric',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_OKATO',
                    'label' => 'ОКАТО организации',
                    'rules' => 'trim|is_numeric',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_OKPO',
                    'label' => 'ОКПО организации',
                    'rules' => 'trim|is_numeric',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Lpu_Nick',
                    'label' => 'Ник ЛПУ',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_KPP',
                    'label' => 'КПП организации',
                    'rules' => 'trim|is_numeric',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Name',
                    'label' => 'Наименование',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Nick',
                    'label' => 'Краткое наименование',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_StickNick',
                    'label' => 'Наименование для ЛВН',
                    'rules' => 'required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Description',
                    'label' => 'Описание',
                    'rules' => '',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_begDate',
                    'label' => 'Дата открытия',
                    'rules' => '',
                    'type' => 'date'
                ),
                array(
                    'field' => 'Org_endDate',
                    'label' => 'Дата закрытия',
                    'rules' => '',
                    'type' => 'date'
                ),
				array(
					'field' => 'isminzdrav',
					'label' => 'Minzdrav',
					'rules' => '',
					'type' => 'string'
				),
                array(
                    'field' => 'Org_rid',
                    'label' => 'Наследователь',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_nid',
                    'label' => 'Правопреемник',
                    'rules' => '',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_ONMSZCode',
                    'label' => 'Код ОНМСЗ',
                    'rules' => '',
                    'type' => 'string'
                ),
				array(
					'field' => 'OrgType_id',
					'label' => 'Тип организации',
					'rules' => '',
					'type'  => 'id'
				),
                array(
                    'field' => 'Org_OGRN',
                    'label' => 'ОГРН организации',
                    'rules' => 'trim|is_numeric',
                    'type' => 'string'
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
                    'field' => 'PPersonSprTerrDop_id',
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
                    'field' => 'KLTown_id',
                    'label' => 'Населенный пункт',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_Phone',
                    'label' => 'Телефон',
                    'length' => 100,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Email',
                    'label' => 'E-mail',
                    'length' => 50,
                    'rules' => 'trim',
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
                    'field' => 'UPersonSprTerrDop_id',
                    'label' => 'Район',
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
                    'default' => 1,
                    'field' => 'check_double_inn_cancel',
                    'label' => 'Принудительное сохранение совпадающего ИНН',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'default' => 1,
                    'field' => 'check_double_ogrn_cancel',
                    'label' => 'Принудительное сохранение совпадающего ОГРН',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgStac_Code',
                    'label' => 'Код стационарного учреждения',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_IsNotForSystem',
                    'label' => 'Не работает в данной Системе',
                    'rules' => '',
                    'type' => 'swcheckbox'
                ),
				array(
					'field' => 'Org_Marking',
					'label' => 'Идентификатор в ИС "Маркировка"',
					'rules' => '',
					'length' => 36,
					'type' => 'string'
				),
				array(
					'field' => 'Org_f003mcod',
					'label' => 'Федеральный реестровый код',
					'rules' => '',
					'type' => 'string'
                )
            ),
            'saveLpu' => array(
                array(
                    'field' => 'Org_Code',
                    'label' => 'Код организации',
                    'rules' => 'required',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Org_id',
                    'label' => 'Идентификатор организации',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_INN',
                    'label' => 'ИНН организации',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Name',
                    'label' => 'Наименование',
                    'rules' => 'trim|required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Nick',
                    'label' => 'Краткое наименование',
                    'rules' => 'trim|required',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Phone',
                    'label' => 'Телефон',
                    'length' => 100,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Org_Email',
                    'label' => 'E-mail',
                    'length' => 50,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'LpuType_id',
                    'label' => 'Тип ЛПУ',
                    'rules' => 'trim|required',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Lpu_RegNomC',
                    'label' => 'Код организации - поле 1',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_RegNomN',
                    'label' => 'Код организации - поле 2',
                    'rules' => 'trim',
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_Ouz',
                    'label' => 'Код на выписку рецептов',
                    'hidden' => true,
                    'type' => 'int'
                ),
                array(
                    'field' => 'Lpu_IsOMS',
                    'label' => 'Работает в ОМС',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'Org_Email',
                    'label' => 'E-mail',
                    'length' => 50,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'default' => 'org',
                    'field' => 'OrgType',
                    'label' => 'Тип организации',
                    'rules' => 'trim|required',
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
                )
            ),
            'saveOrgAnatom' => array(
                array(
                    'field' => 'OrgAnatom_id',
                    'label' => 'Идентификатор патологоанатомического бюро',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'saveOrgDep' => array(
                array(
                    'field' => 'OrgDep_id',
                    'label' => 'Идентификатор организации выдающей документы',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'saveOrgBank' => array(
                array(
                    'field' => 'OrgBank_id',
                    'label' => 'Идентификатор банка',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgBank_KSchet',
                    'label' => 'Кореспонденсткий счёт',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'OrgBank_BIK',
                    'label' => 'БИК',
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'field' => 'Okved_id',
                    'label' => 'ОКВЭД',
                    'rules' => '',
                    'type' => 'id'
                )				
            ),
			'saveOrgSMO' => array(
                array(
                    'field' => 'OrgSMO_id',
                    'label' => 'Идентификатор СМО',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'KLRGNSmo_id',
                    'label' => 'Регион',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
                    'field' => 'OrgSMO_RegNomC',
                    'label' => 'Код 1',
                    'rules' => 'trim',
                    'type' => 'int'				
				),
				array(
                    'field' => 'OrgSMO_RegNomN',
                    'label' => 'Код 2',
                    'rules' => 'trim',
                    'type' => 'int'				
				),
				array(
                    'field' => 'Orgsmo_f002smocod',
                    'label' => 'Федеральный код',
                    'rules' => 'trim',
                    'type' => 'int'				
				),
                array(
                    'default' => 1,
                    'field' => 'OrgSMO_isDMS',
                    'label' => 'Флаг ДМС',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'saveOrgFarmacy' => array(
                array(
                    'field' => 'OrgFarmacy_id',
                    'label' => 'Идентификатор аптеки',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'field' => 'OrgFarmacy_ACode',
                    'label' => 'Код аптеки',
                    'rules' => 'trim|required',
                    'type' => 'int'
                ),
                array(
                    'field' => 'OrgFarmacy_HowGo',
                    'label' => 'Как добраться',
                    'length' => 50,
                    'rules' => 'trim',
                    'type' => 'string'
                ),
                array(
                    'default' => 1,
                    'field' => 'OrgFarmacy_IsEnabled',
                    'label' => 'Открыта/Закрыта',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'default' => 1,
                    'field' => 'OrgFarmacy_IsFedLgot',
                    'label' => 'Фед. льгота',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'default' => 1,
                    'field' => 'OrgFarmacy_IsRegLgot',
                    'label' => 'Рег. льгота',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'default' => 1,
                    'field' => 'OrgFarmacy_IsNozLgot',
                    'label' => '7 нозологий',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
				array(
                    'default' => 1,
                    'field' => 'OrgFarmacy_IsNarko',
                    'label' => 'НС и ПВ',
                    'rules' => 'trim',
                    'type' => 'id'
                ),
                array(
                    'default' => 2,
                    'field' => 'OrgFarmacy_IsFarmacy',
                    'label' => 'Аптека/не аптека',
                    'rules' => 'trim',
                    'type' => 'id'
                )
            ),
            'loadOrgRSchet' => array (
                array(
                    'field' => 'OrgRSchet_id',
                    'label' => 'Идентификатор расчетного счета организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'loadOrgRSchetKBK' => array (
                array(
                    'field' => 'OrgRSchetKBK_id',
                    'label' => 'Идентификатор КБК расчетного счета организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'loadOrgRSchetGrid' => array (
                array(
                    'field' => 'Lpu_id',
                    'label' => 'Идентификатор ЛПУ',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
			'loadOrgRSchetList' => array (
				array(
					'field' => 'Lpu_id',
					'label' => 'Идентификатор МО',
					'rules' => '',
					'type' => 'id'
				),
				array(
					'field' => 'Org_id',
					'label' => 'Идентификатор организации',
					'rules' => '',
					'type' => 'id'
				)
			),
            'loadOrgRSchetKBKGrid' => array (
                array(
                    'field' => 'OrgRSchet_id',
                    'label' => 'Идентификатор счёта',
                    'rules' => '',
                    'type' => 'int'
                )
            ),
            'loadOrgHead' => array (
                array(
                    'field' => 'OrgHead_id',
                    'label' => 'Идентификатор руководства',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'deleteOrgRSchet' => array (
                array(
                    'field' => 'OrgRSchet_id',
                    'label' => 'Идентификатор расчетного счета организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'deleteOrgRSchetKBK' => array (
                array(
                    'field' => 'OrgRSchetKBK_id',
                    'label' => 'Идентификатор КБК расчетного счета организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
            'deleteOrgHead' => array (
                array(
                    'field' => 'OrgHead_id',
                    'label' => 'Идентификатор руководителя организации',
                    'rules' => 'trim|required',
                    'type' => 'id'
                )
            ),
			'getOrgForContragents' => array(
				array('default' => 0, 'field' => 'start', 'label' => 'Начальный номер записи', 'rules' => '', 'type' => 'int'),
				array('default' => 100, 'field' => 'limit', 'label' => 'Количество возвращаемых записей', 'rules' => '', 'type' => 'int'),
				array('field' => 'Name', 'label' => 'Фильтр по полному наименованию', 'rules' => '', 'type' => 'string'),
				array('field' => 'Type', 'label' => 'Фильтр по типу', 'rules' => '', 'type' => 'int'),
				array('field' => 'endDate', 'label' => 'Дата окончания', 'rules' => '', 'type' => 'date')
			),
			'getOrgOGRN' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
			),
			'getLpuOnOrg' => array(
				array('field' => 'Org_id', 'label' => 'Идентификатор организации', 'rules' => '', 'type' => 'id')
 			)
        );
        $this->init();
    }

    /**
    */
	private function init() {
		$curArm = isset($_SESSION['CurArmType']) ? $_SESSION['CurArmType'] : null;
		$method = $this->router->fetch_method();

		if ($this->usePostgreLis && in_array($method, $this->moduleMethods) && in_array($curArm, ['lab', 'reglab', 'pzm'])) {
			$this->load->swapi('lis');
		} else {
			$this->load->database();
		}
	}

	/**
	 * Функция Index
	 */
    function Index() {
        return false;
    }
	
	/**
	 * Выдача или запрет доступа в систему конкретной организации
	 */
	function giveOrgAccess()
	{
		$this->load->model('Org_model', 'dbmodel');
		$this->load->model('User_model', 'User_model');
	
		$data = $this->ProcessInputData('giveOrgAccess', true);
		if ($data === false) { return false; }
		
		if ( !isSuperadmin() ) {
			$this->ReturnError('У вас нет прав на изменение доступа организации в систему!');
			return false;
		}
		
		// проверить заведен ли хотя бы один пользователь в организации, проверяем по кэшу..
		if ($data['grant'] == 1 || $this->User_model->checkExistUserInOrg($data)) {
			$response = $this->dbmodel->giveOrgAccess($data);
			$this->ProcessModelSave($response, true, 'При изменении доступа организации в систему возникли ошибки')->ReturnData();
		} else {
			$this->ReturnData(array('success' => false, 'Error_Code' => 1));
		}
		
		return true;
	}
	
	/**
	 * Получение списка организаций (перенёс из контроллера Spr)
	 */
	function getOrgView()
	{
		$this->load->model('Org_model', 'dbmodel');
		
		$data = $this->ProcessInputData('getOrgView', true);
		if ($data === false) { return false; }

		$response = $this->dbmodel->getOrgView($data);

		$icons = array(
			'bank' => ' <img src="/img/icons/org-bank16.png" alt="Банк" title="Банк" > ',
			'dep' => ' <img src="/img/icons/org-gos16.png" alt="Государственное учреждение" title="Государственное учреждение" > ',
			'smo' => ' <img src="/img/icons/org-strah16.png" alt="СМО" title="СМО" > ',
			'farm' => ' <img src="/img/icons/org-pharm16.png" alt="Аптека" title="Аптека" > ',
			'lpu' => ' <img src="/img/icons/org-lpu16.png" alt="ЛПУ" title="ЛПУ" > '
			
		);
		
		if (is_array($response['data']) && (count($response['data'])>0))
		{
			foreach ($response['data'] as &$row)
			{
				$image = strtr($row['Org_Type'], $icons);
				if ($image == $row['Org_Type']) {
					$row['Org_Type'] = ' <img src="/img/icons/spr-org16.png" alt="" title="" > ';
				} else {
					$row['Org_Type'] = $image;
				}
			}
		}
		
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}
	
    /**
     * Удаление руководителя
     */
    function deleteOrgHead() {

        $this->load->model('Org_model', 'dbmodel');

        $data = $this->ProcessInputData('deleteOrgHead',true);
        if ($data === false) {return false;}

        $response = $this->dbmodel->deleteOrgHead($data);
        $this->ProcessModelSave($response,true,'При удалении руководителя возникли ошибки')->ReturnData();

        return true;
    }

    /**
     * Удаление расчетного счета
     */
    function deleteOrgRSchet() {
        $data = $this->ProcessInputData('deleteOrgRSchet',true);
        if ($data === false) {return false;}
		
		// Проверка используется ли счет в реестрах на реестровой бд
		$dbConnection = getRegistryChecksDBConnection();
		if ( $dbConnection != 'default' ) {
			unset($this->db);
			$this->load->database($dbConnection);
		}
		$this->load->model('Registry_model', 'Reg_model');
		$check = $this->Reg_model->ckeckOrgRSchetOnUsedInRegistry($data);
		if(is_array($check)) {
			$this->ProcessModelSave($check, true)->ReturnData();
			return false;
		}
		
		if ( $dbConnection != 'default' ) {
			unset($this->db);
			$this->load->database();
		}
		$this->load->model('Utils_model', 'umodel');
		
        $response = $this->umodel->ObjectRecordDelete($data, "OrgRSchet", true, $data['OrgRSchet_id'], 'dbo');
		if( isset($response[0]['Error_Code']) && $response[0]['Error_Code']==547 )
			$response[0]['Error_Msg'] = 'Невозможно удалить данный счет, поскольку существует привязка к КБК';
		else
			$response[0]['Error_Msg'] = '';
        $this->ProcessModelSave($response,true,'При удалении счета возникли ошибки')->ReturnData();
        return true;
    }

    /**
     * Получение информации руководстве
     */
    function loadOrgHead() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgHead',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->loadOrgHead($data);
        $this->ProcessModelList($response,true)->ReturnData();
        return true;
    }

    /**
     * Получение информации о счете организации
     */
    function loadOrgRSchet() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgRSchet',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->loadOrgRSchet($data);
        $this->ProcessModelList($response,true)->ReturnData();
        return true;
    }

    /**
     *  Получение информации о КБК счета организации
     */
    function loadOrgRSchetKBK() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgRSchetKBK',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->loadOrgRSchetKBK($data);
        $this->ProcessModelList($response,true)->ReturnData();

        return true;
    }

    /**
     * Получение информации об организации
     */
    function getOrgData() 
	{
        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgData',true);
        if ($data === false) {return false;}

		$response = $this->orgmodel->getOrgData($data);
        $this->ProcessModelList($response,true, true)->ReturnData();
        return true;
    }

    /**
     * Получение информации об организации
     */
    function getLpuData() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getLpuData',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->getLpuData($data);
        $this->ProcessModelList($response,true)->ReturnData();

        return true;
    }

    /**
     * Получение паспорта ЛПУ
     */
    function getLpuPassport() {
        $this->load->model("Org_model", "orgmodel");
       
        $data = $this->ProcessInputData('getLpuPassport',true);
        if ($data === false) {return false;}
        
        $response = $this->orgmodel->getLpuPassport($data);
        $this->ProcessModelList($response,true)->ReturnData();
        return true;
    }

    /**
     * Получение списка руководства
     */
    function loadOrgHeadGrid() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgHeadGrid',true);
        if ($data === false) {return false;}

        $org_head_data = $this->orgmodel->loadOrgHeadGrid($data);
        $this->ProcessModelList($org_head_data,true,true)->ReturnData();
        return true;
    }

    /**
     * Получение списка расчетных счетов ЛПУ
     */
    function loadOrgRSchetGrid() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgRSchetGrid',true);
        if ($data === false) {return false;}

        $org_rschet_data = $this->orgmodel->loadOrgRSchetGrid($data);
        $this->ProcessModelList($org_rschet_data,true,true)->ReturnData();

        return true;
    }


    /**
     * Получение списка расчетных счетов организации
     */
    function loadOrgRSchetList() {
        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgRSchetList', true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->loadOrgRSchetList($data);
        $this->ProcessModelList($response, true, true)->ReturnData();

        return true;
    }


    /**
     *  Получение списка КБК на счет организации
     */
    function loadOrgRSchetKBKGrid() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('loadOrgRSchetKBKGrid',true);
        if ($data === false) {return false;}

        $org_rschet_data = $this->orgmodel->loadOrgRSchetKBKGrid($data);
        $this->ProcessModelList($org_rschet_data,true,true)->ReturnData();
        return true;
    }

    /**
     * Получение списка организаций по запросу в комбобокс
     * Результат расцвечивается
     */
    function getOrgColoredList() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgColoredList',true);
        if ($data === false) {return false;}

        $org_data = $this->orgmodel->getOrgColoredList($data);

        if ( isset($org_data) && is_array($org_data) && count($org_data) > 0 && !empty($data['query'])) {
            foreach ($org_data as &$row) {
                $row['Org_ColoredName'] =
                    @preg_replace('/('.$data['query'].')/i','<span style="color:red">\\1</span>',$row['Org_Name']);
            }
        }

        $this->ReturnData($org_data);
        return true;
    }

	/**
	 * Получаем список ЛПУ
	 */
	public function getAllLpuList() {
		$val = array();
		$this->load->model("Org_model", "orgmodel");
		$response = $this->orgmodel->getAllLpuList();

		foreach ($response as $row) {
			array_walk($row, 'ConvertFromWin1251ToUTF8');
			$val[] = $row;
		}

		Echo '{rows:' . json_encode($val) . '}';
		return true;
	}


    /**
     * Получение списка организаций по заданным фильтрам
     */
    function getOrgList() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgList',true);
        if ($data === false) {return false;}

		$curArm = isset($_SESSION['CurArmType']) ? $_SESSION['CurArmType'] : null;

        switch ($data['OrgType']) {
            case 'anatom':
                $org_data = $this->orgmodel->getOrgAnatomList($data);
                break;

            case 'anatom_old':
                $org_data = $this->orgmodel->getOrgAnatomOldList($data);
                break;

            case 'bank':
                $org_data = $this->orgmodel->getOrgBankList($data);
                break;

            case 'dep':
                $org_data = $this->orgmodel->getOrgDepList($data);
                break;

            case 'farm':
                $org_data = $this->orgmodel->getOrgFarmacyList($data);
                break;

            case 'lpu':
				if ($this->usePostgreLis && in_array($curArm, ['lab', 'reglab', 'pzm'])) {
					$subParams = ['Org_Name', 'Org_Nick'];
					foreach ($subParams as $val) {
						if (!empty($data[$val]))
							//url encoding пробелов, которые пропускаются
							$data[$val] = str_replace(' ', '%20', $data[$val]);
					}
					$res = $this->lis->GET('Org/List', $data);
					if (!$this->isSuccessful($res)) {
						return $res;
					}
					$this->ProcessRestResponse($res, 'list')->ReturnData();
					return true;
				} else {
					$org_data = $this->orgmodel->getLpuList($data);
				}
                break;

            case 'lic':
                $org_data = $this->orgmodel->getOrgLicList($data);
                break;

            case 'orgstac':
            case 'orgstaceducation':
                $org_data = $this->orgmodel->getOrgStacList($data);
                break;

            case 'smo':
                $err = getInputParams($data, $this->inputRules['getOrgSmoList'], false);

                if ( strlen($err) > 0 ) {
                    echo json_return_errors($err);
                    return false;
                }

                $org_data = $this->orgmodel->getOrgSmoList($data);
                break;

            case 'smodms':
                $err = getInputParams($data, $this->inputRules['getOrgSmoList'], false);

                if ( strlen($err) > 0 ) {
                    echo json_return_errors($err);
                    return false;
                }

                $org_data = $this->orgmodel->getOrgSmoDmsList($data);
                break;

            default:
				if ($this->usePostgreLis && in_array($curArm, ['lab', 'reglab', 'pzm'])) {
					if (isset($data['Lpu_oid'])) {
						$data['Org_id'] = $data['Lpu_oid'];
					}
					$res = $this->lis->GET('Org/List', $data);
					if (!$this->isSuccessful($res)) {
						return $res;
					}
					$this->ProcessRestResponse($res, 'list')->ReturnData();
					return true;
				} else {
					$org_data = $this->orgmodel->getOrgList($data);
				}
                break;
        }
        $this->ProcessModelList($org_data, true, true)->ReturnData();

        return true;
    }

    /**
     * Получение списка типов организаций
     */
    function getOrgTypeList() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgTypeList',true);
        if ($data === false) {return false;}

        $org_data = $this->orgmodel->getOrgTypeList($data);
        $this->ProcessModelList($org_data, true, true)->ReturnData();

        return true;
    }


    /**
     * Получение списка аптек по заданным фильтрам
     */
    function getOrgFarmacyList() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('getOrgFarmacyList',true);
        if ($data === false) {return false;}

        $org_data = $this->orgmodel->getOrgFarmacyNewList($data);
        $this->ProcessModelList($org_data,true)->ReturnData();

        return true;
    }

    /**
     * Получение списка аптек по заданным фильтрам
     */
    function loadOrgFarmacyList() {

        $this->load->model("Org_model", "dbmodel");

        $data = array();

        $response = $this->dbmodel->loadOrgFarmacyList($data);
        $this->ProcessModelList($response,true)->ReturnData();

        return true;
    }

	/**
	 * Получение списка МО для проставления OID
	 */
	function loadLpuSetOIDGrid() {
		$this->load->model("Org_model", "dbmodel");
		$data = $this->ProcessInputData('loadLpuSetOIDGrid',true);

		$response = $this->dbmodel->loadLpuSetOIDGrid($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Cохранение значения флага ФРМО
	 */
	function setLpuIsFRMO() {
		$this->load->model("Org_model", "dbmodel");
		$data = $this->ProcessInputData('setLpuIsFRMO',true);

		$response = $this->dbmodel->setLpuIsFRMO($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

	/**
	 * Cохранение значения OID
	 */
	function setLpuOID() {
		$this->load->model("Org_model", "dbmodel");
		$data = $this->ProcessInputData('setLpuOID',true);

		$response = $this->dbmodel->setLpuOID($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}

    /**
     * Получение списка МО у которых не проставлен ОИД
     */
    function loadLpuWithoutOID() {

        $this->load->model("Org_model", "dbmodel");
        $data = $this->ProcessInputData('loadLpuWithoutOID',true);

        $response = $this->dbmodel->loadLpuWithoutOID($data);
		$this->ProcessModelMultiList($response, true, true)->ReturnData();
        return true;
    }

    /**
     * Проставление ОИД для МО
     */
    function setOID() {

        $this->load->model("Org_model", "dbmodel");
        $data = $this->ProcessInputData('setOID',true);

		$data['lpus'] = json_decode($data['lpus']);
		if (!is_array($data['lpus']) || count($data['lpus']) == 0){
			return false;
		}

		foreach ($data['lpus'] as $key => $value){
			$data['Lpu_id'] = $value;
			$response = $this->dbmodel->setOID($data);
		}

		$response[0]['count_org'] = count($data['lpus']);

        $this->ProcessModelSave($response,true)->ReturnData();
        return true;
    }

    /**
     * Сохранение руководства
     */
    function saveOrgHead() {

        $this->load->model("Org_model", "orgmodel");
        $data = $this->ProcessInputData('addOrgHead',true);
        if ($data === false) {return false;}

        if ( $data["action"] == 'edit' )
        {
            $data2 = $this->ProcessInputData('editOrgHead',false);
            if ($data2 === false) {return false;}
            $data = array_merge($data2, $data);
        }
        $response = $this->orgmodel->saveOrgHead($data);
        $this->ProcessModelSave($response,true)->ReturnData();

        return true;
    }

    /**
     * Сохранение мыла
     */
    function saveLpuEmail() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('saveLpuEmail',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->saveLpuEmail($data);
        $this->ProcessModelSave($response,true)->ReturnData();

        return true;
    }

    /**
     * Сохранение расчетного счета организации
     */
    function saveOrgRSchet() 
	{
        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('saveOrgRSchet',true);
        if ($data === false) { return false; }

        $response = $this->orgmodel->saveOrgRSchet($data);
        $this->ProcessModelSave($response,true)->ReturnData();

        return true;
    }

    /**
     * Сохранение КБК расчетного счета организации
     */
    function saveOrgRSchetKBK() {

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('saveOrgRSchetKBK',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->saveOrgRSchetKBK($data);
        $this->ProcessModelSave($response,true)->ReturnData();
        return true;
    }

    /**
     * Сохранение организации
     */
    function saveOrg() {
		
        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('saveOrg',true);
        if ($data === false) {return false;}

        if ($result = $this->orgmodel->checkOrgHasMIID($data, 'Редактирование')) {
            $this->ReturnData($result);
            return false;
        }

		$data['OrgType_SysNick'] = $this->orgmodel->getOrgTypeSysNick($data['OrgType_id']);
		
		if (!empty($data['Org_rid'])) {
			if (!empty($data['Org_endDate'])) {
				// проверить что дата открытия у Org_rid больше чем дата закрытия Org_endDate (refs #10122)
				if (!$this->orgmodel->checkOrgRidBegDate($data)) {
					$this->ReturnError('Дата открытия организации наследователя меньше чем дата закрытия сохраняемой организации.');
					return false;
				}
			} else {
				$data['Org_rid'] = null;
			}
		}
		//echo $data['OrgType_SysNick'];
        switch ( $data['OrgType_SysNick'] ) {
            case 'anatom':
                // проверка аттрибутов по OrgAnatom
				$data2 = $this->ProcessInputData('saveOrgAnatom',false,true,false,false,false);
				if ($data2 === false) {return false;}
				$data = array_merge($data, $data2);
			break;

            case 'dep':
                // проверка аттрибутов по OrgDep
				$data2 = $this->ProcessInputData('saveOrgDep',false,true,false,false,false);
				if ($data2 === false) {return false;}
				$data = array_merge($data, $data2);
			break;

            case 'farm':
                if ( !(havingGroup('OrgAdmin') && !empty($data['Org_id']) && !empty($data['session']['org_id']) && $data['session']['org_id'] == $data['Org_id']) && !isSuperadmin() && !isFarmacyadmin() && $data["isminzdrav"]==false ) {
                    $this->ReturnError('У вас нет прав на редактирование данных аптек!');
                    return false;
                }
				
				if (empty($data['Org_OGRN'])) {
                    $this->ReturnError('Поле "ОГРН" обязательно для заполнения.');
                    return false;
				}
				
				$data2 = $this->ProcessInputData('saveOrgFarmacy',false,true,false,false,false);
				if ($data2 === false) {return false;}
				$data = array_merge($data, $data2);
			break;

            case 'bank':
				$data2 = $this->ProcessInputData('saveOrgBank',false,true,false,false,false);
				if ($data2 === false) {return false;}
				$data = array_merge($data, $data2);
			break;
			
            case 'smo':
                if ( !(havingGroup('OrgAdmin') && !empty($data['Org_id']) && !empty($data['session']['org_id']) && $data['session']['org_id'] == $data['Org_id']) && !isSuperadmin() ) {
                    $this->ReturnError('Добавление и редактирование данных страховых медицинских организаций недоступно пользователям МО!');
                    return false;
                }
				
				$data2 = $this->ProcessInputData('saveOrgSMO',false,true,false,false,false);
				if ($data2 === false) {return false;}
				$data = array_merge($data, $data2);
			break;
        }
		
        $response = $this->orgmodel->saveOrg($data);
        $this->ProcessModelSave($response,true)->ReturnData();

        return true;
    }

    /**
     * Сохранение ЛПУ
     */
    function saveLpu() {


        if ( ($_SESSION['region']['nick']!='ufa') && !isSuperAdmin() )
        {
            echo json_encode(array('success'=>false, 'Error_Code' => 666 , 'Error_Msg' => toUTF('Вам не доступен этот функционал!')));
            return false;
        }

        $this->load->model("Org_model", "orgmodel");

        $data = $this->ProcessInputData('saveLpu',true);
        if ($data === false) {return false;}

        $response = $this->orgmodel->saveLpu($data);
        $this->ProcessModelSave($response,true)->ReturnData();

        return true;
    }

    /**
     * Получение максимального кода существующей организации
     * для автозаполнения поля кода новой организации
     */
    function getMaxOrgCode() {

        $this->load->model("Org_model", "orgmodel");

        $data = array();

        $org_data = $this->orgmodel->getMaxOrgCode($data);
        $this->ProcessModelSave($org_data,true)->ReturnData();

        return true;
    }

	/**
	 * Получение списка организаций
	 */
	function getOrgForContragents() {
		$this->load->model('Org_model', 'dbmodel');
		$data = $this->ProcessInputData('getOrgForContragents', true);
		$response = $this->dbmodel->getOrgForContragents($data);
		$this->ProcessModelList($response, true, true)->ReturnData();
		return true;
	}

	/**
	 * Получение ОГРН организации
	 */
	function getOrgOGRN() {
		$this->load->model('Org_model', 'dbmodel');
		$data = $this->ProcessInputData('getOrgOGRN', true);
		$response = $this->dbmodel->getOrgOGRN($data);
		$this->ProcessModelSave($response)->ReturnData();
		return true;
	}
	
	/**
	 * Получение Идентификатора ЛПУ по идентификатору организации
	 */
	function getLpuOnOrg() {
		$this->load->model('Org_model', 'dbmodel');
		$data = $this->ProcessInputData('getLpuOnOrg', true);
		$response = $this->dbmodel->getLpuOnOrg($data);
		return $this->ReturnData($response);
	}	
}

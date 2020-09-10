<?php
defined('BASEPATH') or die ('No direct script access allowed');
/**
 * SearchBase - базовый контроллер для работы с формами поиска
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      10.04.2018
 *
 **/
class SearchBase extends swController {
	/**
	*  Описание правил для входящих параметров
	*  @var array
	*/
	public $inputRules = array(
		'searchData' => array(
			// Тип формы поиска
			array(
				'field' => 'SearchFormType',
				'label' => 'Тип формы поиска',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'getCountOnly',
				'label' => 'Посчитать только каунт',
				'rules' => '',
				'type' => 'id'
			),
			// Тип поиска человека
			array(
				'default' => 1,
				'field' => 'PersonPeriodicType_id',
				'label' => 'Тип поиска человека',
				'rules' => '',
				'type' => 'id'
			),
			// Пациент
			array(
				'default' => 0,
				'field' => 'OMSSprTerr_id',
				'label' => 'Территория страхования ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'OrgSmo_id',
				'label' => 'СМО, выдавшая полис ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'soc_card_id',
				'label' => 'Идентификатор социальной карты',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Person_Birthday',
				'label' => 'Дата рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Person_BirthdayYear',
				'label' => 'Год рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'int'
			),
			array(
				'field' => 'Person_Birthday_Range',
				'label' => 'Диапазон дат рождения ("Пациент")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Person_Code',
				'label' => 'Единый номер полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
            array(
                'field' => 'Person_Phone',
                'label' => 'Телефон',
                'rules' => '',
                'type' => 'string'
            ),
			array(
				'field' => 'Person_Firname',
				'label' => 'Имя ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Secname',
				'label' => 'Отчество ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Person_Surname',
				'label' => 'Фамилия ("Пациент")',
				'rules' => 'ban_percent|trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'PersonAge',
				'label' => 'Возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_Max',
				'label' => 'Максимальный возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonAge_Min',
				'label' => 'Минимальный возраст человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear',
				'label' => 'Год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear_Max',
				'label' => 'Максимальный год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayYear_Min',
				'label' => 'Минимальный год рождения человека ("Пациент")',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'PersonBirthdayMonth',
				'label' => 'Месяц рождения',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_Code',
				'label' => 'Номер карты ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Num',
				'label' => 'Номер полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Polis_Ser',
				'label' => 'Серия полиса ("Пациент")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'PolisType_id',
				'label' => 'Тип полиса ("Пациент")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoPolis',
				'label' => 'Фильтр поиска пациентов без полиса',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'Person_NoOrgSMO',
				'label' => 'Фильтр поиска пациентов без СМО',
				'rules' => '',
				'type' => 'checkbox'
			),
			array(
				'field' => 'HasPolis_Code',
				'label' => 'Наличие полиса',
				'rules' => '',
				'type' 	=> 'id'
			),
			array(
				'field' => 'IsBDZ',
				'label' => 'БДЗ',
				'rules' => '',
				'type' 	=> 'id'
			),
			array(
				'field' => 'TFOMSIdent',
				'label' => 'Идентификатор с ТФОМС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PolisClosed',
				'label' => 'Данные о закрытии полиса',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PolisClosed_Date_Range',
				'label' => 'Диапазон дат закрытия полиса',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// Пациент (доп.)
			array(
				'field' => 'Document_Num',
				'label' => 'Номер документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'field' => 'Document_Ser',
				'label' => 'Серия документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'string'
			),
			array(
				'default' => 0,
				'field' => 'DocumentType_id',
				'label' => 'Тип документа ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Org_id',
				'label' => 'Место работы ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'OrgDep_id',
				'label' => 'Организация, выдавшая документ ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_IsBDZ',
				'label' => 'БДЗ ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_isIdentified',
				'label' => 'Идентифицирован ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SnilsExistence',
				'label' => 'Наличие СНИЛС',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_Snils',
				'label' => 'СНИЛС ("Пациент (доп.)")',
				'rules' => 'trim',
				'type' => 'snils'
			),
			array(
				'field' => 'Person_Inn',
				'label' => 'ИНН пациента',
				'rules' => 'trim|is_numeric',
				'type' => 'string'
			),
			array(
				'field' => 'Person_IsDisp',
				'label' => 'Диспансерный учет ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Post_id',
				'label' => 'Должность ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'Sex_id',
				'label' => 'Пол ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'SocStatus_id',
				'label' => 'Социальный статус ("Пациент (доп.)")',
				'rules' => '',
				'type' => 'id'
			),
			// Прикрепление
			array(
				'default' => 0,
				'field' => 'AttachLpu_id',
				'label' => 'ЛПУ прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'LpuRegion_id',
				'label' => 'Участок ("Прикрепление")',
				'rules' => '',
				'type' => 'int'
			),
            array(
                'field' => 'LpuRegion_Fapid',
                'label' => 'Участок ФАП ("Прикрепление")',
                'rules' => '',
                'type' => 'int'
            ),
			array(
				'field' => 'LpuAttachType_id',
				'label' => 'Тип прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'LpuRegionType_id',
				'label' => 'Тип участка ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'MedPersonal_id',
				'label' => 'Врач участка ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCard_begDate',
				'label' => 'Дата прикрепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonCard_begDate_Range',
				'label' => 'Диапазон дат прикрепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_endDate',
				'label' => 'Дата открепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'PersonCard_endDate_Range',
				'label' => 'Диапазон дат открепления ("Прикрепление")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'PersonCard_IsAttachCondit',
				'label' => 'Условное прикрепление ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonCardAttach',
				'label' => 'Заявление',
				'rules' => '',
				'type'  => 'id'
			),
			array(
				'field' => 'PersonCard_IsDms',
				'label' => 'ДМС прикрепление ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 1,
				'field' => 'PersonCardStateType_id',
				'label' => 'Актуальность прикрепления ("Прикрепление")',
				'rules' => '',
				'type' => 'id'
			),
			// Адрес
			array(
				'field' => 'Address_House',
				'label' => 'Номер дома ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Address_Corpus',
				'label' => 'Корпус ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'field' => 'Address_Street',
				'label' => 'Улица ("Адрес")',
				'rules' => 'trim',
				'type' => 'russtring'
			),
			array(
				'default' => 0,
				'field' => 'AddressStateType_id',
				'label' => 'Тип адреса ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLAreaType_id',
				'label' => 'Тип населенного пункта ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCity_id',
				'label' => 'Город ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLCountry_id',
				'label' => 'Страна ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLRgn_id',
				'label' => 'Регион ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLStreet_id',
				'label' => 'Улица ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLSubRgn_id',
				'label' => 'Район ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'KLTown_id',
				'label' => 'Населенный пункт ("Адрес")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Person_NoAddress',
				'label' => 'Без адреса ("Адрес")',
				'rules' => '',
				'type' => 'string'
			),
			// Льгота
			array(
				'field' => 'Privilege_begDate',
				'label' => 'Дата начала действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Privilege_begDate_Range',
				'label' => 'Диапазон дат начала действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'field' => 'Privilege_endDate',
				'label' => 'Дата окончания действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'Privilege_endDate_Range',
				'label' => 'Диапазон дат окончания действия льготы ("Льгота")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'defaul' => 1,
				'field' => 'PrivilegeStateType_id',
				'label' => 'Актуальность льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'defaul' => 0,
				'field' => 'WithDrugComplexMnn',
				'label' => 'Только с комплексным МНН',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'field' => 'Lpu_prid',
				'label' => 'ЛПУ добавления льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PrivilegeType_id',
				'label' => 'Категория льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'SubCategoryPrivType_id',
				'label' => 'Подгатегория ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'Refuse_id',
				'label' => 'Отказ от льготы ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RefuseNextYear_id',
				'label' => 'Отказ на следующий год ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'RegisterSelector_id',
				'label' => 'Регистр льготников ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'PersonPrivilege_deleted',
				'label' => 'Льгота удалена ("Льгота")',
				'rules' => '',
				'type' => 'id'
			),
			// Пользователь
			array(
				'field' => 'InsDate',
				'label' => 'Дата добавления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'InsDate_Range',
				'label' => 'Диапазон дат добавления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			array(
				'default' => 0,
				'field' => 'pmUser_insID',
				'label' => 'Пользователь, добавивший запись ("Пользователь")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 0,
				'field' => 'pmUser_updID',
				'label' => 'Пользователь, обновивший запись ("Пользователь")',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'field' => 'UpdDate',
				'label' => 'Дата обновления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'date'
			),
			array(
				'field' => 'UpdDate_Range',
				'label' => 'Диапазон дат обновления ("Пользователь")',
				'rules' => 'trim',
				'type' => 'daterange'
			),
			// Параметры страничного вывода
			array(
				'default' => 0,
				'field' => 'start',
				'label' => 'Номер стартовой записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 0,
				'field' => 'archiveStart',
				'label' => 'Номер стартовой архивной записи',
				'rules' => '',
				'type' => 'int'
			),
			array(
				'default' => 50,
				'field' => 'limit',
				'label' => 'Количество записей',
				'rules' => '',
				'type' => 'id'
			),
			array(
				'default' => 'off',
				'field' => 'onlySQL',
				'label' => 'Вывести SQL-запрос',
				'rules' => 'ban_percent', // TO-DO: добавить в правила обработки права пользователя, под которыми можно использовать этот параметр (?)
				'type' => 'string'
			)
		)
	);

	/**
	 * Название модели для поиска
	 */
	protected $model_name = 'SearchBase_model';

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Поиск
	 */
	function searchData()
	{
		$data = $this->ProcessInputData('searchData', true, true);
		if ($data === false) { return false; }

		// подключаем БД
		$this->loadDataBase($data);
		$this->load->model($this->model_name, 'dbmodel');

		$response = $this->dbmodel->searchData($data);
		if (!empty($data['getCountOnly'])) {
			$this->ProcessModelSave($response, true)->ReturnData();
		} else {
			$this->ProcessModelMultiList($response, true, true)->ReturnData();
		}
		return true;
	}

	/**
	 * Подключение БД
	 */
	function loadDataBase($data) {
		//определяем какую базу использовать для поиска
		$archive_database_enable = $this->config->item('archive_database_enable');
		$search_database_only = $this->config->item('search_database_only');
		$database_type = null;

		$bdSearchRegistry = false;
		switch(true){
			case !empty($data['EvnVizitPL_isPaid']):
			case !empty($data['EvnPLDispOrp_isPaid']):
			case !empty($data['EvnPLDispProf_isPaid']):
			case !empty($data['EvnPLDispScreen_isPaid']):
			case !empty($data['EvnPLDispScreenChild_isPaid']):
			case !empty($data['EvnPLDispDop13Second_isPaid']):
			case !empty($data['EvnPLDispDop13_isPaid']):
			case !empty($data['EvnVizitPLStom_isPaid']):
			case !empty($data['CmpCallCard_isPaid']):
			case !empty($data['EvnSection_isPaid']):
			case !empty($data['EvnPLDispTeenInspection_isPaid']):
				$bdSearchRegistry = true;
				break;
		}

		// если по архивным - используем архивную БД
		if (!empty($archive_database_enable) && !empty($_REQUEST['useArchive'])) {
			$database_type = 'archive';
		} else if($bdSearchRegistry && (!defined('ENABLE_REGISTRY_DB_SEARCH') || ENABLE_REGISTRY_DB_SEARCH === true)){
			$database_type = 'registry';
		} else {
			if (
			(
				// https://redmine.swan.perm.ru/issues/28999
				in_array($data['SearchFormType'], array('HepatitisRegistry', 'OnkoRegistry', 'NarkoRegistry', 'TubRegistry', 'DiabetesRegistry',
						'OrphanRegistry', 'CrazyRegistry', 'VenerRegistry', 'HIVRegistry','ACSRegistry','FmbaRegistry', 'EvnInfectNotify', 'EvnNotifyHepatitis', 'EvnOnkoNotify',
						'EvnNotifyOrphan', 'EvnNotifyCrazy', 'EvnNotifyTub', 'EvnNotifyNarko', 'EvnNotifyVener','EvnNotifyNephro', 'EvnNotifyProf', 'IBSRegistry', 'ProfRegistry', 'PalliatRegistry', 'NephroRegistry', 'EndoRegistry',
						'EvnNotifyRegister', 'PersonRegisterBase','ReabRegistry','BskRegistry','IPRARegistry', 'EvnUslugaPar', 'PersonDopDispPlan', 'EvnPLDispMigrant'
					)
				)
				|| (
					// Только для поиска ТАП, посещений, КВС и рецептов, и параклинических услуг (#6439) и ДД и ДД ДС (#6768)
					in_array($data['SearchFormType'], array('PersonCallCenter', 'EvnPL', 'EvnVizitPL', 'EvnPLStom', 'EvnVizitPLStom', 'EvnPS', 'EvnSection',
							'EvnRecept', 'EvnReceptGeneral', 'EvnPLDispDop', 'EvnPLDispDop13', 'EvnPLDispDop13Sec', 'EvnPLDispProf', 'EvnPLDispScreen', 'EvnPLDispScreenChild', 'EvnPLDispOrp', 'EvnPLDispOrpOld', 'EvnPLDispOrpSec',
							'EvnPLDispTeenInspectionPeriod', 'EvnPLDispTeenInspectionProf', 'EvnPLDispTeenInspectionPred', 'PersonDopDisp', 'PersonDispOrp',
							'PersonDispOrpPeriod', 'PersonDispOrpProf', 'PersonDispOrpPred', 'PersonDispOrpOld', 'PersonPrivilege', 'PersonCardStateDetail')
					)
					// Только если поиск по активной периодике
					&& $data['PersonPeriodicType_id'] == 1
					&& (!empty($data['Person_Surname']) || !empty($data['PersonCard_Code']) ||
						!empty($data['EvnPS_NumCard']) || !empty($data['EvnPL_NumCard']) ||
						(!empty($data['EvnRecept_Ser']) && !empty($data['EvnRecept_Num'])) ||
						(!empty($data['Polis_Ser']) && !empty($data['Polis_Num'])) ||
						!empty($data['Person_Code']) || !empty($data['Person_Snils']) ||
						(!empty($data['Document_Ser']) && !empty($data['Document_Num'])) ||
						(!empty($data['LpuSection_cid']) && !empty($data['EvnSection_disDate_Range'])) // журнал выбывших (refs #26313)
					)
				)
				// если это форма поточного ввода в регистр по ДД
				|| ($data['SearchFormType'] == 'PersonDopDisp' && isset($data['dop_disp_reg_beg_date']))
				|| ( //Или АРМ регистратора поликлиники
					$data['SearchFormType'] == 'WorkPlacePolkaReg' && (
						!empty($data['Person_Surname']) || !empty($data['Person_Firname']) || !empty($data['Person_Secname']) ||
						isset($data['Person_Birthday']) || !empty($data['PersonCard_Code']) || !empty($data['Polis_Num'])
					)
				)
				// https://redmine.swan.perm.ru/issues/17135
				// Добавил CmpCloseCard
				// @task https://redmine.swan.perm.ru/issues/107112
				|| (in_array($data['SearchFormType'], array('CmpCallCard','CmpCloseCard')) && !empty($data['CmpCallCard_InRegistry']))
			)
			) {
				if ($search_database_only) {
					// Всегда используем базу поиска 
					$database_type = 'search';
				} else {
					// Используем базу по умолчанию
					$database_type = 'default';
				}
			} else { // Используем специально обученную базу для поиска
				$database_type = 'search';
			}
		}

		// при поиске рецептов в АРМ провизора, всегда используем базу по умолчанию
		// https://redmine.swan.perm.ru/issues/110630
		if ($data['SearchFormType'] == 'EvnRecept' && !empty($data['DistributionPoint'])) {
			$database_type = 'default';
		}

		// подключаем базу
		if(!empty($database_type)) {
			if ($database_type != 'default') {
				$this->load->database($database_type, false);
			} else {
				$this->load->database(); // используем базу по умолчанию
			}
		}

		//Выставляем таймауты для выполнения запросов, пока вручную
		$this->db->query_timeout = 600;
	}
}
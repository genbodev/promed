<?php
/**
* Options_model - глобальные опции из таблицы
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
*/

class Options_model extends swModel {
	// Значение для выбора "Нет"
	const VALIDATION_CONTROL_NO = 'no';
	// Значение для выбора "Предупреждение"
	const VALIDATION_CONTROL_WARNING = 'warning';
	// Значение для выбора "Запрет"
	const VALIDATION_CONTROL_DENY = 'deny';

	public $allowed_medpersonal_list = array(
		'["full_list", "Все"]',
		'["all", "Все врачи и средний мед. персонал"]',
		'["doct_feld_ak", "Врачи, фельдшеры, акушеры"]',
		'["doct_only", "Только врачи"]'
	);

	private $double_vizit_control_array = array(
		'["1", "Отключена"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);

	private $arm_vizit_create_array = array(
		'["1", "Вручную"]',
		'["2", "Автоматически"]'
	);

	private $use_glossary_tag_array = array(
		'["1", "Показывать все"]',
		'["2", "Искать слова и фразы в зависимости от контекста"]'
	);

	private $arm_evn_xml_copy_array = array(
		'["1", "Не копировать осмотр из предыдущего посещения"]',
		'["2", "Копировать осмотр из предыдущего посещения"]'
	);

	private $barcode_format_array = array(
		'["128", "code 128"]',
		'["39", "code 39"]'
	);

	private $menu_types =array(
		'["simple", "Обычное меню"]'
		//,'["ribbon", "Лента"]'
	);

	private $allowed_usluga_list =array(
		 '["all", "Все"]'
		,'["lpu", "ЛПУ"]'
		,'["lpubuilding", "Подразделения"]'
		,'["lpuunit", "Группы отделений"]'
		,'["lpusection", "Отделения"]'
	);

	private $doc_signtype_list =array(
		'["cryptopro", "КриптоПро"]'
		,'["vipnet", "ViPNet PKI Client (Web Unit)"]'
		,'["authapi", "AuthApi"]'
		,'["authapitomee", "AuthApi (TomEE)"]'
	);

	private $doc_readcardtype_list =array(
		'["authapi", "AuthApi"]'
		,'["authapitomee", "AuthApi (TomEE)"]'
	);

	private $paper_format_array = array(
		'["1", "А4"]'
	,'["2", "А5"]'
	);

	private $paper_orientation_array = array(
		'["1", "Альбом"]'
	,'["2", "Портрет"]'
	);

	private $font_size_array = array(
		'["6", "6"]'
	,'["8", "8"]'
	,'["10", "10"]'
	,'["12", "12"]'
	,'["14", "14"]'
	);

	private $recept_print_format_array = array(
		'["1", "На двух листах формата А4"]',
		'["2", "На трех листах формата А5"]',
		'["3", "На одном листе формата А4"]'
	);

	private $recept_copies_count_array = array(
		'["1", "2"]',
		'["2", "3"]'
	);

	private $blank_form_creation_method_array = array(
		'["1", "в типографии по заказу"]',
		'["2", "из информационной системы с генерацией номеров"]'
	);

	private $evnps_print_format_array = array(
		'["1", "Печать на А4"]',
		'["2", "Печать на А3"]'
	);

	private $registry_evn_sort_order_array = array(
		'["1", "По дате выписки"]',
		'["2", "По сумме (по убыванию)"]',
	);

	private $recept_print_extension_array = array(
		'["1", "PDF (c оборотной стороной)"]',
		'["2", "PDF (без оборотной стороны)"]'//,
		//'["3", "HTML"]'
	);

	private $cost_print_extension_array = array(
		'["1", "PDF"]',
		'["2", "XLS"]',
		'["3", "HTML"]'
	);

	private $register_f01_extension_array = array(
		'["1", "PDF"]',
		'["2", "HTML"]',
		'["3", "DOC"]'
	);
	private $register_f02_extension_array = array(
		'["1", "PDF"]',
		'["2", "HTML"]',
		'["3", "DOC"]'
	);
	private $register_f03_extension_array = array(
		'["1", "PDF"]',
		'["2", "HTML"]',
		'["3", "DOC"]'
	);
	private $home_vizit_journal_print_extension_array = array(
		'["1", "PDF"]',
		'["2", "DOC"]',
		'["3", "HTML"]'
	);

	private $evnxml_print_type_array = array(
		'["1", "PDF"]',
		'["2", "HTML"]'
	);

	private $file_format_type_array = array( //https://redmine.swan.perm.ru/issues/54256
		'["1", "DOC"]',
		'["2", "ODT"]'
	);
	
	private $stac_schedule_time_binding_array = array(
		'["1", "с привязкой ко времени"]',
		'["2", "без привязки ко времени"]'
	);
	
	private $stac_schedule_priority_duration_array = array(
		'["1", "По максимальному значению стандарта лечения"]',
		'["2", "По среднему значению стандарта лечения"]',
		'["3", "По минимальному значению стандарта лечения"]',
		'["4", "По средней продолжительности лечения за год (профиль койки)"]',
		'["5", "По средней продолжительности лечения за год (Диагноз)"]'
	);
    /*
     * Настройки для Башкирии
     * Использование в лабаратории принтера Zebra Для печати штрихкодов
     * https://90.150.189.84/issues/90247 
	 * Открыли для всех регионов
	 * https://redmine.swan.perm.ru//issues/109286
     */
    

    /**
     * Метод печати Zebra
     */
    private $barcode_print_method = array(
    	'["1", "JS Print Setup"]',
    	'["2", "PDF"]',
    	'["3", "JAVA applet"]'
    );

    private $barcode_list_print_method = [
    	'["1", "XLS"]',
		'["2", "HTML"]',
		'["3", "PDF"]'
	];

	private $direction_print_form = [
		'["1", "Обычное направление"]',
		'["2", "Направление в ЦКДЛ"]'
	];

	/**
	 * Принтер для печати этикеток (ЛИС)
	 */
	private $barcode_printer = array();

    /**
     * Размер этикеток Zebra 
     */
    private $barcode_size_array = array(
        '["3050", "30x60"]',
        '["2540", "25x40"]',
        '["2030", "20x30"]',
        '["2040", "20x40"]'
    );

    /**
     * Количество для печати
     */
    private $ZebraPrintCount = array (
    	'["1", "1"]',
    	'["2", "2"]',
    	'["3", "3"]'
    );

    /**
     * фамилию пациента и инициалы
     */
    private $ZebraFIO = array(
        '["1", "Нет"]',
        '["2", "Да"]'        
    );    
    /**
     * номер пробы тот что штрих код
     */
    private $ZebraSampleNumber = array(
        '["1", "Нет"]',
        '["2", "Да"]'        
    );    
    /*
     * наименование отделения направления
     */
    private $ZebraLpuBuldingName = array(
        '["1", "Нет"]',
        '["2", "Да"]'        
    );
    /**
     * наименование службы где выполняется 
     */    
    private $ZebraServicesName = array(
        '["1", "Нет"]',
        '["2", "Да"]'        
    );
    /*
     * end #90247
     */

	/**
	 * Принтер Zebra
	 */
	private $band_printer = array(
	);

	/**
	 * Модель принтера
	 */
	private $band_printer_model = array(
		'["1", "HC100"]',
		'["2", "ZD410"]'
	);


	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Получение глобальных настроек из базы
	 * @param $data
	 * @param string $option
	 * @return array
	 */
	function getOptionsGlobals($data,$option="")
	{
		$this->load->database();
		$default_options = array(
			'globals' => array(
				'enable_action_reference_by_admref_group' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kareliya') ? true : false,
				'contact_info' => '',
				'normativ_fed_lgot' => 480,
				'normativ_reg_lgot' => 150,
				'koef_fed_lgot' => 1,
				'koef_reg_lgot' => 0.5,
				'drug_spr_using' => 'dbo',
				'person_privilege_add_source' => (isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('msk')) ? 1 : 2),
				'person_privilege_add_request_postmoderation' => true,
				'vzn_privilege_diag_available_checking' => (isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('msk'))),
				'social_privilege_document_available_checking' => (isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('msk'))),
				'check_excess_sum_fed' => true,
				'check_priemdiag_allow' => true,
				'enable_fss_send_diag' => false,

				// Если Пермь или Хакасия, то значение 2 иначе 1
				'rules_filling_doctors_workrelease' => ((isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('perm', 'khak')))? 2:1),

				'disp_control' => 1,
				'eps_control' => 1,
				'es_iscardshock_control' => 1,
				'euo_ballonbeg_control' => 1,
				'euo_ckvend_control' => 1,
				'check_excess_sum_reg' => true,
				'check_registry_exists_errors' => true,
				'registry_mz_approve_lpu' => false,
				'registry_disable_edit_inreg' => 2,
				'registry_disable_edit_paid' => 2,
				'check_registry_access' => false,
				'enable_registry_auto_identify' => false,
				'is_remove_drug' => false, // Признак удаления медикаментов при удалении человека
				'dlo_logistics_system' => 'level2',
				'select_drug_from_list' => 'allocation',
				'recept_drug_ostat_viewing' => true,
				'recept_drug_ostat_control' => true,
				'recept_empty_drug_ostat_allow' => true,
				'recept_by_farmacy_drug_ostat' => false,
				'recept_electronic_allow' => false,
				'recept_farmacy_type' => 'all',
				'recept_by_ras_drug_ostat' => false,
				'use_numerator_for_recept' => 2,
				'use_external_service_for_recept_num' => 0,
				'recept_diag_control' => 1,
				'enable_semiautomatic_identification' => true,
				'semiautomatic_identification_timeout' => 10,
				'identification_actual_date' => date('d.m.Y'),
				'ident_login' => '',
				'ident_password' => '',
				'manual_identification_timeout' => 10,
				'message_time_limit' => 5,
				'evndriection_from_journal' => false,
				'setOIDForNewLpu' => false,
				'is_create_drugrequest' => true, // признак доступности последнего периода для ЛПУ
				//,'medsvid_num' => 0
				'farmacy_checking_expiration_date' => true, // контроль ЛС по сроку годности
				'farmacy_remainig_exp_date_less_2_years' => 70, // остаточный срок годности на момент поставки не менее (в процентах), для ЛС со сроком годности до 2-х лет включительно
				'farmacy_remainig_exp_date_more_2_years' => 50, //остаточный срок годности на момент поставки не менее (в процентах), для ЛС со сроком годности более 2-х лет
				'lis_address' => 'http://office.roslabs.ru:9089/phox/',
				'lis_server' => 'office.roslabs.ru',
				'lis_port' => 9089,
				'lis_path' => '/phox/',
				'lis_version' => '3.8',
				'lis_buildnumber' => '38479',
				//'lis_login' => 'svan',
				//'lis_password' => '123',
				//'lis_clientid' => '41757468656E746963414D442D30303630304631322D39453938323230332D3137384246424646,56525455414C202D2035303031323233,5669727475616C2048442D312E312E30',
				'lis_export_auto' => false,
				'is_distributors_in_system' => true,
				'is_farmacy_in_system' => true,
				'is_registry_kept' => true,
				'new_day_time' => '17:00',
				'close_next_day_record_time' => '17:00',
				'promed_new_day_time' => '17:00',
				'promed_close_next_day_record_time' => '17:00',
				'promed_waiting_period_polka' => null,
				'promed_waiting_period_stac' => null,
				'portal_record_day_count' => 14,
				'queue_max_accept_time' => 24,
				'queue_max_cancel_count' => 3,
				'fillScheduleMaxDays' => 7,
				'pol_record_day_count' => 14,
				'pol_record_day_count_reg' => 14,
				'pol_record_day_count_cc' => 14,
				'pol_record_day_count_own' => 14,
				'pol_record_day_count_other' => 14,
				'stac_record_day_count' => 21,
				'stac_record_day_count_reg' => 21,
				'stac_record_day_count_own' => 21,
				'stac_record_day_count_other' => 21,
				'lpu_cancel_stick_access' => ((isset($_SESSION['region']['nick']) && in_array($_SESSION['region']['nick'], array('perm', 'khak')))? 1:2),
				'medservice_record_day_count' => 14,
				'medservice_record_day_count_reg' => 14,
				'medservice_record_day_count_own' => 14,
				'medservice_record_day_count_other' => 14,
				'absence_count_to_ban' => 0,
				'correct_data_snils_not_empty' => false,
				'correct_data_snils_empty_for_baby' => false,
				'correct_data_snils_check_copy' => false,
				'mvd_org' => null,
				'mvd_org_schet' => null,
				'check_htm_dates' => false,
				'onkoctrlAccessAllLpu' => '0', // (Tagir) признак доступа МО к данным журнала анкетирования онко любой МО
				'consoleLogEnable' => '0', // (Tagir) признак вывода сообщений на экран консоли 1-Вкл/0-выкл
				'vacSprAccesFull' => '0', // (Tagir) признак доступа пользователя на редактирование справочников вакцинации
				'check_attach_allow' => false,
				'allow_edit_attach_date' => false,
				'request_personcard_correction_email' => true,
				'request_personcard_correction_message' => true,
				'inform_lpu_personcard_attach_email' => true,
				'inform_lpu_personcard_attach_email_with_xml' => true,
				'inform_person_personcard_attach_sms' => true,
				'inform_person_personcard_attach_email' => true,
				'inform_smo_personcard_attach_email' => true,
				'request_person_phone_verification' => false,
				'send_onkoctrl_msg' => false,
				'notify_on_upcoming_hosp_lpu' => null,
				'notify_on_upcoming_hosp_by_sms' => false,
				'notify_on_upcoming_hosp_by_email' => false,
				'notify_on_upcoming_disp_visits' => false,
				'person_card_log_email_list' => '',
				'vizit_direction_control' => 2,
				'vizit_kvs_control' => 2,
				'vizit_intersection_control' => 2,
				'kvs_intersection_control' => 2,
				'vizit_direction_control_paytype' => false,
				'vizit_kvs_control_paytype' => false,
				'vizit_intersection_control_paytype' => false,
				'kvs_intersection_control_paytype' => false,
				'is_need_tfoms_export' => false,
				'tfoms_export_time' => '',
				'password_expirationperiod' => '',
				'password_tempexpirationperiod' => 5,
				'password_daystowarn' => '',
				'password_minlength' => 6,
				'password_mindifference' => 1,
				'count_check_passwords' => '',
				'check_passwords_all' => false,
				'check_fail_login' => false,
				'block_time_fail_login' => 10,
				'count_bad_fail_login' => 3,
				'check_user_activity' => false,
				'password_haslowercase' => true,
				'password_hasuppercase' => false,
				'password_hasnumber' => false,
				'password_hasspec' => false,
				'misrb_transfer_type' => 0,
				'misrb_transfer_time' => '00:00',
				'misrb_transfer_day' => 7,
				'allowed_disp_med_staff_fact_group' => 2,
				'accept_tfoms_answer' => '',
				'evn_recept_level_of_control' => 1,
				'llo_price_edit_enabled' => false,
				'enp_validation_control' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kareliya') ? self::VALIDATION_CONTROL_NO : self::VALIDATION_CONTROL_DENY,
				'audioRecordTimelimit' => 3,
				'evndirection_check_profile' => 1,
				'ais_reporting_period' => 1,
				'disallow_recording_for_elapsed_time' => false,
				'disallow_tt_actions_for_elapsed_time' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'perm') ? true : false,
				'disallow_canceling_el_dir_for_elapsed_time' => false,
				'allow_canceling_without_el_dir_for_past_days' => false,
				'grant_individual_add_to_wait_list' => false,
				'limit_record_patients_with_closed_polis' => false,
				'dont_display_dummy_staff' => false,
				'smp_default_lpu_building_112' => false,
				'smp_default_lpu_import_git' => false,
				'smp_default_system112' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'astra') ? 3 : 2,
				'smp_is_all_lpubuilding_with112' => 2,
				'smp_allow_transfer_of_calls_to_another_MO' => false,
				'smp_show_expert_tab_in_headdocwp' => false,
				'smp_show_112_indispstation' => false,
				'day_start_call_time' => '00:00',
				'smp_call_time_format' => (isset($_SESSION['region']['nick']) && ($_SESSION['region']['nick'] == 'perm' || $_SESSION['region']['nick'] == 'ufa')) ? 2 : 1,
				'limit_days_after_death_to_create_call' => 0,
				'nmp_monday_beg_time' => '',
				'nmp_monday_end_time' => '',
				'nmp_tuesday_beg_time' => '',
				'nmp_tuesday_end_time' => '',
				'nmp_wednesday_beg_time' => '',
				'nmp_wednesday_end_time' => '',
				'nmp_thursday_beg_time' => '',
				'nmp_thursday_end_time' => '',
				'nmp_friday_beg_time' => '',
				'nmp_friday_end_time' => '',
				'nmp_saturday_beg_time' => '',
				'nmp_saturday_end_time' => '',
				'nmp_sunday_beg_time' => '',
				'nmp_sunday_end_time' => '',
				'nmp_edit_work_time' => false,
				'use_depersonalized_expertise' => false,
				'use_esia_only' => false,
				'check_count_parallel_sessions' => false,
				'count_parallel_sessions' => 1,
				'allow_fio_search' => false,
				'check_implementFLK' => false,
				'recept_data_acc_method' => 1,
				'citizenship_control' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'ekb') ? 1 : 3,
				'snils_control' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kz') ? 1 : 3,
				'snils_double_control' => (isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kz') ? 1 : 3,
				'inn_correctness_control' => 1,
				'check_fullpregnancyanketa_allow' => '',
				'check_menstrdatepregnancyanketa_allow' => '',
				'evn_direction_cancel_right_mo_where_created' => $this->getRegionNumber() == 59 ? 2 : 1,
				'evn_direction_cancel_right_mo_where_adressed' => $this->getRegionNumber() == 59 ? 2 : 1,
				'ais_reporting_period25_9y' => 1,
				'exceptionprofiles' => [97, 57, 58, 42, 68, 3, 136, 85, 89, 171]
			)
		);

		if ($option!="" && !array_key_exists($option, $default_options['globals']))
		{
			die("Настройка ".$option." не обнаружена!");
		}

		if (!isset($data['session']['login']))
		{
			if ($option!="")
				return $default_options['globals'][$option];
			else
				return $default_options;
		}
		if ($option!="")
			$where = "and DataStorage_Name = '".$option."'";
		else
			$where = "";
		$sql = "
			select
				*
			from 
				DataStorage (nolock)
			where Lpu_id is null {$where}
		";

		$this->load->library('swCache', array('use' => 'memcache'), 'swcache');
		$cacheQueryKey = md5($option);
		$cacheObject = 'DataStorage_' . $cacheQueryKey;

		// Читаем из кэша
		if ($rs = $this->swcache->get($cacheObject)) {
			// Прочитали из кэша
		} else {
			$result = $this->db->query($sql);
			if (is_object($result)) {
				$rs = $result->result('array');
				$this->swcache->set($cacheObject, $rs, array('ttl' => 3600)); // кэшируем настройки каждый час
			} else {
				if ($option != "")
					return $default_options['globals'][$option];
				else
					return $default_options;
			}
		}
		
		$options = ['globals' => []];

		if (is_array($rs) && count($rs) > 0) {
			foreach ($rs as $rows) {
				$options['globals'][$rows['DataStorage_Name']] = $rows['DataStorage_Value'];
			}
		}

		$options['globals'] = array_merge($default_options['globals'],$options['globals']);

		if (isset($_SESSION['ArmMenuTitle'])) {
			$options['globals']['ArmMenuTitle'] = $_SESSION['ArmMenuTitle'];
		}

		if (!empty($option)) {
			return $options['globals'][$option];
		} else {
			return $options;
		}
	}

	/**
	 * setPMGenValue
	 */
	function setPMGenValue($data, $obj = '', $value = NULL) {
		if ( empty($obj) || empty($value) || empty($data['session']['lpu_id']) ) {
			return false;
		}

		$query = "
			declare @ObjID bigint;
			exec xp_SetpmID 
				@ObjectName = :ObjectName, 
				@Lpu_id = :Lpu_id,
				@NewObjectID = :NewObjectID,
				@ObjectID = @ObjID output;
			select @ObjID as ObjID;
		";

		$result = $this->db->query($query, array(
			'ObjectName' => $obj,
			'Lpu_id' => $data['session']['lpu_id'],
			'NewObjectID' => $value
		));

		if ( !is_object($result) ) {
			return false;
		}
		else {
			return true;
		}
	}

	/**
	 * getPMGenValue
	 */
	function getPMGenValue($data, $obj = '') {
		if ( empty($obj) ) {
			return false;
		}

		$obj_value = 1;
		$gentable = "pmGen_".$data['session']['lpu_id'];
		$query = "
		if (Object_Id('{$gentable}') is null) begin
				SELECT top 1
					pmGen_Value
				FROM
					pmGen with (nolock)
				WHERE
					pmGen_ObjectName = :pmGen_ObjectName
					and Lpu_id = :Lpu_id
				ORDER by
					pmGen_ObjectValue desc
			end
		else 
			begin
				SELECT top 1
					pmGen_Value
				FROM
					{$gentable} with (nolock)
				WHERE
					pmGen_ObjectName = :pmGen_ObjectName
					and Lpu_id = :Lpu_id
				ORDER by
					pmGen_ObjectValue desc
			end
		";

		$result = $this->db->query($query, array('Lpu_id' => $data['session']['lpu_id'], 'pmGen_ObjectName' => $obj));

		if ( is_object($result) ) {
			$resp_arr = $result->result('array');

			if ( isset($resp_arr[0]) ) {
				$obj_value = $resp_arr[0]['pmGen_Value'];
			}
			else {
				$obj_value = 1;
			}
		}
		else {
			$obj_value = 1;
		}

		return $obj_value;
	}

	/**
	 * Получение признака авторизации только через ЕСИА
	 */
	function checkEsiaOnly() {
		$resp = $this->queryResult("
			select top 1
				DataStorage_id
			from
			    v_DataStorage (nolock)
			where
				DataStorage_Name = 'use_esia_only'
				and DataStorage_Value = '1'
		");

		return !empty($resp[0]['DataStorage_id']);
	}

	/**
	 * Сохранение общих настроек
	 */
	function saveOptionsGlobals($data)
	{
		//$this->load->helper('Date');
		if (!empty($data['session']['pmuser_id']))
			$pmUser_id = $data['session']['pmuser_id'];
		else
			$pmUser_id = 0;
		foreach ($data as $key=>$value)
		{
			if ( !is_array($value) ) {
				$sql = "
				Declare @DataStorage_id bigint;
				Declare @Error_Code int;
				Declare @Error_Message varchar(4000);
				Set @DataStorage_id = Null;
				Set @Error_Code = 0;
				Set @Error_Message = '';
				exec p_DataStorage_set @DataStorage_id=@DataStorage_id output, @DataStorage_Name = '".$key."', @DataStorage_Value='".$value."', @pmUser_id=".$pmUser_id.", @Error_Code = @Error_Code output, @Error_Message = @Error_Message output;
				select @DataStorage_id as DataStorage_id, @Error_Code as Error_Code, @Error_Message as Error_Message;";
				$result = $this->db->query($sql);
				if (!is_object($result))
				{
					return false;
				}
			}
		}

		//кеширование при сохранении
		$sql = "
			select
				*
			from
				DataStorage (nolock)
			where Lpu_id is null
		";

		$result = $this->db->query($sql);
		if (is_object($result)) {
			$rs = $result->result('array');

			$cacheObject = 'DataStorage_' . md5('');
			$this->load->library('swCache', array('use' => 'memcache'));
			$this->swcache->set($cacheObject, $rs);
		}


		return true;
	}

	/**
	 * Получение версии справочников
	 */
	function getLocalDBVersion()
	{
		$sql = "
			Select MAX(LocalDBVersion_Ver) as LocalDBVersion from stg.LocalDBVersion (nolock)";
		$result = $this->db->query($sql);
		if (is_object($result))
		{
			$rs = $result->result('array');
			return $rs[0]['LocalDBVersion'];
		}
		else
		{
			return 0;
		}
	}
	
	/**
	 * Получение номера региона БД
	 */
	function getDBRegionNumber()
	{
		return $this->getFirstResultFromQuery("select dbo.getRegion() as region");
	}

	/**
	 * Возвращает номер региона, nick и название.
	 */
	function getRegion()
	{
		$config = & get_config();
		$region = $this->getRegionNumber();
		if (!isset($config['regions'][$region])) {
			DieWithError('Регион не определен!');
		}
		$config['regions'][$region]['number'] = $region;
		return $config['regions'][$region];
	}

	/**
	 * Возвращает номер региона
	 */
	function getRegionNumber()
	{
		$config = &get_config();
		$nick = getRegionNick();
		foreach ($config['regions'] as $number => $region) {
			if ($nick == $region['nick']) {
				return $number;
			}
		}

		return 0;
	}

	/**
	 * Возвращает одно или несколько значений настроек по заданным фильтрам
	 * Переданный массив может выглядеть как
	 * Lpu_id => ЛПУ для которого выбираются значения
	 * DataStorage_Name => поле для которого выбирается конкретное значение
	 * DataStorageGroup_SysNick => группа
	 * dates => период в котором происходит выбор значений
	 * dates => DataStorage_begDT => начало периода
	 * dates => DataStorage_endDT => конец периода
	 * @param $data
	 * @param $session
	 * @return bool|mixed
	 */
	function getDataStorageValues($data, $session)
	{
		$filter = "(0=0)";
		$params = array();

		if ((isset($data['Lpu_id'])) && $data['Lpu_id'] > 0 && ($data['Lpu_id'] == $session['lpu_id'])) {
			$filter .= " and Lpu_id=:Lpu_id";
			$params['Lpu_id'] = $data['Lpu_id'];
		} else if ((isset($data['Org_id'])) && ($data['Org_id'] == $session['org_id'])) {
			$filter .= " and Org_id=:Org_id";
			$params['Org_id'] = $data['Org_id'];
		}

		if ((isset($data['DataStorage_Name'])) && (!empty($data['DataStorage_Name'])))
		{
			$filter .= " and DataStorage_Name=:DataStorage_Name";
			$params['DataStorage_Name'] = $data['DataStorage_Name'];
		}
		if ((isset($data['DataStorageGroup_SysNick'])) && (!empty($data['DataStorageGroup_SysNick'])))
		{
			$filter .= " and DataStorageGroup.DataStorageGroup_SysNick=:DataStorageGroup_SysNick";
			$params['DataStorageGroup_SysNick'] = $data['DataStorageGroup_SysNick'];
		}

		if ((isset($data['dates'])) && (count($data['dates'])>0))
		{
			if ((isset($data['dates']['DataStorage_begDT'])) && (!empty($data['dates']['DataStorage_begDT'])))
			{
				$filter .= " and (DataStorage_begDT>=cast(:DataStorage_begDT as datetime) or DataStorage_begDT is null)";
				$params['DataStorage_begDT'] = $data['dates']['DataStorage_begDT'];
			}
			if ((isset($data['dates']['DataStorage_endDT'])) && (!empty($data['dates']['DataStorage_endDT'])))
			{
				$filter .= " and (DataStorage_endDT<=cast(:DataStorage_endDT as datetime) or DataStorage_endDT is null)";
				$params['DataStorage_endDT'] = $data['dates']['DataStorage_endDT'];
			}
		}

		$sql = "
			with list as (
				SELECT *
				FROM v_DataStorage DataStorage (nolock)
				WHERE {$filter}
			)
			select
				DataStorage.DataStorage_id,
				DataStorageGroup.DataStorageGroup_SysNick,
				DataStorage.DataStorage_Name,
				DataStorage.DataStorage_Value
			from (
					select distinct DataStorage_Name from list
				) t
				outer apply(
					select top 1 *
					from list where DataStorage_Name = t.DataStorage_Name
					order by case when Org_id is null then 1 else 2 end
				) DataStorage
				left join DataStorageGroup (nolock) on DataStorageGroup.DataStorageGroup_id = DataStorage.DataStorageGroup_id
		";
		/*
		echo getDebugSql($sql, $params);
		exit;
		*/
		$result = $this->db->query($sql, $params);
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
	 * Сохранение настроек
	 */
	function setDataStorageValues($data, $session)
	{
		$params = array();
		$result_data = array();
		$filter = "(0=0)";
		$filter .= " and Lpu_id=:Lpu_id";
		if ((isset($data['Lpu_id'])) && ($data['Lpu_id']=$session['lpu_id']))
		{
			$params['Lpu_id'] = $data['Lpu_id'];
		}

		if (count($data)>0)
		{
			for ($i=0; $i < count($data); $i++)
			{
				if ($data[$i])
				if (!isset($data[$i]['DataStorage_id']))
				{
					$data[$i]['DataStorage_id'] = null;
				}
				if (!isset($data[$i]['pmUser_id']))
				{
					$data[$i]['pmUser_id'] = $session['pmuser_id'];
				}
				$params = $data[$i];
				if (isset($data[$i]['DataStorage_Name']))
				{
					$query = "
						declare
							@Error_Code bigint,
							@Error_Message varchar(4000),
							@DataStorage_id bigint = :DataStorage_id
						exec p_DataStorage_set
							";
					foreach($data[$i] as $k=>&$v)
					{
						$query .= "@{$k}=:{$k}, ";
					}
					$query .= "
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output;
						select @DataStorage_id as DataStorage_id, @Error_Code as Error_Code, @Error_Message as Error_Msg;
					";
					/*
					echo getDebugSql($query, $params);
					exit;
					*/
					$result = $this->db->query($query, $params);
					if (is_object($result))
					{
						$result_data[$i] = $result->result('array');
					}
					else
					{
						$result_data[$i] = null;
					}
				}
			}
		}
		return $result_data;
	}

	/**
	 * Загрузка настроек
	 */
	function getDataStorageOptions($data)
	{
        if (empty($data['session'])) {
            $session_data = getSessionParams();
            $session = $session_data['session'];
        } else {
            $session = $data['session'];
            unset($data['session']);
        }
		$data['Lpu_id'] = $session['lpu_id'];
		$data['Org_id'] = $session['org_id'];
		$data['dates'] = array();
		$data['dates']['DataStorage_begDT'] = date('Y-m-d');
		$data['dates']['DataStorage_endDT'] = null;
		$response = $this->getDataStorageValues($data, $session);
		$result = array();
		if (is_array($response) && count($response) > 0)
		{
			foreach ($response as $row)
			{
				if ($row['DataStorageGroup_SysNick'] == null)
				{
					$result[$row['DataStorage_Name']] = $row['DataStorage_Value'];
				}
				else
				{
					if (!isset($result[$row['DataStorageGroup_SysNick']]))
					{
						$result[$row['DataStorageGroup_SysNick']] = array();
					}
					$result[$row['DataStorageGroup_SysNick']][$row['DataStorage_Name']] = $row['DataStorage_Value'];
				}
			}
		}

		return $result;
	}

	/**
	 * Получение всех настроек
	 */
	function getOptionsAll($data)
	{
		$db_options = array();
		$db_lpu_options = array();
		$all_options = array();
		// общие настройки для всех
		$db_options = $this->getOptionsGlobals($data);
		// настройки для ЛПУ
		$db_lpu_options  = $this->getDataStorageOptions($data);
		$db_options = array_merge_recursive_distinct($db_options, $db_lpu_options);
		// настройки из LDAP + общие
		$all_options = getBaseOptions($db_options);
		return $all_options;
	}

	/**
	 * Получение списка МО с запретом формирования реестров
	 */
	function loadCheckRegistryAccessGrid($data)
	{
		$params = array();
		$query = "
			select
				DS.DataStorage_id,
				L.Lpu_id,
				L.Lpu_Nick
			from v_DataStorage DS with(nolock)
				inner join v_Lpu L with(nolock) on L.Lpu_id = DS.Lpu_id
			where
				DS.DataStorage_Name like 'check_registry_access_lpu'
			order by DS.DataStorage_id
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return array('data' => $result->result('array'));
	}

	/**
	 * Сохранение запрета формирования реестров для МО
	 */
	function saveCheckRegistryAccess($data) {
		$params = $data;

		$query = "
			select count(DataStorage_id) as Count
			from v_DataStorage with(nolock)
			where Lpu_id = :Lpu_id
			and DataStorage_Name like 'check_registry_access_lpu'
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');
		if ($resp[0]['Count'] > 0) {
			return array(array('Error_Msg' => 'Для выбранного МО уже запрещено формирование реестров'));
		}

		$query = "
			declare
				@DataStorage_id bigint,
				@ErrCode bigint,
				@ErrMessage varchar(4000);
			exec p_DataStorage_ins
				@DataStorage_id = @DataStorage_id output,
				@DataStorage_Name = 'check_registry_access_lpu',
				@DataStorage_Value = 1,
				@Lpu_id = :Lpu_id,
				@pmUser_id = :pmUser_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @DataStorage_id as DataStorage_id, @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Удаление запрета формирования реестров для МО
	 */
	function deleteCheckRegistryAccess($data) {
		$params = $data;

		$query = "
			declare
				@ErrCode bigint,
				@ErrMessage varchar(4000);
			exec p_DataStorage_del
				@DataStorage_id = :DataStorage_id,
				@Error_Code = @ErrCode output,
				@Error_Message = @ErrMessage output;
			select @ErrCode as Error_Code, @ErrMessage as Error_Msg;
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		return $result->result('array');
	}

	/**
	 * Проверка запрета на формирование реестров
	 */
	function checkRegistryAccess($data) {
		$params = array('Lpu_id' => $data['Lpu_id']);
		$query = "
			select
				DS.DataStorage_Value
			from v_DataStorage DS with(nolock)
			where DS.DataStorage_Name in('check_registry_access','check_registry_access_lpu')
			and (DS.Lpu_id is null or DS.Lpu_id = :Lpu_id)
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array('Error_Msg' => 'Ошибка при проверке доступности формирования реестров');
		}
		$resp = $result->result('array');
		$access = true;
		foreach($resp as $item) {
			if ($item['DataStorage_Value']==1) {
				$access = false;
			}
		}
		return array('access' => $access);
	}

	/**
	 * Загрузка списка для настройки автоматического влючения в регистры
	 */
	function loadPersonRegisterAutoIncludeGrid($data) {
		$params = array();

		$query = "
			select
				DS.DataStorage_id,
				DS.DataStorage_Name,
				DS.DataStorage_Value
			from v_DataStorage DS with(nolock)
			where DS.DataStorage_Name like 'register_%_auto_include'
		";

		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return false;
		}
		$resp = $result->result('array');
		$settings = array();
		foreach($resp as $item) {
			$key = $item['DataStorage_Name'];
			$settings[$key] = $item;
		}

		$register_list = array(
			'orphan' => 'Орфанное',
			'crazy' => 'Психиатрия',
			'narko' => 'Наркология',
			'tub' => 'Туберкулез',
			'vener' => 'Венерология',
			'hiv' => 'ВИЧ',
			'hepa' => 'Гепатит'
		);

		$response = array();
		foreach($register_list as $sysnick => $name) {
			$key = "register_{$sysnick}_auto_include";
			$response[] = array(
				'DataStorage_id' => isset($settings[$key]) ? $settings[$key]['DataStorage_id'] : null,
				'DataStorage_Value' => isset($settings[$key]) ? $settings[$key]['DataStorage_Value'] : 0,
				'DataStorage_Name' => $key,
				'PersonRegisterType_SysNick' => $sysnick,
				'PersonRegisterType_Name' => $name
			);
		}

		return array('data' => $response);
	}
		
	/**
	 * Получить список типов отделений мест работы пользователя
	 */
	function getLpuUnitTypes($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['session']['medpersonal_id']
		);
		$query = "
			select distinct lut.LpuUnitType_SysNick
			from
				v_MedStaffFact msf with (nolock)
				left join v_LpuUnit lu with (nolock) on lu.LpuUnit_id = msf.LpuUnit_id
				left join v_LpuUnitType lut with (nolock) on lut.LpuUnitType_id=lu.LpuUnitType_id
			where msf.MedStaffFact_disDate is null 
				and lut.LpuUnitType_SysNick is not null 
				and msf.MedPersonal_id=:MedPersonal_id
				and lu.Lpu_id = :Lpu_id";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array();
		}
		$types = array();
		foreach($result->result('array') as $row) {
			$types[] = $row['LpuUnitType_SysNick'];
		}
		return $types;
	}
	
	/**
	 * Получить список типов служб пользователя
	 */
	function getMedServiceTypes($data) {
		$params = array(
			'Lpu_id' => $data['Lpu_id'],
			'MedPersonal_id' => $data['session']['medpersonal_id']
		);
		$query = "
			select distinct MST.MedServiceType_SysNick from MedServiceMedPersonal MSP 
				inner join MedService MS on MS.MedService_id=MSP.MedService_id and MS.Lpu_id=:Lpu_id
				inner join MedServiceType MST on MST.MedServiceType_id=MS.MedServiceType_id
			where MSP.MedPersonal_id=:MedPersonal_id and MSP.MedServiceMedPersonal_endDT is null
		";
		$result = $this->db->query($query, $params);
		if (!is_object($result)) {
			return array();
		}
		$types = array();
		foreach($result->result('array') as $row) {
			$types[] = $row['MedServiceType_SysNick'];
		}
		return $types;
	}

	/**
	 * Проверка запрета на формирование реестров
	 */
	function getQueueOptions($data) {

		$params = array('Lpu_id' => $data['Lpu_id']);

		$query = "
		
			select
				DS.DataStorage_Name,
				DS.DataStorage_Value
			from v_DataStorage DS with(nolock)
			where DS.DataStorage_Name in('grant_individual_add_to_wait_list','queue_max_cancel_count', 'queue_max_accept_time')
				and DS.Lpu_id is null
					
			union all
		
			select
				DS.DataStorage_Name,
				DS.DataStorage_Value
			from v_DataStorage DS with(nolock)
			where DS.DataStorage_Name in('allow_queue','allow_queue_auto')
				and DS.Lpu_id = :Lpu_id			
		";

		$raw_options = $this->queryResult($query, $params);
		$queue_options = array();

		if (!empty($raw_options)) {
			foreach ($raw_options as $option) {
				$queue_options[$option['DataStorage_Name']] = $option['DataStorage_Value'];
			}
		}

		return $queue_options;
	}

	/**
	 * Получение настроек по умолчанию
	 */
	function getDefaultOptions($data) {
		if ( havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('FarmacyAdmin') || havingGroup('FarmacyNetAdmin') )
		{
			$admin_field_disabled = false;
		} else {
			$admin_field_disabled = true;
		}

		$this->load->model("Options_model", "opmodel");
		
		$LpuUnitTypes = $this->getLpuUnitTypes($data);
		$MedServiceTypes = $this->getMedServiceTypes($data);
		
		$isLpu = false;
		if (!empty($data['Lpu_id'])) {
			$isLpu = true;
		}

		$homevizitLabelTimeStart = toUtf('Время начала');
		$homevizitLabelTimeStartWidth = 84;
		$homevizitLabelTimeStartStyle = 'margin-left: 10px;';
		$homevizitLabelTimeEnd = toUtf('Время окончания');
		$homevizitLabelTimeEndWidth = 110;
		$homevizitLabelTimeEndStyle = 'margin-left: 24px;';
		$homevizitTimeFieldWidth = 60;
		$homevizitLabelDayWidth = 120;
		$admin_field_disabled = false;

		$languages = array();
		// Языки заполняются из конфига, если есть
		$lng = $this->config->item('languages');
		if (isset($lng) && is_array($lng)) {
			foreach ($lng as $k=>$row) {
				$languages[] = '["'.$k.'", "'.$row.'"]';
			}
		}

		$haveARMadminCod = haveArmType('superadmin');
		$haveArmAdminLpu = haveArmType('lpuadmin');

		$default_options = array(
			'polka' => array(
				array(
					'title' => toUtf('Проверки талона амбулаторного пациента'),
					'xtype' => 'fieldset',
					'labelWidth' => 200,
					'hidden' => $admin_field_disabled,
					'items' => array(
						array(
							'boxLabel' => toUtf('Сравнение даты рождения пациента и даты поликлинического обследования'),
							'checked' => true,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'name' => 'check_person_birthday',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						/*array(
							'boxLabel' => toUtf('Проверка на повторные посещения по одному профилю'),
							'checked' => true,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'name' => 'double_vizit_control',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),*/
						array(
							'boxLabel' => toUtf('Контроль впервые выявленных заболеваний'),
							'checked' => true,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'hidden' => true,
							'name' => 'first_detected_diag_control',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Обязательность заполнения поля "вид травмы"'),
							'checked' => false,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'hidden' => true,
							'name' => 'prehosp_trauma_control',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Запрет ввода результата лечения для незаконченного случая'),
							'checked' => true,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'name' => 'is_finish_result_block',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'allowBlank' => false,
							'disabled' => $admin_field_disabled,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Проверка на повторные посещения по одному профилю'),
							'hiddenName' => 'double_vizit_control',
							'name' => 'double_vizit_control',
							'options' => toUtf('[' . implode(",", $this->double_vizit_control_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 150,
							'xtype' => 'combo'
						),
						array(
							'autoHeight' => true,
							'hidden' => getRegionNick() !== 'pskov',
							'xtype' => 'panel',
							'items' => array(
								array (
									'title' => 'Проверка соответствия между профилем и специальностью',
									'xtype' => 'fieldset',
									'border' => false,
									'autoHeight' => true,
									'labelWidth' => 120,
									'items' => array(
										array(
											'xtype' => 'panel',
											'layout' => 'column',
											'items' => array(
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 0,
															'name' => 'evnvizitpl_profile_medspecoms_check',
															'boxLabel' => toUtf('Отключена')
														)
													)
												),
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 1,
															'name' => 'evnvizitpl_profile_medspecoms_check',
															'boxLabel' => toUtf('Предупреждение')
														)
													)
												),
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 2,
															'name' => 'evnvizitpl_profile_medspecoms_check',
															'boxLabel' => toUtf('Ошибка'),
														)
													)
												)
											)
										)
									)
								)
							)
						)
					)
				),
				/*
				array(
					'label' => toUtf('Диспансерный учет'),
					'xtype' => 'fieldset',
					'hidden' => $admin_field_disabled,
					'items' => array(
						array(
							'boxLabel' => toUtf('При добавлении в регистр -> добавление в дисп. учет'),
							'checked' => false,
							'disabled' => true,
							'hideLabel' => true,
							'name' => 'disp_reg_to_disp_reg',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				*/
				array(
					'title' => toUtf('АРМ врача'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Создание посещений'),
							'hiddenName' => 'arm_vizit_create',
							'name' => 'arm_vizit_create',
							'options' => toUtf('[' . implode(",", $this->arm_vizit_create_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 150,
							'xtype' => 'combo'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Копирование осмотров'),
							'hiddenName' => 'arm_evn_xml_copy',
							'name' => 'arm_evn_xml_copy',
							'options' => toUtf('[' . implode(",", $this->arm_evn_xml_copy_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 300,
							'listWidth' => 350,
							'xtype' => 'combo'
						),
					)
				),
				array(
					'title' => toUtf('Печать талона'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Формат бумаги'),
							'hiddenName' => 'print_format',
							'name' => 'print_format',
							'options' => toUtf('[' . implode(",", $this->paper_format_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'saveDisabled' => true,
							'xtype' => 'combo'
						),
						array(
							'fieldLabel' => toUtf('Двусторонняя печать'),
							'hiddenName' => 'print_two_side',
							'name' => 'print_two_side',
							'valueField' => 'YesNo_id',
							'displayField' => 'YesNo_Name',
							'allowBlank' => false,
							'vfield' => 'value',
							'width' => 200,
							'saveDisabled' => true,
							'xtype' => 'swyesnocombo'
						),
						array(
							'boxLabel' => toUtf('Печать ТАП на одном листе'),
							'checked' => false,
							'hideLabel' => true,
							'name' => 'print_single_list',
							'vfield' => 'checked',
							'listeners' => array(
								'check' => 'function(f, c) {
									Ext.getCmp("options_window").find("name", "print_format")[0].setDisabled(c);
									Ext.getCmp("options_window").find("name", "print_two_side")[0].setDisabled(c);
								}',
							),
							'xtype' => 'checkbox'
						),
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Картохранилище'),
					'xtype' => 'fieldset',
					'hidden' => $admin_field_disabled,
					'items' => array(
						array(
							'boxLabel' => toUtf('Разрешить доступ к функционалу «Картохранилище»'),
							'checked' => false,
							'hideLabel' => true,
							'name' => 'allow_access_to_the_functionality_card_store',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'title' => toUtf('Печать осмотра (PDF)'),
					'labelWidth' => 120,
					'xtype' => 'fieldset',
					'hidden' => true,
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Формат бумаги'),
							'hiddenName' => 'vizit_print_paper_format',
							'name' => 'vizit_print_paper_format',
							'options' => toUtf('[' . implode(",", $this->paper_format_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'combo'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Ориентация'),
							'hiddenName' => 'vizit_print_paper_orient',
							'name' => 'vizit_print_paper_orient',
							'options' => toUtf('[' . implode(",", $this->paper_orientation_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'combo'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Шрифт'),
							'hiddenName' => 'vizit_print_font_size',
							'name' => 'vizit_print_font_size',
							'options' => toUtf('[' . implode(",", $this->font_size_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'combo'
						),
						array(
							'fieldLabel' => toUtf('Отступ сверху, мм'),
							'name' => 'vizit_print_margin_top',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Отступ справа, мм'),
							'name' => 'vizit_print_margin_right',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Отступ снизу, мм'),
							'name' => 'vizit_print_margin_bottom',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Отступ слева, мм'),
							'name' => 'vizit_print_margin_left',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Картотека'),
					'xtype' => 'fieldset',
					'hidden' => $admin_field_disabled,
					'items' => array(
						/*array(
							'boxLabel' => toUtf('Разрешать прикрепление только по истечении года с момента предыдущего'),
							'checked' => false,
							'disabled' => $admin_field_disabled,
							'hideLabel' => true,
							'name' => 'attach_if_year_expires',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),*/
						array(
							'fieldLabel' => toUtf('Следующий номер карты'),
							'name' => 'next_card_code',
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => $admin_field_disabled,
							'maxValue' => 999999999999,
							'minValue' => 0,
							'maxLength' =>12,
							'size' => 12,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'title' => toUtf('Прочее'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items'=>array(
						/*array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Отображать в поле "Врач" только врачей'),
							'checked'=>false,
							'hidden' => $admin_field_disabled,
							'disabled' => $admin_field_disabled,
							'hideLabel'=>true,
							'name'=> 'enable_is_doctor_filter',
							'vfield'=> 'checked'
						),*/
						array(
							'fieldLabel' => toUtf('Следующий номер ТАП (поликлиника)'),
							'name' => 'evnpl_numcard_next_num',
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => !$haveARMadminCod && !$haveArmAdminLpu,
							'minValue' => 1,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Следующий номер ТАП (стоматология)'),
							'name' => 'evnplstom_numcard_next_num',
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => !$haveARMadminCod && !$haveArmAdminLpu,
							'minValue' => 1,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				)
			),
			'dispprof' => array(
				array(
					'title' => toUtf('Маршрутная карта'),
					'xtype' => 'fieldset',
					'autoHeight' => true,
					'hidden' => false,
					'items'=>array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Не отображать в печатной форме пройденные осмотры/исследования'),
							'checked' => false,
							'hideLabel' => true,
							'name' => 'do_not_show_unchecked_research',
							'vfield' => 'checked'
						)
					)
				)
			),
			'recepts'=>array(
				array(
					'title' => toUtf('Проверки рецепта'),
					'xtype' => 'fieldset',
					'hidden' => $admin_field_disabled,
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'disabled' => (!isSuperadmin()),
							'boxLabel'=>toUtf('Запрещать сохранение рецепта, если нарушается уникальность серии и номера рецепта'),
							'checked'=>true,
							'hideLabel'=>true,
							'hidden'=>($_SESSION['region']['number']==2),
							'name'=> 'unique_ser_num',
							'vfield'=> 'checked'
						),
						array(
							'xtype'=>'checkbox',
							'disabled' => $admin_field_disabled,
							'boxLabel'=>toUtf('Запрещать сохранение рецепта, если дата выписки рецепта больше текущей даты'),
							'checked'=>true,
							'hideLabel'=>true,
							'name'=> 'validate_start_date',
							'vfield'=> 'checked'
						)
					)
				),
				array(
					'title' => toUtf('Печать рецепта'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'hidden' => in_array(getRegionNick(), array('kz', 'ufa')),
							'xtype' => 'panel',
							'layout' => 'form',
							'labelWidth' => 170,
							'items' => array(
								array(
									'displayField' => 'name',
									'editable' => false,
									'fieldLabel' => toUtf('Способ создания бланков льготного рецепта'),
									'hiddenName' => 'blank_form_creation_method',
									'name' => 'blank_form_creation_method',
									'options' => toUtf('[' . implode(",", $this->blank_form_creation_method_array) . ']'),
									'valueField' => 'val',
									'vfield' => 'value',
									'width' => 320,
									'xtype' => 'combo',
									'enableKeyEvents' => false,
									'disabled' => $admin_field_disabled
								)
							)
						),
						array(
							'title' => toUtf('Печать рецептов «на листе»'),
							'xtype' => 'fieldset',
							'labelWidth' => 200,
							'items' => array(
								array(
									'displayField' => 'name',
									'editable' => false,
									'fieldLabel' => toUtf('Количество экземпляров'),
									'hiddenName' => 'copies_count',
									'name' => 'copies_count',
									'options' => toUtf('[' . implode(",", $this->recept_copies_count_array) . ']'),
									'valueField' => 'val',
									'vfield' => 'value',
									'width' => 50,
									'xtype' => 'combo',
									'listeners' => array(
										'change' =>
											'function(f, c) {
										if(c==1) {
											Ext.getCmp("options_window").find("name", "print_format")[0].setDisabled(true);
											Ext.getCmp("options_window").find("name", "print_format")[0].setValue(3);
										} else {
											Ext.getCmp("options_window").find("name", "print_format")[0].setDisabled(false);
										}
										if(!c)
											{
												Ext.getCmp("options_window").find("name", "copies_count")[0].setValue(1);
												Ext.getCmp("options_window").find("name", "print_format")[0].setValue(3);
												Ext.getCmp("options_window").find("name", "print_format")[0].setDisabled(true);
											}
									}'
									),
									'enableKeyEvents' => false
								),
								array(
									'displayField' => 'name',
									'editable' => false,
									'fieldLabel' => toUtf('Формат бумаги'),
									'hiddenName' => 'print_format',
									'name' => 'print_format',
									'options' => toUtf('[' . implode(",", $this->recept_print_format_array) . ']'),
									'valueField' => 'val',
									'vfield' => 'value',
									'width' => 200,
									'xtype' => 'combo',
									'enableKeyEvents' => false,
									'listeners' => array(
										'change' => 'function(f, c) {
																	if(!c)
																		{
																			Ext.getCmp("options_window").find("name", "print_format")[0].setValue(1);
																		}
																}'
									),
								),
								array(
									'displayField' => 'name',
									'editable' => false,
									'fieldLabel' => toUtf('Формат печати'),
									'hiddenName' => 'print_extension',
									'name' => 'print_extension',
									'options' => toUtf('[' . implode(",", $this->recept_print_extension_array) . ']'),
									'valueField' => 'val',
									'vfield' => 'value',
									'width' => 200,
									'xtype' => 'combo',
									'enableKeyEvents' => false,
									'enabled' => 'false',
									'listeners' => array(
										'change' => 'function(f, c) {
																	if(!c || c==3)
																		{
																			Ext.getCmp("options_window").find("name", "print_extension")[0].setValue(1);
																		}
																}',
										''
									),
								)
							)
						)
					)
				),
				array(
					'title' => toUtf('Серия рецепта '),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'hidden'=>($_SESSION['region']['number']!=2 || $admin_field_disabled),
					'items' => array(
						array(
							'allowBlank' => ($_SESSION['region']['number']!=2 || $admin_field_disabled),//false, https://redmine.swan.perm.ru/issues/55367
							'disabled' => (getRegionNick() == 'ufa' && !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin'))),
							'saveDisabled' => true,
							'fieldLabel' => toUtf('Федеральные рецепты'),
							'name' => 'evn_recept_fed_ser',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'textfield'
						),
						array(
							'allowBlank' => ($_SESSION['region']['number']!=2 || $admin_field_disabled),//false, https://redmine.swan.perm.ru/issues/55367
							'disabled' => (getRegionNick() == 'ufa' && !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin'))),
							'saveDisabled' => true,
							'fieldLabel' => toUtf('Региональные рецепты'),
							'name' => 'evn_recept_reg_ser',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'textfield'
						)
					)
				),
				array(
					'title' => toUtf('Номера федерального рецепта'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'hidden'=>($_SESSION['region']['number']!=2 || $admin_field_disabled),
					'items' => array(
						array(
							'allowBlank' => ($_SESSION['region']['number']!=2 || $admin_field_disabled),//false, https://redmine.swan.perm.ru/issues/55367
							'disabled' => (getRegionNick() == 'ufa' && !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin'))),
							'saveDisabled' => true,
							'fieldLabel' => toUtf('Диапазон, от'),
							'name' => 'evn_recept_fed_num_min',
							'vfield' => 'value',
							'width' => 80,
							'maxValue' => 99999999,
							'minValue' => 1,
							'maxLength' =>8,
							'size' => 10,
							'xtype' => 'numberfield'
						),
						array(
							'allowBlank' => ($_SESSION['region']['number']!=2 || $admin_field_disabled),//false, https://redmine.swan.perm.ru/issues/55367
							'disabled' => (getRegionNick() == 'ufa' && !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin'))),
							'saveDisabled' => true,
							'fieldLabel' => toUtf('до'),
							'name' => 'evn_recept_fed_num_max',
							'vfield' => 'value',
							'width' => 80,
							'maxValue' => 99999999,
							'minValue' => 1,
							'maxLength' =>8,
							'size' => 10,
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'title' => toUtf('Номер регионального рецепта'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'hidden'=>($_SESSION['region']['number']!=2 || $admin_field_disabled),
					'items' => array(
						array(
							'allowBlank' => ($_SESSION['region']['number']!=2 || $admin_field_disabled),//false, https://redmine.swan.perm.ru/issues/55367
							'disabled' => (getRegionNick() == 'ufa' && !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin'))),
							'saveDisabled' => true,
							'fieldLabel' => toUtf('Начать с номера'),
							'name' => 'evn_recept_reg_num',
							'vfield' => 'value',
							'width' => 80,
							'maxValue' => 9999999999999,
							'minValue' => ($_SESSION['region']['number']!=2 || $admin_field_disabled)?0:1, //https://redmine.swan.perm.ru/issues/55367
							'maxLength' => 13,
							'size' => 15,
							'xtype' => 'numberfield'
						)
					)
				)
				/*array(
					'label' => toUtf('Выписка рецептов'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(

						)
					)
				),*/
			),
			'appearance' => array(
				array(
					'title' => toUtf('Навигация'),
					'xtype' => 'fieldset',
					'labelWidth' => 300,
					'hidden'=> false,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Тип меню (изменение вступает в силу после перезапуска)'),
							'allowBlank' => false,
							'name' => 'menu_type',
							'xtype' => 'combo',
							'hiddenName' => 'menu_type',
							'options' => toUtf('[' . implode(",", $this->menu_types) . ']'),
							'editable' => false,
							'valueField' => 'val',
							'displayField' => 'name',
							'vfield' => 'value',
							'listeners' => array(
								'render' => 'function(f) {
									if (f.getValue() == \'ribbon\') {
										f.setValue(\'\');
									}
								}'
							)
						)
					)
				),
				array(
					'title' => toUtf('Локализация'),
					'xtype' => 'fieldset',
					'labelWidth' => 300,
					'hidden'=> (count($languages)==0),
					'items' => array(
						array(
							'fieldLabel' => toUtf('Язык (изменение вступает в силу после перезапуска)'),
							'allowBlank' => false,
							'name' => 'language',
							'xtype' => 'combo',
							'hiddenName' => 'language',
							'options' => toUtf('[' . implode(",", $languages) . ']'),
							'editable' => false,
							'valueField' => 'val',
							'displayField' => 'name',
							'vfield' => 'value'
						)
					)
				),
				array(
					'title' => toUtf('Тема'),
					'xtype' => 'fieldset',
					'labelWidth' => 115,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Выберите тему'),
							'name' => 'user_theme',
							'xtype' => 'themecombo',
							'hiddenName' => 'user_theme',
							'options' => toUtf($this->genThemeList()),
							'editable' => false,
							'valueField' => 'val',
							'displayField' => 'name',
							'vfield' => 'value'
						)
					)
				),
				array(
					'boxLabel' => toUtf('Отображать панель быстрого переключения окон'),
					'checked' => false,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'taskbar_enabled',
					'vfield' => 'checked',
					'xtype' => 'checkbox'
				)
			),
			'stac' => array(
				array(
					'title' => toUtf('Проверки карты выбывшего из стационара'),
					'hidden' => getRegionNick() !== 'pskov',
					'labelWidth' => 120,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'autoHeight' => true,
							'bodyStyle' => 'margin-top: 10px;',
							'xtype' => 'panel',
							'items' => array(
								array (
									'title' => 'Проверка соответствия между профилем и специальностью',
									'xtype' => 'fieldset',
									'border' => false,
									'autoHeight' => true,
									'labelWidth' => 120,
									'items' => array(
										array(
											'xtype' => 'panel',
											'layout' => 'column',
											'items' => array(
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 0,
															'name' => 'evnsection_profile_medspecoms_check',
															'boxLabel' => toUtf('Отключена')
														)
													)
												),
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 1,
															'name' => 'evnsection_profile_medspecoms_check',
															'boxLabel' => toUtf('Предупреждение')
														)
													)
												),
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														array(
															'xtype' => 'radio',
															'vfield' => 'checked',
															'disabled' => ! (havingGroup('SuperAdmin') || havingGroup('LpuAdmin')),
															'hideLabel' => true,
															'inputValue' => 2,
															'name' => 'evnsection_profile_medspecoms_check',
															'boxLabel' => toUtf('Ошибка'),
														)
													)
												)
											)
										)
									)
								)
							)
						)


					)
				),
				array(
					'title' => toUtf('Номер карты'),
					'hidden' => $admin_field_disabled,
					'labelWidth' => 120,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Префикс'),
							'disabled' => $admin_field_disabled,
							'name' => 'evnps_numcard_prefix',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Суффикс'),
							'disabled' => $admin_field_disabled,
							'name' => 'evnps_numcard_postfix',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Текущий номер'),
							'disabled' => $admin_field_disabled,
							'name' => 'evnps_numcard_next_num',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'title' => toUtf('Отчетность'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('По статсуткам'),
							'disabled' => $admin_field_disabled,
							'name' => 'isstat',
							'xtype' => 'swyesnocombo',
							'hiddenName' => 'isstat',
							'valueField' => 'YesNo_id',
							'displayField' => 'YesNo_Name',
							'allowBlank' => false,
							'vfield' => 'value'
						)
					)
				),
				array(
					'title' => toUtf('Печать "Истории болезни"'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Формат бумаги'),
							'hiddenName' => 'evnps_print_format',
							'name' => 'evnps_print_format',
							'options' => toUtf('[' . implode(",", $this->evnps_print_format_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Полный перечень врачей, оказывающих оперативные услуги'),
					'checked' => false,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'oper_usluga_full_med_personal_list',
					'vfield' => 'checked'
				),
				array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Запретить создание КВС в профильных отделениях'),
					'checked' => false,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'disable_patient_additions_for_profile_branches',
					'vfield' => 'checked'
				),
				/*array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Обязательность ввода диагноза в приемном отделении'),
					'checked' => true,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'is_required_evnps_diag_pid',
					'vfield' => 'checked'
				),*/
				array(
					'title'      => toUtf('Печать браслетов'),
					'labelWidth' => 150,
					'xtype'       => 'fieldset',
					'hidden'     => !in_array ( $_SESSION['region']['nick'], array ( 'ufa', 'buryatiya')),
					'items'      => array(
						array(
							'xtype'      => 'panel',
							'layout'     => 'form',
							'disabled'   => !in_array ( $_SESSION['region']['nick'], array ( 'ufa', 'buryatiya')),
							'items'      => array(
								array(
									'allowBlank'   => true,
									'editable'     => true,
									'fieldLabel'   => toUtf('Принтер'),
									'hiddenName'   => 'band_printer',
									'name'         => 'band_printer',
									'displayField' => 'name',
									'valueField'   => 'val',
									'vfield'       => 'value',
									'width'        => 100,
									'xtype'        => 'combo',
									'options' => '[' . implode(",", $this->band_printer) . ']',
									'listeners'    => array(
										 'beforequery' => 'function(queryEvent) {
											combo = queryEvent.combo;

											combo.store.removeAll();
											BrowserPrint.getLocalDevices(function(printers) {
												if (printers != undefined) {
													for(var i = 0; i < printers.length; i++) {
														if (printers[i].deviceType == "printer") {
															pName = printers[i].name;
															combo.store.add([new Ext.data.Record({"val":pName,"name":pName})]);
														}
													}
												}
												combo.expand();
											}, undefined, "printer");
										 }'
										 
									)
								),
								array(
									'allowBlank'   => true,
									'editable'     => true,
									'fieldLabel'   => toUtf('Модель'),
									'hiddenName'   => 'band_printer_model',
									'name'         => 'band_printer_model',
									'displayField' => 'name',
									'valueField'   => 'val',
									'vfield'       => 'value',
									'width'        => 100,
									'xtype'        => 'combo',
									'options' => '[' . implode(",", $this->band_printer_model) . ']',
									'listeners'     => array(
										'change' => 'function(combo, value) {
											Ext.getCmp("band_options_fieldset").setVisible(!value ? true : false);
										}'
									)
								),
								array(
									'boxLabel'  => toUtf('ФИО пациента'),
									'checked'   => false,
									'disabled'  => false,
									'hideLabel' => true,
									'name'      => 'band_fio',
									'vfield'    => 'checked',
									'xtype'     => 'checkbox',
									'listeners' => array(
										'check' => 'function(combo, rec) {
											var ZebraDateOfBirth = Ext.getCmp("band_birthday");
											if(rec) {
												ZebraDateOfBirth.setDisabled(false);
											} else {
												ZebraDateOfBirth.setDisabled(true);
												ZebraDateOfBirth.setValue(false);
											}
										}'
									)
								),
								array(
									'boxLabel'  => toUtf('Дата рождения пациента'),
									'checked'   => false,
									'disabled'  => false,
									'hideLabel' => true,
									'name'      => 'band_birthday',
									'id'        => 'band_birthday',
									'vfield'    => 'checked',
									'xtype'     => 'checkbox'
								),
								array(
									'boxLabel'  => toUtf('Номер медицинской карты'),
									'checked'   => false,
									'disabled'  => false,
									'hideLabel' => true,
									'name'      => 'band_numcard',
									'vfield'    => 'checked',
									'xtype'     => 'checkbox'
								),
								array(
									'boxLabel'  => toUtf('Краткое наименование МО'),
									'checked'   => false,
									'disabled'  => false,
									'hideLabel' => true,
									'name'      => 'band_lpu_nick',
									'vfield'    => 'checked',
									'xtype'     => 'checkbox'
								),
								array(
									'boxLabel'  => toUtf('Наименование отделения'),
									'checked'   => false,
									'disabled'  => false,
									'hideLabel' => true,
									'name'      => 'band_lpusection',
									'vfield'    => 'checked',
									'xtype'     => 'checkbox'
								),
								array(
									'title'      => toUtf('Ручная настройка печати'),
									'id'         => 'band_options_fieldset',
									'xtype'      => 'fieldset',
									'labelWidth' => 250,
									'items'      => array(
										array(
											'title'      => toUtf('Текстовое поле'),
											'xtype'      => 'fieldset',
											'labelWidth' => 250,
											'items'      => array(
												array(
													'fieldLabel' => toUtf('Высота шрифта'),
													'name'       => 'band_font_height',
													'id'         => 'band_font_height',
													'minValue'   => 0,
													'maxValue'   => 150,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Ширина шрифта'),
													'name'       => 'band_font_width',
													'id'         => 'band_font_width',
													'minValue'   => 0,
													'maxValue'   => 150,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Ширина текстового поля'),
													'name'       => 'band_text_width',
													'id'         => 'band_text_width',
													'minValue'   => 0,
													'maxValue'   => 2000,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Отступ слева'),
													'name'       => 'band_margin_left',
													'id'         => 'band_margin_left',
													'minValue'   => 0,
													'maxValue'   => 2000,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Отступ снизу'),
													'name'       => 'band_margin_bottom',
													'id'         => 'band_margin_bottom',
													'minValue'   => 0,
													'maxValue'   => 2000,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												)
											)
										),
										array(
											'title'      => toUtf('Штрих-код'),
											'xtype'      => 'fieldset',
											'labelWidth' => 250,
											'items'      => array(
												array(
													'fieldLabel' => toUtf('Высота'),
													'name'       => 'band_barcode_height',
													'id'         => 'band_barcode_height',
													'minValue'   => 0,
													'maxValue'   => 2000,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 150,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Размер'),
													'name'       => 'band_barcode_size',
													'id'         => 'band_barcode_size',
													'minValue'   => 0,
													'maxValue'   => 10,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												),
												array(
													'fieldLabel' => toUtf('Отступ снизу'),
													'name'       => 'band_barcode_margin_bottom',
													'id'         => 'band_barcode_margin_bottom',
													'minValue'   => 0,
													'maxValue'   => 2000,
													'size'       => 10,
													'vfield'     => 'value',
													'width'      => 100,
													'xtype'      => 'numberfield',
													'allowDecimals' => false
												)
											)
										)
									)
								)
							)
						)
					)
				),
				array(
					'title' => toUtf('Расписание'),
					'xtype' => 'fieldset',
					'hidden' => !in_array(getRegionNick(), array('msk', 'vologda', 'kz', 'ufa')),
					'labelWidth' => 180,
					'items' => array(
						array(
							'xtype' => 'panel',
							'layout' => 'form',
							'labelWidth' => 180,
							'hidden' => !in_array(getRegionNick(), array('msk', 'vologda', 'ufa')),
							'items' => array(
								array(
									'xtype' => 'checkbox',
									'boxLabel' => toUtf('Автоматическое создание расписания'),
									'checked' => false,
									'disabled' => !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('SchedulingPS')),
									'hideLabel' => true,
									'name' => 'stac_schedule_auto_create',
									'vfield' => 'checked',
									'listeners' => array(
										'check' => 'function(r, c) { Ext.getCmp("options_window").find("name", "stac_schedule_priority_duration")[0].ownerCt.setVisible(c); }'
									)
								)
							)
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Расписание госпитализации'),
							'hiddenName' => 'stac_schedule_time_binding',
							'name' => 'stac_schedule_time_binding',
							'disabled' => !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('SchedulingPS')),
							'options' => toUtf('[' . implode(",", $this->stac_schedule_time_binding_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 270,
							'xtype' => 'combo'
						),
						array(
							'xtype' => 'panel',
							'layout' => 'form',
							'labelWidth' => 180,
							'items' => array(
								array(
									'displayField' => 'name',
									'editable' => false,
									'allowBlank' => true,
									'fieldLabel' => toUtf('Приоритет продолжительности лечения'),
									'hiddenName' => 'stac_schedule_priority_duration',
									'name' => 'stac_schedule_priority_duration',
									'disabled' => !(havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('SchedulingPS')),
									'options' => toUtf('[' . implode(",", $this->stac_schedule_priority_duration_array) . ']'),
									'valueField' => 'val',
									'vfield' => 'value',
									'width' => 270,
									'xtype' => 'combo'
								)
							)
						)
					)
				)
			),
			'registry' => array(
				array(
					'title' => toUtf('Реестры'),
					'hidden' => ($_SESSION['region']['nick'] != 'astra' && !isSuperadmin()),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Проверять на ошибки персональных данных при формировании'),
							'hidden' => (!isSuperadmin()),
							'disabled' => (!isSuperadmin()),
							'checked' => true,
							'hideLabel' => true,
							'vfield' => 'checked',
							'name' => 'check_person_error'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Разрешить переформирование по ошибкам вне очереди'),
							'hidden' => (!isSuperadmin()),
							'disabled' => (!isSuperadmin()),
							'checked' => false,
							'hideLabel' => true,
							'vfield' => 'checked',
							'name' => 'check_access_reform'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => 'Выгружать номер амбулаторной карты вместо номера ТАП',
							'hidden' => $_SESSION['region']['nick'] != 'astra',
							'disabled' => ($_SESSION['region']['nick'] != 'astra' || (!isLpuAdmin() && !isSuperadmin())),
							'checked' => false,
							'hideLabel' => true,
							'vfield' => 'checked',
							'name' => 'export_personcardcode_instead_of_evnplnumcard'
						)
					)
				),
				array(
					'title' => 'Порядок сортировки случаев',
					'labelWidth' => 160,
					'xtype' => 'fieldset',
					'hidden' => ($_SESSION['region']['nick'] != 'ufa'),
					'items' => array(
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Стационар',
							'hiddenName' => 'registry_evn_sort_order_stac',
							'name' => 'registry_evn_sort_order_stac',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Поликлиника',
							'hiddenName' => 'registry_evn_sort_order_polka',
							'name' => 'registry_evn_sort_order_polka',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Скорая помощь',
							'hiddenName' => 'registry_evn_sort_order_smp',
							'name' => 'registry_evn_sort_order_smp',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Дисп-ция взр. населения с 2013 года',
							'hiddenName' => 'registry_evn_sort_order_dvn',
							'name' => 'registry_evn_sort_order_dvn',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Дисп-ция детей-сирот с 2013 года',
							'hiddenName' => 'registry_evn_sort_order_dds',
							'name' => 'registry_evn_sort_order_dds',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Высокотехнологичная медицинская помощь',
							'hiddenName' => 'registry_evn_sort_order_htm',
							'name' => 'registry_evn_sort_order_htm',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'disabled' => (!isLpuAdmin() && !isSuperadmin()),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Профилактические медицинские осмотры',
							'hiddenName' => 'registry_evn_sort_order_prof',
							'name' => 'registry_evn_sort_order_prof',
							'options' => '[' . implode(",", $this->registry_evn_sort_order_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
					),
				),
			),
			'medsvid' => array(
				array(
					'labelWidth' => 150,
					'title' => toUtf('Серия и номер'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'disabled' => ($_SESSION['region']['nick'] == 'khak' && !isSuperadmin()),
							'fieldLabel' => toUtf('Серия'),
							'name' => 'medsvid_ser',
							'vfield' => 'value',
							'xtype' => 'textfield'
						)
					)
				),
				array(
					'autoHeight' => true,
					'xtype' => 'panel',
					'items' => array(
						array(
							'title' => toUtf('В свидетельствах о смерти использовать адрес'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'disabled' => !isLpuAdmin() && !isSuperAdmin(),
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('МО'),
									'name' => 'deathmedsvid_address_type'
								),
								array(
									'disabled' => !isLpuAdmin() && !isSuperAdmin(),
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('Подразделение'),
									'name' => 'deathmedsvid_address_type'
								)
							)
						)
					)
				),
				array(
					'title' => toUtf('Печать'),
					'xtype' => 'fieldset',
					'hidden' => ($_SESSION['region']['nick'] != 'khak'),
					'items' => array(
						array(
							'xtype' => 'panel',
							'layout' => 'form',
							'labelWidth' => 170,
							'items' => array(
								array(
									'labelWidth' => 150,
									'title' => toUtf('Печать МСС на типографическом бланке'),
									'xtype' => 'fieldset',
									'items' => array(
										array(
											'allowDecimals' => true,
											'allowNegative' => true,
											'fieldLabel' => toUtf('Отступ сверху (мм)'),
											'name' => 'medsvid_print_topmargin',
											'vfield' => 'value',
											'xtype' => 'numberfield',
											'local' => true
										),
										array(
											'allowDecimals' => true,
											'allowNegative' => true,
											'fieldLabel' => toUtf('Отступ слева (мм)'),
											'name' => 'medsvid_print_leftmargin',
											'vfield' => 'value',
											'xtype' => 'numberfield',
											'local' => true
										)
									)
								)
							)
						)
					)
				)
			),
			'evnstick' => array(
				array(
					'labelWidth' => 150,
					'title' => toUtf('Печать'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'allowDecimals' => true,
							'allowNegative' => true,
							'fieldLabel' => toUtf('Отступ сверху (мм)'),
							'name' => 'evnstick_print_topmargin',
							'vfield' => 'value',
							'xtype' => 'numberfield',
							'local' => true
						),
						array(
							'allowDecimals' => true,
							'allowNegative' => true,
							'fieldLabel' => toUtf('Отступ слева (мм)'),
							'name' => 'evnstick_print_leftmargin',
							'vfield' => 'value',
							'xtype' => 'numberfield',
							'local' => true
						)
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Подпись ЭЛН'),
					'hidden' => getRegionNick() == 'kz',
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'hidden' => false,
							'disabled' => !isLpuAdmin() && !isSuperAdmin(),
							'boxLabel' => toUtf('Разрешить подписывать уполномоченному лицу'),
							'checked' => false,
							'hideLabel' => true,
							'name' => 'enable_sign_evnstick_auth_person',
							'vfield' => 'checked'
						)
					)
				)
			),
			'usluga' => array(
				array(
					'xtype' => 'checkbox',
					'hidden' => false,
					'disabled' => false,
					'boxLabel' => toUtf('Фильтр по месту выполнения'),
					'checked' => false,
					'hideLabel' => true,
					'name' => 'enable_usluga_section_load',
					'vfield' => 'checked'
				),
				array(
					'xtype' => 'checkbox',
					'hidden' => false,
					'disabled' => false,
					'boxLabel' => toUtf('Фильтр по месту посещения'),
					'checked' => false,
					'hideLabel' => true,
					'name' => 'enable_usluga_section_load_filter',
					'vfield' => 'checked'
				),
				array(
					'title' => toUtf('Доступные услуги для выбора'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Услуги'),
							'hiddenName' => 'allowed_usluga',
							'name' => 'allowed_usluga',
							'options' => toUtf('[' . implode(",", $this->allowed_usluga_list) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				)
			),
			'glossary' => array(
				array(
					'title' => toUtf('Использование глоссария'),
					'xtype' => 'fieldset',
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать базовый глоссарий'),
							'checked'=>false,
							'hideLabel'=>true,
							'name'=> 'enable_base_glossary',
							'vfield'=> 'checked'
						),
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать личный глоссарий'),
							'checked'=>false,
							'hideLabel'=>true,
							'name'=> 'enable_pers_glossary',
							'vfield'=> 'checked'
						)
					)
				),
				array(
					'title' => toUtf('Поиск слов в глоссарии'),
					'xtype' => 'fieldset',
					'items'=>array(
						array(
							'fieldLabel' => toUtf('Поиск слов в глоссарии'),
							'hideLabel'=>true,
							'allowBlank' => false,
							'name' => 'use_glossary_tag',
							'width' => 400,
							'xtype' => 'combo',
							'hiddenName' => 'use_glossary_tag',
							'options' => toUtf('[' . implode(",", $this->use_glossary_tag_array) . ']'),
							'editable' => false,
							'valueField' => 'val',
							'displayField' => 'name',
							'vfield' => 'value'
						)
					)
				)
			),
			'drugpurchase' => array(
				array(
					'title' => toUtf('Правила формирования лотов'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'boxLabel' => toUtf('Пользователем'),
							'name' => 'drugpurchase_rules_formation_lots'
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 2,
							'boxLabel' => toUtf('Автоматически в соответствии с настройками'),
							'listeners' => array(
								'check' => 'function(r, c) { Ext.getCmp("options_window").find("name", "drugpurchase_panel")[0].setVisible(c); }'
							),
							'name' => 'drugpurchase_rules_formation_lots'
						),
						array(
							'xtype' => 'panel',
							'layout' => 'form',
							'hidden' => true,
							'listeners' => array(
								'render' => 'function(f) {
									f.setVisible(Ext.getCmp("options_window").find("name", "drugpurchase_rules_formation_lots")[1].checked);
								}'
							),
							'name' => 'drugpurchase_panel',
							'labelAlign' => 'right',
							'items' => array(
								array(
									'xtype' => 'swbaselocalcombo',
									'fieldLabel' => toUtf('Группировка'),
									'name' => 'drugpurchase_grouping',
									'hiddenName' => 'drugpurchase_grouping',
									'store' => 'new Ext.data.SimpleStore({
										fields: [
											{ name: "val", type: "string" },
											{ name: "name", type: "string" }
										],
										autoLoad: true,
										data: [
											[ "atc", "' . toUtf("По классификации АТХ") . '" ],
											[ "farm", "' . toUtf("По фармакотерапевтическим группам") . '" ],
											[ "mnn", "' . toUtf("По МНН") . '" ],
											[ "mzrf", "' . toUtf("По классификации МЗ РФ") . '" ]
										]
									})',
									'editable' => false,
									'value' => 'atc',
									'valueField' => 'val',
									'listeners' => array(
										'render' => 'function(f) { f.fireEvent("select", f); }',
										'select' => 'function(f) { Ext.getCmp("options_window").find("name", "drugpurchase_atc_code_count_symbols")[0].ownerCt.setVisible(f.getValue() == "atc"); }'
									),
									'displayField' => 'name',
									'vfield' => 'value',
									'width' => 230,
									'listWidth' => 250
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => 220,
									'items' => array(
										'xtype' => 'numberfield',
										'name' => 'drugpurchase_atc_code_count_symbols',
										'vfield' => 'value',
										'fieldLabel' => toUtf('По количеству символов кода АТХ')
									)
								),
								array(
									'xtype' => 'fieldset',
									'title' => toUtf('Формировать отдельные лоты для'),
									'autoHeight' => true,
									'width' => 480,
									'defaults' => array(
										'hideLabel' => true
									),
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'vfield' => 'checked',
											'name' => 'drugpurchase_narc_psych_drugs',
											'boxLabel' => toUtf('Наркотические и/или психотропные вещества')
										),
										array(
											'xtype' => 'checkbox',
											'vfield' => 'checked',
											'name' => 'drugpurchase_each_drug_listed_tradename',
											'boxLabel' => toUtf('Каждого медикамента, указанного по торговому наименованию')
										),
										array(
											'xtype' => 'checkbox',
											'vfield' => 'checked',
											'name' => 'drugpurchase_used_to_solve_vk',
											'boxLabel' => toUtf('ЛС, применяемых по решению ВК')
										),
										array(
											'xtype' => 'panel',
											'layout' => 'column',
											'items' => array(
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'items' => array(
														'xtype' => 'checkbox',
														'hideLabel' => true,
														'vfield' => 'checked',
														'name' => 'drugpurchase_sum_than',
														'listeners' => array(
															'check' => 'function(f, c) {
																Ext.getCmp("options_window").find("name", "drugpurchase_sum_than_value")[0].setVisible(c);
																Ext.getCmp("options_window").find("name", "drugpurchase_sum_than_currency")[0].setVisible(c);
															}',
															'render' => 'function(f) { f.fireEvent("check", f, f.checked); }'
														),
														'boxLabel' => toUtf('ЛС, стоимость которых превышает')
													)
												),
												array(
													'xtype' => 'panel',
													'layout' => 'form',
													'style' => 'margin-left: 3px; padding-top: 3px',
													'items' => array(
														'xtype' => 'numberfield',
														'listeners' => array(
															'show' => 'function(f) { f.focus(true, 100) }'
														),
														'vfield' => 'value',
														'hidden' => true,
														'name' => 'drugpurchase_sum_than_value',
														'hideLabel' => true
													)
												),
												array(
													'layout' => 'form',
													'style' => 'width: 50px; margin-left: 5px; padding-top: 5px',
													'items' => array(
														'xtype' => 'label',
														'hidden' => true,
														'name' => 'drugpurchase_sum_than_currency',
														'style' => 'font-size: 9pt',
														'text' => toUtf('руб.'),
														'hideLabel' => true
													)
												)
											)
										),
										array(
											'xtype' => 'checkbox',
											'vfield' => 'checked',
											'name' => 'drugpurchase_select_uot_with_single_producer',
											'boxLabel' => toUtf('ЛС, производимых единственным производителем')
										)
									)
								),
								array(
									'xtype' => 'checkbox',
									'vfield' => 'checked',
									'boxLabel' => toUtf('Разрешить автоматическое переформирование лотов'),
									'name' => 'drugpurchase_auto_reconfig_uot',
									'hideLabel' => true
								),
								array(
									'xtype' => 'checkbox',
									'height' => 40,
									'vfield' => 'checked',
									'hidden' => true,
									'boxLabel' => toUtf('Включать в лот торговое наименование медикаментов, если оно используется в заявке'),
									'name' => 'drugpurchase_inc_in_uot_tradename_when_used_in_request',
									'hideLabel' => true
								),
								array(
									'xtype' => 'checkbox',
									'height' => 40,
									'vfield' => 'checked',
									'boxLabel' => toUtf('Выделять в отдельный лот лекарственные средства, производимые единственным производителем'),
									'name' => 'drugpurchase_select_uot_with_single_producer',
									'hideLabel' => true
								)
							)
						)
					)
				),
				array(
					'title' => toUtf('Требования к подписанию лотов'),
					'xtype' => 'fieldset',
					'hidden' => true,
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Требовать подписания лотов'),
							'hideLabel' => true,
							'listeners' => array(
								'render' => 'function(f) { f.fireEvent("check", f, f.checked); }',
								'check' => 'function(f, c) {
									Ext.getCmp("options_window").find("name", "drugpurchase_allow_signing_uots")[0].setVisible(c);
								}'
							),
							'name' => 'drugpurchase_requirements_for_signing_uots',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'height' => 40,
							'boxLabel' => toUtf('Разрешить подписание лотов, только после полного соответствия всех лотов "заявке на закуп"'),
							'hideLabel' => true,
							'name' => 'drugpurchase_allow_signing_uots',
							'vfield' => 'checked'
						)
					)
				)
			),
			'drugcontrol' => array(
				array(
					'title' => toUtf('Модуль учета медикаментов'),
					'xtype' => 'fieldset',
					'hidden' => !$isLpu,
					'items'=>array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'checked'=>(empty($_SESSION['lpu_id']) ? false : true),
							'boxLabel' => (empty($_SESSION['lpu_id']) ? toUtf('Аптека') : toUtf('Аптека МО')),
							'name' => 'drugcontrol_module',
                            'listeners' => array(
                                'check' => 'function(r, c) {
                                    var orgtype = getGlobalOptions().orgtype;
                                	Ext.getCmp("options_window").find("name", "suppliers_ostat_control")[0].setVisible(!c);
                                	Ext.getCmp("options_window").find("name", "doc_uc_operation_control")[0].setVisible(orgtype != \'lpu\' || !c);
                                	Ext.getCmp("options_window").find("name", "doc_uc_different_goods_unit_control")[0].setVisible(!c);
                                }'
                            )
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'checked'=>(!empty($_SESSION['lpu_id']) ? false : true),
							'inputValue' => 2,
							'boxLabel' => toUtf('АРМ Товароведа'),
							'name' => 'drugcontrol_module',
							'listeners' => array(
                                'check' => 'function(r, c) {
                                    var orgtype = getGlobalOptions().orgtype;
                                	Ext.getCmp("options_window").find("name", "suppliers_ostat_control")[0].setVisible(c);
                                	Ext.getCmp("options_window").find("name", "doc_uc_operation_control")[0].setVisible(orgtype != \'lpu\' || c);
                                	Ext.getCmp("options_window").find("name", "doc_uc_different_goods_unit_control")[0].setVisible(c);
                                }'
                            )
						),
					)
				),
                array(
                    'boxLabel' => toUtf('Контроль остатков поставщиков при поставке'),
                    'checked' => false,
                    'disabled' => false,
                    'hideLabel' => true,
                    'name' => 'suppliers_ostat_control',
                    'vfield' => 'checked',
                    'xtype' => 'checkbox'
                ),
                array(
                    'boxLabel' => toUtf('Выполнять учет операций по документам учета медикаментов'),
                    'checked' => false,
                    'disabled' => false,
                    'hideLabel' => true,
                    'name' => 'doc_uc_operation_control',
                    'vfield' => 'checked',
                    'xtype' => 'checkbox'
                ),
                array(
                    'boxLabel' => toUtf('Разрешить списание в единицах отличных от единиц учета'),
                    'checked' => false,
                    'disabled' => (!havingGroup('SuperAdmin') && !havingGroup('LpuAdmin')),
                    'hideLabel' => true,
                    'name' => 'doc_uc_different_goods_unit_control',
                    'vfield' => 'checked',
                    'xtype' => 'checkbox'
                )
			),
			'others' => array(
				array(
					'title' => toUtf('Локальное хранилище'),
					'xtype' => 'fieldset',
					'hidden' => true,
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать локальное хранилище для хранения справочников'), // Для принятия изменений нужно перезайти в промед
							'checked'=>true,
							'hideLabel'=>true,
							'hidden' => false,
							'name'=> 'enable_localdb',
							'vfield'=> 'checked'
						)
					)
				),
				/*array(
					'label' => toUtf('Экспериментальные функции'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать получение данных с УЭК (требуется перезапуск ПроМед)'),
							'checked'=>true,
							'hideLabel'=>true,
							'name'=> 'enable_uecreader',
							'vfield'=> 'checked'
						),
						array(
							'allowDecimals' => false,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Интервал чтения УЭК (мс)'),
							'name' => 'uecreader_interval',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
			    array(
					'label' => toUtf('Чтение Электроного полиса'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать получение данных с эл. полиса (требуется перезапуск ПроМед)'),
							'checked'=>true,
							'hideLabel'=>true,
							'name'=> 'enable_bdzreader',
							'vfield'=> 'checked'
						),
						array(
							'allowDecimals' => false,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Интервал чтения Эл. полиса (мс)'),
							'name' => 'bdzreader_interval',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),*/
				array(
					'title' => toUtf('Сканер штрих-кода'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items'=>array(
						array(
							'xtype'=>'checkbox',
							'boxLabel'=>toUtf('Использовать чтение штрих-кода (требуется перезапуск системы)'),
							'checked'=>true,
							'hideLabel'=>true,
							'name'=> 'enable_barcodereader',
							'vfield'=> 'checked'
						),
						array(
							'allowDecimals' => false,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Интервал чтения штрих-кода (мс)'),
							'name' => 'barcodereader_interval',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						),
						array(
							'allowBlank' => true,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Порт'),
							'hiddenName' => 'barcodereader_port',
							'name' => 'barcodereader_port',
							'options' => toUtf('[]'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'title' => toUtf('Запись через интернет'),
					'labelWidth' => 180,
					'xtype' => 'fieldset',
					'hidden' => !$isLpu,
					'items' => array(
						array(
							'xtype'=>'checkbox',
							'boxLabel' => toUtf('Блокировать запись через интернет пациентов при неявках'),
							'name' => 'ban_on_absences',
							'checked'=>true,
							'vfield'=> 'checked',
							'hidden' => false,
							'hideLabel'=>true,
						),
						array(
							'xtype'=>'checkbox',
							'boxLabel' => toUtf('Разрешить постановку в очередь'),
							'name' => 'allow_queue',
							'checked'=>true,
							'vfield'=> 'checked',
							'hidden' => false,
							'hideLabel'=>true,
						),
						array(
							'xtype'=>'checkbox',
							'boxLabel' => toUtf('Разрешить автоматическое обслуживание очереди'),
							'name' => 'allow_queue_auto',
							'checked'=>true,
							'vfield'=> 'checked',
							'hidden' => false,
							'hideLabel'=>true,
						)
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Чтение карт'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							//'disabled' => !isLpuAdmin() && !isSuperAdmin(),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Метод чтения'),
							'hiddenName' => 'doc_readcardtype',
							'name' => 'doc_readcardtype',
							'options' => toUtf('[' . implode(",", $this->doc_readcardtype_list) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Подпись документов'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							//'disabled' => !isLpuAdmin() && !isSuperAdmin(),
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Метод подписи'),
							'hiddenName' => 'doc_signtype',
							'name' => 'doc_signtype',
							'options' => toUtf('[' . implode(",", $this->doc_signtype_list) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'xtype'=>'checkbox',
					'boxLabel' => toUtf('Demo-сервер'),
					'name' => 'demo_server',
					'checked'=>true,
					'vfield'=> 'checked',
					'hidden' => false,
					'hideLabel'=>true
				),
				array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Проверять дату выписки направления и дату случая (формы: выполнение параклинической услуги/ТАП/КВС)'),
					'name' => 'checkEvnDirectionDate',
					'vfield'=> 'checked',
					'hidden' => ($this->getRegionNick() !== 'ekb' || $admin_field_disabled),
					'hideLabel'=>true
				),
				array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Оповещать о предыдущем доступе в систему'),
					'name' => 'notifyAboutLastAuth',
					'vfield'=> 'checked',
					'hidden' => ($this->getRegionNick() == 'kz'),
					'hideLabel'=>true
				),
				array(
					'xtype' => 'checkbox',
					'boxLabel' => toUtf('Просмотровщик DIGIPACS'),
					'name' => 'onDigipacsViewer',
					'vfield' => 'checked',
					'hidden' => ($this->getRegionNick() !== 'ufa'),
					'disabled' => (!(havingGroup('SuperAdmin'))),
					'hideLabel' => true,
					'listeners' => array(
						'check' => 'function(checkbox, checked) {
							var fieldDigiPacsAddress = Ext.getCmp("digiPacs_ip");
							if(checked == true) {
								fieldDigiPacsAddress.allowBlank = false;
								fieldDigiPacsAddress.setDisabled(false);
							} else {
								fieldDigiPacsAddress.allowBlank = true;
								fieldDigiPacsAddress.setDisabled(true);
								fieldDigiPacsAddress.setValue();
							}
						}'
					)
				),
				array(
					'xtype' => 'textfield',
					'fieldLabel' => toUtf('Адрес DIGIPACS'),
					'name' => 'digiPacsAddress',
					'id' => 'digiPacs_ip',
					'vfield' => 'value',
					'hidden' => ($this->getRegionNick() !== 'ufa'),
					'allowBlank' => false,
					'disabled' => (!(havingGroup('SuperAdmin'))),
					'valueField' => 'val',
					'width' => '90%',
					'listeners' => array(
						'disable' => 'function(textField) {
							textField.setDisabled(true);
						}'
					)
				),
				array(
					'labelWidth' => 150,
					'title' => toUtf('Учетные данные для взаимодействия с РИР МО'),
					'style' => 'margin-top: 10px;',
					'hidden' => $this->getRegionNick() != 'msk',
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'disabled' => !havingGroup('SchedulingPS') && !isLpuAdmin(),
							'fieldLabel' => toUtf('Логин (стационар)'),
							'name' => 'rir_login',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'textfield'
						),
						array(
							'disabled' => !havingGroup('SchedulingPS') && !isLpuAdmin(),
							'fieldLabel' => toUtf('Пароль (стационар)'),
							'name' => 'rir_pass',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'textfield',
							'inputType' => 'password'
						)
					)
				)
			),
			'emk' => array(
				array(
					'xtype'=>'checkbox',
					'boxLabel'=>toUtf('Отключить автоматический вызов окна выбора структурированных параметров'),
					'checked'=>true,
					'hideLabel'=>true,
					'hidden' => false,
					'name'=> 'disable_structured_params_auto_show',
					'vfield'=> 'checked'
				),
				array(
					'xtype'=>'checkbox',
					'boxLabel'=>toUtf('Версия для слабовидящих'),
					'checked'=>false,
					'hideLabel'=>true,
					'hidden' => false,
					'name'=> 'version_for_visually_impaired',
					'vfield'=> 'checked'
				)
			),
			'prescription' => array(
				array(
					'title' => toUtf('Отображение услуг'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'autoHeight' => true,
							'xtype' => 'panel',
							'items' => array(
								array(
									'xtype'=>'checkbox',
									'boxLabel'=>toUtf('Отображать код услуги'),
									'checked'=>true,
									'hideLabel'=>true,
									'name'=> 'enable_show_service_code',
									'listeners' => array(
										'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
									),
									'vfield'=> 'checked'
								),
								array(
									'title' => toUtf('Отображение наименований услуг'),
									'autoHeight' => true,
									'xtype' => 'fieldset',
									'items' => array(
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 1,
											'checked'=>true,
											'boxLabel' => toUtf('Фактическое наименование услуг'),
											'listeners' => array(
												'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
											),
											'name' => 'service_name_show_type'
										),
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 2,
											'boxLabel' => toUtf('Справочник ГОСТ-2011'),
											'listeners' => array(
												'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
											),
											'name' => 'service_name_show_type'
										),
									)
								),
								array(
									'xtype'=>'checkbox',
									'boxLabel'=>toUtf('Отображать состав лабораторной услуги (тесты) при помощи кратких наименований услуг, при наличии'),
									'checked'=>false,
									'hideLabel'=>true,
									'name'=> 'enable_show_service_nick',
									'listeners' => array(
										'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
									),
									'vfield'=> 'checked'
								),
							)
						)
					)
				),
				array(
					'title' => toUtf('Группировка услуг'),
					'xtype' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'autoHeight' => true,
							'xtype' => 'panel',
							'items' => array(
								array(
									'xtype'=>'checkbox',
									'boxLabel'=>toUtf('Группировать услуги по связным услугам ГОСТ 2011'),
									'checked'=>false,
									'hideLabel'=>true,
									'name'=> 'enable_grouping_by_gost2011',
									'listeners' => array(
										'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
									),
									'vfield'=> 'checked'
								),
								array(
									'title' => toUtf('Форма поиска по умолчанию'),
									'autoHeight' => true,
									'xtype' => 'fieldset',
									'items' => array(
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 1,
											'checked'=>true,
											'boxLabel' => toUtf('Группировка по услугам'),
											'listeners' => array(
												'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
											),
											'name' => 'default_service_search_form_type'
										),
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 2,
											'boxLabel' => toUtf('Группировка услуг по местам оказания'),
											'listeners' => array(
												'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
											),
											'name' => 'default_service_search_form_type'
										),
									)
								),
							)
						)
					)
				),
			),
			'address' => array(
				array(
					'xtype'=>'checkbox',
					'boxLabel'=>toUtf('Спец.объект в адресах'),
					'checked'=>true,
					'hideLabel'=>true,
					'hidden' => false,
					'name'=> 'specobject_show',
					'vfield'=> 'checked'
				)
			),
			'homevizit' => array(
				array(
					'xtype'=>'checkbox',
					'boxLabel'=>toUtf('Возможность вызова врача на дом'),
					'disabled' => $admin_field_disabled,
					'checked'=>false,
					'hideLabel'=>true,
					'hidden' => false,
					'listeners' => array(
						'check' => 'function(field, checked) {
							if ( field.disabled ) {
								return false;
							}

							Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

							if ( checked == true ) {
								var homevizitSchedule = Ext.getCmp("homevizitSchedule");
									if(homevizitSchedule) homevizitSchedule.enable();

								/*for (i=1;i<=7;i++) {
									Ext.getCmp("homevizit_day"+i).enable();
									Ext.getCmp("homevizit_begtime"+i).enable();
									Ext.getCmp("homevizit_endtime"+i).enable();
								}*/
							}
							else {
								var homevizitSchedule = Ext.getCmp("homevizitSchedule");
									if(homevizitSchedule) homevizitSchedule.disable();

								/*for (i=1;i<=7;i++) {
									Ext.getCmp("homevizit_day"+i).disable();
									Ext.getCmp("homevizit_begtime"+i).disable();
									Ext.getCmp("homevizit_endtime"+i).disable();
								}*/
							}
						}.createDelegate(this)'
					),
					'name'=> 'homevizit_isallowed',
					'id'=> 'homevizit_isallowed',
					'vfield'=> 'checked'
				),
				/*
				array(
					'label' => toUtf('Время работы сервиса'),
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время начала'),
							'disabled' => $admin_field_disabled,
							'hidden' => false,
							'id' => 'OPT_homevizit_begtime',
							'name'=> 'homevizit_begtime',
							'vfield'=> 'value'
						),
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время окончания'),
							'disabled' => $admin_field_disabled,
							'hidden' => false,
							'id' => 'OPT_homevizit_endtime',
							'name'=> 'homevizit_endtime',
							'vfield'=> 'value'
						)
					)
				), */
				array(
					'xtype'=>'checkbox',
					'boxLabel'=>toUtf('Оформление вызовов узких специалистов через регистратуру МО'),
					'disabled' => $admin_field_disabled,
					'checked'=>false,
					'hideLabel'=>true,
					'hidden' => false,
					'name'=> 'homevizit_spec_isallowed',
					'vfield'=> 'checked'
				),
				array(
					'border' => false,
					'labelWidth' => 200,
					'layout' => 'form',
					'xtype' => 'panel',
					'items' => array(
						array(
							'fieldLabel' => 'Телефон службы НМП',
							'width' => 200,
							'name' => 'homevizit_nmp_phone',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => 'Телефон службы СМП',
							'width' => 200,
							'name' => 'homevizit_smp_phone',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => 'Телефон вызова врача на дом.',
							'width' => 200,
							'name' => 'homevizit_phone',
							'vfield' => 'value',
							'xtype' => 'textfield'
						)
					)
				),
				array(
					'title' => toUtf('Расписание работы сервиса'),
					'id' => 'homevizitSchedule',
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'width' => 500,
					'disabled' => $admin_field_disabled,
					'items' => array(
						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Понедельник',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day1',
											'id' => 'homevizit_day1',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime1").enable();
														Ext.getCmp("homevizit_endtime1").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime1").disable();
														Ext.getCmp("homevizit_endtime1").disable();
														Ext.getCmp("homevizit_begtime1").setValue(null);
														Ext.getCmp("homevizit_endtime1").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_begtime1',
											'id'=> 'homevizit_begtime1',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_endtime1',
											'id'=> 'homevizit_endtime1',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Вторник',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day2',
											'id' => 'homevizit_day2',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime2").enable();
														Ext.getCmp("homevizit_endtime2").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime2").disable();
														Ext.getCmp("homevizit_endtime2").disable();
														Ext.getCmp("homevizit_begtime2").setValue(null);
														Ext.getCmp("homevizit_endtime2").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'width' => $homevizitTimeFieldWidth,
											'hidden' => false,
											'name'=> 'homevizit_begtime2',
											'id'=> 'homevizit_begtime2',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'width' => $homevizitTimeFieldWidth,
											'hidden' => false,
											'name'=> 'homevizit_endtime2',
											'id'=> 'homevizit_endtime2',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Среда',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day3',
											'id' => 'homevizit_day3',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime3").enable();
														Ext.getCmp("homevizit_endtime3").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime3").disable();
														Ext.getCmp("homevizit_endtime3").disable();
														Ext.getCmp("homevizit_begtime3").setValue(null);
														Ext.getCmp("homevizit_endtime3").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'width' => $homevizitTimeFieldWidth,
											'hidden' => false,
											'name'=> 'homevizit_begtime3',
											'id'=> 'homevizit_begtime3',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'width' => $homevizitTimeFieldWidth,
											'hidden' => false,
											'name'=> 'homevizit_endtime3',
											'id'=> 'homevizit_endtime3',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Четверг',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day4',
											'id' => 'homevizit_day4',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime4").enable();
														Ext.getCmp("homevizit_endtime4").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime4").disable();
														Ext.getCmp("homevizit_endtime4").disable();
														Ext.getCmp("homevizit_begtime4").setValue(null);
														Ext.getCmp("homevizit_endtime4").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_begtime4',
											'id'=> 'homevizit_begtime4',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_endtime4',
											'id'=> 'homevizit_endtime4',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Пятница',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day5',
											'id' => 'homevizit_day5',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime5").enable();
														Ext.getCmp("homevizit_endtime5").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime5").disable();
														Ext.getCmp("homevizit_endtime5").disable();
														Ext.getCmp("homevizit_begtime5").setValue(null);
														Ext.getCmp("homevizit_endtime5").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_begtime5',
											'id'=> 'homevizit_begtime5',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_endtime5',
											'id'=> 'homevizit_endtime5',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Суббота',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day6',
											'id' => 'homevizit_day6',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime6").enable();
														Ext.getCmp("homevizit_endtime6").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime6").disable();
														Ext.getCmp("homevizit_endtime6").disable();
														Ext.getCmp("homevizit_begtime6").setValue(null);
														Ext.getCmp("homevizit_endtime6").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_begtime6',
											'id'=> 'homevizit_begtime6',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_endtime6',
											'id'=> 'homevizit_endtime6',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								)
							)
						),

						array(
							'xtype' => 'panel',
							'layout' => 'column',
							'items' => array(
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'items' => array(
										array(
											'xtype' => 'checkbox',
											'boxLabel' => 'Воскресенье',
											'width' => $homevizitLabelDayWidth,
											'hideLabel' => true,
											'vfield' => 'checked',
											'name' => 'homevizit_day7',
											'id' => 'homevizit_day7',
											'listeners' => array(
												'check' => 'function(field, checked) {
													if ( field.disabled || '.(($admin_field_disabled)?'true':'false').') {
														return false;
													}

													Ext.getCmp("options_window").checkHomeVisitEnableAdditionalForm();

													if ( checked == true) {
														Ext.getCmp("homevizit_begtime7").enable();
														Ext.getCmp("homevizit_endtime7").enable();
													}
													else {
														Ext.getCmp("homevizit_begtime7").disable();
														Ext.getCmp("homevizit_endtime7").disable();
														Ext.getCmp("homevizit_begtime7").setValue(null);
														Ext.getCmp("homevizit_endtime7").setValue(null);
													}
												}.createDelegate(this)'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeStartWidth,
									'style' => $homevizitLabelTimeStartStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeStart,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_begtime7',
											'id'=> 'homevizit_begtime7',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),
								array(
									'xtype' => 'panel',
									'layout' => 'form',
									'labelWidth' => $homevizitLabelTimeEndWidth,
									'style' => $homevizitLabelTimeEndStyle,
									'items' => array(
										array(
											'xtype'=>'swtimefield',
											'fieldLabel' => $homevizitLabelTimeEnd,
											'disabled' => $admin_field_disabled,
											'hidden' => false,
											'width' => $homevizitTimeFieldWidth,
											'name'=> 'homevizit_endtime7',
											'id'=> 'homevizit_endtime7',
											'vfield'=> 'value',
											'listeners' => array(
												'change' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}',
												'blur' => 'function() {
													Ext.getCmp("options_window").checkHomeVisitForm(this);
												}'
											)
										)
									)
								),



								/*
								array(
									'xtype' => 'button',
									'text' => toUtf('Дополнительный период работы/выходных'),
									'listeners' => array(
										'click' => 'function() { getWnd(\'swAdditionalParams\').show(); }'
									),
								),
								*/

							),

						)
					)
				),
				array(

					'xtype' => 'fieldset',
					'title' => toUtf('Дополнительный период работы/выходных'),
					'disabled' => $admin_field_disabled,
					'items' => array(
						array (
							'xtype' => 'panel',
							'items' => array(
								array(
									'id' => 'additional_grid',
									'xtype' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'DataStorage',
									'dataUrl' => '/?c=HomeVisit&m=loadHomeVisitAdditionalSettings',
									'root' => 'data',
									'layout' => 'fit',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												//'wnd' => 'swCheckRegistryAccessEditWindow',
												'wnd' => 'swHomeVisitAdditionalSettingsEditWindow',
												'action' => 'add'
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swHomeVisitAdditionalSettingsEditWindow',
												'action' => 'edit',
												'key' => 'HomeVisitAdditionalSettings_id'
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swHomeVisitAdditionalSettingsEditWindow',
												'action' => 'view',
												'key' => 'HomeVisitAdditionalSettings_id'
											),
											'disabled' => true,
											'hidden' => true
										),
										'action_delete' => array(
											'params' => array(
												'url' => '/?c=HomeVisit&m=deleteHomeVisitAdditionalSettings',
												'action' => 'delete',
												'key' => 'HomeVisitAdditionalSettings_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'disabled' => true,
											'hidden' => true
										)
									),

									'onRowSelect' => 'function(sm, rowIdx, record) {
										if(
											record.data.HomeVisitAdditionalSettings_id &&
											(
												record.data.HomeVisitAdditionalSettings_begDate > new Date() ||
												record.data.HomeVisitAdditionalSettings_begDate.dateFormat(\'d.m.Y\') == new Date().dateFormat(\'d.m.Y\') ||
												record.data.HomeVisitAdditionalSettings_endDate > new Date() ||
												record.data.HomeVisitAdditionalSettings_endDate.dateFormat(\'d.m.Y\') == new Date().dateFormat(\'d.m.Y\')
											)
										){
											this.ViewActions.action_edit.setDisabled(false);
										}
										else{
											this.ViewActions.action_edit.setDisabled(true);
										}

									}',

									'stringfields' => array(
										//array('name' => 'DataStorage_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'HomeVisitAdditionalSettings_id', 'type' => 'int', 'hidden'=>true, 'key' => true),
										array('name' => 'HomeVisitPeriodType_id', 'hidden'=>true, 'type' => 'int'),
										array('name' => 'HomeVisitPeriodType_Name', 'type' => 'string', 'header' => toUtf('Тип'), 'width' => 180),
										array('name' => 'HomeVisitAdditionalSettings_begDate', 'type' => 'date', 'header' => toUtf('Период с'), 'width' => 80),
										array('name' => 'HomeVisitAdditionalSettings_endDate', 'type' => 'date', 'header' => toUtf('Период по'), 'width' => 80),
										array('name' => 'HomeVisitAdditionalSettings_begTime', 'type' => 'time', 'header' => toUtf('Время с'), 'width' => 60),
										array('name' => 'HomeVisitAdditionalSettings_endTime', 'type' => 'time', 'header' => toUtf('Время по'), 'width' => 60)
									)
								)
							)
						)
					)
				),
			),
			'lis' => array(
				array(
					'title' => toUtf('Настройки АС МЛО'),
					'labelWidth' => 150,
					'hidden' => !isSuperAdmin(),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Адрес сервиса'),
							'width' => 300,
							'name' => 'asmlo_server',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Логин'),
							'width' => 300,
							'name' => 'asmlo_login',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Пароль'),
							'width' => 300,
							'name' => 'asmlo_password',
							'vfield' => 'value',
							'xtype' => 'textfield'
						)
					)
				),
				array(
					'title' => toUtf('Печать штрих-кода'),
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Ширина, мм'),
							'name' => 'labsample_barcode_width',
                                                        'id' => 'labsample_barcode_width',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Высота, мм'),
							'name' => 'labsample_barcode_height',
                                                        'id' => 'labsample_barcode_height',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						/*array(
							'fieldLabel' => toUtf('Отступ справа, мм'),
							'name' => 'labsample_barcode_margin_right',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Отступ слева, мм'),
							'name' => 'labsample_barcode_margin_left',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),*/
						array(
							'fieldLabel' => toUtf('Отступ сверху, мм'),
							'name' => 'labsample_barcode_margin_top',
                                                        'id' => 'labsample_barcode_margin_top',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),
						/*array(
							'fieldLabel' => toUtf('Отступ снизу, мм'),
							'name' => 'labsample_barcode_margin_bottom',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						),*/
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Формат печати'),
							'disabled' => false,
							'hiddenName' => 'barcode_format',
							'name' => 'barcode_format',
							'options' => toUtf('[' . implode(",", $this->barcode_format_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'title' => 'Печать направлений на исследования',
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => 'Печать страницы с исследованиями',
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'PrintResearchDirections',
							'id' => 'PrintResearchDirections',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function (eventObject, checked)
								{
									if (getRegionNick() === "kz")
									{
										return true;
									}

									Ext.getCmp("PrintMnemonikaDirections").setDisabled(checked);

									return true;
								}'
							)
						),
						array(
							'boxLabel' => 'Печать страницы с мнемоникой',
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'hidden' => getRegionNick() === "kz",
							'name' => 'PrintMnemonikaDirections',
							'id' => 'PrintMnemonikaDirections',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function (eventObject, checked)
								{
									Ext.getCmp("PrintResearchDirections").setDisabled(checked);

									return true;
								}'
							)
						),
						[
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Печатная форма направления'),
							'hidden' => getRegionNick() != 'perm',
							'disabled' => !($haveARMadminCod || $haveArmAdminLpu),
							'hiddenName' => 'direction_print_form',
							'name' => 'direction_print_form',
							'options' => toUtf('[' . implode(',', $this->direction_print_form) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 250,
							'xtype' => 'combo'
						]
					)
				),
				array(
					'title' => 'Печать протоколов исследований',
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'hidden' => getRegionNick() != 'ufa',
					'items' => array(
						array(
							'boxLabel' => 'Печать протоколов исследований COVID',
							'checked' => false,
							'disabled' => !($haveARMadminCod || $haveArmAdminLpu),
							'hideLabel' => true,
							'hidden' => getRegionNick() != 'ufa',
							'name' => 'PrintResearchCovid',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'title' => toUtf('Настройки принтера штрих-кода'),
					'labelWidth' => 150,
					//'hidden' => $_SESSION['region']['nick'] != 'ufa',
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false, //$_SESSION['region']['nick'] != 'ufa',
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Метод печати'),
							'disabled' => false,
							'hiddenName' => 'barcode_print_method',
							'name' => 'barcode_print_method',
							'options' => toUtf('[' . implode(",", $this->barcode_print_method) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo',
	                        'listeners' => array(
								'render' => 'function(combo) {
									if(getRegionNick() == "ufa") {
										combo.store.add([new Ext.data.Record({ "val" : "4", "name" : "Zebra Browser Print"})]);
									}
								}',
								'change' => 'function(combo,newValue) {
									var ZebraUsluga_Name = Ext.getCmp("ZebraUsluga_Name");
									switch (parseInt(newValue)) {
										case 1:
											ZebraUsluga_Name.setDisabled(false);
											if(typeof(jsPrintSetup) === "undefined") {
												sw.swMsg.alert(lang["preduprejdenie"], lang["ustanovite_rashirenie_jsprintsetup"]);
											}
											Ext.getCmp("barcode_printer_form").hide();
										break;
										case 2:
										case 3:
											ZebraUsluga_Name.setDisabled(true);
											ZebraUsluga_Name.setValue(false);
											Ext.getCmp("barcode_printer_form").hide();
										break;
										case 4:
											Ext.getCmp("barcode_printer_form").show();
											ZebraUsluga_Name.setDisabled(false);
										break;
									}
								}'
		                    )
						),
						array(
							'labelWidth' => 150,
							'id' => 'barcode_printer_form',
							'hidden' => 'true',
							'xtype' => 'panel',
							'layout' => 'form',
							'items' => array(
								array(
									'allowBlank'   => true,
									'editable'     => true,
									'fieldLabel'   => toUtf('Принтер'),
									'hiddenName'   => 'barcode_printer',
									'name'         => 'barcode_printer',
									'displayField' => 'name',
									'valueField'   => 'val',
									'vfield'       => 'value',
									'width'        => 100,
									'xtype'        => 'combo',
									'options' => '[' . implode(",", $this->barcode_printer) . ']',
									'listeners'    => array(
										'render' => 'function(combo) {
											if( Ext.globalOptions.lis.barcode_print_method == 4 )
												this.ownerCt.show();
										}',
										 'beforequery' => 'function(queryEvent) {
											combo = queryEvent.combo;

											combo.store.removeAll();
											BrowserPrint.getLocalDevices(function(printers) {
												if (printers != undefined) {
													for(var i = 0; i < printers.length; i++) {
														if (printers[i].deviceType == "printer") {
															pName = printers[i].name;
															combo.store.add([new Ext.data.Record({"val":pName,"name":pName})]);
														}
													}
												}
												combo.expand();
											}, undefined, "printer");
										 }'
										 
									)
								),
						)),
                        array(
                            'allowBlank' => false, //$_SESSION['region']['nick'] != 'ufa',
                            'displayField' => 'name',
                            'editable' => false,
                            'fieldLabel' => toUtf('Размер штрих-кода'),
                            'disabled' => false,
                            'hiddenName' => 'barcode_size',
                            'name' => 'barcode_size',
                            'options' => toUtf('[' . implode(",", $this->barcode_size_array) . ']'),
                            'valueField' => 'val',
                            'vfield' => 'value',
                            'width' => 100,
                            'xtype' => 'combo',
                        ),
                         array(
					        'allowBlank' => false, //$_SESSION['region']['nick'] != 'ufa',
					        'displayField' => 'name',
					        'editable' => false,
					        'fieldLabel' => toUtf('Количество копий печати'),
					        'disabled' => false,
					        'hiddenName' => 'ZebraPrintCount',
					        'name' => 'ZebraPrintCount',
                            'options' => toUtf('[' . implode(",", $this->ZebraPrintCount) . ']'),
					        'valueField' => 'val',
					        'vfield' => 'value',
					        'width' => 100,
					        'xtype' => 'combo'
						),
						array(
							'boxLabel' => toUtf(' ФИО пациента'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraFIO',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
		                        'check' => 'function(combo, rec) {
		                        	var ZebraDateOfBirth = Ext.getCmp("ZebraDateOfBirth");
		                        	if(rec)
		                        	{
		                        		ZebraDateOfBirth.setDisabled(false);
		                        	} else {
		                        		ZebraDateOfBirth.setDisabled(true);
		                        		ZebraDateOfBirth.setValue(false);
		                        	}
		                        }',
		                    )
						),
						array(
							'boxLabel' => toUtf(' Дата рождения пациента'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraDateOfBirth',
							'id' => 'ZebraDateOfBirth',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf(' Номер пробы'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraSampleNumber',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf(' Наименование службы'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraServicesName',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf(' Кем направлен'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraDirect_Name',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf(' Услуга'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'ZebraUsluga_Name',
							'id' 	=> 'ZebraUsluga_Name',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
					)
				),
				[
					'title' => toUtf('Настройка печати списка проб'),
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => [
						[
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Формат печати'),
							'disabled' => false,
							'hiddenName' => 'list_of_samples_print_method',
							'name' => 'list_of_samples_print_method',
							'options' => toUtf('[' . implode(',', $this->barcode_list_print_method) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						]
					]
				],
				[
					'title' => toUtf('Учет реактивов'),
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => [
						[
							'fieldLabel' => toUtf('Остаточный срок годности в днях'),
							'name' => 'reagents_GodnDate',
							'id' => 'reagents_GodnDate',
							'minValue' => 0,
							'size' => 10,
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'numberfield'
						]
					]
				]
			),
			'notice' => array(
				array(
					'title' => toUtf('Способы уведомления'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Система сообщений'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'evn_notify_is_message',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('СМС'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'evn_notify_is_sms',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('E-Mail'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'evn_notify_is_email',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Всплывающие сообщения'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_popup_message',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'id' => 'notice_evn',
					'title' => toUtf('Уведомления по классам событий для врача стационара'),
					//доступ: пользователь входит в группу «Пользователь МО», и
					// сотрудник, связанный с учётной записью, является врачом 
					// с открытым местом работы в отделении из группы отделений типа «Стационар»
					'hidden' => !($isLpu && havingGroup('LpuUser') && in_array('stac', $LpuUnitTypes)),
						//~ 'hidden' => !($isLpu && havingGroup('LpuUser') && in_array('stac', $data['session']['ARMList']) ),
					'xtype' => 'fieldset',
					'items' => $this->getNoticeEvnFields()
				),
				array(
					'title' => toUtf('Уведомления по пациентам (стационар)'),
					'hidden' => !($isLpu && havingGroup('LpuUser') && in_array('stac', $LpuUnitTypes)),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'boxLabel' => toUtf('Все пациенты в отделении'),
							'name' => 'evn_notify_person_group_type'
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 2,
							'boxLabel' => toUtf('Пациенты, для которых является лечащим врачом'),
							'name' => 'evn_notify_person_group_type'
						),
					)
				),
				array(
					'title' => toUtf('Уведомления по классам событий для врача поликлиники'),
					//Доступ: сотрудник, связанный с учётной записью, является врачом с открытым местом работы 
					//	 в отделении из группы отделений типа «Поликлиника» или «ФАП». 
					'hidden' => !( in_array('polka', $LpuUnitTypes) ||  in_array('fap', $LpuUnitTypes)
							),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Параклиническая услуга'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'EvnUslugaParPolka',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Телемедицинская услуга'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'EvnUslugaTelemed',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Включение пациента в регистр паллиативной помощи'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'PersonRegisterPalliat',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
					)
				),
				array(
					'title' => toUtf('Уведомления МСЭ'),
					'xtype' => 'fieldset',
					'hidden' => !$isLpu || !havingGroup('LpuUser') || 
						!(
							in_array('polka', $LpuUnitTypes) || in_array('fap', $LpuUnitTypes)
							|| in_array('vk', $MedServiceTypes)
						)
					,'items' => array(
						array(
							'boxLabel' => toUtf('Уведомлять об изменении направления на МСЭ'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'EvnPrescrMse',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Уведомлять об изменении протокола ВК'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'EvnPrescrVK',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'hidden' => !(in_array('polka', $LpuUnitTypes) || in_array('fap', $LpuUnitTypes))
						)
					)
				),
				array(
					'title' => toUtf('Уведомления по пациентам (поликлиника)'),
					//Доступ: сотрудник, связанный с учётной записью, является врачом с открытым местом работы 
					//	 в отделении из группы отделений типа «Поликлиника» или «ФАП». 
					'hidden' => !( in_array('polka', $LpuUnitTypes) ||  in_array('fap', $LpuUnitTypes)),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'boxLabel' => toUtf('Все пациенты, прикреплённые к участку'),
							'name' => 'evn_notify_person_polka_group_type'
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 2,
							'boxLabel' => toUtf('Пациенты, для которых является лечащим врачом'),
							'name' => 'evn_notify_person_polka_group_type'
						),
					)
				),
				//TAG: интерфейс в модели: флаг вывода конвертика
				array(
					'id' => 'system_notice',
					'title' => toUtf('Системные уведомления'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Выводить информ-панель сообщений'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_infopanel_message',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Системные сообщения'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_popup_info',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Системные предупреждения'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_popup_warning',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'id' => 'extra_notice',
					'title' => toUtf('Экстренные сообщения'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Получать экстренные сообщения'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_extra_message',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'id' => 'other_notice',
					'title' => toUtf('Прочие уведомления'),
					'hidden' => getRegionNick() == 'kz' || ( !isLpuAdmin() && !isSuperAdmin() && empty($this->sessionParams['isMedStatUser'])), //потому что пока всего одна опция в группе. Добавятся еще - можно будет убрать hidden
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => 'Создание запроса в ФСС, получение ответа из ФСС',
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'hidden' => getRegionNick() == 'kz' || ( !isLpuAdmin() && !isSuperAdmin() && empty($this->sessionParams['isMedStatUser'])),
							'name' => 'StickFSSData',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'id' => 'clinic_group',
					'title' => toUtf('Клиническая группа'),
					'hidden' => getRegionNick() != 'msk' || $this->getFirstResultFromQuery("
						select top 1 msf.MedStaffFact_id
						from v_MedStaffFact msf (nolock)
						inner join v_pmUserCache puc with (nolock) on puc.MedPersonal_id = msf.MedPersonal_id
						inner join v_PostMed pm (nolock) on pm.PostMed_id = msf.Post_id
						where pm.PostMed_Name like '%онколог%' and puc.PMUser_id = :pmUser_id
					", $data) == false,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => 'Получать уведомления о присвоении клинической группы пациенту',
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_clinic_group_change_msg',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'id' => 'perinatal_haemorrhage',
					'title' => toUtf('Акушерское кровотечение'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Получать сообщения'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_perinatal_haemorrhage',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				)
			),
			'print' => array(
				array(
					'title' => toUtf('Книга записи вызовов врачей на дом'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Формат печати'),
							'hiddenName' => 'home_vizit_journal_print_extension',
							'name' => 'home_vizit_journal_print_extension',
							'options' => toUtf('[' . implode(",", $this->home_vizit_journal_print_extension_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						)
					)
				),
				array(
					'title' => toUtf('XML-документы'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Формат печати'),
							'hiddenName' => 'evnxml_print_type',
							'name' => 'evnxml_print_type',
							'options' => toUtf('[' . implode(",", $this->evnxml_print_type_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						)
					)
				),
				array( //https://redmine.swan.perm.ru/issues/54256
					'title' => toUtf('Формат файлов печатных форм'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Формат файла'),
							'hiddenName' => 'file_format_type',
							'name' => 'file_format_type',
							'options' => toUtf('[' . implode(",", $this->file_format_type_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						)
					)
				),
				array(
					'title' => toUtf('Формат файлов'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items' => array(
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Справка о стоимости лечения'),
							'hideLabel' => ! in_array(getRegionNick(), array('perm', 'kz', 'ufa')),
							'hiddenName' => 'cost_print_extension',
							'hidden' => ! in_array(getRegionNick(), array('perm', 'kz', 'ufa')),
							'name' => 'cost_print_extension',
							'options' => toUtf('[' . implode(",", $this->cost_print_extension_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Форма 01-ФР'),
							'hiddenName' => 'register_f01_extension',
							'name' => 'register_f01_extension',
							'options' => toUtf('[' . implode(",", $this->register_f01_extension_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Форма 02-ФР'),
							'hiddenName' => 'register_f02_extension',
							'name' => 'register_f02_extension',
							'options' => toUtf('[' . implode(",", $this->register_f02_extension_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						),
						array(
							'displayField' => 'name',
							'editable' => false,
							'allowBlank' => false,
							'fieldLabel' => toUtf('Форма 03-ФР'),
							'hiddenName' => 'register_f03_extension',
							'name' => 'register_f03_extension',
							'options' => toUtf('[' . implode(",", $this->register_f03_extension_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'combo',
							'enabled' => 'false'
						)
					)
				),
				array(
					'title' => toUtf('Настройка печати регистратора платных услуг'),
					'hidden' => !havingGroup('DrivingCommissionReg'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items' => array(
						array(
							'boxLabel' => toUtf('Используется двусторонняя печать'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_driving_commission_twosidedprint',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
			),
			'costprint' => array(
				array(
					'labelWidth' => 200,
					'frame' => false,
					'title' => toUtf('Общее'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Следующий номер справки'),
							'name' => 'next_num',
							'readOnly' => true,
							'vfield' => 'value',
							'xtype' => 'textfield'
						)
					)
				)
			),
			'rmis' => array(
				array(
					'labelWidth' => 60,
					'frame' => false,
					'title' => toUtf('Данные для аутентификации'),
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Логин'),
							'name' => 'rmis_login',
							'vfield' => 'value',
							'xtype' => 'textfield',
							'width' => 200
						),
						array(
							'fieldLabel' => toUtf('Пароль'),
							'name' => 'rmis_password',
							'vfield' => 'value',
							'xtype' => 'textfield',
							'inputType' => 'password',
							'width' => 200
						)
					)
				)
			),
			'medpers' => array(
				/*array(
					'allowBlank' => false,
					'disabled' => $admin_field_disabled,
					'displayField' => 'name',
					'editable' => false,
					'fieldLabel' => 'Посещения',
					'hiddenName' => 'allowed_medpersonal_ev',
					'name' => 'allowed_medpersonal_ev',
					'options' => toUtf('[' . implode(",", $this->allowed_medpersonal_list) . ']'),
					'valueField' => 'val',
					'vfield' => 'value',
					'width' => 300,
					'xtype' => 'combo'
				),
				array(
					'allowBlank' => false,
					'disabled' => $admin_field_disabled,
					'displayField' => 'name',
					'editable' => false,
					'fieldLabel' => 'Движения в КВС',
					'hiddenName' => 'allowed_medpersonal_es',
					'name' => 'allowed_medpersonal_es',
					'options' => toUtf('[' . implode(",", $this->allowed_medpersonal_list) . ']'),
					'valueField' => 'val',
					'vfield' => 'value',
					'width' => 300,
					'xtype' => 'combo'
				),*/
				array(
					'id' => 'allowed_medpersonal_ev',
					'title' => toUtf('Посещения'),
					'xtype' => 'fieldset',
					'items' => $this->getVizitPostKindsFields($admin_field_disabled)
				),
				array(
					'id' => 'allowed_medpersonal_es',
					'title' => toUtf('Движения в КВС'),
					'xtype' => 'fieldset',
					'items' => $this->getSectionPostKindsFields($admin_field_disabled)
				)
			),
			'editorT9' => array(
				array(
					'title' => toUtf('Предиктивный ввод текста (Т9) в осмотрах'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'items' => array(
						array(
							'boxLabel' => toUtf('Включить Т9'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'enableT9',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				)
			),
			'EditIndividualPeriod' => array(
				array(
					'label' => toUtf('Период записи для регистраторов МО'),
					'xtype' => 'panel',
					'items' => array(
						array(
							'id' => 'IndividualPeriodGrid',
							'xtype' => 'grid',
							'paging' => false,
							'autoLoadData' => true,
							'border' => false,
							'height' => 440,
							'object' => 'LpuIndividualPeriod',
							'dataUrl' => '/?c=LpuIndividualPeriod&m=getIndividualPeriodList',
							'root' => 'data',
							'actions' => array(
								'action_add' => array(
									'menu' => array(
										array(
											'wnd' => 'swIndividualPeriodEditWindow',
											'text' => 'Место работы врача поликлиники',
											'action' => 'add',
											'menu_action' => 'MedStaffFact'
										),
										array(
											'wnd' => 'swIndividualPeriodEditWindow',
											'text' => 'Отделение стационара',
											'action' => 'add',
											'menu_action' => 'LpuSection'
										),
										array(
											'wnd' => 'swIndividualPeriodEditWindow',
											'text' => 'Служба',
											'action' => 'add',
											'menu_action' => 'MedService'
										),
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_edit' => array(
									'params' => array(
										'wnd' => 'swIndividualPeriodEditWindow',
										'action' => 'edit',
										'key' => 'IndividualPeriod_id',
										'params' => array(
											'IndividualPeriodType_id'
										)
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_view' => array(
									'params' => array(
										'wnd' => 'swIndividualPeriodEditWindow',
										'action' => 'view',
										'key' => 'IndividualPeriod_id'
									),
									'disabled' => true,
									'hidden' => true
								),
								'action_delete' => array(
									'params' => array(
										'url' => '/?c=LpuIndividualPeriod&m=deleteIndividualPeriod',
										'key' => 'IndividualPeriod_id'
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_print' => array(
									'params' => array(
										
										'key' => 'IndividualPeriod_id'
									),
									'disabled' => true,
									'hidden' => true
								)
							),
							'stringfields' => array(
								array('name' => 'IndividualPeriod_id', 'type' => 'int', 'header' => 'ID', 'hidden' => true, 'key' => true),
								array('name' => 'IndividualPeriodType_id', 'type' => 'int', 'hidden' => true),
								array('name' => 'IndividualPeriodType_Name', 'type' => 'string', 'header' => toUtf('Тип периода')),
								array('name' => 'IndividualPeriodObject_Name', 'type' => 'string', 'header' => toUtf('Объект периода'), 'autoexpand' => true ),
								array('name' => 'IndividualPeriod_value', 'width' => 135, 'type' => 'int', 'header' => toUtf('Период записи (дней)'))
							)
						)
					)
				)
			),
			'electronicqueue' => array(
				array(
					'title' => toUtf('Связь электронного табло\инфомата'),
					'xtype' => 'fieldset',
					'labelWidth' => 180,
					'id' => 'eq-settings-fieldset',
					'listeners' => array(
						'render' => 'function() { 
							Ext.QuickTips.init();
							Ext.QuickTips.register({
								target: \'eq-settings-fieldset\',
								text: \'Выбор способа определения перечня ЭО, информация о которых отображается на электронном табло/инфомате\',
								width: 400
							});
						 }'
					),
					'items' => array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'checked'=>true,
							'boxLabel' => toUtf('с электронной очередью'),
							'listeners' => array(
								'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
							),
							'name' => 'electronic_queue_direct_link'
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 2,
							'boxLabel' => toUtf('с кабинетом'),
							'listeners' => array(
								'check' => 'function() { Ext.getCmp("options_window").buttons[0].enable(); }'
							),
							'name' => 'electronic_queue_direct_link'
						)
					)
				)
			),
			'ecg' => array(
				array(
					'title' => toUtf('Настройки сервиса Easy ECG'),
					'labelWidth' => 150,
					'xtype' => 'fieldset',
					'items' => array(
						array(
							'fieldLabel' => toUtf('Адрес сервиса'),
							'width' => 300,
							'name' => 'ecg_server',
							'id' => 'ecg_server',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Порт'),
							'width' => 300,
							'name' => 'ecg_port',
							'id' => 'ecg_port',
							'vfield' => 'value',
							'xtype' => 'textfield'
						),
						array(
							'xtype' => 'button',
							'text' => toUtf('Проверка подключения'),
							'name' => 'ecg_button',
							'listeners' => array(
								'click' => 'function() { 
									Ext.getCmp("options_window").connectServiceECG();}'
							)
						)
					)
				)
			),
			'pregnantMonitor' =>array(
					array(
						'title' => toUtf('Печать'),
						'xtype' => 'fieldset',
						'hidden' => ($_SESSION['region']['nick'] != 'khak'),
						'items' => array(
							array(
								'xtype' => 'panel',
								'layout' => 'form',
								'labelWidth' => 170,
								'items' => array(
									array(
										'labelWidth' => 150,
										'title' => toUtf('Печать МСР на типографическом бланке'),
										'xtype' => 'fieldset',
										'items' => array(
											array(
												'allowDecimals' => true,
												'allowNegative' => true,
												'fieldLabel' => toUtf('Отступ сверху (мм)'),
												'name' => 'birth_certificate_print_topmargin',
												'vfield' => 'value',
												'xtype' => 'numberfield',
												'local' => true
											),
											array(
												'allowDecimals' => true,
												'allowNegative' => true,
												'fieldLabel' => toUtf('Отступ слева (мм)'),
												'name' => 'birth_certificate_print_leftmargin',
												'vfield' => 'value',
												'xtype' => 'numberfield',
												'local' => true
											)
										)
									)
								)
							)
						)
					)
				)
			);

		return $default_options;
	}

	/**
	 * Проходит по подпапкам папки css/themes
	 * Ищет css файлы с информацией о теме
	 * Генерирует массив для загрузки в комбобокс на клиенте
	 */
	function genThemeList()
	{
		$themesArray = Array();
		$theme_dir = 'css/themes/';
		if (is_dir($theme_dir)) {
			if ($dh = opendir($theme_dir)) {
				while (false !== ($dir = readdir($dh))) {
					if (is_dir($theme_dir.'/'.$dir)) {
						if (file_exists($theme_dir.'/'.$dir.'/xtheme.css')) {
							$fh = fopen($theme_dir.'/'.$dir.'/xtheme.css', "r");
							$header = fgets($fh); // читаем первую строку
							$header = substr($header, 2); // отбрасываем первые 2 символа, ибо это должен быть комментарий
							$header = trim($header);
							$themesArray[]='["'.$dir.'", "'.$header.'"]';
						}
					}
				}
				closedir($dh);
			}
		}
		return '['.Implode(",",$themesArray).']';
	}

	/**
	 * Получение полей для настройки оповещений о событиях
	 */
	function getNoticeEvnFields() {
		$this->load->model("Evn_model", "evnmodel");
		$notice_evn_class_list = $this->evnmodel->getAllowedEvnClassListForNotice();

		$fields = array();
		foreach($notice_evn_class_list as $evn_class) {
			$fields[] = array(
				'boxLabel' => toUtf($evn_class['EvnClass_Name']),
				'checked' => false,
				'disabled' => false,
				'hideLabel' => true,
				'name' => $evn_class['EvnClass_SysNick'],
				'vfield' => 'checked',
				'xtype' => 'checkbox'
			);
		}

		return $fields;
	}

	/**
	 * Получение полей для настройки Фильтрация мед.персонала в документах
	 */
	function getVizitPostKindsFields($admin_field_disabled = false) {
		$this->load->model("Post_model", "postmodel");
		$post_kind_list = $this->postmodel->getPostKinds();

		$fields = array();
		foreach($post_kind_list as $post_kind) {
			$fields[] = array(
				'boxLabel' => toUtf($post_kind['name']),
				'checked' => false,
				'disabled' => $admin_field_disabled,
				'hideLabel' => true,
				'name' => 'vizitpost'.$post_kind['id'],
				'vfield' => 'checked',
				'xtype' => 'checkbox'
			);
		}

		return $fields;
	}

	/**
	 * Получение полей для настройки Фильтрация мед.персонала в документах
	 */
	function getSectionPostKindsFields($admin_field_disabled = false) {
		$this->load->model("Post_model", "postmodel");
		$post_kind_list = $this->postmodel->getPostKinds();

		$fields = array();
		foreach($post_kind_list as $post_kind) {
			$fields[] = array(
				'boxLabel' => toUtf($post_kind['name']),
				'checked' => false,
				'disabled' => $admin_field_disabled,
				'hideLabel' => true,
				'name' => 'sectionpost'.$post_kind['id'],
				'vfield' => 'checked',
				'xtype' => 'checkbox'
			);
		}

		return $fields;
	}

	/**
	 * Заполнение времени работы служб НМП из настроек системы
	 */
	function fillNMPWorkTime() {
		$options = $this->globalOptions['globals'];
		$params = array(
			'nmp_monday_beg_time' => !empty($options['nmp_monday_beg_time'])?$options['nmp_monday_beg_time']:null,
			'nmp_monday_end_time' => !empty($options['nmp_monday_end_time'])?$options['nmp_monday_end_time']:null,
			'nmp_tuesday_beg_time' => !empty($options['nmp_tuesday_beg_time'])?$options['nmp_tuesday_beg_time']:null,
			'nmp_tuesday_end_time' => !empty($options['nmp_tuesday_end_time'])?$options['nmp_tuesday_end_time']:null,
			'nmp_wednesday_beg_time' => !empty($options['nmp_wednesday_beg_time'])?$options['nmp_wednesday_beg_time']:null,
			'nmp_wednesday_end_time' => !empty($options['nmp_wednesday_end_time'])?$options['nmp_wednesday_end_time']:null,
			'nmp_thursday_beg_time' => !empty($options['nmp_thursday_beg_time'])?$options['nmp_thursday_beg_time']:null,
			'nmp_thursday_end_time' => !empty($options['nmp_thursday_end_time'])?$options['nmp_thursday_end_time']:null,
			'nmp_friday_beg_time' => !empty($options['nmp_friday_beg_time'])?$options['nmp_friday_beg_time']:null,
			'nmp_friday_end_time' => !empty($options['nmp_friday_end_time'])?$options['nmp_friday_end_time']:null,
			'nmp_saturday_beg_time' => !empty($options['nmp_saturday_beg_time'])?$options['nmp_saturday_beg_time']:null,
			'nmp_saturday_end_time' => !empty($options['nmp_saturday_end_time'])?$options['nmp_saturday_end_time']:null,
			'nmp_sunday_beg_time' => !empty($options['nmp_sunday_beg_time'])?$options['nmp_sunday_beg_time']:null,
			'nmp_sunday_end_time' => !empty($options['nmp_sunday_end_time'])?$options['nmp_sunday_end_time']:null,
			'pmUser_id' => $this->sessionParams['pmuser_id']
		);

		$query = "
			set nocount on
			declare
				@LpuHMPWorkTime_MoFrom time = :nmp_monday_beg_time,
				@LpuHMPWorkTime_MoTo time = :nmp_monday_end_time,
				@LpuHMPWorkTime_TuFrom time = :nmp_tuesday_beg_time,
				@LpuHMPWorkTime_TuTo time = :nmp_tuesday_end_time,
				@LpuHMPWorkTime_WeFrom time = :nmp_wednesday_beg_time,
				@LpuHMPWorkTime_WeTo time = :nmp_wednesday_end_time,
				@LpuHMPWorkTime_ThFrom time = :nmp_thursday_beg_time,
				@LpuHMPWorkTime_ThTo time = :nmp_thursday_end_time,
				@LpuHMPWorkTime_FrFrom time = :nmp_friday_beg_time,
				@LpuHMPWorkTime_FrTo time = :nmp_friday_end_time,
				@LpuHMPWorkTime_SaFrom time = :nmp_saturday_beg_time,
				@LpuHMPWorkTime_SaTo time = :nmp_saturday_end_time,
				@LpuHMPWorkTime_SuFrom time = :nmp_sunday_beg_time,
				@LpuHMPWorkTime_SuTo time = :nmp_sunday_end_time,
				@pmUser_id bigint = :pmUser_id,
				@Error_Code bigint,
				@Error_Message varchar(4000)

			begin try
				declare 
					@Lpu_id bigint,
					@MedService_id bigint,
					@LpuHMPWorkTime_id bigint

				declare
					med_service_cursor cursor read_only for
				select
					MS.Lpu_id,
					MS.MedService_id,
					WT.LpuHMPWorkTime_id
				from 
					v_MedService MS with(nolock)
					inner join v_MedServiceType MST with(nolock) on MST.MedServiceType_id = MS.MedServiceType_id
					left join v_LpuHMPWorkTime WT with(nolock) on WT.MedService_id = MS.MedService_id
				where 
					MST.MedServiceType_SysNick = 'slneotl'

				open med_service_cursor
				fetch next from med_service_cursor into @Lpu_id, @MedService_id, @LpuHMPWorkTime_id

				while @@FETCH_STATUS = 0 and @Error_Code is null
				begin
					if @LpuHMPWorkTime_id is null 
					begin
						exec p_LpuHMPWorkTime_ins
						@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
						@Lpu_id = @Lpu_id,
						@MedService_id = @MedService_id,
						@LpuHMPWorkTime_MoFrom = @LpuHMPWorkTime_MoFrom,
						@LpuHMPWorkTime_MoTo = @LpuHMPWorkTime_MoTo,
						@LpuHMPWorkTime_TuFrom = @LpuHMPWorkTime_TuFrom,
						@LpuHMPWorkTime_TuTo = @LpuHMPWorkTime_TuTo,
						@LpuHMPWorkTime_WeFrom = @LpuHMPWorkTime_WeFrom,
						@LpuHMPWorkTime_WeTo = @LpuHMPWorkTime_WeTo,
						@LpuHMPWorkTime_ThFrom = @LpuHMPWorkTime_ThFrom,
						@LpuHMPWorkTime_ThTo = @LpuHMPWorkTime_ThTo,
						@LpuHMPWorkTime_FrFrom = @LpuHMPWorkTime_FrFrom,
						@LpuHMPWorkTime_FrTo = @LpuHMPWorkTime_FrTo,
						@LpuHMPWorkTime_SaFrom = @LpuHMPWorkTime_SaFrom,
						@LpuHMPWorkTime_SaTo = @LpuHMPWorkTime_SaTo,
						@LpuHMPWorkTime_SuFrom = @LpuHMPWorkTime_SuFrom,
						@LpuHMPWorkTime_SuTo = @LpuHMPWorkTime_SuTo,
						@pmUser_id = @pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					end
					else
					begin
						exec p_LpuHMPWorkTime_upd
						@LpuHMPWorkTime_id = @LpuHMPWorkTime_id output,
						@Lpu_id = @Lpu_id,
						@MedService_id = @MedService_id,
						@LpuHMPWorkTime_MoFrom = @LpuHMPWorkTime_MoFrom,
						@LpuHMPWorkTime_MoTo = @LpuHMPWorkTime_MoTo,
						@LpuHMPWorkTime_TuFrom = @LpuHMPWorkTime_TuFrom,
						@LpuHMPWorkTime_TuTo = @LpuHMPWorkTime_TuTo,
						@LpuHMPWorkTime_WeFrom = @LpuHMPWorkTime_WeFrom,
						@LpuHMPWorkTime_WeTo = @LpuHMPWorkTime_WeTo,
						@LpuHMPWorkTime_ThFrom = @LpuHMPWorkTime_ThFrom,
						@LpuHMPWorkTime_ThTo = @LpuHMPWorkTime_ThTo,
						@LpuHMPWorkTime_FrFrom = @LpuHMPWorkTime_FrFrom,
						@LpuHMPWorkTime_FrTo = @LpuHMPWorkTime_FrTo,
						@LpuHMPWorkTime_SaFrom = @LpuHMPWorkTime_SaFrom,
						@LpuHMPWorkTime_SaTo = @LpuHMPWorkTime_SaTo,
						@LpuHMPWorkTime_SuFrom = @LpuHMPWorkTime_SuFrom,
						@LpuHMPWorkTime_SuTo = @LpuHMPWorkTime_SuTo,
						@pmUser_id = @pmUser_id,
						@Error_Code = @Error_Code output,
						@Error_Message = @Error_Message output
					end

					fetch next from med_service_cursor into @Lpu_id, @MedService_id, @LpuHMPWorkTime_id
				end

				close med_service_cursor
				deallocate med_service_cursor
			end try

			begin catch
				set @Error_Code = error_number()
				set @Error_Message = error_message()
				close med_service_cursor
				deallocate med_service_cursor
			end catch

			set nocount off
			select @Error_Code as Error_Code, @Error_Message as Error_Msg
		";

		$resp = $this->queryResult($query, $params);
		if (!is_array($resp)) {
			$this->createError('','Ошибка при заполнении времени работы кабинетов НМП');
		}
		return $resp;
	}

	/**
	 * Проверка наличия МО в списке МО, имеющих доступ к индивидуальной настройке периодов записи
	 */
	function checkLpuIndividualPeriod($Lpu_id) {
		$query = "
			select top 1 Lpu_id
			from v_LpuIndividualPeriod
			where Lpu_id = :Lpu_id
		";
		$response = $this->getFirstRowFromQuery($query, array('Lpu_id' => $Lpu_id));
		return !empty($response);
	}

	/**
	 * Проверка доступа к настройке T9
	 */
	function checkAllowT9($session) {
		if(!isset($session['Lpu_id']) or !isset($session['session']['groups'])) return false;
		
		$groups = explode('|',$session['session']['groups']);
		if(count($groups)==0) return false;
		$groups = implode("','",$groups);
		$params = array('Lpu_id'=>$session['Lpu_id']);
		$sql = "
			select
				count(*)
			from AccessRightsT9Limit t9 with (nolock) 
				left join pmUserCacheGroup ug with (nolock) on ug.pmUserCacheGroup_id = t9.pmUserCacheGroup_id
				left join v_Lpu L with (nolock) on L.Org_id=t9.Org_id and L.Lpu_id = :Lpu_id
			where ug.pmUserCacheGroup_SysNick in ('".$groups."')
		";
		
		$cnt = $this->getFirstResultFromQuery($sql, $params);
		return $cnt>0;
	}

	function getDataStorageValueByName($data)
	{
		return $this->getFirstResultFromQuery("
			select top 1
				DataStorage_Value
			from v_DataStorage DS with (nolock)
			where DS.DataStorage_Name = :DataStorage_Name
		", $data);
	}
}

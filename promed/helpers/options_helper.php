<?php
/**
* Options_helper - хелпер для работы с настройками
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version      ?
*/
defined('BASEPATH') or die ('No direct script access allowed');

/**
 * Получения настроек для журнала событий
 */
function getEvnNoticeOptions() {
	$settings = array(
		'allowed_evn_class_arr' => array(
			'EvnDoctor',
			'EvnUslugaCommon',
			'EvnUslugaOper',
			'EvnUslugaPar',
			'EvnPS',
			'EvnSection',
			'EvnDie',
			'EvnLeave',
			'EvnOtherLpu',
			'EvnOtherSection',
			'EvnOtherStac',
			'EvnDirection',
			'StickFSSData',
            'EvnReanimatPeriod'  //BOB - 10.04.2017
		),
		// Базовые статусы из базового класса событий Evn
		'base_status_list' => array(
			'ins' => 'Создано',
			'set' => 'Выполнено',
			'dis' => 'Завершено',
			'did' => 'Выполнено',
			'sign' => 'Подписано'
		),
		//Специфичные статусы по событиям и подписи к базовым статусам
		'specific_evn_status_list' => array(
			'EvnDoctor' => array(
				'set' => 'Врач назначен'
			),
			'EvnPS' => array(
				'set' => 'Поступление',
				'dis' => 'Исход'
			),
			'EvnSection' => array(
				'set' => 'Поступление',
				'dis' => 'Выписка'
			),
			'EvnDie' => array(
				'set' => 'Установлено',
				'exp' => 'Экспертиза'
			),
			'EvnDirection' => array(
				'set' => null,
				'dis' => null,
				'did' => null,
				'sign' => null,
				//'conf' => 'Подтверждено',
				'fail' => 'Отменено'
			),
            'EvnReanimatPeriod' => array(     //BOB - 10.04.2017
				'set' => 'Начало',
				'dis' => 'Конец'
            )
		)
	);

	//Полный список статусов по событиям
	$settings['full_evn_status_list'] = array();
	foreach($settings['allowed_evn_class_arr'] as $EvnClass_SysNick) {
		if (!empty($settings['specific_evn_status_list'][$EvnClass_SysNick])) {
			$settings['full_evn_status_list'][$EvnClass_SysNick] = array_merge(
				$settings['base_status_list'],
				$settings['specific_evn_status_list'][$EvnClass_SysNick]
			);
		} else {
			$settings['full_evn_status_list'][$EvnClass_SysNick] = $settings['base_status_list'];
		}
		foreach($settings['full_evn_status_list'][$EvnClass_SysNick] as $key => $value) {
			if (empty($value)) {
				unset($settings['full_evn_status_list'][$EvnClass_SysNick][$key]);
			}
		}
	}

	return $settings;
}

/**
 * Получения настроек для Фильтрации мед.персонала в документе
 */
function getPostKindOptions() {

	$CI = & get_instance();
	$CI->load->model('Post_model');
	$postKinds = $CI->Post_model->getPostKinds();
	$vizit_post_kind_arr = array();
	$section_post_kind_arr = array();
	foreach ($postKinds as $postKind) {
		array_push($vizit_post_kind_arr,'vizitpost'.$postKind['id']);
		array_push($section_post_kind_arr,'sectionpost'.$postKind['id']);
	}
	$settings = array(
		'vizit_post_kind_arr' => $vizit_post_kind_arr,
		'section_post_kind_arr' => $section_post_kind_arr
	);

	return $settings;
}

/**
 *	Получение списка настроек из LDAP
 */
function getBaseOptions( $db_options = array() )
{
	$CI = & get_instance();
	// Настройки по умолчанию
	$default_options = array(
		'polka' => array(
			 'attach_if_year_expires' => false
			,'check_person_birthday' => true
			,'disp_reg_to_disp_reg' => false
			,'double_vizit_control' => 2 // Предупреждение
			,'first_detected_diag_control' => true
			,'is_finish_result_block' => true
			,'prehosp_trauma_control' => true
			,'arm_vizit_create' => (in_array($_SESSION['region']['nick'], array('astra', 'ufa')) ? 1 : 2)
			,'arm_evn_xml_copy' => 1
			,'print_format' => 1
			,'print_two_side' => 2
			,'print_single_list' => 0
			,'medsvidgrant_add' => 0
			//,'evnstickblank_access' => 0
			,'next_card_code' => 1
			,'evnpl_numcard_next_num' => 0
			,'evnplstom_numcard_next_num' => 0
			,'vizit_print_paper_format' => 1
			,'vizit_print_paper_orient' => 2
			,'vizit_print_margin_bottom' => 10
			,'vizit_print_margin_left' => 10
			,'vizit_print_margin_right' => 10
			,'vizit_print_margin_top' => 10
			,'vizit_print_font_size' => 8
			,'evnvizitpl_profile_medspecoms_check' => 0
			//,'enable_is_doctor_filter' => 0
			,'allow_access_to_the_functionality_card_store' => (in_array($_SESSION['region']['nick'], array('perm', 'krym')) ? true : false)
		),
		'dispprof' => array(
			'do_not_show_unchecked_research' => false
		),
		'address' => array(
			'specobject_show' => false
		),
		'recepts' => array(
			'block_drug_extracting' => true,
			//'evn_recept_blank_serial' => 57,
			'evn_recept_fed_ser' => '02-10',
			'evn_recept_reg_ser' => 'PP-',
			'evn_recept_fed_num_min' => null,
			'evn_recept_fed_num_max' => null,
			'evn_recept_reg_num' => 0,
			'blank_form_creation_method' => 1,
			//'org_farmacy_is_foreign_warning' => true,
			'print_format' => 1,
            'copies_count' => 2,
			'print_extension' => 1,
			'validate_drug_ostat' => true,
			'validate_end_of_lgot' => true,
			'validate_start_date' => true,
			'unique_ser_num' => true
		),
		'appearance' => array(
			'user_theme' => 'blue',
			'menu_type' => 'ribbon', // https://redmine.swan.perm.ru/issues/20948
			'language' => 'ru',
			'taskbar_enabled' => false
		),
		'stac' => array(
			'evnps_numcard_prefix' => '',
			'evnps_numcard_postfix' => '',
			'evnps_numcard_next_num' => 0,
			'isstat' => 2,
			'oper_usluga_full_med_personal_list' => false,
			'disable_patient_additions_for_profile_branches' => false,
			'evnps_print_format' => 1,
			'evnsection_profile_medspecoms_check' => 0,
			//'is_required_evnps_diag_pid' => true,
			'band_font_width'   => 45,
			'band_font_height'  => 35,
			'band_margin_left'   => 1200,
			'band_margin_bottom'=> 20,
			'band_barcode_height'=> 150,
			'band_barcode_size' => 4,
			'band_barcode_margin_bottom' => 100,
			'band_text_width'   => 1100,
			'band_fio'          => false,
			'band_birthday'     => false,
			'band_lpu_nick'     => true,
			'band_numcard'      => true,
			'band_printer'      => null,
			'band_lpusection'   => false,
			'band_printer_model'=> null,
			'stac_schedule_auto_create'=> false,
			'stac_schedule_time_binding'=> ($_SESSION['region']['nick'] == 'msk') ? 1 : 2,
			'stac_schedule_priority_duration'=> null
		),
		'registry' => array(
			'check_person_error' => 1,
			'check_access_reform' => 0,
			'export_personcardcode_instead_of_evnplnumcard' => 0,
			'registry_evn_sort_order_stac' => 1,
			'registry_evn_sort_order_polka' => 1,
			'registry_evn_sort_order_smp' => 1,
			'registry_evn_sort_order_dvn' => 1,
			'registry_evn_sort_order_dds' => 1,
			'registry_evn_sort_order_htm' => 1,
			'registry_evn_sort_order_prof' => 1,
		),
		'evnstick' => array(
			'evnstick_print_topmargin' => 0,
			'evnstick_print_leftmargin' => 0,
			'enable_sign_evnstick_auth_person' => false
		),
		'medsvid' => array(
			'medsvid_ser' => ($_SESSION['region']['nick']=='khak')?95:0,
			//,'medsvid_num' => 0
			'deathmedsvid_address_type' => 1,
			'medsvid_print_topmargin' => 0,
			'medsvid_print_leftmargin' => 0,
		),
		'usluga' => array(
			'enable_usluga_section_load' => false,
			'enable_usluga_section_load_filter' => false,
			'allowed_usluga' => 'all'
		),
		'glossary' => array(
			'enable_glossary' => false,
			'enable_base_glossary' => false,
			'enable_pers_glossary' => false,
			'use_glossary_tag' => 2
		),
		'drugpurchase' => array(
			'drugpurchase_rules_formation_lots' => 1,
			'drugpurchase_grouping' => 'atc',
			'drugpurchase_atc_code_count_symbols' => 3,
			'drugpurchase_narc_psych_drugs' => false,
			'drugpurchase_each_drug_listed_tradename' => false,
			'drugpurchase_used_to_solve_vk' => false,
			'drugpurchase_sum_than' => false,
			'drugpurchase_sum_than_value' => 0,
			'drugpurchase_auto_reconfig_uot' => false,
			'drugpurchase_inc_in_uot_tradename_when_used_in_request' => false,
			'drugpurchase_select_uot_with_single_producer' => false,
			'drugpurchase_requirements_for_signing_uots' => false,
			'drugpurchase_allow_signing_uots' => false
		),
		'drugcontrol' => array(
			'drugcontrol_module' => (empty($_SESSION['lpu_id']) ? 2 : 1),
            'suppliers_ostat_control' => false,
            'doc_uc_operation_control' => false,
            'doc_uc_different_goods_unit_control' => (!empty($_SESSION['lpu_id']) && $_SESSION['region']['nick'] != 'kz')
		),
		'others' => array(
			'enable_localdb' => true,
			'enable_uecreader' => false,
			'uecreader_interval' => 5000,
			'enable_bdzreader' => false,
			'bdzreader_interval' => 3000,
			'enable_barcodereader' => false,
			'barcodereader_interval' => 1000,
			'barcodereader_port' => '',
			'ban_on_absences' => false,
			'allow_queue' => false,
			'allow_queue_auto' => false,
			'doc_readcardtype' => 'authapi',
			'doc_signtype' => 'cryptopro',
			'demo_server' => false,
			'checkEvnDirectionDate' => false,
			'notifyAboutLastAuth' => false,
			'onDigipacsViewer' => false,
			'digiPacsAddress' => '',
			'rir_login' => '',
			'rir_pass' => ''
		),
		'homevizit' => array(
			'homevizit_isallowed' => false,
			'homevizit_begtime' => '',
			'homevizit_endtime' => '',
			'homevizit_spec_isallowed' => false,
			'homevizit_nmp_phone' => 'XXX-XX-XX',
			'homevizit_smp_phone' => '103',
			'homevizit_phone' => '',
		),
		'emk' => array(
			'disable_structured_params_auto_show' => false,
			'version_for_visually_impaired' => false
		),
		'prescription' => array(
			'enable_show_service_code' => true,
			'service_name_show_type' => 1,
			'enable_show_service_nick' => false,
			'enable_grouping_by_gost2011' => false,
			'default_service_search_form_type' => 1,
		),
		'lis' => array(
			'barcode_format' => 128
			,'asmlo_server' => $CI->config->item('asmlo_server')
			,'asmlo_login' => $CI->config->item('asmlo_login')
			,'asmlo_password' => $CI->config->item('asmlo_password')
			,'direction_print_form' => 1
			,'labsample_barcode_width' => 40
			,'labsample_barcode_height' => 25
			,'labsample_barcode_margin_right' => 5
			,'labsample_barcode_margin_left' => 5
			,'labsample_barcode_margin_top' => 0
			,'labsample_barcode_margin_bottom' => 0
			,'PrintResearchDirections' => false
			, 'PrintMnemonikaDirections' => false
			,'list_of_samples_print_method' => 1
			,'use_postgresql_lis' => $CI->usePostgre ? true : false
                        /*
                         * https://90.150.189.84/issues/90247
                         */
                        ,'barcode_print_method' => null
                        ,'barcode_size' => 3050
                        ,'ZebraPrintCount' => 1
                        ,'ZebraFIO' => false
                        ,'ZebraSampleNumber'=>false
                        ,'ZebraLpuBuldingName'=>false
                        ,'ZebraServicesName'=>false
                        ,'ZebraDirect_Name'=>false
                        ,'ZebraUsluga_Name'=>false
						,'ZebraDateOfBirth'=>false
						,'barcode_printer' => null
                         /**
                          * end #90247
                          */
			,'PrintResearchCovid' => false
			,'reagents_GodnDate'=>1
		),
		'notice' => array(
			'evn_notify_is_message' => 0
			,'evn_notify_is_sms' => 0
			,'evn_notify_is_email' => 0
			,'evn_notify_person_group_type' => 1
			,'evn_notify_person_polka_group_type' => 2
			,'is_infopanel_message' => 0 //отображение "конвертика"
			,'is_popup_message' => 0 // TODO: пока скрыл т.к. у пользователей стали валится сообщения с 2014 года
			,'is_popup_info' => 1
			,'is_popup_warning' => 1
			,'popup_delay' => 8 	//Время отображение всплывающих сообщений в секундах
			,'is_extra_message' => 1
			,'EvnUslugaTelemed' => 1
			,'EvnUslugaParPolka' => 1
			,'PersonRegisterPalliat' => 0
			,'EvnPrescrMse' => 1
			,'EvnPrescrVK' => 1
			,'is_perinatal_haemorrhage' => 0
			,'is_clinic_group_change_msg' => 0
		),
		'export_tfoms' => array(
			'tfoms_export_time' => ''
			,'is_need_tfoms_export' => false
		),
		'print' => array( //https://redmine.swan.perm.ru/issues/54256
			'cost_print_extension' => 1,
			'home_vizit_journal_print_extension' => 1,
			'evnxml_print_type' => 2,
			'file_format_type' => 1,
			'is_driving_commission_twosidedprint' => true
		),
		'rmis' => array(
			'rmis_login' => null,
			'rmis_password' => null
		),
		'medpers' => array(
			'allowed_medpersonal_ev' => '1,6',
			'allowed_medpersonal_es' => '1,6',
		),
		'editorT9' => array(
			'enableT9' => 1
		),
		'electronicqueue' => array(
			'electronic_queue_direct_link' => 1
		),
		'ecg' => array(
			'ecg_server' =>'localhost',
			'ecg_port' => '2223'
		),
		'pregnantMonitor' => array(
			'birth_certificate_print_topmargin' => 0,
			'birth_certificate_print_leftmargin' => 0,
		)
	);
	// Настройки оповещений о событиях
	$settings = getEvnNoticeOptions();
	$notice_evn_class_list = $settings['allowed_evn_class_arr'];
	foreach($notice_evn_class_list as $EvnClass_SysNick) {
		$default_options['notice'][$EvnClass_SysNick] = 0;
	}

	// getRegionOptions определяем в региональных хэлперах, дабы некоторые значения по-умолчанию можно было для разных регионов указывать свои
	if ( function_exists('getRegionOptions') ) {
		mergeArraysRecursive($default_options, getRegionOptions());
	}

	if ( !isset($_SESSION['login']) )
	{
		return $default_options;
	}
	
	if (empty($_SESSION['settings'])) {
		$user = pmAuthUser::find($_SESSION['login']);
		$_SESSION['settings'] = $user->settings;
	}
	else {
		$user = NULL;
		//$_SESSION['settings'] = ''; с этой строкой загружаются настройки пользователя по умолчанию
	}
	if ( isset($_SESSION['settings']) && $_SESSION['settings'] != '{}' ) {
		$opt = @unserialize($_SESSION['settings']);
	} else {
		$opt = NULL;
	}
	// Объединяем два массива (настройки по умолчанию и то что сохранено на пользователе)
	if ( $opt )
	{
		foreach ( $default_options as $key => $value)
		{
			if (!array_key_exists($key, $opt))
			{
				$opt[$key] = array();
			}
			foreach ( $value as $key_temp => $value_temp )
			{
				if (!array_key_exists($key_temp, $opt[$key]))
				{
					$opt[$key][$key_temp] = $value_temp;
				}
			}
		}
	}
	else
	{
		$opt = $default_options;
	}
	// Присоединяем $db_options, если передан
	$opt = array_merge_recursive_distinct($opt, $db_options);
	if (!empty($opt['others']['doc_signtype']) && $opt['others']['doc_signtype'] == 'authapplet') {
		$opt['others']['doc_signtype'] = 'cryptopro'; // тут по умолчанию стал КриптоПро
	}
	if (!empty($opt['others']['doc_readcardtype']) && $opt['others']['doc_readcardtype'] == 'authapplet') {
		$opt['others']['doc_readcardtype'] = 'authapi'; // тут по умолчанию стал AuthApi
	}
	
	// todo: Все настройки ниже до конца функции (globals) нужны только для getGlobalOptions и по идее их можно перенести отсюда туда.
	$superadmin = false;
	if ($user) {
		if ( $user->havingGroup('SuperAdmin') )
			$superadmin = true;
	}
	else {
		$groups = explode('|', $_SESSION['groups']);
		if ( in_array('SuperAdmin', $groups) )
			$superadmin = true;
	}
	$opt["globals"]["superadmin"] = $superadmin;
	$opt["globals"]["lpu"] = array();

	if (!isset($_SESSION['lpus'])) {
		$CI->load->model('Org_model', 'Org_model');
		if (empty($user)) {
			$user = pmAuthUser::find($_SESSION['login']);
		}
		$lpuArr = array();
		foreach ( $user->org as $key => $value ) {
			$lpuArr[] = $CI->Org_model->getLpuOnOrg(array('Org_id' => $value['org_id']));
		}
		$_SESSION['lpus'] = implode('|',$lpuArr);
	}

	$opt["globals"]["lpu"] = explode('|', $_SESSION['lpus']);

	$opt["globals"]["lpu_id"] = $_SESSION['lpu_id'];
	if (isset($_SESSION['OrgFarmacy_id'])) {
		$opt["globals"]["OrgFarmacy_id"] = $_SESSION['OrgFarmacy_id'];
	}
	$opt["globals"]["usePostgre"] = $CI->usePostgre;
	$opt["globals"]["isOnko"] = isOnko();
	$opt["globals"]["isRA"] = isRA();
	$opt["globals"]["isPsih"] = isPsih();
	$opt["globals"]["isMinZdrav"] = isMinZdrav();
	$opt["globals"]["isMinZdravOrNotLpu"] = isMinZdravOrNotLpu();
	$opt["globals"]["isOnkoGem"] = isOnkoGem();
	$opt["globals"]["isFarmacy"] = isFarmacy();
	$opt["globals"]["medsvidgrant_add"] = $_SESSION['medsvidgrant_add'];
	$opt["globals"]["isMedStatUser"] = !empty($_SESSION['isMedStatUser'])?$_SESSION['isMedStatUser']:'';
	$opt["globals"]["isPathoMorphoUser"] = !empty($_SESSION['isPathoMorphoUser'])?$_SESSION['isPathoMorphoUser']:'';
	$opt["globals"]["AisPolkaEvnPLsync"] = $CI->config->item('AisPolkaEvnPLsync');
	if (is_array($opt["globals"]["AisPolkaEvnPLsync"]) && count($opt["globals"]["AisPolkaEvnPLsync"])) {
		$opt["globals"]["AisPolkaEvnPLsync"]['lpu259list'] = array_merge($opt["globals"]["AisPolkaEvnPLsync"]['lpu259list'], $opt["globals"]["AisPolkaEvnPLsync"]['lpu255and259list']);
	}
	
	return $opt;
}

/**
 * Возврат настроек пользователя из ранее сохраненной сессии
 * @group - группа настроек
 * @name - наименование настройки
 * Если не передан ни один параметр - вернет все настройки
 */
function getOptions($group = '', $name = '') {
	$options = getBaseOptions();
	if (!empty($group)) {
		if (isset($options[$group])) {
			if (!empty($name)) {
				if (isset($options[$group][$name])) {
					return $options[$group][$name];
				}
			} else {
				return $options[$group];
			}
		}
	} elseif (!empty($name) && isset($options[$name])) {
		return $options[$name];
	} else {
		return $options;
	}
	return null;
}
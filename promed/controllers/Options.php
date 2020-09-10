<?php	defined('BASEPATH') or die ('No direct script access allowed');
/**
* Options - контроллер для управления настройками пользователя (загрузка, сохранение)
* Вынесено из контроллера dlo_ivp
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package			Common
* @access			public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author			Petukhov Ivan aka Lich (megatherion@list.ru)
* @originalauthor	Pshenitcyn Ivan aka IvP (ipshon@rambler.ru)
* @version			12.07.2009
 *
 * @property Options_model opmodel
*/

class Options extends swController {
	private $recept_diag_control_array = array(
		'["1", "Нет"]',
		'["2", "Да"]'
	);
	
	private $yesno_array = array(
		'["1", "Нет"]',
		'["2", "Да"]'
	);

	private $drug_spr_using_array = array(
		'["dbo", "Региональный справочник"]',
		'["rls", "Справочник медикаментов ГРЛС, ИМН, ДС"]'
	);

	private $dlo_logistics_system_array = array(
		'["level2", "Двухуровневая"]',
		'["level3", "Трехуровневая"]'
	);

	private $select_drug_from_list_array = array(
		'["jnvlp", "ЖНВЛП"]',
		'["request", "из заявки"]',
		'["allocation", "из разнарядки"]',
		/*'["request_and_allocation", "из заявки и разнарядки"]'*/
	);

	private $select_disp_control = array(
		'["1", "Отключен"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);

	private $select_eps_control = array(
		'["1", "Отключен"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);

	private $disp_med_staff_fact_group = array(
		'["1", "Все"]',
		'["2", "Только врачи и средний мед. персонал"]'
	);

	private $smp_call_time_format = array(
		'["1", "ЧЧ:ММ:СС"]',
		'["2", "ЧЧ:ММ"]'
	);

	private $recept_farmacy_type_array = array(
		'["all", "Все"]',
		'["mo_farmacy", "Аптеки, прикрепленные к МО"]'
	);

	private $kvs_check_array = array(
		'["1", "Нет"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет"]'
	);

	private $validation_control_array = array(
		'["no", "Нет"]',
		'["warning", "Предупреждение"]',
		'["deny", "Запрет"]'
	);

	private $misrb_transfer_type_array = array(
		'["0", "Отключено"]',
		// '["1", "Онлайн"]', // по ТЗ "пока недоступно для выбора"
		'["2", "Ежедневно"]',
		'["3", "Еженедельно"]'
	);

	private $days_array = array(
		'["1", "Понедельник"]',
		'["2", "Вторник"]',
		'["3", "Среда"]',
		'["4", "Четверг"]',
		'["5", "Пятница"]',
		'["6", "Суббота"]',
		'["7", "Воскресенье"]'
	);

	private $select_registry_disable_edit = array(
		'["1", "Отключен"]',
		'["2", "Включен"]',
		'["3", "Предупреждение"]'
	);

	private $recept_data_acc_method_array = array(
		'["1", "Признак обеспечения"]',
		'["2", "Выбор наименования отпущенного ЛП из справочника"]',
		'["3", "Выбор наименования отпущенного ЛП из остатков организации"]'
	);
	
	private $fill_citizenship_control = array(
		'["1", "Отключен"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);

	private $fill_snils_control = array(
		'["1", "Отключен"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);
	
	private $fill_inn_correctness_control = array(
		'["1", "Отключен"]',
		'["2", "Предупреждение"]',
		'["3", "Запрет сохранения"]'
	);

	private $rules_filling_doctors_workrelease_array = array(
		'["1", "Разрешить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника"]',
		'["2", "Запретить выбирать в поле «Врач 3» (председатель ВК) сотрудника, указанного в поле «Врач 1» и/или «Врач 2»"]',
		'["3", "Запретить выбирать в поле «Врач 1», «Врач 2», «Врач 3» одного сотрудника"]'
	);

	/**
	 * Глобальные настройки по умолчанию
	 */
	private $default_global_options = array();
	
	/**
	 * Настройки по умолчанию
	 */
	private $default_options = array();
	
	/**
	 * Признак работы под администратором
	 */
	private $admin_field_disabled = NULL;
	
	/**
	 * Дерево глобальных настроек
	 */
	private $global_options_tree = array();
	private $global_options_node = array();
	private $access_rights_node = array();
	
	private $moduleMethods = [
		'getNewOptions'
	];

	/**
	 * Конструктор
	 */
	function __construct()
	{
		parent::__construct();
		$this->init();
		$this->load->helper('Options');

		$this->global_options_node = array(
			'text' => toUtf('Общие настройки'),
			'id' => 'global_options_node',
			'leaf' => false,
			'children' => array(
				array(
					'text' => toUtf('Справочники'),
					'id' => 'reference',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('ЛЛО'),
					'id' => 'dlo',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Аптека ЛЛО'),
					'id' => 'farmacyllo',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => $_SESSION['region']['nick'] != 'saratov'
				),
				array(
					'text' => toUtf('Реестры'),
					'id' => 'registry',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => $_SESSION['region']['nick'] == 'saratov'
				),
				array(
					'text' => toUtf('Стационар'),
					'id' => 'stac',
					'leaf' => true,
					'cls' => 'file',
					//'hidden' => $_SESSION['region']['nick'] != 'saratov'
				),
				//gaf 07022018 #106655
				array(
					'text' => toUtf('Регистр беременных'),
					'id' => 'personpregnancy',
					'leaf' => true,
					'cls' => 'file',
				),				
				array(
					'text' => toUtf('Контроль на обязательность полей'),
					'id' => 'fields_control',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('ЛВН'),
					'id' => 'evnstick',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => getRegionNick() == 'kz'
				),
				array(
					'text' => toUtf('Идентификация'),
					'id' => 'identify',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Разное'),
					'id' => 'other',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Запись пациентов'),
					'id' => 'er',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Склад-Аптека'),
					'id' => 'farmacy',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('ЛИС'),
					'id' => 'lis',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => true
				),
				array(
					'text' => toUtf('Контроль корректности данных'),
					'id' => 'correct_data',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => $_SESSION['region']['nick'] != 'saratov'
				),
				array(
					'text' => toUtf('Включение в регистр по СЗЗ'),
					'id' => 'person_register_szz',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Уведомления'),
					'id' => 'notification',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Диспансерные карты пациентов'),
					'id' => 'disp',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Экспорт данных в ТФОМС'),
					'id' => 'export_tfoms',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => $_SESSION['region']['nick'] != 'buryatiya'
				),
				array(
					'text' => toUtf('Параметры безопасности паролей'),
					'id' => 'security',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('СМП'),
					'id' => 'smp',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('НМП'),
					'id' => 'nmp',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('МСЭ'),
					'id' => 'mse',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Авторизация'),
					'id' => 'auth',
					'leaf' => true,
					'cls' => 'file',
					'hidden' => $_SESSION['region']['nick'] == 'kz'
				)
			)
		);

		if (getRegionNick() == 'buryatiya') {
			$this->global_options_node['children'][] = array(
				'text' => toUtf('Интеграция с ИЭМК'),
				'id' => 'misrb',
				'leaf' => true,
				'cls' => 'file'
			);
		}

		$this->access_rights_node = array(
			'text' => toUtf('Ограничения прав доступа'),
			'id' => 'access_rights_node',
			'leaf' => false,
			'children' => array(
				array(
					'text' => toUtf('Льгота'),
					'id' => 'privilege',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Группа диагнозов'),
					'id' => 'diag_group',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Группа МО/Подразделений МО'),
					'id' => 'lpu_group',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('АРМ СМО/ТФОМС'),
					'id' => 'smo_arm',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Прикрепления'),
					'id' => 'attach',
					'leaf' => true,
					'cls' => 'file'
				),
				array(
					'text' => toUtf('Группа тестов'),
					'id' => 'test_group',
					'leaf' => true,
					'cls' => 'file'
				)
			)
		);

		if (getRegionNick() == 'vologda') {
				$this->access_rights_node['children'][] = array(
					'text' => toUtf('T9'),
					'id' => 't9_group',
					'leaf' => true,
					'cls' => 'file'
				);
		}

		$this->global_options_tree = array(
			$this->global_options_node,
			$this->access_rights_node,
		);

		$this->default_global_options = array(
			'reference' => array(
				array(
					'label' => toUtf('Справочники'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array
						(
							'boxLabel' => toUtf('Разрешить действия со справочником организаций только для группы «Администратор справочника организаций»'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'enable_action_reference_by_admref_group',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'fieldLabel' => toUtf('Контактная информация'),
							'name' => 'contact_info',
							'vfield' => 'value',
							'width' => 300,
							'xtype' => 'textfield'
						)
					)
				)
			),
			'dlo' => array(
				array(
					'label' => toUtf('Справочник медикаментов ЛЛО'),
					'type'	=> 'fieldset',
					'labelWidth'	=> 255,
					'items'	=> array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Использование справочника медикаментов'),
							'hiddenName' => 'drug_spr_using',
							'name' => 'drug_spr_using',
							'id' => 'OPT_drug_spr_using',
							'options' => toUtf('[' . implode(",", $this->drug_spr_using_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'listWidth' => 400,
							'width' => 220,
							'xtype' => 'combo',
							'listeners' => array(
								'select' => 'function(f) {
									var	drug_application_field = Ext.getCmp("OPT_check_excess_sum_fed").ownerCt;
									var dlo_logistics_system_field = Ext.getCmp("OPT_dlo_logistics_system").ownerCt;
									var recept_expert_field = Ext.getCmp("OPT_evn_recept_level_of_control").ownerCt.ownerCt.ownerCt;
									var recept_diag_control_field = Ext.getCmp("OPT_recept_diag_control");
									var select_drug_from_list_field = Ext.getCmp("OPT_select_drug_from_list");
									var recept_drug_ostat_viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var recept_drug_ostat_control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var recept_empty_drug_ostat_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");
									var recept_by_farmacy_drug_ostat_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var recept_farmacy_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var recept_by_ras_drug_ostat_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");	

									if (f.getValue() == \'dbo\') {
										drug_application_field.hide();
										dlo_logistics_system_field.hide();
										recept_expert_field.hide();
										recept_diag_control_field.hideContainer();
										select_drug_from_list_field.hideContainer();
										recept_drug_ostat_viewing_field.hide();
										recept_drug_ostat_control_field.hide();
										recept_empty_drug_ostat_allow_field.hide();
										recept_by_farmacy_drug_ostat_field.hide();
										recept_farmacy_type_field.hideContainer();
										recept_by_ras_drug_ostat_field.hide();
									} else {
										drug_application_field.show();
										dlo_logistics_system_field.show();
										if(getGlobalOptions().region.nick == "saratov")
											recept_expert_field.show();
										recept_diag_control_field.showContainer();
										select_drug_from_list_field.showContainer();
										recept_drug_ostat_viewing_field.show();
										recept_drug_ostat_control_field.show();
										recept_empty_drug_ostat_allow_field.show();
										recept_by_farmacy_drug_ostat_field.show();
										recept_farmacy_type_field.showContainer();
										recept_by_ras_drug_ostat_field.show();
									}
								}',
								'render' => 'function(f){
									var	drug_application_field = Ext.getCmp("OPT_check_excess_sum_fed").ownerCt;
									var dlo_logistics_system_field = Ext.getCmp("OPT_dlo_logistics_system").ownerCt;
									var recept_expert_field = Ext.getCmp("OPT_evn_recept_level_of_control").ownerCt.ownerCt.ownerCt;

									var recept_diag_control_field = Ext.getCmp("OPT_recept_diag_control");
									var select_drug_from_list_field = Ext.getCmp("OPT_select_drug_from_list");
									var recept_drug_ostat_viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var recept_drug_ostat_control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var recept_empty_drug_ostat_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");
									var recept_by_farmacy_drug_ostat_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var recept_farmacy_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var recept_by_ras_drug_ostat_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");	
									if (f.getValue() == \'dbo\') {
										drug_application_field.hide();
										dlo_logistics_system_field.hide();
										recept_expert_field.hide();
										recept_diag_control_field.hideContainer();
										select_drug_from_list_field.hideContainer();
										recept_drug_ostat_viewing_field.hide();
										recept_drug_ostat_control_field.hide();
										recept_empty_drug_ostat_allow_field.hide();
										recept_by_farmacy_drug_ostat_field.hide();
										recept_farmacy_type_field.showContainer();
										recept_by_ras_drug_ostat_field.hide();
									} else {
										drug_application_field.show();
										dlo_logistics_system_field.show();
										if(getGlobalOptions().region.nick == "saratov")
											recept_expert_field.show();
										recept_diag_control_field.showContainer();
										select_drug_from_list_field.showContainer();
										recept_drug_ostat_viewing_field.show();
										recept_drug_ostat_control_field.show();
										recept_empty_drug_ostat_allow_field.show();
										recept_by_farmacy_drug_ostat_field.show();
										recept_farmacy_type_field.showContainer();
										recept_by_ras_drug_ostat_field.show();
									}
								}'
							)
						)
					)
				),
				array(
					'label' => toUtf('Ведение льготных регистров'),
					'type' => 'fieldset',
					'labelWidth' => 290,
					'autoHeight' => true,
					'hidden' => $_SESSION['region']['nick'] == 'kz',
					'items' => array(
						array(
							'title' => toUtf('Добавление льготы'),
							'xtype' => 'fieldset',
							'labelWidth' => 290,
							'autoHeight' => true,
							'items' => array(
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('По запросу в ситуационный центр'),
									'name' => 'person_privilege_add_source'
								),
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('Пользователями'),
									'name' => 'person_privilege_add_source'
								)
							)
						),
						array(
							'boxLabel' => toUtf('Постмодерация'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'person_privilege_add_request_postmoderation',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Региональные льготы по нозологиям. Контроль на наличие диагноза обязателен'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'vzn_privilege_diag_available_checking',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Региональные льготы социальные и федеральные льготы. Контроль на наличие данных документа, подтверждающего наличие льгот обязателен'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'social_privilege_document_available_checking',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'label' => toUtf('Заявка на лекарственные средства'),
					'type' => 'fieldset',
					'labelWidth' => 290,
					'items' => array(
						array(
							'boxLabel' => toUtf('Контроль превышения суммы заявки федерального льготника'),
							'checked' => true,
							'id' => 'OPT_check_excess_sum_fed',
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_excess_sum_fed',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Контроль превышения суммы заявки регионального льготника'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_excess_sum_reg',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Принудительно удалять медикаменты при удалении пациента из заявки'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_remove_drug',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Создание ЛПУ новых заявок'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'is_create_drugrequest',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'type' => 'fieldset',
					'labelWidth' => 150,
					'label'	=> toUtf('Логистическая система'),
					'items' => array(
						array(
							'allowBlank' => false,
							'id' => 'OPT_dlo_logistics_system',
							'displayField' => 'name',
							'editable' => false,
							//'fieldLabel' => toUtf('Логистическая система'),
							'hideLabel' => true,
							'hiddenName' => 'dlo_logistics_system',
							'name' => 'dlo_logistics_system',
							'options' => toUtf('[' . implode(",", $this->dlo_logistics_system_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 300,
							'xtype' => 'combo',
							'listeners' => array(
								'render' => 'function(f) {
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");
									var drug_spr_using_field = Ext.getCmp("OPT_drug_spr_using");
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");
									if (f.getValue() == \'level3\' && drug_spr_using_field.getValue() == \'rls\') {
										br_ost_field.show();
									} else {
										br_ost_field.hide();
									}
								}',
								'select' => 'function(f) {
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");
									var drug_spr_using_field = Ext.getCmp("OPT_drug_spr_using");
									if (f.getValue() == \'level3\' && drug_spr_using_field.getValue() == \'rls\') {
										br_ost_field.show();

										if (e_allow_field.getValue()) {
											br_ost_field.disable();
											br_ost_field.setValue(true);
										} else {
											br_ost_field.enable();
										}
									} else {
										br_ost_field.hide();
										br_ost_field.setValue(false);
									}
								}'
							)
						)
					)
				),
				array(
					'label' => toUtf('Выписка льготных рецептов'),
					'type' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'boxLabel' => toUtf('Разрешить выписку рецептов в электронной форме'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'recept_electronic_allow',
							'name' => 'recept_electronic_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'hidden' => getRegionNick() == 'kz'
						),
						array(
                            'allowBlank' => false,
                            'displayField' => 'name',
                            'editable' => false,
                            'fieldLabel' => toUtf('Использовать нумератор для рецептов «на листе»'),
                            'hiddenName' => 'use_numerator_for_recept',
                            'name' => 'use_numerator_for_recept',
                            'options' => toUtf('[' . implode(",", $this->yesno_array) . ']'),
                            'valueField' => 'val',
                            'vfield' => 'value',
                            'width' => 215,
                            'xtype' => 'combo',
							'listeners' => array(
								'select' => 'function(f) {
									if (getRegionNick() == \'msk\') {
										var	use_external_service_for_recept_num_checkbox = Ext.getCmp("OPT_use_external_service_for_recept_num");
										
										if (f.getValue() == 1) {
											use_external_service_for_recept_num_checkbox.showContainer();
										}
										else {
											use_external_service_for_recept_num_checkbox.setValue(false);
											use_external_service_for_recept_num_checkbox.hideContainer();
										}
									}
								}',
								'render' => 'function(f) {
									f.fireEvent(\'select\', f);
								}',
							)
                        ),
						array(
							'boxLabel' => toUtf('Использовать внешний сервис для получения номеров рецептов'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'hidden' => true,
							'name' => 'use_external_service_for_recept_num',
							'id' => 'OPT_use_external_service_for_recept_num',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
                        array(
                            'allowBlank' => false,
                            'displayField' => 'name',
                            'editable' => false,
                            'fieldLabel' => toUtf('Выполнять контроль диагноза на соответствие льготе'),
                            'hiddenName' => 'recept_diag_control',
                            'name' => 'recept_diag_control',
                            'id' => 'OPT_recept_diag_control',
                            'options' => toUtf('[' . implode(",", $this->recept_diag_control_array) . ']'),
                            'valueField' => 'val',
                            'vfield' => 'value',
                            'width' => 200,
                            'xtype' => 'combo',
                            'listeners' => array(
								'render' => 'function(f) {
									if(getGlobalOptions().drug_spr_using == "dbo")
										f.hideContainer();
									else
										f.showContainer();
								}'
							)
                        ),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Выбор ЛП в рецепте выполняется из списка'),
							'hiddenName' => 'select_drug_from_list',
							'name' => 'select_drug_from_list',
							'id' => 'OPT_select_drug_from_list',
							'options' => toUtf('[' . implode(",", $this->select_drug_from_list_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo',
							'listeners' => array(
								'render' => 'function(f) {
									if(getGlobalOptions().drug_spr_using == "dbo")
										f.hideContainer();
									else
										f.showContainer();
								}'
							)
						),
						array(
							'boxLabel' => toUtf('Просмотр остатков'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'OPT_recept_drug_ostat_viewing',
							'name' => 'recept_drug_ostat_viewing',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'render' => 'function() {
									var	viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var	control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");

									var	bf_ost_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var	f_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");

									if (e_allow_field.getValue()) {
										control_field.setValue(true);
										control_field.setDisabled(true);
									}
									if (control_field.getValue()) {
										viewing_field.setValue(true);
										viewing_field.setDisabled(true);
									}

									if (viewing_field.getValue() || control_field.getValue() || e_allow_field.getValue())  {
										bf_ost_field.enable();
										if (bf_ost_field.checked) {
											f_type_field.enable();
										} else {
											f_type_field.disable();
										}
										br_ost_field.enable();
									} else {
										bf_ost_field.disable();
										f_type_field.disable();
										br_ost_field.disable();

										bf_ost_field.setValue(false);
										f_type_field.setValue("all");
										br_ost_field.setValue(false);
									}

									if (!br_ost_field.hidden) {
										if (e_allow_field.getValue()) {
											br_ost_field.disable();
											br_ost_field.setValue(true);
										} else {
											br_ost_field.enable();
										}
									} else {
										br_ost_field.setValue(false);
									}
								}',
								'check' => 'function(field, checked) {
									if (field.disabled) {
										return false;
									}

									var	viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var	control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");

									var	bf_ost_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var	f_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");

									if (viewing_field.getValue() || control_field.getValue() || e_allow_field.getValue())  {
										bf_ost_field.enable();
										if (bf_ost_field.checked) {
											f_type_field.enable();
										} else {
											f_type_field.disable();
										}
										br_ost_field.enable();
									} else {
										bf_ost_field.disable();
										f_type_field.disable();
										br_ost_field.disable();

										bf_ost_field.setValue(false);
										f_type_field.setValue("all");
										br_ost_field.setValue(false);
									}
								}'
							)
						),
						array(
							'boxLabel' => toUtf('Контроль остатков'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'OPT_recept_drug_ostat_control',
							'name' => 'recept_drug_ostat_control',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function(field, checked) {
									if (field.disabled) {
										return false;
									}

									var	viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var	control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");

									var	bf_ost_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var	f_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");

									if (checked) {
										viewing_field.setValue(checked);
										viewing_field.setDisabled(checked);
									} else {
										viewing_field.setDisabled(checked);
									}

									if (viewing_field.getValue() || control_field.getValue() || e_allow_field.getValue())  {
										bf_ost_field.enable();
										if (bf_ost_field.checked) {
											f_type_field.enable();
										} else {
											f_type_field.disable();
										}
										br_ost_field.enable();
									} else {
										bf_ost_field.disable();
										f_type_field.disable();
										br_ost_field.disable();

										bf_ost_field.setValue(false);
										f_type_field.setValue("all");
										br_ost_field.setValue(false);
									}
								}'
							)
						),
						array(
							'boxLabel' => toUtf('Разрешить выписку рецепта при нулевых остатках'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'OPT_recept_empty_drug_ostat_allow',
							'name' => 'recept_empty_drug_ostat_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function(field, checked) {
									if (field.disabled) {
										return false;
									}

									var	viewing_field = Ext.getCmp("OPT_recept_drug_ostat_viewing");
									var	control_field = Ext.getCmp("OPT_recept_drug_ostat_control");
									var	e_allow_field = Ext.getCmp("OPT_recept_empty_drug_ostat_allow");

									var	bf_ost_field = Ext.getCmp("OPT_recept_by_farmacy_drug_ostat");
									var	f_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									var	br_ost_field = Ext.getCmp("OPT_recept_by_ras_drug_ostat");

									if (viewing_field.getValue() || control_field.getValue() || e_allow_field.getValue())  {
										bf_ost_field.enable();
										if (bf_ost_field.checked) {
											f_type_field.enable();
										} else {
											f_type_field.disable();
										}
										br_ost_field.enable();
									} else {
										bf_ost_field.disable();
										f_type_field.disable();
										br_ost_field.disable();

										bf_ost_field.setValue(false);
										f_type_field.setValue("all");
										br_ost_field.setValue(false);
									}

									if (checked) {
										viewing_field.setValue(checked);
										viewing_field.setDisabled(checked);
									}
									if (checked) {
										control_field.setValue(checked);
										control_field.setDisabled(checked);
									} else {
										control_field.setDisabled(checked);
									}

									if (!br_ost_field.hidden) {
										if (checked) {
											br_ost_field.disable();
											br_ost_field.setValue(true);
										} else {
											br_ost_field.enable();
										}
									} else {
										br_ost_field.setValue(false);
									}
								}'
							)
						),
						array(
							'boxLabel' => toUtf('По остаткам аптек'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'OPT_recept_by_farmacy_drug_ostat',
							'name' => 'recept_by_farmacy_drug_ostat',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function(f) {
									var	f_type_field = Ext.getCmp("OPT_recept_farmacy_type");
									if (f.checked) {
										f_type_field.enable();
									} else {
										f_type_field.disable();
										f_type_field.setValue("all");
									}
								}'
							)
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => toUtf('Вид аптек'),
							'hiddenName' => 'recept_farmacy_type',
							'id' => 'OPT_recept_farmacy_type',
							'name' => 'recept_farmacy_type',
							'options' => toUtf('[' . implode(",", $this->recept_farmacy_type_array) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo',
							'listeners' => array(
								'render' => 'function(f) {
									if (f.getValue() == \'\') {
										f.setValue(\'all\');
									}
									if(getGlobalOptions().drug_spr_using == "dbo")
										f.hideContainer();
									else
										f.showContainer();
								}'
							)
						),
						array(
							'boxLabel' => toUtf('По остаткам РАС'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'OPT_recept_by_ras_drug_ostat',
							'name' => 'recept_by_ras_drug_ostat',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'label' => toUtf('Экспертиза рецептов'),
					'type' => 'fieldset',
					'hidden' => getRegionNick() != 'saratov',
					'labelWidth' => 150,
					'items' => array(
						array(
							'autoHeight' => true,
							'xtype' => 'panel',
							'layout' => 'form',
							'frame' => true,
							'items' => array(
								array(
									'title' => toUtf('Уровень контроля при приеме данных от Поставщика'),
									'autoHeight' => true,
									'xtype' => 'fieldset',
									'items' => array(
										array(
											'xtype' => 'radio',
											'id' => 'OPT_evn_recept_level_of_control',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 1,
											'boxLabel' => toUtf('Высокий – к экспертизе принимаются лишь рецепты, о выписке которых в системе есть сведения'),
											'name' => 'evn_recept_level_of_control'
										),
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 2,
											'boxLabel' => toUtf('Средний - к экспертизе могут быть приняты рецепты, выписанные на бланке, о выписке которых в системе нет сведений'),
											'name' => 'evn_recept_level_of_control'
										),
										array(
											'xtype' => 'radio',
											'vfield' => 'checked',
											'hideLabel' => true,
											'inputValue' => 3,
											'boxLabel' => toUtf('Низкий - к экспертизе могут быть приняты все рецепты'),
											'name' => 'evn_recept_level_of_control'
										)
									)
								)
							)
						)
					)
				),
				array(
					'label' => toUtf('Заявочная кампания'),
					'type' => 'fieldset',
					'labelWidth' => 150,
					'items' => array(
						array(
							'boxLabel' => toUtf('Разрешить редактирование цен на заявляемые ЛС в АРМ специалиста ЛЛО ОУЗ'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'llo_price_edit_enabled',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				)
			),
			'farmacyllo' => array(
				array(
					'label' => toUtf('Аптека ЛЛО'),
					'type' => 'fieldset',
					'hidden'=> false,
					'items' => array(
						array(
							'boxLabel' => toUtf('Поставщики ЛС работают в системе'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'is_distributors_in_system',
							'name' => 'is_distributors_in_system',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'check' => 'function(field) {
									if (field.checked) {
										Ext.getCmp("is_farmacy_in_system").enable();
									} else {
										Ext.getCmp("is_farmacy_in_system").setValue(false);
										Ext.getCmp("is_farmacy_in_system").disable();
									}
								}'
							)
						),	
						array(
							'boxLabel' => toUtf('Аптечные учреждения работают в системе'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'is_farmacy_in_system',
							'name' => 'is_farmacy_in_system',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'render' => 'function(field) {
									if (Ext.getCmp("is_distributors_in_system").checked) {
										field.enable();
									} else {
										field.disable();
									}
								}',
								'check' => 'function(field) {
									if (field.checked) {
										Ext.getCmp("is_registry_kept").setValue(true);
										Ext.getCmp("is_registry_kept").disable();
									} else {
										Ext.getCmp("is_registry_kept").enable();
									}
								}'
							)
						),
						array(
							'boxLabel' => toUtf('Регистры остатков ведутся'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'id' => 'is_registry_kept',
							'name' => 'is_registry_kept',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'render' => 'function(field) {
									if (Ext.getCmp("is_farmacy_in_system").checked) {
										field.setValue(true);
										field.disable();
									} else {
										field.enable();
									}
								}'
							)
						)
					)
				)
			),
			'registry' => array(
				array(
					'label' => toUtf('Настройки формирования'),
					'type' => 'fieldset',
					'labelWidth' => 130,
					'items' => array(
						array
						(
							'boxLabel' => toUtf('Запрещать отмечать реестр "К оплате", пока есть ошибки.'),
							'hidden' => getRegionNick() == 'kareliya',
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_registry_exists_errors',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array
						(
							'boxLabel' => toUtf('Запрет формирования реестров'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_registry_access',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array
						(
							'label' => toUtf('Запрет формирования реестров для МО'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'check_registry_access_lpu_grid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'DataStorage',
									'dataUrl' => '/?c=Options&m=loadCheckRegistryAccessGrid',
									'root' => 'data',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swCheckRegistryAccessEditWindow',
												'action' => 'add',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'disabled' => true,
											'hidden' => true
										),
										'action_view' => array(
											'disabled' => true,
											'hidden' => true
										),
										'action_delete' => array(
											'params' => array(
												'url' => '/?c=Options&m=deleteCheckRegistryAccess',
												'key' => 'DataStorage_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'DataStorage_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'Lpu_id', 'type' => 'int','hidden'=>true),
										array('name' => 'Lpu_Nick', 'type' => 'string','header' => toUtf('МО'), 'id' => 'autoexpand')
									)
								)
							)
						)
					)
				),
				array(
					'label' => toUtf('Редактирование случаев реестров'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					//'hidden' => getRegionNick() == 'ufa',
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Запрет на редактирование случаев реестров'),
							'hiddenName' => 'registry_disable_edit_inreg',
							'name' => 'registry_disable_edit_inreg',
							'options' => toUtf('[' . implode(",", $this->select_registry_disable_edit) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Запрет на редактирование оплаченных случаев реестров'),
							'hiddenName' => 'registry_disable_edit_paid',
							'name' => 'registry_disable_edit_paid',
							'options' => toUtf('[' . implode(",", $this->select_registry_disable_edit) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'label' => toUtf('Реестры по бюджету'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'hidden' => !in_array(getRegionNick(), array('astra', 'ufa', 'kareliya', 'krym', 'perm', 'pskov')),
					'items' => array(
						array(
							'boxLabel' => toUtf('Утверждение объёмов в МО'),
							'checked' => false,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'registry_mz_approve_lpu',
							'vfield' => 'checked',
							'xtype' => 'checkbox',
							'listeners' => array(
								'render' => 'function(c) {
									var el = Ext.get("x-form-el-" + c.id);
									if (el) {
										el.setAttribute("data-qtip", "При установленном флаге для добавления объёмов Минздравом потребуется утверждение со стороны МО (АРМ медицинского статистика, арм администратора МО).");
									}
								}'
							)
						)
					)
				),
				array(
					'label' => toUtf('МВД'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'displayField' => 'Org_Name',
							'enableKeyEvents' => true,
							'fieldLabel' => toUtf('Организация'),
							'hiddenName' => 'mvd_org',
							'id' => 'globaloptions_mvd_org',
							'valueField' => 'Org_id',
							'width' => 350,
							'xtype' => 'sworgcombo'
						),
						array(
							'displayField' => 'OrgRSchet_Name',
							'enableKeyEvents' => true,
							'fieldLabel' => toUtf('Р/счет'),
							'hiddenName' => 'mvd_org_schet',
							'id' => 'globaloptions_mvd_org_schet',
							'valueField' => 'OrgRSchet_id',
							'width' => 350,
							'xtype' => 'swbaselocalcombo'
						)
					)
				),
				array(
					'label' => toUtf('Коэффициенты индексации'),
					'type' => 'panel',
					'items' => array(
						array(
							'id' => 'coeff_index_grid',
							'type' => 'grid',
							'paging' => false,
							'autoLoadData' => true,
							'border' => false,
							'object' => 'CoeffIndex',
							'dataUrl' => '/?c=CoeffIndex&m=loadCoeffIndexGrid',
							'root' => 'data',
							'actions' => array(
								'action_add' => array(
									'params' => array(
										'wnd' => 'swCoeffIndexEditWindow',
										'action' => 'add',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_edit' => array(
									'params' => array(
										'wnd' => 'swCoeffIndexEditWindow',
										'action' => 'edit',
										'key' => 'CoeffIndex_id',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_view' => array(
									'params' => array(
										'wnd' => 'swCoeffIndexEditWindow',
										'action' => 'view',
										'key' => 'CoeffIndex_id',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_delete' => array(
									'params' => array(
										'key' => 'CoeffIndex_id',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_print' => array(
									'params' => array(
										'key' => 'CoeffIndex_id',
									),
									'disabled' => true,
									'hidden' => true
								)
							),
							'stringfields' => array(
								array('name' => 'CoeffIndex_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
								array('name' => 'CoeffIndex_Code', 'type' => 'int', 'header' => toUtf('Код')),
								array('name' => 'CoeffIndex_SysNick', 'type' => 'string', 'header' => toUtf('Краткое наименование')),
								array('name' => 'CoeffIndex_Name', 'type' => 'string', 'header' => toUtf('Полное наименование'), 'id' => 'autoexpand'),
								array('name' => 'CoeffIndex_Min', 'type' => 'float', 'header' => toUtf('Минимальное значение')),
								array('name' => 'CoeffIndex_Max', 'type' => 'float', 'header' => toUtf('Максимальное значение'))
							)
						)
					)
				),
				array
				(
					'xtype' => 'button',
					'text' => toUtf('Настройка кодов видов медицинской помощи'),
					'params' => array(
						'wnd' => 'swMedicalCareKindLinkViewWindow'
					)
				),
				array
				(
					'boxLabel' => toUtf('Проверка дат по ВМП'),
					'checked' => false,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'check_htm_dates',
					'vfield' => 'checked',
					'xtype' => 'checkbox'
				),
				array
				(
					'boxLabel' => toUtf('Осуществлять ФЛК'),
					'checked' => false,
					'disabled' => false,
					'hideLabel' => true,
					'name' => 'check_implementFLK',
					'vfield' => 'checked',
					'xtype' => 'checkbox',
					'listeners'  => array(
						'check' => 'function(f) {
							var setFLK=Ext.getCmp("settingFLK_grid").ownerCt;
							if(f.checked) {
								setFLK.show();
							}else{
								setFLK.hide();
							}
						}',
						'render' => 'function(f) {
							var setFLK=Ext.getCmp("settingFLK_grid").ownerCt;
							if(f.checked) {
								setFLK.show();
							}else{
								setFLK.hide();
							}
						}',
					)
				),
				array(
					'label' => toUtf('Настройка ФЛК'),
					'type' => 'panel',
					'items' => array(
						array(
							'id' => 'settingFLK_grid',
							'type' => 'grid',
							'paging' => false,
							'autoLoadData' => true,
							'border' => false,
							'object' => 'Registry',
							'dataUrl' => '/?c=Registry&m=loadRegistryEntiesGrid',
							'root' => 'data',
							'actions' => array(
								'action_add' => array(
									'params' => array(
										'wnd' => 'swAddingRegistryEntriesWindow',
										'action' => 'add',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_edit' => array(
									'params' => array(
										'wnd' => 'swAddingRegistryEntriesWindow',
										'action' => 'edit',
										'key' => 'FLKSettings_id',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_view' => array(
									'params' => array(
										'wnd' => 'swAddingRegistryEntriesWindow',
										'action' => 'view',
										'key' => 'FLKSettings_id',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_delete' => array(
									'params' => array(
										'key' => 'FLKSettings_id',
										'url' => '/?c=Registry&m=deleteRegistryFLK',
									),
									'disabled' => false,
									'hidden' => false
								),
								'action_print' => array(
									'params' => array(
										'key' => 'FLKSettings_id',
									),
									'disabled' => true,
									'hidden' => true
								)
							),
							'stringfields' => array(
								array('name' => 'FLKSettings_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
								array('name' => 'RegistryType_id', 'type' => 'int', 'hidden' => true ),
								array('name' => 'RegistryGroupType_id', 'type' => 'int', 'hidden' => true ),
								array('name' => 'RegistryType_Name', 'type' => 'string', 'hidden' => in_array($_SESSION['region']['nick'], array('kareliya','buryatiya','penza')), 'header' => toUtf('Тип реестра')),
								array('name' => 'RegistryGroupType_Name', 'type' => 'string', 'hidden' => in_array($_SESSION['region']['nick'], array('ufa','khak','astra')), 'header' => toUtf('Тип объединённого реестра')),
								array('name' => 'FLKSettings_EvnData', 'type' => 'string', 'header' => toUtf('Шаблон файла со случаями')),
								array('name' => 'FLKSettings_PersonData', 'type' => 'string', 'header' => toUtf('Шаблон файла с персональными данными'), 'id' => 'autoexpand'),
								array('name' => 'FLKSettings_begDate', 'type' => 'date', 'header' => toUtf('Дата начала действия')),
								array('name' => 'FLKSettings_endDate', 'type' => 'date', 'header' => toUtf('Дата окончания действия'))
							)
						)
					)
				),
			),
			'stac' => array(
				array(
					'label' => toUtf('Приемное отделение'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'boxLabel' => toUtf('Обязательность ввода диагноза в приемном отделении'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_priemdiag_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				
			),
			//gaf 07022018 #106655
			'personpregnancy' => array(
				array(
					'label' => toUtf('Анкета'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'boxLabel' => toUtf('Обязательность заполнения параметра «Последние менструации с» и «по»'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_menstrdatepregnancyanketa_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'boxLabel' => toUtf('Требование заполнения раздела «Анкета» в полном объеме'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'check_fullpregnancyanketa_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				
			),			
			'identify' => array(
				array(
					'label' => toUtf('Авторизация'),
					'type' => 'fieldset',
					'hidden' => $_SESSION['region']['nick'] != 'ekb',
					'labelWidth' => 200,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Логин'),
							'name' => 'ident_login',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'textfield'
						),
						array(
							'fieldLabel' => toUtf('Пароль'),
							'name' => 'ident_password',
							'vfield' => 'value',
							'width' => 100,
							'inputType' => 'password',
							'xtype' => 'textfield'
						)
					)
				),
				array(
					'label' => toUtf('Ручной режим'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Таймаут ожидания ответа, сек'),
							'name' => 'manual_identification_timeout',
							'maxValue' => 99,
							'minValue' => 0,
							'maxLength' =>2,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'label' => toUtf('Полуавтоматический режим'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array
						(
							'boxLabel' => toUtf('Включить режим'),
							'checked' => true,
							'disabled' => false,
							'hideLabel' => true,
							'name' => 'enable_semiautomatic_identification',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						),
						array(
							'fieldLabel' => toUtf('Таймаут ожидания ответа, сек'),
							'name' => 'semiautomatic_identification_timeout',
							'maxValue' => 99,
							'minValue' => 0,
							'maxLength' =>2,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'label' => toUtf('Сводная база застрахованных'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Дата актуальности'),
							'name' => 'identification_actual_date',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'swdatefield'
						)
					)
				)
			),
			'other' => array(
				array(
					'label' => toUtf('Сообщения'),
					'type' => 'fieldset',
					'labelWidth' => 300,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Проверка наличия новых сообщений, 1 раз в X мин'),
							'name' => 'message_time_limit',
							'maxValue' => 60,
							'minValue' => 1,
							'maxLength' =>2,
							'size' => 10,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'label' => toUtf('Журнал направлений'),
					'type' => 'fieldset',
					'labelWidth' => 300,
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Ввод направлений из журнала направлений'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'evndriection_from_journal',
							'vfield' => 'checked'
						)
					)
				),
				array(
					'label' => toUtf('Контроль пересечения случаев лечения'),
					'type' => 'fieldset',
					'labelWidth' => 160,
					'items'=>array(
						array(
							'layout' => 'column',
							'width' => 480,
							'items' => array(
								array(
									'layout' => 'form',
									'labelWidth' => 160,
									'items' => array(
										array(
											'allowBlank' => false,
											'displayField' => 'name',
											'editable' => false,
											'fieldLabel' => toUtf('Контроль пересечения посещения с КВС'),
											//'disabled' => in_array($_SESSION['region']['nick'], array('ufa', 'perm')),
											'hiddenName' => 'vizit_kvs_control',
											'name' => 'vizit_kvs_control',
											'options' => toUtf('[' . implode(",", $this->kvs_check_array) . ']'),
											'valueField' => 'val',
											'vfield' => 'value',
											'width' => 150,
											'xtype' => 'combo'
										),
									)
								),
								array(
									'layout' => 'form',
									'labelWidth' => 140,
									'labelAlign' => 'right',
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Учитывая вид оплаты'),
											'name' => 'vizit_kvs_control_paytype',
											'checked' => false,
											'labelSeparator' => '',
											'vfield' => 'checked',
											'xtype' => 'checkbox'
										),
									)
								)
							)
						),
						array(
							'layout' => 'column',
							'width' => 480,
							'items' => array(
								array(
									'layout' => 'form',
									'labelWidth' => 160,
									'items' => array(
										array(
											'allowBlank' => false,
											'displayField' => 'name',
											'editable' => false,
											'fieldLabel' => toUtf('Контроль пересечения движения с посещением'),
											//'disabled' => in_array($_SESSION['region']['nick'], array('ufa', 'perm')),
											'hiddenName' => 'vizit_direction_control',
											'name' => 'vizit_direction_control',
											'options' => toUtf('[' . implode(",", $this->kvs_check_array) . ']'),
											'valueField' => 'val',
											'vfield' => 'value',
											'width' => 150,
											'xtype' => 'combo'
										),
									)
								),
								array(
									'layout' => 'form',
									'labelWidth' => 140,
									'labelAlign' => 'right',
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Учитывая вид оплаты'),
											'name' => 'vizit_direction_control_paytype',
											'checked' => false,
											'labelSeparator' => '',
											'vfield' => 'checked',
											'xtype' => 'checkbox'
										),
									)
								)
							)
						),
						array(
							'layout' => 'column',
							'width' => 480,
							//'hidden' => !in_array($_SESSION['region']['nick'], array('kareliya')),
							'items' => array(
								array(
									'layout' => 'form',
									'labelWidth' => 160,
									'items' => array(
										array(
											//'allowBlank' => !in_array($_SESSION['region']['nick'], array('kareliya')),
											'allowBlank' => false,
											'displayField' => 'name',
											'editable' => false,
											'fieldLabel' => toUtf('Контроль пересечения посещений'),
											//'disabled' => in_array($_SESSION['region']['nick'], array('ufa', 'perm')),
											//'hidden' => !in_array($_SESSION['region']['nick'], array('kareliya')),
											//'hideLabel' => !in_array($_SESSION['region']['nick'], array('kareliya')),
											'hiddenName' => 'vizit_intersection_control',
											'name' => 'vizit_intersection_control',
											'options' => toUtf('[' . implode(",", $this->kvs_check_array) . ']'),
											'valueField' => 'val',
											'vfield' => 'value',
											'width' => 150,
											'xtype' => 'combo'
										),
									)
								),
								array(
									'layout' => 'form',
									'labelWidth' => 140,
									'labelAlign' => 'right',
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Учитывая вид оплаты'),
											'name' => 'vizit_intersection_control_paytype',
											'checked' => false,
											'labelSeparator' => '',
											'vfield' => 'checked',
											'xtype' => 'checkbox'
										),
									)
								)
							)
						),
						array(
							'layout' => 'column',
							'width' => 480,
							'items' => array(
								array(
									'layout' => 'form',
									'labelWidth' => 160,
									'items' => array(
										array(
											'allowBlank' => false,
											'displayField' => 'name',
											'editable' => false,
											'fieldLabel' => toUtf('Контроль пересечения движений в разных КВС'),
											'hiddenName' => 'kvs_intersection_control',
											'name' => 'kvs_intersection_control',
											'options' => toUtf('[' . implode(",", $this->kvs_check_array) . ']'),
											'valueField' => 'val',
											'vfield' => 'value',
											'width' => 150,
											'xtype' => 'combo'
										)
									)
								),
								array(
									'layout' => 'form',
									'labelWidth' => 140,
									'labelAlign' => 'right',
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Учитывая вид оплаты'),
											'name' => 'kvs_intersection_control_paytype',
											'checked' => false,
											'labelSeparator' => '',
											'vfield' => 'checked',
											'xtype' => 'checkbox'
										),
									)
								)
							)
						),
					)
				),
				array(
					'label' => toUtf('Добавление новых МО'),
					'type' => 'fieldset',
					'labelWidth' => 300,
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Проставлять OID = -1 для новых МО'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'setOIDForNewLpu',
							'vfield' => 'checked'
						)
					)
				),
				array(
					'label' => toUtf('Отправка ошибок прикрепления'),
					'type' => 'fieldset',
					'labelAlign' => 'top',
					'labelWidth' => 120,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Список e-mail адресов'),
							'name' => 'person_card_log_email_list',
							'vfield' => 'value',
							'xtype' => 'textarea',
							'anchor' => '100%'
						)
					)
				),
				array(
					'label' => '',
					'labelWidth' => 170,
					'type' => 'fieldset',
					'hidden' => $_SESSION['region']['nick'] == 'kz',
					'items'=>array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Контроль корректности ЕНП',
							'hiddenName' => 'enp_validation_control',
							'name' => 'enp_validation_control',
							'options' => '[' . implode(",", $this->validation_control_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 150,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'label' => '',
					'labelWidth' => 170,
					'type' => 'fieldset',
					//'hidden' => $_SESSION['region']['nick'] == 'kz',
					'items'=>array(
						array(
							'fieldLabel' => toUtf('Срок хранения аудиозаписей вызовов СМП (месяцев)'),
							'name' => 'audioRecordTimelimit',
							'maxValue' => 60,
							'minValue' => 3,
							'maxLength' =>2,
							'size' => 3,
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				),
				array(
					'label' => toUtf('Контроль корректности профиля отделения в посещении'),
					'type' => 'fieldset',
					'labelAlign' => 'top',
					'labelWidth' => 120,
					'items' => array(
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 1,
							'boxLabel' => toUtf('Предупреждать о несоответствии электронного направления профилю отделения в первом посещении ТАП'),
							'name' => 'evndirection_check_profile'
						),
						array(
							'xtype' => 'radio',
							'vfield' => 'checked',
							'hideLabel' => true,
							'inputValue' => 2,
							'boxLabel' => toUtf('Запрещать сохранение посещения/ТАП при несоответствии электронного направления профилю отделения в первом посещении ТАП'),
							'name' => 'evndirection_check_profile'
						)
					)
				),

				// Выводим только для Казахстана
				(
					(
						isset($_SESSION['region']['nick']) &&
						$_SESSION['region']['nick'] == 'kz'
					) ?
						(
							array(
								'allowBlank' => false,
								'displayField' => 'name',
								'editable' => false,
								'fieldLabel' => 'Правила заполнения врачей в периоде освобождения',
								'hiddenName' => 'rules_filling_doctors_workrelease',
								'name' => 'rules_filling_doctors_workrelease',
								'options' => '[' . implode(",", $this->rules_filling_doctors_workrelease_array) . ']',
								'valueField' => 'val',
								'vfield' => 'value',
								'width' => 350,
								'xtype' => 'combo'
							)
						):
						array()
				),

				array(
					'label' => toUtf('Сервисы'),
					'type' => 'fieldset',
					'hidden' => getRegionNick() != 'kz',
					'labelWidth' => 370,
					'items' => array(
						array(
							'fieldLabel' => toUtf('Отчетный период для передачи в АИС Поликлиника 25-5у (в месяцах)'),
							'name' => 'ais_reporting_period',
							'id' => 'ais_reporting_period_id',
							'maxValue' => 6,
							'minValue' => 1,
							'maxLength' => 1,
							'size' => 2,
							'allowBlank' => getRegionNick() != 'kz',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						),
						array(
							'fieldLabel' => toUtf('Отчетный период для передачи в АИС Поликлиника 25-9у (в месяцах)'),
							'name' => 'ais_reporting_period25_9y',
							'id' => 'ais_reporting_period25_9y_id',
							'maxValue' => 6,
							'minValue' => 1,
							'maxLength' => 1,
							'size' => 2,
							'allowBlank' => getRegionNick() != 'kz',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						)
					)
				)
			),
			'er' => array(
				array(
					'label' => toUtf('Портал мед. услуг'),
					'labelWidth' => 250,
					'type' => 'fieldset',
					'border' => false,
					'items' => array(
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время открытия нового дня для записи'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'new_day_time',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время запрета записи на завтра'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'close_next_day_record_time',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Блокировать пациентов после X неявок'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'absence_count_to_ban',
							'width' => 40,
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('На сколько дней вперед разрешить запись'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'portal_record_day_count',
							'vfield' => 'value',
							'maxValue' => 90,
							'minValue' => 1,
							'maxLength' => 2,
							'width' => 40,
							'allowDecimals' => false,
							'allowNegative' => false
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Разрешить физическим лицам включать пациентов из картотеки в лист ожидания'),
							'name' => 'grant_individual_add_to_wait_list',
							'checked' => false,
							'hideLabel' => true,
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Ограничить запись пациентов с закрытым полисом ОМС'),
							'name' => 'limit_record_patients_with_closed_polis',
							'checked' => false,
							'hideLabel' => true,
							'vfield' => 'checked'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Ожидание подтверждения пациентом времени приема (часы)'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'queue_max_accept_time',
							'vfield' => 'value',
							'maxLength' => 2,
							'width' => 40,
							'allowDecimals' => false,
							'allowNegative' => false
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Макс. кол-во отказов от предложенной бирки на прием'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'queue_max_cancel_count',
							'vfield' => 'value',
							'maxLength' => 2,
							'width' => 40,
							'allowDecimals' => false,
							'allowNegative' => false
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Не отображать сотрудников с фиктивными ставками'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'dont_display_dummy_staff',
							'vfield' => 'checked'
						),
						array(
							'label' => toUtf('Параметры проверки прикрепления'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'AttachmentCheckGrid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'AttachmentCheck',
									'dataUrl' => '/?c=AttachmentCheck&m=loadAttachmentCheckGrid',
									'root' => 'data',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swAttachmentCheckEditWindow',
												'action' => 'add',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swAttachmentCheckEditWindow',
												'action' => 'edit',
												'key' => 'AttachmentCheck_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swAttachmentCheckEditWindow',
												'action' => 'view',
												'key' => 'AttachmentCheck_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_delete' => array(
											'params' => array(
												'key' => 'AttachmentCheck_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'params' => array(
												'key' => 'AttachmentCheck_id',
											),
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'AttachmentCheck_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'LpuAttachType_Name', 'type' => 'string', 'header' => toUtf('Тип прикрепления')),
										array('name' => 'LpuAttachType_id', 'type' => 'int', 'hidden' => 'true'),
										array('name' => 'Lpu_Name', 'type' => 'string', 'header' => toUtf('МО')),
										array('name' => 'Lpu_id', 'type' => 'int', 'hidden' => 'true'),
										array('name' => 'LpuSectionProfile_Name', 'type' => 'string', 'header' => toUtf('Профиль')),
										array('name' => 'LpuSectionProfile_id', 'type' => 'int', 'hidden' => 'true'),
										array('name' => 'MedSpecOms_Name', 'type' => 'string', 'header' => toUtf('Специальность')),
										array('name' => 'MedSpecOms_id', 'type' => 'int', 'hidden' => 'true'),
										array('name' => 'AttachmentCheck_Period', 'type' => 'string', 'header' => toUtf('Период действия'))
									)
								)
							)
						),
					)
				),
				array(
					'label' => toUtf('ПроМед'),
					'labelWidth' => 250,
					'type' => 'fieldset',
					'border' => false,
					'items' => array(
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время открытия нового дня для записи'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'promed_new_day_time',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время с которого недоступна запись на следующий день пользователям других МО'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'promed_close_next_day_record_time',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Срок ожидания медицинской помощи в поликлинике (дней)'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'promed_waiting_period_polka',
							'vfield' => 'value',
							'width' => 40,
							'allowDecimals' => false,
							'allowNegative' => false
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Срок ожидания медицинской помощи в стационаре (дней)'),
							'disabled' => &$this->admin_field_disabled,
							'name' => 'promed_waiting_period_stac',
							'vfield' => 'value',
							'width' => 40,
							'allowDecimals' => false,
							'allowNegative' => false
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Запретить запись на прошедшее время, включая текущий день'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'disallow_recording_for_elapsed_time',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Запретить действия с бирками на прошедшее время (включая текущий день)'),
							'disabled' => $_SESSION['region']['nick'] == 'perm',
							'checked' => false,
							'hideLabel' => true,
							'name' => 'disallow_tt_actions_for_elapsed_time',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Запретить отменять запись с электронным направлением на прошедшее время (в том числе в рамках текущего дня)'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'disallow_canceling_el_dir_for_elapsed_time',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Разрешить отменять запись без электронных направлений на прошедшие дни'),
							'disabled' => false,
							'checked' => false,
							'hideLabel' => true,
							'name' => 'allow_canceling_without_el_dir_for_past_days',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'numberfield',
							'fieldLabel' => toUtf('Максимальное количество дней для создания (копирования) расписания'),
							'name' => 'fillScheduleMaxDays',
							'allowDecimals' => false,
							'allowNegative' => false,
							'allowBlank' => false,
							'vfield' => 'value',
							'listeners' => array(
								'change' => 'function(obj, val) {
									if(val > 7) {
										Ext.Msg.alert("Предупреждение", "Рекомендуемое значение не более 7 дней. Большее количество может существенно снизить производительность системы");
									}
								}'
							)

						),
						array(
							'title' => toUtf('На сколько дней вперед разрешить запись в поликлинике'),
							'autoHeight' => true,
							'labelWidth' => 300,
							'style' => 'margin-top: 10px',
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'pol_record_day_count',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'pol_record_day_count_reg',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для оператора call-центра'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'pol_record_day_count_cc',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'pol_record_day_count_own',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'pol_record_day_count_other',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								)
							)
						),
						array(
							'title' => toUtf('На сколько дней вперед разрешить запись в стационар'),
							'autoHeight' => true,
							'labelWidth' => 300,
							'style' => 'margin-top: 10px',
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'stac_record_day_count',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'stac_record_day_count_reg',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'stac_record_day_count_own',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'stac_record_day_count_other',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								)
							)
						),
						array(
							'title' => toUtf('На сколько дней вперед разрешить запись на службы'),
							'autoHeight' => true,
							'labelWidth' => 300,
							'style' => 'margin-top: 10px',
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'medservice_record_day_count',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для регистратора запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'medservice_record_day_count_reg',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в свою МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'medservice_record_day_count_own',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								),
								array(
									'xtype'=>'numberfield',
									'fieldLabel' => toUtf('Для остальных пользователей запись в чужую МО'),
									'disabled' => &$this->admin_field_disabled,
									'name' => 'medservice_record_day_count_other',
									'vfield' => 'value',
									'maxValue' => 90,
									'minValue' => 1,
									'maxLength' => 2,
									'width' => 40,
									'allowDecimals' => false,
									'allowNegative' => false
								)
							)
						),
						array(
							'label' => toUtf('МО, имеющие доступ к индивидуальной настройке периодов записи'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'LpuIndividualPeriodGrid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'LpuIndividualPeriod',
									'dataUrl' => '/?c=LpuIndividualPeriod&m=getLpuIndividualPeriodList',
									'root' => 'data',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swLpuIndividualPeriodEditWindow',
												'action' => 'add'
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swLpuIndividualPeriodEditWindow',
												'action' => 'edit',
												'key' => 'LpuIndividualPeriod_id'
											),
											'disabled' => true,
											'hidden' => true
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swLpuIndividualPeriodEditWindow',
												'action' => 'view',
												'key' => 'LpuIndividualPeriod_id'
											),
											'disabled' => true,
											'hidden' => true
										),
										'action_delete' => array(
											'params' => array(
												//'url' => '/?c=Lpu&m=deleteLpuIndividualPeriod',
												'key' => 'LpuIndividualPeriod_id'
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'params' => array(
												
												'key' => 'LpuIndividualPeriod_id'
											),
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'LpuIndividualPeriod_id', 'type' => 'int', 'header' => 'ID', 'hidden' => true, 'key' => true),
										array('name' => 'Lpu_id', 'type' => 'int', 'hidden' => true),
										array('name' => 'Lpu_Nick', 'type' => 'string', 'header' => toUtf('МО'), 'autoexpand' => true )
									)
								)
							)
						)
					)
				),
				array(
					'label' => toUtf('Доступ к отмене направлений и записей'),
					'labelWidth' => 250,
					'type' => 'fieldset',
					'border' => false,
					'items' => array(
						array(
							'title' => toUtf('На стороне МО, в которой создано направление (запись)'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('Сотрудник, создавший направление (запись)'),
									'name' => 'evn_direction_cancel_right_mo_where_created'
								),
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('МО сотрудника, создавшего направление (запись)'),
									'name' => 'evn_direction_cancel_right_mo_where_created'
								)
							)
						),
						array(
							'title' => toUtf('На стороне МО, в которую выписано направление (запись)'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('Сотрудник, к которому выписано направление (запись)'),
									'name' => 'evn_direction_cancel_right_mo_where_adressed'
								),
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('МО сотрудника, к которому выписано направление'),
									'name' => 'evn_direction_cancel_right_mo_where_adressed'
								)
							)
						)
					)
				)
			),
			'export_tfoms' => array(
				array(
					'labelWidth' => 230,
					'label' => toUtf('Настройки извещений о прикреплении'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Экспорт данных в ТФОМС'),
							'hideLabel' => true,
							'checked' => false,
							'name' => 'is_need_tfoms_export',
							'id' => 'is_need_tfoms_export_id',
							'vfield' => 'checked',
							'listeners' => array(
								'check' => 'function(field) {
									if (field.checked) {
										Ext.getCmp("tfoms_export_time_id").enable();
										Ext.getCmp("tfoms_export_time_id").setAllowBlank(false);
									} else {
										Ext.getCmp("tfoms_export_time_id").disable();
										Ext.getCmp("tfoms_export_time_id").setAllowBlank(true);
										Ext.getCmp("tfoms_export_time_id").setValue("");
									}
								}'
							)
						),
						array(
							'xtype'=>'swtimefield',
							'fieldLabel' => toUtf('Время формирования файла выгрузки'),
							'disabled' => true,
							'name' => 'tfoms_export_time',
							'id' => 'tfoms_export_time_id',
							'vfield' => 'value'
						)
					)
				)
			),
			'security' => array(
				array(
					'labelWidth' => 250,
					'label' => toUtf('Параметры безопасности паролей'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Срок действия пароля (дней)'),
							'cls' => 'textFieldWithHint',
							'qtip' => 'Количество дней использования пользовательского пароля',
							'minValue' => 1,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'password_expirationperiod',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Срок действия временного пароля (дней)'),
							'cls' => 'textFieldWithHint',
							'qtip' => 'Количество дней, в течение которых пользователь может осуществить первый вход в систему по временному паролю. При первом входе в систему пользователь обязан сменить пароль',
							'minValue' => 5,
							'maxValue' => 30,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'password_tempexpirationperiod',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('За сколько дней предупреждать об истечении срока действия пароля'),
							'cls' => 'textFieldWithHint',
							'qtip' => 'Указывается количество дней до истечения срока действия пароля, когда при входе система начинает предупреждать пользователя о приближении времени смены пароля',
							'minValue' => 1,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'password_daystowarn',
							'vfield' => 'value'
						),
						array(
							'xtype'=>'numberfield',
							'fieldLabel' => toUtf('Минимальная длина пароля (символов)'),
							'minValue' => 6,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'password_minlength',
							'vfield' => 'value'
						),
						array(
							'layout' => 'column',
							'width' => 480,
							'hidden' => (getRegionNick() == 'kz') ? true : false,
							'items' => array(
								array(
									'layout' => 'form',
									'labelWidth' => 250,
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Количество проверяемых на совпадение последних паролей'),
											'qtip' => 'Количество паролей, введенных ранее (отсчет с конца), с которыми сравнивается новый пароль',
											'hiddenName' => 'count_check_passwords',
											'minValue' => 1,
											'maxValue' => 1000,
											'allowDecimals' => false,
											'allowNegative' => false,
											'name' => 'count_check_passwords',
											'id' => 'count_check_passwords_id',
											'valueField' => 'val',
											'vfield' => 'value',
											'xtype' => 'numberfield'
										),
									)
								),
								array(
									'layout' => 'form',
									'labelWidth' => 30,
									'labelAlign' => 'right',
									'items' => array(
										array(
											'displayField' => 'name',
											'fieldLabel' => toUtf('Все'),
											'name' => 'check_passwords_all',
											'checked' => false,
											'vfield' => 'checked',
											'xtype' => 'checkbox',
											'listeners' => array(
												'check' => 'function(field) {
													if (field.checked) {
														Ext.getCmp("count_check_passwords_id").disable();
													} else {
														Ext.getCmp("count_check_passwords_id").enable();
													}
												}',
												'render' => 'function(field) {
													if (field.checked) {
														Ext.getCmp("count_check_passwords_id").disable();
													} else {
														Ext.getCmp("count_check_passwords_id").enable();
													}
												}'
											)
										),
									)
								)
							)
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Строчные буквы (нижний регистр)'),
							'hideLabel' => true,
							'checked' => true,
							'disabled' => true,
							'name' => 'password_haslowercase',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Прописные/заглавные буквы (верхний регистр)'),
							'hideLabel' => true,
							'checked' => false,
							'name' => 'password_hasuppercase',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Цифры'),
							'hideLabel' => true,
							'checked' => false,
							'name' => 'password_hasnumber',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Спец. символы (@, #, $, *, %, и т.д.)'),
							'hideLabel' => true,
							'checked' => false,
							'name' => 'password_hasspec',
							'vfield' => 'checked'
						),
						array(
							'xtype'=>'numberfield',
							'allowBlank' => false,
							'fieldLabel' => toUtf('Минимальное количество различающихся символов, при смене пароля'),
							'minValue' => 1,
							'disabled' => false,
							'name' => 'password_mindifference',
							'vfield' => 'value'
						),
						array(
							'xtype'=> 'checkbox',
							'boxLabel' =>  'Блокировать учетную запись пользователя после неудачных попыток входа в систему.',
							'name' => 'check_fail_login',
							'checked'=> false,
							'vfield'=> 'checked',
							'hidden' => false,
							'hideLabel'=> true,
							'listeners' => array(
								'check' => 'function(field) {
									if (field.checked) {
										Ext.getCmp("block_time_fail_login_id").enable();
										Ext.getCmp("count_bad_fail_login_id").enable();
									} else {
										Ext.getCmp("block_time_fail_login_id").disable();
										Ext.getCmp("count_bad_fail_login_id").disable();
									}
								}',
								'render' => 'function(field) {
									if (field.checked) {
										Ext.getCmp("block_time_fail_login_id").enable();
										Ext.getCmp("count_bad_fail_login_id").enable();
									} else {
										Ext.getCmp("block_time_fail_login_id").disable();
										Ext.getCmp("count_bad_fail_login_id").disable();
									}
								}'
							)
						),
						array(
							'xtype'=> 'numberfield',
							'fieldLabel' => 'Время блокировки (минут)',
							'id' => 'block_time_fail_login_id',
							'minValue' => 1,
							'maxValue' => 1000000,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'block_time_fail_login',
							'hidden' => false,
							'vfield' => 'value'
						),array(
							'xtype'=> 'numberfield',
							'fieldLabel' => 'Количество неудачных попыток',
							'id' => 'count_bad_fail_login_id',
							'minValue' => 1,
							'maxValue' => 1000,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'count_bad_fail_login',
							'hidden' => false,
							'vfield' => 'value'
						),
						array(
							'xtype'=> 'numberfield',
							'fieldLabel' => 'Блокировать пользователя после истечения срока отсутствия активности (в днях)',
							'cls' => 'textFieldWithHint',
							'qtip' => 'Срок отсутствия действий пользователя, после истечения которого, блокируется учетная запись',
							'minValue' => 1,
							'maxValue' => 10000,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'check_user_activity',
							'hidden' => (getRegionNick() == 'kz') ? true : false,
							'vfield' => 'value'
						)
					)
				)
			),
			'smp' => array(
				array(
					'label' => toUtf('СМП'),
					'type' => 'fieldset',
					'labelAlign' => 'right',
					'labelWidth' => 250,
					'items' => array(
						array(
							'fieldLabel' => 'Время начала суток для нумерации вызовов',
							'name' => 'day_start_call_time',
							'vfield' => 'value',
							'width' => 60,
							'ctCls' => 'text-bigger-one-line',
							'xtype' => 'swtimefield'
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Формат времени для отображения в АРМ'),
							'hiddenName' => 'smp_call_time_format',
							'name' => 'smp_call_time_format',
							'options' => toUtf('[' . implode(",", $this->smp_call_time_format) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 85,
							'xtype' => 'combo'
						),
						array(
							'xtype'=>'numberfield',
							'allowBlank' => false,
							'fieldLabel' => toUtf('Предельный срок после смерти пациента для создания вызова, дней'),
							'minValue' => 0,
							'disabled' => false,
							'ctCls' => 'text-bigger-one-line',
							'name' => 'limit_days_after_death_to_create_call',
							'vfield' => 'value'
						),
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Разрешить передачу вызовов в другую МО',
							'name' => 'smp_allow_transfer_of_calls_to_another_MO',
							'checked' => false,
							'hidden' => false,
							'vfield' => 'checked',
						),
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Отображать вкладку «Экспертная оценка» в АРМ Старшего врача СМП',
							'name' => 'smp_show_expert_tab_in_headdocwp',
							'checked' => false,
							'hidden' => false,
							'vfield' => 'checked',
						)
					)
				),
				array(
					'label' => toUtf('Взаимодействие с 112'),
					'type' => 'fieldset',
					'labelAlign' => 'top',
					'labelWidth' => 120,
					'items' => array(
						array(
							'title' => toUtf('Выбор системы 112'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('«Система-112», ЗАО «ИскраУралТЕЛ».'),
									'name' => 'smp_default_system112'
								),
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('«ЕДДС-ПРОТЕЙ», ООО НТЦ «Протей»'),
									'name' => 'smp_default_system112'
								),
								$_SESSION['region']['nick'] == 'astra' ? (
									array(
										'xtype' => 'radio',
										'vfield' => 'checked',
										'hideLabel' => true,
										'inputValue' => 3,
										'boxLabel' => toUtf('«Система - 112», Астраханской области'),
										'name' => 'smp_default_system112'
									)
								):array(),
							)
						),
						array(
							'title' => toUtf('Отображать вызовы 112 без адреса'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'boxLabel' => toUtf('Подразделение СМП'),
									'name' => 'smp_is_all_lpubuilding_with112',
									'listeners'  => array(
										'check' => 'function(f) {
											var defLB=Ext.getCmp("default_lpu_building_112").getEl().parent(".x-form-item");
											defLB.setDisplayed(f.checked)

										}',
										'render' => 'function(f) {
											var defLB=Ext.getCmp("default_lpu_building_112").getEl().parent(".x-form-item");
											defLB.setDisplayed(f.checked)

										}',
									)
								),
								array(
									'xtype' => 'radio',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'boxLabel' => toUtf('Все подразделения СМП, работающие с 112'),
									'name' => 'smp_is_all_lpubuilding_with112'
								),
							)
						),
						array(
							'xtype' => 'swregionsmpunitscombo',
							'fieldLabel' => 'Подразделение СМП по умолчанию для приема карточки вызова 112',
							'name' => 'smp_default_lpu_building_112',
							'id' => 'default_lpu_building_112',
							'hiddenName' => 'smp_default_lpu_building_112',
							'valueField' => 'LpuBuilding_id',
							'vfield' => 'value'
						),
						array(
							'title' => toUtf('Отображать информацию о вызовах 112'),
							'autoHeight' => true,
							'labelWidth' => 300,
							'style' => 'margin-top: 10px',
							'xtype' => 'fieldset',
							'items' => array(
								array
								(
									'boxLabel' => toUtf('АРМ Диспетчера подстанции'),
									'checked' => false,
									'disabled' => false,
									'hideLabel' => true,
									'name' => 'smp_show_112_indispstation',
									'vfield' => 'checked',
									'xtype' => 'checkbox'
								)
							)
						),
					)
				),
				array(
					'label' => toUtf('Взаимодействие с ГИТ СМП'),
					'type' => 'fieldset',
					'hidden' => !in_array(getRegionNick(), array('penza', 'pskov')),
					//'hidden' => getRegionNick() != 'penza',
					'labelAlign' => 'right',
					'labelWidth' => 250,
					'items' => array(
						array(
							'xtype' => 'swlpuwithmedservicecombo',
							'fieldLabel' => 'МО передачи вызовов СМП ',
							'name' => 'smp_default_lpu_import_git',
							'id' => 'default_lpu_import_git',
							'hiddenName' => 'smp_default_lpu_import_git',
							'medServiceTypeId' => 19,
							'valueField' => 'Lpu_id',
							'vfield' => 'value'
						),
					)
				)
			),
			'nmp' => array(
				array(
					'label' => toUtf('Время работы служб НМП'),
					'type' => 'fieldset',
					'labelAlign' => 'right',
					'items' => array_merge($this->createNMPWorkTimeFields(), array(
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Разрешить редактирование времени работы служб НМП',
							'name' => 'nmp_edit_work_time',
							'checked' => false,
							'vfield' => 'checked',
						)
					))
				)
			),
			'mse' => array(
				array(
					'label' => toUtf('Проведение деперсонифицированной экспертизы'),
					'type' => 'fieldset',
					'labelAlign' => 'right',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Проводить деперсонифицированную экспертизу',
							'name' => 'use_depersonalized_expertise',
							'checked' => false,
							'vfield' => 'checked',
						)
					)
				)
			),
			'auth' => array(
				array(
					'label' => toUtf('ЕСИА'),
					'type' => 'fieldset',
					'labelAlign' => 'right',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Разрешить авторизацию только через ЕСИА',
							'name' => 'use_esia_only',
							'checked' => false,
							'vfield' => 'checked',
						)
					)
				),
				array(
					'label' =>toUtf('Параллельные сеансы'),
					'type' => 'fieldset',
					'labelAlign' => 'right',
					'labelWidth' => 250,
					'hidden' => (getRegionNick() == 'kz') ? true : false,
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'hideLabel' => true,
							'boxLabel' => 'Проверять количество параллельных сеансов',
							'name' => 'check_count_parallel_sessions',
							'checked' => false,
							'vfield' => 'checked',
						),
						array(
							'xtype'=> 'numberfield',
							'fieldLabel' => 'Количество параллельных сеансов доступа',
							'minValue' => 1,
							'allowDecimals' => false,
							'allowNegative' => false,
							'disabled' => false,
							'name' => 'count_parallel_sessions',
							'vfield' => 'value',
						),
						array(
							'label' => toUtf('Исключения'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'ip_session_count_grid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'IPSessionCount',
									'dataUrl' => '/?c=IPSessionCount&m=loadIPSessionCountGrid',
									'root' => 'data',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swExceptionsParallelSessionsEditWindow',
												'action' => 'add',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swExceptionsParallelSessionsEditWindow',
												'action' => 'edit',
												'key' => 'IPSessionCount_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swExceptionsParallelSessionsEditWindow',
												'action' => 'view',
												'key' => 'IPSessionCount_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_delete' => array(
											'params' => array(
												'key' => 'IPSessionCount_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'params' => array(
												'key' => 'IPSessionCount_id',
											),
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'IPSessionCount_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'IPSessionCount_IP', 'type' => 'string', 'header' => toUtf('IP-адрес')),
										array('name' => 'IPSessionCount_Max', 'type' => 'int', 'header' => toUtf('Количество параллельных сеансов доступа'), 'width' => 250),
									)
								)
							)
						)
					)
				)
			),
			'misrb' => array(
				array(
					'labelWidth' => 150,
					'label' => toUtf('Интеграция с ИЭМК'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'Способ передачи',
							'hiddenName' => 'misrb_transfer_type',
							'name' => 'misrb_transfer_type',
							'options' => '[' . implode(",", $this->misrb_transfer_type_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 150,
							'xtype' => 'combo'
						),
						array(
							'xtype' => 'swtimefield',
							'fieldLabel' => toUtf('Время'),
							'name' => 'misrb_transfer_time',
							'vfield' => 'value'
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'fieldLabel' => 'День недели',
							'hiddenName' => 'misrb_transfer_day',
							'name' => 'misrb_transfer_day',
							'options' => '[' . implode(",", $this->days_array) . ']',
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 150,
							'xtype' => 'combo'
						)
					)
				)
			),
			'evnstick' => array(
				array(
					'labelWidth' => 150,
					'label' => toUtf('Выгрузка ЭЛН в ФСС'),
					'hidden' => getRegionNick() == 'kz',
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'hidden' => false,
							'disabled' => false,
							'boxLabel' => toUtf('Передавать информацию о диагнозе в ФСС'),
							'checked' => true,
							'hideLabel' => true,
							'name' => 'enable_fss_send_diag',
							'vfield' => 'checked'
						),

						// Выводим для всех регионов кроме Казахстана
						(
							(
								isset($_SESSION['region']['nick']) &&
								$_SESSION['region']['nick'] == 'kz'
							) ?
								array() :
								(
									array(
										'allowBlank' => false,
										'displayField' => 'name',
										'editable' => false,
										'fieldLabel' => 'Правила заполнения врачей в периоде освобождения',
										'hiddenName' => 'rules_filling_doctors_workrelease',
										'name' => 'rules_filling_doctors_workrelease',
										'options' => '[' . implode(",", $this->rules_filling_doctors_workrelease_array) . ']',
										'valueField' => 'val',
										'vfield' => 'value',
										'width' => 350,
										'xtype' => 'combo'
									)
								)
						)

					)
				),
				array(
					'labelWidth' => 150,
					'label' => toUtf('Доступ к аннулированию ЭЛН'),
					'hidden' => getRegionNick() == 'kz',
					'type' => 'fieldset',
					'items' => array(
						array(
							'title' => toUtf('Аннулировать ЭЛН могут пользователи следующих МО'),
							'autoHeight' => true,
							'xtype' => 'fieldset',
							'items' => array(
								array(
									'boxLabel' => toUtf('Только МО, в которой ЭЛН был оформлен'),
									'name' => 'lpu_cancel_stick_access',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 1,
									'checked' => in_array(getRegionNick(), array('perm','khak')),
									'xtype' => 'radio'
								),
								array(
									'boxLabel' => toUtf('МО, открывшая ЭЛН, и МО, закрывшая ЭЛН'),
									'name' => 'lpu_cancel_stick_access',
									'vfield' => 'checked',
									'hideLabel' => true,
									'inputValue' => 2,
									'checked' => !in_array(getRegionNick(), array('perm','khak')),
									'xtype' => 'radio'
								),
							)	
						)		
					)
				)
			),
			'fields_control' => array(
				array(
					'label' => toUtf('Карты ПОН / ДДС'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность полей для экспорта на федеральный портал'),
							'hiddenName' => 'disp_control',
							'name' => 'disp_control',
							'options' => toUtf('[' . implode(",", $this->select_disp_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'label' => toUtf('КВС'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность заполнения поля «Время с начала заболевания» для экстренной госпитализации'),
							'hiddenName' => 'eps_control',
							'name' => 'eps_control',
							'options' => toUtf('[' . implode(",", $this->select_eps_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность заполнения поля «Осложнен кардиогенным шоком»'),
							'hiddenName' => 'es_iscardshock_control',
							'name' => 'es_iscardshock_control',
							'options' => toUtf('[' . implode(",", $this->select_eps_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'label' => toUtf('Оперативные услуги'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность заполнения поля «Дата и время начала раздувания баллона»'),
							'hiddenName' => 'euo_ballonbeg_control',
							'name' => 'euo_ballonbeg_control',
							'options' => toUtf('[' . implode(",", $this->select_eps_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность заполнения поля «Дата и время окончания ЧКВ»'),
							'hiddenName' => 'euo_ckvend_control',
							'name' => 'euo_ckvend_control',
							'options' => toUtf('[' . implode(",", $this->select_eps_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						)
					)
				),
				array(
					'label' => toUtf('Человек'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Обязательность заполнения поля «Гражданство»'),
							'hiddenName' => 'citizenship_control',
							'name' => 'citizenship_control',
							'options' => toUtf('[' . implode(",", $this->fill_citizenship_control) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 200,
							'xtype' => 'combo'
						),
						isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kz' ? array() : (
							array(
								'allowBlank' => false,
								'displayField' => 'name',
								'editable' => false,
								'disabled' => false,
								'fieldLabel' =>  toUtf('Обязательность заполнения поля «СНИЛС»'),
								'hiddenName' => 'snils_control',
								'name' => 'snils_control',
								'options' => toUtf('[' . implode(",", $this->fill_snils_control) . ']'),
								'valueField' => 'val',
								'vfield' => 'value',
								'width' => 200,
								'xtype' => 'combo'
							)
						),
						isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kz' ? array() : (
							array(
								'allowBlank' => false,
								'displayField' => 'name',
								'editable' => false,
								'disabled' => false,
								'fieldLabel' =>  toUtf('Контроль на дублирование СНИЛС'),
								'hiddenName' => 'snils_double_control',
								'name' => 'snils_double_control',
								'options' => toUtf('[' . implode(",", $this->fill_snils_control) . ']'),
								'valueField' => 'val',
								'vfield' => 'value',
								'width' => 200,
								'xtype' => 'combo'
							)
						),
						isset($_SESSION['region']['nick']) && $_SESSION['region']['nick'] == 'kz' ? array() : (
							array(
								'allowBlank' => false,
								'displayField' => 'name',
								'editable' => false,
								'disabled' => false,
								'fieldLabel' =>  toUtf('Контроль на корректность ИНН'),
								'hiddenName' => 'inn_correctness_control',
								'name' => 'inn_correctness_control',
								'options' => toUtf('[' . implode(",", $this->fill_inn_correctness_control) . ']'),
								'valueField' => 'val',
								'vfield' => 'value',
								'width' => 200,
								'xtype' => 'combo'
							)
						)
					)
				)

			),
			'farmacy' => array(
				array(
					'xtype' => 'checkbox',
					'hidden' => false,
					'disabled' => false,
					'boxLabel' => toUtf('Осуществлять контроль по сроку годности'),
					'checked' => false,
					'hideLabel' => true,
					'id' => 'gow_CheckingField',
					'name' => 'farmacy_checking_expiration_date',
					'vfield' => 'checked',
					'listeners' => array(
						'check' => 'function(field) {
							if (field.checked) {
								Ext.getCmp("gow_Less2YField").enable();
								Ext.getCmp("gow_More2YField").enable();
							} else {
								Ext.getCmp("gow_Less2YField").disable();
								Ext.getCmp("gow_More2YField").disable();
							}
						}'
					)
				), array(
					'labelWidth' => 315,
					'label' => toUtf('Остаточный срок годности на момент поставки не менее'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'allowDecimals' => true,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Со сроком хранения до 2-х лет % от основного'),
							'id' => 'gow_Less2YField',
							'name' => 'farmacy_remainig_exp_date_less_2_years',
							'vfield' => 'value',
							'xtype' => 'numberfield',
							'local' => true,
							'listeners' => array(
								'render' => 'function(field) {
									if (Ext.getCmp("gow_CheckingField").checked) {
										field.enable();
									} else {
										field.disable();
									}
								}'
							)
						),
						array(
							'allowDecimals' => true,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Со сроком хранения более 2-х лет % от основного'),
							'id' => 'gow_More2YField',
							'name' => 'farmacy_remainig_exp_date_more_2_years',
							'vfield' => 'value',
							'xtype' => 'numberfield',
							'local' => true,
							'listeners' => array(
								'render' => 'function(field) {
									if (Ext.getCmp("gow_CheckingField").checked) {
										field.enable();
									} else {
										field.disable();
									}
								}'
							)
						)
					)
				)
			),
			'lis' => array(
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройки подключения'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Адрес'),
							'name' => 'lis_address',
							'vfield' => 'value',
							'width' => 280,
							'xtype' => 'textfield'
						),
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Сервер'),
							'name' => 'lis_server',
							'vfield' => 'value',
							'width' => 280,
							'xtype' => 'textfield'
						),
						array(
							'allowDecimals' => false,
							'allowNegative' => false,
							'fieldLabel' => toUtf('Порт'),
							'id' => 'lis_port',
							'name' => 'lis_port',
							'vfield' => 'value',
							'xtype' => 'numberfield',
							'width' => 80
						),
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Путь'),
							'name' => 'lis_path',
							'vfield' => 'value',
							'width' => 280,
							'xtype' => 'textfield'
						),
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Версия'),
							'name' => 'lis_version',
							'vfield' => 'value',
							'width' => 80,
							'xtype' => 'textfield'
						),
						array(
							'allowDecimals' => false,
							'allowNegative' => false,
							'width' => 80,
							'fieldLabel' => toUtf('Билд'),
							'id' => 'lis_buildnumber',
							'name' => 'lis_buildnumber',
							'vfield' => 'value',
							'xtype' => 'numberfield'
						),

					)
				),
				/*array(
					'labelWidth' => 50,
					'label' => toUtf('Настройки для загрузки справочников'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Логин'),
							'name' => 'lis_login',
							'vfield' => 'value',
							'width' => 100,
							'xtype' => 'textfield'
						),
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Пароль'),
							'name' => 'lis_password',
							'vfield' => 'value',
							'width' => 100,
							'inputType' => 'password',
							'xtype' => 'textfield'
						),
						array(
							'allowBlank' => false,
							'fieldLabel' => toUtf('Client ID'),
							'name' => 'lis_clientid',
							'vfield' => 'value',
							'xtype' => 'textfield',
							'anchor' => '100%'
						)
					)
				),*/
				array(
					'labelWidth' => 50,
					'label' => toUtf('Синхронизация с ЛИС'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'boxLabel' => toUtf('Автоматический экспорт в ЛИС'),
							'hideLabel' => true,
							'name' => 'lis_export_auto',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				)
			),
			'correct_data' => array(
				array(
					'labelWidth' => 50,
					'label' => toUtf('СНИЛС'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Oбязательно'),
							'hideLabel' => true,
							'name' => 'correct_data_snils_not_empty',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Не обязательно для детей в возрасте до трех лет'),
							'hideLabel' => true,
							'name' => 'correct_data_snils_empty_for_baby',
							'vfield' => 'checked'

						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Проверять на дублирование при сохранении'),
							'hideLabel' => true,
							'name' => 'correct_data_snils_check_copy',
							'vfield' => 'checked'
						)
					)
				)
			),
			'notification' => array(
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройки извещений о прикреплении'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Отправлять запрос от МО1 на корректность информации в заявлении и ответ от МО2 по электронной почте'),
							'hideLabel' => true,
							'name' => 'request_personcard_correction_email',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Отправлять запрос от МО1 на корректность информации в заявлении и ответ от МО2 средствами внутренних сообщений ПроМед'),
							'hideLabel' => true,
							'name' => 'request_personcard_correction_message',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Информировать МО1 о прикреплении гражданина в МО2'),
							'hideLabel' => true,
							'name' => 'inform_lpu_personcard_attach_email',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Отправлять XML файл в МО2 с данными о прикреплении гражданина в МО1'),
							'hideLabel' => true,
							'name' => 'inform_lpu_personcard_attach_email_with_xml',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Информировать гражданина о прикреплении через СМС'),
							'hideLabel' => true,
							'name' => 'inform_person_personcard_attach_sms',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Информировать гражданина о прикреплении через электронную почту'),
							'hideLabel' => true,
							'name' => 'inform_person_personcard_attach_email',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Информировать СМО о прикреплении застрахованного в ней гражданина'),
							'hideLabel' => true,
							'name' => 'inform_smo_personcard_attach_email',
							'vfield' => 'checked'
						),
						array(
							'xtype' => 'label',
							'text' => 'МО1 – организация, принявшая заявление'
						),
						array(
							'xtype' => 'label',
							'text' => 'МО2 – организация, в которой гражданин находится на обслуживании'
						)
					)
				),
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройки извещений о пациенте'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Запрашивать подтверждение номера телефона'),
							'hideLabel' => true,
							'name' => 'request_person_phone_verification',
							'vfield' => 'checked'
						),
					)
				),
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройка уведомлений о прохождении анкетирования'),
					'hidden' => getRegionNick() != 'msk',
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Уведомлять пациентов о прохождении анкетирования'),
							'hideLabel' => true,
							'name' => 'send_onkoctrl_msg',
							'vfield' => 'checked'
						),
					)
				),
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройки уведомлений о предстоящем диспансерном приеме'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Уведомлять пациентов о предстоящем диспансерном приеме'),
							'hideLabel' => true,
							'name' => 'notify_on_upcoming_disp_visits',
							'vfield' => 'checked',
							'listeners' => array(
								'check' => 'function(el, checked) {
									var noticeGrid = Ext.getCmp("NoticeDiagGroupGrid");
									noticeGrid.setDisabled(!checked);
								}',
								'render' => 'function(el) {
									var noticeGrid = Ext.getCmp("NoticeDiagGroupGrid");
									noticeGrid.setDisabled(!el.getValue());
								}'
							)
						),
						array(
							'label' => toUtf('Группа диагнозов'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'NoticeDiagGroupGrid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'NoticeDiagGroup',
									'dataUrl' => '/?c=NoticeDiagGroup&m=loadNoticeDiagGroupGrid',
									'root' => 'data',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swNoticeDiagGroupEditWindow',
												'action' => 'add',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swNoticeDiagGroupEditWindow',
												'action' => 'edit',
												'key' => 'NoticeDiagGroup_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swNoticeDiagGroupEditWindow',
												'action' => 'view',
												'key' => 'NoticeDiagGroup_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_delete' => array(
											'params' => array(
												'key' => 'NoticeDiagGroup_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'params' => array(
												'key' => 'NoticeDiagGroup_id',
											),
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'NoticeDiagGroup_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'NoticeDiagGroup_Name', 'type' => 'string', 'header' => toUtf('Группа')),
										array('name' => 'NoticeDiagGroup_Codes', 'type' => 'string', 'header' => toUtf('Диагнозы'), 'id' => 'autoexpand')
									)
								)
							)
						)
					)
				),
				array(
					'labelWidth' => 50,
					'label' => toUtf('Настройка уведомлений о предстоящей госпитализации'),
					'hidden' => !in_array(getRegionNick(), array('msk','ufa','vologda')),
					'type' => 'fieldset',
					'items' => array(
						array(
							'label' => toUtf('Установленные режимы уведомлений'),
							'type' => 'panel',
							'items' => array(
								array(
									'id' => 'NoticeModeSettingsGrid',
									'type' => 'grid',
									'paging' => false,
									'autoLoadData' => true,
									'border' => false,
									'object' => 'NoticeModeSettings',
									'dataUrl' => '/?c=NoticeModeSettings&m=loadNoticeModeSettingsGrid',
									'actions' => array(
										'action_add' => array(
											'params' => array(
												'wnd' => 'swNoticeModeSettingsEditWindow',
												'action' => 'add',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_edit' => array(
											'params' => array(
												'wnd' => 'swNoticeModeSettingsEditWindow',
												'action' => 'edit',
												'key' => 'NoticeModeSettings_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_view' => array(
											'params' => array(
												'wnd' => 'swNoticeModeSettingsEditWindow',
												'action' => 'view',
												'key' => 'NoticeModeSettings_id',
											),
											'disabled' => true,
											'hidden' => true
										),
										'action_delete' => array(
											'params' => array(
												'key' => 'NoticeModeSettings_id',
											),
											'disabled' => false,
											'hidden' => false
										),
										'action_print' => array(
											'params' => array(
												'key' => 'NoticeModeSettings_id',
											),
											'disabled' => true,
											'hidden' => true
										)
									),
									'stringfields' => array(
										array('name' => 'NoticeModeSettings_id', 'type' => 'int', 'header' => 'ID', 'key' => true),
										array('name' => 'Lpu_Name', 'type' => 'string', 'header' => toUtf('МО')),
										array('name' => 'NoticeModeSettings_IsSMS', 'type' => 'string', 'header' => toUtf('СМС')),
										array('name' => 'NoticeModeSettings_IsEmail', 'type' => 'string', 'header' => toUtf('Email'))
									)
								)
							)
						)
					)
				)
			),
			'disp' => array(
				array(
					'labelWidth' => 200,
					'label' => toUtf('Настройки диспансерных карт'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'allowBlank' => false,
							'displayField' => 'name',
							'editable' => false,
							'disabled' => false,
							'fieldLabel' => toUtf('Доступные места работы врачей'),
							'hiddenName' => 'allowed_disp_med_staff_fact_group',
							'name' => 'allowed_disp_med_staff_fact_group',
							'options' => toUtf('[' . implode(",", $this->disp_med_staff_fact_group) . ']'),
							'valueField' => 'val',
							'vfield' => 'value',
							'width' => 280,
							'xtype' => 'combo'
						),
					)
				),
				array(
					'labelWidth' => 200,
					'label' => toUtf('Экспорт в ТФОМС планов контрольных посещений в рамках диспансерного наблюдения'),
					'type' => 'fieldset',
					'items' => array(
						array(
							'xtype' => 'checkbox',
							'boxLabel' => toUtf('Принимать ответы от ТФОМС'),
							'hideLabel' => true,
							'name' => 'accept_tfoms_answer',
							'vfield' => 'checked'
						)
					)

				),
			),
			'person_register_szz' => array(
				'type' => 'jsobject',
				'file' => 'swPersonRegisterIncludeOptionsObject'
			),
			'privilege' => array(
				'type' => 'jsobject',
				'file' => 'swAccessRightsPrivilegeTypeObjects'
			),
			'diag_group' => array(
				'type' => 'jsobject',
				'file' => 'swAccessRightsDiagObjects'
			),
			'test_group' => array(
				'type' => 'jsobject',
				'file' => 'swAccessRightsTestObjects'
			),
			't9_group' => array(
				'type' => 'jsobject',
				'file' => 'swAccessT9Objects'
			),
			'lpu_group' => array(
				'type' => 'jsobject',
				'file' => 'swAccessRightsLpuObjects'
			),
			'smo_arm' => array(
				'type' => 'jsobject',
				'file' => 'swAccessRightsArmSmoObjects'
			),
			'attach' => array(
				array(
					'label' => toUtf('Доступ к функционалу'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'boxLabel' => toUtf('Доступно только для группы "Прикрепление к МО"'),
							'checked' => false,
							'disabled' => false,
							'id' => 'check_attach_allow_id',
							'hideLabel' => true,
							'name' => 'check_attach_allow',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				),
				array(
					'label' => toUtf('Прикрепления'),
					'type' => 'fieldset',
					'labelWidth' => 200,
					'items' => array(
						array(
							'boxLabel' => toUtf('Редактирование даты прикрепления'),
							'checked' => false,
							'disabled' => false,
							'id' => 'allow_edit_attach_date_id',
							'hideLabel' => true,
							'name' => 'allow_edit_attach_date',
							'vfield' => 'checked',
							'xtype' => 'checkbox'
						)
					)
				)
			)
		);

	}

	/**
	 * Дополнительная инициализация
	*/
	private function init()
	{
		$method = $this->router->fetch_method();
		if (!$this->usePostgreLis || !in_array($method, $this->moduleMethods)) {
			$this->load->database();
			$this->load->model("Options_model", "opmodel");
		}
	}

	/**
	 * Создание полей для ввода времени работы служб НМП
	 */
	function createNMPWorkTimeFields() {
		$response = array();

		$days = array(
			'monday' => 'Понедельник',
			'tuesday' => 'Вторник',
			'wednesday' => 'Среда',
			'thursday' => 'Четверг',
			'friday' => 'Пятница',
			'saturday' => 'Суббота',
			'sunday' => 'Воскресенье'
		);

		foreach($days as $day_nick => $day_name) {
			$beg_time_field_name = "nmp_{$day_nick}_beg_time";
			$end_time_field_name = "nmp_{$day_nick}_end_time";

			$response[] = array(
				'layout' => 'column',
				'bodyStyle' => 'background:#DFE8F6;',
				'items' => array(
					array(
						'layout' => 'form',
						'width' => '110px',
						'items' => array(
							array(
								'xtype' => 'label',
								'bodyStyle' => 'padding-top: 3px; background:#DFE8F6;',
								'style' => 'font-size: 12px; font-weight: bold;',
								'text' => $day_name
							)
						)
					),
					array(
						'layout' => 'form',
						'labelWidth' => 20,
						'items' => array(
							array(
								'xtype' => 'swtimefield',
								'fieldLabel' => 'С',
								'id' => $beg_time_field_name,
								'name' => $beg_time_field_name,
								'vfield' => 'value',
								'hideTrigger' => true,
								'onChange' => "function(field, newValue, oldValue) {
									var endTimeField = Ext.getCmp('{$end_time_field_name}');
									endTimeField.setAllowBlank(Ext.isEmpty(newValue));
									endTimeField.fireEvent('focus', endTimeField);
								}"
							)
						)
					),
					array(
						'layout' => 'form',
						'labelWidth' => 30,
						'items' => array(
							array(
								'xtype' => 'swtimefield',
								'fieldLabel' => 'По',
								'id' => $end_time_field_name,
								'name' => $end_time_field_name,
								'vfield' => 'value',
								'hideTrigger' => true,
								'onChange' => "function(field, newValue, oldValue) {
									var begTimeField = Ext.getCmp('{$beg_time_field_name}');
									begTimeField.setAllowBlank(Ext.isEmpty(newValue));
									begTimeField.fireEvent('focus', begTimeField);
								}"
							)
						)
					)
				)
			);
		}

		return $response;
	}

	/**
	 * Сохранение настроек пользователя
	 */
	function saveOptionsForm()
	{
		$node = $_POST['node'];
		$data = $_POST;
		// Получаем сессионные переменные
		$data['session'] = $_SESSION;

		$this->load->model("Options_model", "opmodel");

		$opt = $this->opmodel->getOptionsAll($data);

		if ( empty($opt['appearance']['menu_type']) || $opt['appearance']['menu_type'] == 'ribbon' ) {
			$opt['appearance']['menu_type'] = 'simple';
		}

		$dbdata = array();

		$i=0;
		if ($_SESSION['region']['nick'] == 'ufa' && empty($_POST['ignoreReceptFedExist']) && isset($_POST['evn_recept_fed_ser']) && isset ($_POST['evn_recept_fed_num_min']) && isset ($_POST['evn_recept_fed_num_max'])) {
			// проверка что в заданном диапазоне нет рецептов
			$recept['Lpu_id'] = $_SESSION['lpu_id'];
			$recept['EvnRecept_Ser'] = $_POST['evn_recept_fed_ser'];
			$recept['MinValue'] = $_POST['evn_recept_fed_num_min'];
			$recept['MaxValue'] = $_POST['evn_recept_fed_num_max'];

			$this->load->model('Dlo_EvnRecept_model', 'receptmodel');
			$result = $this->receptmodel->getReceptCount($recept);
			if ( is_array($result) &&  !empty($result[0]['Recept_Count']) ) {
				$this->ReturnData(array('success' => false, 'Error_Code'=> 10));
				return;
			}
		}

		if ($_SESSION['region']['nick'] == 'ufa' && empty($_POST['ignoreReceptRegExist']) && isset($_POST['evn_recept_reg_ser']) && isset ($_POST['evn_recept_reg_num'])) {
			// проверка что в заданном диапазоне нет рецептов
			$recept['Lpu_id'] = $_SESSION['lpu_id'];
			$recept['EvnRecept_Ser'] = $_POST['evn_recept_reg_ser'];
			$recept['MinValue'] = $_POST['evn_recept_reg_num'];
			$recept['MaxValue'] = null;

			$this->load->model('Dlo_EvnRecept_model', 'receptmodel');
			$result = $this->receptmodel->getReceptCount($recept);
			if ( is_array($result) &&  !empty($result[0]['Recept_Count']) ) {
				$this->ReturnData(array('success' => false, 'Error_Code'=> 11));
				return;
			}
		}

		if (!empty($_POST['homevizit_isallowed'])) {
			$this->load->model('HomeVisit_model', 'hvmodel');
			$cdata = $_POST;
			$cdata['Lpu_id'] = $_SESSION['lpu_id'];
			$cdata['pmUser_id'] = $_SESSION['pmuser_id'];
			$result = $this->hvmodel->saveHomeVisitWorkMode($cdata);
		}

		foreach ($opt[$node] as $key=>$value)
		{
			// для чекбоксов:
			if (!isset($data[$key]))
			{
				$data[$key] = false;
			}

			if ( $data[$key] == 'on')
			{
				$data[$key] = true;
			}

			// некоторые настройки обрабатываются отдельно
			switch ( $key )
			{
				case 'evnpl_numcard_next_num':
					$this->load->model("Options_model", "opmodel");
					$this->opmodel->setPMGenValue($data, 'EvnPL', $data[$key]);
				break;

				case 'evnplstom_numcard_next_num':
					$this->load->model("Options_model", "opmodel");
					$this->opmodel->setPMGenValue($data, 'EvnPLStom', $data[$key]);
				break;

				case 'evnps_numcard_next_num':
					$this->load->model("Options_model", "opmodel");
					$this->opmodel->setPMGenValue($data, 'EvnPS', $data[$key]);
				break;

				case 'next_card_code':
					$this->load->model("Options_model", "opmodel");
					$this->opmodel->setPMGenValue($data, 'PersonCard', $data[$key]);
				break;
				case 'direction_print_form':
					//права на редактирование только у суперадмина и админа мо
					if (!haveArmType('superadmin') && !haveArmType('lpuadmin')) {
						break;
					}
				case 'unique_ser_num': case 'evn_recept_fed_ser': case 'evn_recept_reg_ser': case 'evn_recept_fed_num_min': case 'evn_recept_fed_num_max':
				case 'evn_recept_reg_num': case 'blank_form_creation_method': case 'medsvid_ser': case 'deathmedsvid_address_type': /*case 'medsvid_num':*/
				case 'isstat': case 'check_person_error': case 'check_access_reform': case 'is_finish_result_block': /*case 'enable_is_doctor_filter':*/
				case 'check_person_birthday': case 'double_vizit_control': case 'first_detected_diag_control': case 'prehosp_trauma_control':
				case 'evnps_numcard_prefix': case 'evnps_numcard_postfix': case 'homevizit_isallowed': case 'homevizit_begtime': case 'homevizit_endtime': case 'homevizit_spec_isallowed':
				case 'asmlo_server': case 'asmlo_login': case 'asmlo_password':
				case 'ban_on_absences': case 'demo_server': case 'is_required_evnps_diag_pid':
				case 'allow_queue': case 'allow_queue_auto':
				case 'electronic_queue_direct_link':
				case 'rmis_login': case 'rmis_password': case 'export_personcardcode_instead_of_evnplnumcard':
                case 'suppliers_ostat_control':
                case 'doc_uc_operation_control':
                case 'doc_uc_different_goods_unit_control':
                case 'enable_sign_evnstick_auth_person':
				case 'homevizit_nmp_phone': case 'homevizit_smp_phone': case 'homevizit_phone':
                case 'disable_patient_additions_for_profile_branches':
                case 'registry_evn_sort_order_stac': case 'registry_evn_sort_order_polka': case 'registry_evn_sort_order_smp': case 'registry_evn_sort_order_dvn':
				case 'registry_evn_sort_order_dds': case 'registry_evn_sort_order_htm': case 'registry_evn_sort_order_prof': case 'checkEvnDirectionDate':
				case 'is_perinatal_haemorrhage': case 'onDigipacsViewer': case 'digiPacsAddress': case 'PrintResearchCovid':
					/*
						Эти тоже по идее должны храниться на сервере
						case 'new_day_time': case 'close_next_day_record_time': case 'validate_start_date':
					*/
					// Настройки, которые хранятся на сервере
					$dbdata[$i] = array();
					$dbdata[$i]['DataStorageGroup_SysNick'] = $node;
					$dbdata[$i]['DataStorage_Name'] = $key;
					$dbdata[$i]['DataStorage_Value'] = $data[$key].''; // переводим в строку.
					$dbdata[$i]['Lpu_id'] = $_SESSION['lpu_id'];
					$dbdata[$i]['Org_id'] = (empty($dbdata[$i]['Lpu_id']) ? $_SESSION['org_id'] : null);
					$i++;
				break;

				case 'rir_login': case 'rir_pass':
					if (havingGroup('SchedulingPS') || isLpuAdmin()) {
						$dbdata[$i] = array();
						$dbdata[$i]['DataStorageGroup_SysNick'] = $node;
						$dbdata[$i]['DataStorage_Name'] = $key;
						$dbdata[$i]['DataStorage_Value'] = $data[$key].''; // переводим в строку.
						$dbdata[$i]['Lpu_id'] = $_SESSION['lpu_id'];
						$dbdata[$i]['Org_id'] = (empty($dbdata[$i]['Lpu_id']) ? $_SESSION['org_id'] : null);
						$i++;
					}
				break;

				case 'allowed_medpersonal_ev': case 'allowed_medpersonal_es':
					$dbdata[$i] = array();
					$dbdata[$i]['DataStorageGroup_SysNick'] = $node;
					$dbdata[$i]['DataStorage_Name'] = $key;
					$dbdata[$i]['DataStorage_Value'] = '';
					$dbdata[$i]['Lpu_id'] = $_SESSION['lpu_id'];
					//$dbdata[$i]['org_id'] = (!empty($_SESSION['org_id'])?$_SESSION['org_id']:null);
					for ($b=1; $b <= 12; $b++) {
						if($key == 'allowed_medpersonal_ev'){
							$forkey = 'vizitpost'.$b;
						} else if($key == 'allowed_medpersonal_es'){
							$forkey = 'sectionpost'.$b;
						}
						if(!empty($data[$forkey])){
							if(strlen($dbdata[$i]['DataStorage_Value'])>0){
								$dbdata[$i]['DataStorage_Value'] .= ',';
							}
							$dbdata[$i]['DataStorage_Value'] .= $b;
						}
					}
					$i++;
				break;

				default:
					$opt[$node][$key] = $data[$key];
				break;
			}
		}

		if($node == 'glossary')
		{
			$opt[$node]['enable_glossary'] = (!empty($data['enable_base_glossary']) || !empty($data['enable_pers_glossary']));
		}

		if (count($dbdata)>0)
		{
			$this->load->model("Options_model", "opmodel");
			$this->opmodel->setDataStorageValues($dbdata, $_SESSION);
		}
		unset($opt['globals']);
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		if (is_array($settings)) {
			$settings = array_merge($settings, $opt);
		} else {
			$settings = $opt;
		}
		//var_dump($settings);die;
		$user->settings = serialize($settings);
		$_SESSION['settings'] = $user->settings;

		// todo: Временное решение, lis из ldap убрали, если lis есть, то нещадно прибиваем. Нужно будет убрать после правильного пересохранения настроек на пользователе
		/*if (isset($user->lis)) {
			$user->lis = array();
		}*/

		//var_dump($user);die;
		$user->post();
		$this->load->model("User_model", "umodel");
		$this->umodel->ReCacheUserData($user);
		$this->ReturnData(array('success'=>true));
	}

	/**
	 * Сохранение глобальных настроек пользователя
	 */
	function saveGlobalOptionsForm()
	{
		if (!isSuperAdmin()) {
			$this->ReturnError('Недостаточно прав для сохранения параметров системы');
			return false;
		}
		$this->load->model("Options_model", "opmodel");

		$nodes = array(
			'reference'	=> array('enable_action_reference_by_admref_group', 'contact_info'),
			'prov'		=> array('allow_fio_search', 'recept_data_acc_method'),
			'dlo'		=> array('drug_spr_using', 'person_privilege_add_request_postmoderation', 'vzn_privilege_diag_available_checking', 'social_privilege_document_available_checking', 'check_excess_sum_fed', 'check_excess_sum_reg', 'is_remove_drug', 'is_create_drugrequest', 'use_numerator_for_recept','use_external_service_for_recept_num', 'recept_diag_control', 'dlo_logistics_system', 'select_drug_from_list', 'recept_drug_ostat_viewing', 'recept_drug_ostat_control', 'recept_empty_drug_ostat_allow', 'recept_by_farmacy_drug_ostat', 'recept_farmacy_type', 'recept_by_ras_drug_ostat', 'llo_price_edit_enabled', 'evn_recept_level_of_control', 'recept_electronic_allow'),
			'farmacyllo'=> array('is_distributors_in_system', 'is_farmacy_in_system', 'is_registry_kept'),
			'registry'	=> array('check_registry_exists_errors', 'check_registry_access', 'enable_registry_auto_identify', 'registry_mz_approve_lpu', 'mvd_org', 'mvd_org_schet', 'registry_disable_edit_inreg', 'registry_disable_edit_paid', 'check_htm_dates', 'check_implementFLK'),
			'stac'		=> array('check_priemdiag_allow'),
			//gaf 08022018 #106655
			'personpregnancy'		=> array('check_fullpregnancyanketa_allow','check_menstrdatepregnancyanketa_allow'),
			'evnstick' => array('enable_fss_send_diag', 'rules_filling_doctors_workrelease_array', 'lpu_cancel_stick_access'),
			'fields_control' => array('disp_control', 'eps_control'),
			'identify'	=> array('ident_login', 'ident_password', 'manual_identification_timeout', 'enable_semiautomatic_identification', 'semiautomatic_identification_timeout', 'identification_actual_date'),
			'other'		=> array('message_time_limit','evndriection_from_journal','setOIDForNewLpu','person_card_log_email_list','vizit_direction_control_paytype','vizit_kvs_control_paytype','vizit_intersection_control_paytype','kvs_intersection_control_paytype','enp_validation_control', 'rules_filling_doctors_workrelease_array'),
			'er'		=> array('new_day_time', 'close_next_day_record_time', 'absence_count_to_ban', 'portal_record_day_count', 'queue_max_accept_time', 'queue_max_cancel_count', 'disallow_recording_for_elapsed_time', 'disallow_tt_actions_for_elapsed_time', 'disallow_canceling_el_dir_for_elapsed_time', 'allow_canceling_without_el_dir_for_past_days', 'dont_display_dummy_staff', 'grant_individual_add_to_wait_list', 'pol_record_day_count', 'stac_record_day_count', 'medservice_record_day_count', 'evn_direction_cancel_right_mo_where_created', 'evn_direction_cancel_right_mo_where_adressed', 'limit_record_patients_with_closed_polis'),
			'farmacy'	=> array('farmacy_checking_expiration_date', 'farmacy_remainig_exp_date_less_2_years', 'farmacy_remainig_exp_date_more_2_years'),
			'lis'	=> array('lis_address','lis_server','lis_port','lis_path','lis_version','lis_buildnumber',/*'lis_login','lis_password','lis_clientid',*/'lis_export_auto'),
			'correct_data'	=> array('correct_data_snils_not_empty','correct_data_snils_empty_for_baby','correct_data_snils_check_copy'),
			'attach'	=> array('check_attach_allow', 'allow_edit_attach_date'),
			'export_tfoms'	=> array('is_need_tfoms_export', 'tfoms_export_time'),
			'security'	=> array('password_expirationperiod', 'password_tempexpirationperiod', 'password_daystowarn', 'password_minlength', 'password_mindifference', 'password_haslowercase', 'password_hasuppercase', 'password_hasnumber', 'password_hasspec', 'check_fail_login', 'block_time_fail_login', 'count_bad_fail_login', 'check_user_activity', 'count_check_passwords', 'check_passwords_all'),
			'smp'	=> array('day_start_call_time','smp_call_time_format', 'limit_days_after_death_to_create_call','smp_allow_transfer_of_calls_to_another_MO','smp_show_expert_tab_in_headdocwp','smp_default_system112','smp_is_all_lpubuilding_with112','smp_default_lpu_building_112','smp_show_112_indispstation','smp_default_lpu_import_git'),
			'nmp'	=> array('nmp_monday_beg_time','nmp_monday_end_time','nmp_tuesday_beg_time','nmp_tuesday_end_time','nmp_wednesday_beg_time','nmp_wednesday_end_time','nmp_thursday_beg_time','nmp_thursday_end_time','nmp_friday_beg_time','nmp_friday_end_time','nmp_saturday_beg_time','nmp_saturday_end_time','nmp_sunday_beg_time','nmp_sunday_end_time','nmp_edit_work_time'),
			'mse'	=> array('use_depersonalized_expertise'),
			'auth'	=> array('use_esia_only', 'check_count_parallel_sessions', 'count_parallel_sessions'),
			'misrb' => array('misrb_transfer_type', 'misrb_transfer_time', 'misrb_transfer_day'),
			'notification'	=> array('request_personcard_correction_email','request_personcard_correction_message','inform_lpu_personcard_attach_email','inform_lpu_personcard_attach_email_with_xml','inform_person_personcard_attach_sms','inform_person_personcard_attach_email','inform_smo_personcard_attach_email','request_person_phone_verification', 'send_onkoctrl_msg', 'notify_on_upcoming_hosp_lpu', 'notify_on_upcoming_hosp_by_sms', 'notify_on_upcoming_hosp_by_email', 'notify_on_upcoming_disp_visits'),
			'disp'	=> array('allowed_disp_med_staff_fact_group', 'accept_tfoms_answer')
		);

		$cur_node = array_flip($nodes[$_POST['node']]);
		foreach($cur_node as $k=>$v) {
			$cur_node[$k] = false;
		}

		$data = array_merge($cur_node, $_POST);
		$data['session'] = array(
			'login' => $_SESSION['login']
		);

		if (!empty($data['password_mindifference']) && !empty($data['password_minlength']) && $data['password_mindifference'] >= $data['password_minlength']) {
			$this->ReturnError('Значение поля "Минимальное количество различающихся символов, при смене пароля" должно быть строго меньше минимальной длины пароля.');
			return false;
		}

		if (!empty($data['check_fail_login']) && (empty($data['block_time_fail_login']) || empty($data['count_bad_fail_login']))) {
			$this->ReturnError('Значение полей "Время блокировки (минут)" и "Количество неудачных попыток" обязательны для заполнения при отмеченном флаге "Блокировать учетную запись пользователя после неудачных попыток входа в систему".');
			return false;
		}

		if (!empty($data['check_passwords_all'])) {
			unset($data['count_check_passwords']);
		}

		if (empty($data['check_fail_login'])) {
			unset($data['block_time_fail_login']);
			unset($data['count_bad_fail_login']);
		}

		if ($data['node'] == 'nmp') {
			$days = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
			foreach($days as $day) {
				$beg_time_field = "nmp_{$day}_beg_time";
				$end_time_field = "nmp_{$day}_end_time";

				// значение может прилететь в виде "__:__"
				if( isset($data[$end_time_field]) && $data[$end_time_field] == "__:__") $data[$end_time_field]='';
				if( isset($data[$beg_time_field]) && $data[$beg_time_field] == "__:__") $data[$beg_time_field]='';

				if (!empty($data[$end_time_field]) && empty($data[$beg_time_field])) {
					$this->ReturnError('Не заполнено время начала работы кабинета НМП');
					return false;
				}
				if (!empty($data[$beg_time_field]) && empty($data[$end_time_field])) {
					$this->ReturnError('Не заполнено время окончания работы кабинета НМП');
					return false;
				}
				if (!empty($data[$beg_time_field]) && date_create($data[$beg_time_field]) > date_create($data[$end_time_field])) {
					$this->ReturnError('Время начала работы кабинета НМП не может быть больше времени окончания');
					return false;
				}
			}
		}

		$opt = $this->opmodel->getOptionsGlobals($data);

		//значения от чекбоксов которые могут быть задисаблены необходимо обрабатывать специальным образом, иначе значения сохраняемые в бд будут различатся в зависимости от того, задисаблен чекбокс в момент сохранения или нет
		$disabled_checkbox_array = array('recept_drug_ostat_viewing' , 'recept_drug_ostat_control' , 'recept_empty_drug_ostat_allow');

		foreach ($opt['globals'] as $key=>$value) {
			if (isset($data[$key])) {
				if ($data[$key] == 'on' || ($data[$key] == 'true' && in_array($key, $disabled_checkbox_array)))
					$opt['globals'][$key] = true;
				else
					$opt['globals'][$key] = $data[$key];
			}
		}

		$opt['globals']['session'] = $_SESSION;

		$result = $this->opmodel->saveOptionsGlobals($opt['globals']);
		if (!$result) {
			$val = array('success' => false);
		} else {
			unset($opt['globals']['session']);
			$this->opmodel->resetGlobalOptions($opt);
			$val = array('success' => true);
		}

		if ($result && $data['node'] == 'nmp') {
			$this->opmodel->fillNMPWorkTime();
		}

		$this->ReturnData($val);
	}

	/**
	 * Получаем права на объекты, формы и действия
	 */
	function getRoles() {
		$this->load->model("User_model", "umodel");

		// определяем группы, в которые входит пользователь
		// для того, чтобы не читать LDAP возьмем их их сессии
		$groups = explode('|', $_SESSION['groups']);
		$data = array();
		$roles = array();

		for($i=0;$i<count($groups);$i++) {
			$data['Role_id'] = $groups[$i];

			$role = pmAuthGroups::loadRole($data['Role_id']);
			unset($role['menus']);
			$roles = mergeRoles($roles, $role);
		}

		return $roles;
	}

	/**
	 * Получение идентификатора организации минздрава
	 */
	function getMinzdravOrgId() {
		$this->load->model("Org_model", "Org_model");
		return $this->Org_model->getMinzdravOrgId();
	}

	/**
	 * Получение списка ЛПУ связанных с ТОУЗ пользователя
	 */
	function getTouzOrgs() {
		$this->load->model("Org_model", "Org_model");
		if (is_array($_SESSION['orgs']) && count($_SESSION['orgs']) > 0) {
			return $this->Org_model->getTouzOrgs(array('orgs' => $_SESSION['orgs']));
		}

		return array();
	}

	/**
	 * Получение настроек, которые могут обновляться без участия пользователя.
	 */
	function getNewOptions()
	{
		$opt = array(
			'success' => true
		);

		// Читаем список закрытых форм.
		$this->load->helper('Config');
		$files = filetoarray(APPPATH.'config/files.php');
		$opt["globals"]["blockFormList"] = array();
		foreach($files as $key => $value) {
			if (!empty($value['block']) && $value['block']) {
				if (!in_array($key, $opt["globals"]["blockFormList"])) {
					$opt["globals"]["blockFormList"][] = $key;
				}
			}
		}

		echo(@json_encode($opt));
	}

	/**
	 * Получение глобальных настроек и настроек пользователя для работы
	 * и для передачи настроек на клиент
	 */
	function getGlobalOptions()
	{

		$this->load->model("Options_model", "opmodel");
		$this->load->model("Org_model", "Org_model");


		$data = array(
			'MedPersonal_id' => $_SESSION['medpersonal_id'],
			'Lpu_id' => $_SESSION['lpu_id']
		);

		// Получаем сессионные переменные
		$data['session'] = $_SESSION;

		$opt = $this->opmodel->getOptionsAll($data);

		if ( empty($opt['appearance']['menu_type']) || $opt['appearance']['menu_type'] == 'ribbon' ) {
			$opt['appearance']['menu_type'] = 'simple';
		}

		// вот здесь мы можем все настройки загонять в сессию (потом в кэш)
		// то есть настройки брать не из базы, а из сессии (кэша!)
		// пока добавил только глобальные - во всех местах где в серверной части используется обращение к глобальным настройканастройка изм - надо переделать на сессию
		// (с) Night
		$_SESSION['setting'] = array();
		$_SESSION['setting']['server'] = array();
		$_SESSION['setting']['lpu'] = array();
		$_SESSION['setting']['user'] = array();
		// $_SESSION['setting']['roles'] = array();

		$_SESSION['setting']['lpu'] = $opt['registry'];
		//$_SESSION['barcode_format'] = !empty($opt['lis']['barcode_format'])?$opt['lis']['barcode_format']:'lis';
		$_SESSION['asmlo_server'] = !empty($opt['lis']['asmlo_server'])?$opt['lis']['asmlo_server']:$this->config->item('asmlo_server');
		$_SESSION['asmlo_login'] = !empty($opt['lis']['asmlo_login'])?$opt['lis']['asmlo_login']:$this->config->item('asmlo_login');
		$_SESSION['asmlo_password'] = !empty($opt['lis']['asmlo_password'])?$opt['lis']['asmlo_password']:$this->config->item('asmlo_password');

		// Определение локальной версии базы данных
		$opt['globals']['localDBVersion'] = $this->opmodel->getLocalDBVersion();

		$opt['lis']['use_postgresql_lis'] = $this->usePostgreLis;

		if (!isset($_SESSION['region']))
		{
			// Читаем регион (массив)
			$region = $this->opmodel->getRegion();
			// записываем  регион в сессию
			$_SESSION['region'] = $region;
		}
		else
		{
			// или берем из сессии, если сессия уже создана (а она уже должна быть создана)
			$region = $_SESSION['region'];
		}
		// и его же (регион) в глобальные переменные
		$region['name'] = toUTF($region['name'], true);

		if (!empty($_SESSION['SystemError_TechInfo'])) {
			$opt["globals"]['se_techinfo'] = $_SESSION['SystemError_TechInfo'];
		}

		$opt["globals"]['region'] = $region;
		$opt["globals"]['fer_Person_id'] = $this->config->item('FER_PERSON_ID');
		$opt["globals"]['login'] = $_SESSION['login'];
		$opt["globals"]['newSmpServer'] = $this->config->item('newSmpServer');

		// Настройки БД архивных записей
		$opt['globals']['archive_database_enable'] = 0;
		$archive_database_enable = $this->config->item('archive_database_enable');
		if (!empty($archive_database_enable)) {
			$opt['globals']['archive_database_enable'] = 1;
			$opt['globals']['archive_database_date'] = $this->config->item('archive_database_date');
		}

		if (isset($opt['check_registry_access_lpu']) && $opt['check_registry_access_lpu']) {
			$opt['globals']['check_registry_access'] = $opt['check_registry_access_lpu'];
		}

		// Читаем права на объекты, формы и действия
		/*$_SESSION['setting']['roles'] = $this->getRoles();
		if (!empty($_SESSION['setting']['roles']['windows'])) {
			$opt["globals"]["wndroles"] = $_SESSION['setting']['roles']['windows'];
		} else {
			$opt["globals"]["wndroles"] = array();
		}*/

		// Читаем список закрытых форм.
		$this->load->helper('Config');
		$files = filetoarray(APPPATH.'config/files.php');
		$opt["globals"]["blockFormList"] = array();
		foreach($files as $key => $value) {
			if (!empty($value['block']) && $value['block']) {
				if (!in_array($key, $opt["globals"]["blockFormList"])) {
					$opt["globals"]["blockFormList"][] = $key;
				}
			}
		}
		// Время устранения проблемы
		$opt["globals"]["blockFormTime"] = $this->config->item('blockFormTime');

		if ( defined("CARDREADER_IS_ENABLE") && CARDREADER_IS_ENABLE === true )
			$opt["globals"]['card_reader_is_enable'] = true;
		else
			$opt["globals"]['card_reader_is_enable'] = false;

		if (!empty($data['session']['org_id'])) {
			$opt["globals"]["org_id"] = $data['session']['org_id'];
		}

        if (!empty($data['session']['Org_Name'])) {
			$opt["globals"]["Org_Name"] = $data['session']['Org_Name'];
		}

		if (!empty($data['session']['orgtype'])) {
			$opt["globals"]["orgtype"] = $data['session']['orgtype'];
		}

		$token = "";

		if (function_exists("openssl_cipher_iv_length")) {
			// генерируем токен для службы ЭЦП
			$method = 'AES-128-CBC';
			$secret = "password";
			$password = substr(hash('sha256', $secret, true), 0, 16);
			$raw_output = $raw_input = true;

			// 1. Вектор инициализации
			$iv_len = openssl_cipher_iv_length($method);
			$iv = openssl_random_pseudo_bytes($iv_len);
			// 2. Строка <штамп времени>,<идентификатор пользователя> в формате unix_time
			$timestamp = time();
			$token_str = "{$timestamp},{$_SESSION['pmuser_id']}";
			// 3. Шифруем строку и вставляем в начало вектор инициалиизации
			$encrypted = $iv . openssl_encrypt($token_str, $method, $password, $raw_output, $iv);
			// 4. Переведя в base64 получаем токен
			$token = base64_encode($encrypted);
			/*
				// TODO убрать
				// возьмём пока токен который плагин может распарсить, алгоритм формирования своего нужно уточнить и дописать
				// $token = "4E/g8wMwNGK7ae9fscK+oi8SQXEHLeYuUD2+t1qZ+RE=";
				// пытаемся расшифровать токен, чтобы понять что там
				$encrypted = base64_decode($token);
				$iv = substr($encrypted, 0, $iv_len);
				$encrypted = substr($encrypted, $iv_len);
				$decrypted = openssl_decrypt($encrypted, $method, $password, $raw_input, $iv);
				var_dump($decrypted);
				die();
			*/
		} else {
			log_message('error', 'undefined function openssl_cipher_iv_length');
		}

		// 5. Записываем в сессию, да толкаем на клиент на всякий случай тож
		$_SESSION['token'] = $token;
		$opt["globals"]['token'] = $token;

		$IsLocalSMP = $this->config->item('IsLocalSMP');
		if ($IsLocalSMP === true) {
			$opt["globals"]['IsLocalSMP'] = $IsLocalSMP;
		}

		$IsSMPServer = $this->config->item('IsSMPServer');
		if ($IsSMPServer === true) {
			$opt["globals"]['IsSMPServer'] = $IsSMPServer;
		}

		$MedStatAddCards = $this->config->item('MedStatAddCards');
		if (isset($MedStatAddCards)){
			$opt["globals"]['MedStatAddCards'] = $MedStatAddCards;
		}

		$NonLinearElectronicQueueList = $this->config->item('NON_LINEAR_ELECTRONIC_QUEUE_LIST');
		if (isset($NonLinearElectronicQueueList)){
			$opt["globals"]['NonLinearElectronicQueueList'] = $NonLinearElectronicQueueList;
		}

		$EmkEmdDocControls = $this->config->item('ENABLE_EMK_EMD_DOCUMENT_CONTROLS');
		if (isset($EmkEmdDocControls)){
			$opt["globals"]['enableEmkEmdDocControls'] = $EmkEmdDocControls;
		}

		$isFarmacy = false;
		$groups = explode('|', $_SESSION['groups']);
		foreach ($groups as $group) {
			if ($group == 'FarmacyAdmin' || $group == 'FarmacyUser' || $group == 'FarmacyNetAdmin') {
				$isFarmacy = true;
			}
		}

		$opt["globals"]["minzdrav_org_id"] = $this->getMinzdravOrgId();

		$_SESSION['TOUZLpuArr'] = array();
		$opt["globals"]["TOUZLpuArr"] = array();
		
		if ( /*!isset($opt["globals"]["OrgFarmacy_id"])*/ !$isFarmacy ) {
			$opt["stac"]["evnps_numcard_prefix"] = toUtf($opt["stac"]["evnps_numcard_prefix"]);
			$opt["stac"]["evnps_numcard_postfix"] = toUtf($opt["stac"]["evnps_numcard_postfix"]);

			$opt["globals"]["homevizit_nmp_phone"] = $opt['homevizit']['homevizit_nmp_phone'];
			$opt["globals"]["homevizit_smp_phone"] = $opt['homevizit']['homevizit_smp_phone'];
			//$opt["globals"]["homevizit_phone"] = $opt['homevizit']['homevizit_phone'];

			// Если Специалист ОУЗ то достаём список ЛПУ связанных с ТОУЗ пользователя
			if (havingGroup('OuzSpec')) {
				$opt["globals"]["TOUZLpuArr"] = $this->getTouzOrgs();
				$_SESSION['TOUZLpuArr'] = $opt["globals"]["TOUZLpuArr"];
			}
			
			// Данные по текущему ЛПУ
			$this->load->model("User_model", "umodel");

			if (!empty($data['session']['org_id']) && empty($data['Lpu_id'])) {
				$info = $this->umodel->getCurrentOrgData(array(
					'Org_id' => $data['session']['org_id']
				));
				if ($info) {
					$opt["globals"]["org_nick"] = toUtf($info[0]['Org_Nick']);
					$opt["globals"]["lpu_name"] = toUtf($info[0]['Org_Name']);
					$opt["globals"]["lpu_email"] = toUtf($info[0]['Org_Email']);
					$opt["globals"]["lpu_is_secret"] = false;

					$_SESSION['MedStaffFact'] = array();
					$_SESSION['LpuSection'] = array();
					$_SESSION['LpuRegion'] = array();
					$_SESSION['mp_is_zav'] = false;
					$_SESSION['mp_is_uch'] = false;

					$opt["globals"]['medstafffact'] = $_SESSION['MedStaffFact'];
					$opt["globals"]['lpusection'] = $_SESSION['LpuSection'];
					$opt["globals"]['lpuregion'] = $_SESSION['LpuSection'];
					$opt["globals"]['medpersonal_id'] = $_SESSION['medpersonal_id'];
					$opt["globals"]['mp_is_zav'] = $_SESSION['mp_is_zav'];
					$opt["globals"]['mp_is_uch'] = $_SESSION['mp_is_uch'];
				}
			} else {
				$info = $this->umodel->getCurrentLpuData($data);
				if ($info) {
					// TODO: по идее информация об ЛПУ - это отдельный подуровень globals (т.е. $opt["globals"]["lpu"]["nick"] и прочее)
					$opt["globals"]["org_nick"] = toUtf($info[0]['Org_Nick']);
					$opt["globals"]["lpu_name"] = toUtf($info[0]['Org_Name']);
					$opt["globals"]["lpu_email"] = toUtf($info[0]['Org_Email']);
					$opt["globals"]["lpu_nick"] = toUtf($info[0]['Lpu_Nick']);
					$opt["globals"]["lpu_sysnick"] = toUtf($info[0]['Lpu_SysNick']);
					$opt["globals"]["lpu_is_dms"] = ($info[0]['Lpu_IsDMS'] == 2);
					$opt["globals"]["lpu_is_secret"] = ($info[0]['Lpu_IsSecret'] == 2);
					$opt["globals"]["lpu_name"] = toUtf($info[0]['Lpu_Name']);
					$opt["globals"]["lpu_email"] = toUtf($info[0]['Lpu_Email']);
					$opt["globals"]["lpu_level_id"] = $info[0]['LpuLevel_id'];
					$opt["globals"]["lpu_level_code"] = $info[0]['LpuLevel_Code'];
					$opt["globals"]["birth_mes_level_id"] = isset($info[0]['BirthMesLevel_id'])?$info[0]['BirthMesLevel_id']:null;
					$opt["globals"]["birth_mes_level_code"] = isset($info[0]['BirthMesLevel_Code'])?$info[0]['BirthMesLevel_Code']:null;
					$opt["globals"]["lpu_type_id"] = $info[0]['LpuType_id'];
					$opt["globals"]["lpu_type_code"] = $info[0]['LpuType_Code'];
					$opt["globals"]["lpu_regnomc"] = $info[0]['Lpu_RegNomC'];
					$opt["globals"]["lpu_isLab"] = $info[0]['Lpu_IsLab'];

					// Здесь выбрать medstafffact_id (или несклько для этого врача, и отделения туда же и участки)
					$ms = $this->umodel->getMedStaffFact($data);
					$i = 0;
					$_SESSION['linkedLpuIdList'] = $this->Org_model->getLinkedLpuIdList(array('Lpu_id' => $data['Lpu_id']));
					$_SESSION['lpuIsTransit'] = $this->Org_model->getLpuIsTransit(array('Lpu_id' => $data['Lpu_id']));
					$_SESSION['Lpu_IsTest'] = $this->Org_model->getLpuIsTest(array('Lpu_id' => $data['Lpu_id']));
					$_SESSION['MedStaffFact'] = array();
					$_SESSION['LpuSection'] = array();
					$_SESSION['LpuRegion'] = array();
					$_SESSION['mp_is_zav'] = false;
					$_SESSION['mp_is_uch'] = false;
					if (is_array($ms)) {
						foreach ($ms as $row) {
							$_SESSION['MedStaffFact'][$i] = $row['MedStaffFact_id'];
							$_SESSION['LpuSection'][$i] = $row['LpuSection_id'];
							$_SESSION['LpuRegion'][$i] = $row['LpuSection_id'];
							if ($row['mp_is_uch'] == 2)
								$_SESSION['mp_is_uch'] = true;
							if ($row['mp_is_zav'] == 2)
								$_SESSION['mp_is_zav'] = true;
							$i++;
						}
					}
					$opt["globals"]['linkedLpuIdList'] = $_SESSION['linkedLpuIdList'];
					$opt["globals"]['lpuIsTransit'] = $_SESSION['lpuIsTransit'];
					$opt["globals"]['lpu_istest'] = $_SESSION['Lpu_IsTest'];
					$opt["globals"]['medstafffact'] = $_SESSION['MedStaffFact'];
					$opt["globals"]['lpusection'] = $_SESSION['LpuSection'];
					$opt["globals"]['lpuregion'] = $_SESSION['LpuSection'];
					$opt["globals"]['medpersonal_id'] = $_SESSION['medpersonal_id'];
					$opt["globals"]['mp_is_zav'] = $_SESSION['mp_is_zav'];
					$opt["globals"]['mp_is_uch'] = $_SESSION['mp_is_uch'];
				}
			}
			
			//  Tagir Доступ МО к данным журнала анкетирования онко любой МО
            if (isset ($opt['polka']['onkoctrlAccessAllLpu'])) {
                $opt["globals"]["onkoctrlAccessAllLpu"] = $opt['polka']['onkoctrlAccessAllLpu'];
            }
                        
			//  Tagir Разрешение на редактирование справочников вакцинации
			$dataVac = array(
				'Name' => 'vacSprAccesFull',
				'pmUser_id' => $_SESSION['pmuser_id']
			);
			$this->load->model('VaccineCtrl_model', 'vacmodel');
			$vac_access = $this->vacmodel->getVacSettings($dataVac);
            if ( $vac_access ) {
				$opt["globals"]["vacSprAccesFull"] = '1';
			}

            // данные контрагента для пользователя
            $inf_c = $this->umodel->getCurrentOrgContragent($data);
            if ($inf_c) {
                $opt["globals"]["Contragent_id"] = $inf_c[0]['Contragent_id'];
                $opt["globals"]["Contragent_Name"] = toUtf($inf_c[0]['Contragent_Name']);
                $opt["globals"]["ContragentType_SysNick"] = toUtf($inf_c[0]['ContragentType_SysNick']);
                // пишем id в сессию
                $_SESSION['Contragent_id'] = $inf_c[0]['Contragent_id'];
            }
		}
		else {
			// наименование текущей аптеки
			$this->load->model("User_model", "umodel");
			$info = $this->umodel->getCurrentOrgFarmacyData($data);
			if ($info) {
				$opt["globals"]["OrgFarmacy_Nick"] = toUtf($info[0]['OrgFarmacy_Nick']);
			}
			// если под нет админом
			if ( isset($_SESSION['isFarmacyNetAdmin']) && $_SESSION['isFarmacyNetAdmin'] === true )
				$opt["globals"]["isFarmacyNetAdmin"] = true;
			// Выбираем контрагента по текущей аптеке
			$inf_c = $this->umodel->getCurrentOrgFarmacyContragent($data);

			if (!$inf_c) {
				$inf_c = $this->umodel->getCurrentOrgContragent($data);
			}

			if ($inf_c) {
				$opt["globals"]["Contragent_id"] = $inf_c[0]['Contragent_id'];
				$opt["globals"]["Contragent_Name"] = toUtf($inf_c[0]['Contragent_Name']);
				$opt["globals"]["ContragentType_SysNick"] = toUtf($inf_c[0]['ContragentType_SysNick']);
				// И пишем его id в сессию
				$_SESSION['Contragent_id'] = $inf_c[0]['Contragent_id'];
				$_SESSION['Org_pid'] = $inf_c[0]['Org_pid'];
			}
			
		}

		$opt["globals"]['pmuser_id'] = $_SESSION['pmuser_id'];
		$opt["globals"]['pmuser_name'] = $_SESSION['user'];
		$opt["globals"]['pmuser_fullname'] = $_SESSION['surname'] . ' ' . $_SESSION['firname'] . ' ' . $_SESSION['secname'];

		// передаем признак доступности remoteDB (MongoDB)
		$opt["globals"]["useRemoteDB"] = false;
		// Если MongoDB доступно
		$mongoDb = checkMongoDb();
		if (!empty($mongoDb)) {
			// Получаем версию локальных справочников
			$this->load->model('MongoDBWork_model', 'mongodbmodel');
			$opt["globals"]["mongoDBVersion"] = $this->mongodbmodel->getVersion();
			$opt["globals"]["useRemoteDB"] = true;
		}
		// передаем признак мобильности
		$opt["globals"]["client"] = (isset($_SESSION['client']))?$_SESSION['client']:'';
		$opt["globals"]["curARMType"] = (isset($_SESSION['CurARM']['ARMType']))?$_SESSION['CurARM']['ARMType']:'default';
		$opt["globals"]["defaultARM"] = !empty($opt['defaultARM']['ARMType']) ? $opt['defaultARM']['ARMType'] : null;
		$opt["globals"]["setARM_id"] = (isset($_SESSION['SetARM_id']))?$_SESSION['SetARM_id']:'default';
		// unset($_SESSION['SetARM_id']); // убрал, т.к. при перечитывании настроек (при выборе МО) слетает и открывается АРМ по умолчанию.
		// Глобальные опции
		$_SESSION['setting']['server'] = $opt['globals'];
		
		If (isset($_SESSION['groups']))
			$opt["globals"]["groups"] = $_SESSION['groups'];

		if ( isset($_SESSION['medpersonal_id']) && is_numeric($_SESSION['medpersonal_id']) && $_SESSION['medpersonal_id'] > 0 ) {
			$opt["globals"]["medpersonal_id"] = $_SESSION['medpersonal_id'];
			$this->load->model('MedPersonal_model');
			$opt["globals"]["person_id"] = $this->MedPersonal_model->getPersonIdByMedPersonal(array(
				'MedPersonal_id' => $opt["globals"]["medpersonal_id"]
			));
		}
		else {
			$opt["globals"]["medpersonal_id"] = NULL;
			$opt["globals"]["person_id"] = NULL;
		}

		$opt["globals"]["denied_diags"] = getDeniedDiagOptions();
		$opt["globals"]["denied_lpus"] = getDeniedLpuOptions();
		$opt["globals"]["denied_lpu_buildings"] = getDeniedLpuBuildingOptions();

		$opt["globals"]["date"] = date('d.m.Y');
		if (array_key_exists('wialon', $_SESSION)) $opt["globals"]["wialon"] = $_SESSION['wialon'];
		
		$opt["globals"]['birtpath'] = defined('BIRT_SERVLET_PATH')?BIRT_SERVLET_PATH:'';

		$opt["globals"]['confluencepath'] = defined('CONFLUENCE_PATH')?CONFLUENCE_PATH:'';
		$opt["globals"]['confluenceauthpath'] = defined('CONFLUENCE_AUTH_PATH')?CONFLUENCE_AUTH_PATH:'';
		$opt["globals"]['wikipath'] = defined('WIKI_PATH')?WIKI_PATH:'';

		$opt["globals"]['NODE_ENABLED'] = defined('NODE_ENABLED') ? (bool)NODE_ENABLED : true;
		$opt["globals"]["smp"]['NodeJSSocketConnectHost'] = self::getNodeJSSmpSocketConnectHost();

		//доступ пользователю к Т9 в документах. Разрешение группе + личная опция
		$session = getSessionParams();
		$opt["globals"]["enableT9"] = (getRegionNick()=='vologda') && $this->opmodel->checkAllowT9($session) && $opt['editorT9']['enableT9'];

        /**
         * @DEBUG
         */
		$opt["globals"]["nodePortalConnectionHost"] = self::getNodeJSPortalProxyConnectionHost();

		$opt["globals"]["NodeJSControl"]['enable'] = false;
		if (defined('NODEJS_CONTROL_ENABLE')) {
			$opt["globals"]["NodeJSControl"]['enable'] = NODEJS_CONTROL_ENABLE;
		}

		$opt["globals"]["VideoChat"]['enable'] = false;
		if (defined('NODEJS_VIDEOCHAT_ENABLE')) {
			$opt["globals"]["VideoChat"]['enable'] = NODEJS_VIDEOCHAT_ENABLE;
		}
		if (defined('NODEJS_VIDEOCHAT_ICE_SERVERS')) {
			$opt["globals"]["VideoChat"]['iceServers'] = json_decode(NODEJS_VIDEOCHAT_ICE_SERVERS, true);
		}

		$wialon_token='';
		$wialon_user='';
		if(empty($_SESSION['wialon']['user']) || empty($_SESSION['wialon']['WialonToken']) || !$_SESSION['wialon']['user'] || !$_SESSION['wialon']['WialonToken']){
			$this->load->model('Wialon_model');
			$result = $this->Wialon_model->retrieveAccessData( $_SESSION );
			$wialon_user = ( !empty($result['MedService_WialonLogin']) && $result['MedService_WialonLogin'] ) ? $result['MedService_WialonLogin'] : '';
			$wialon_token = ( !empty($result['MedService_WialonToken']) && $result['MedService_WialonToken'] ) ? $result['MedService_WialonToken'] : '';
		}
		
		$opt["globals"]["wialon_local"] = array(
			//"user" => defined('WIALON_LOCAL_USER')?WIALON_LOCAL_USER:'',
			//"token" => defined('WIALON_LOCAL_TOKEN')?WIALON_LOCAL_TOKEN:''
			"wialon_url" =>  defined('WIALON_URL') ?  WIALON_URL : '',
			"user" => ( !empty($_SESSION['wialon']['user']) && $_SESSION['wialon']['user']) ? $_SESSION['wialon']['user'] : $wialon_user,
			"token" => ( !empty($_SESSION['wialon']['WialonToken']) && $_SESSION['wialon']['WialonToken'] ) ? $_SESSION['wialon']['WialonToken'] : $wialon_token
		);

		$opt["globals"]["NodeJSControl"]['host'] = self::getNodeJSSocketConnectHost();
		$opt["globals"]["VideoChat"]['host'] = self::getVideoChatSocketConnectHost();

		if (!empty($_SESSION['getwnd']) && in_array($_SESSION['getwnd'], array('swCmpCallCardNewCloseCardWindow', 'swCmpCallCardCloseStreamWindow','swCmpCallCardNewShortEditWindow','swStorageZoneViewWindow','swReportEndUserWindow'))) {
			unset($opt["globals"]["setARM_id"]);
			
			$opt["defaultARM"]["ARMName"] = "АРМ Администратора СМП";
			$opt["defaultARM"]["ARMType"] = 'smpadmin';
			//$opt["defaultARM"]["ARMForm"] = 'swCmpCallCardCloseStreamWindow';
			$opt["globals"]["getwnd"] = $_SESSION['getwnd'];
			if (!empty($_SESSION['cccid'])) $opt["globals"]["cccid"] = $_SESSION['cccid'];
			if (!empty($_SESSION['showTop'])) $opt["globals"]["showTop"] = $_SESSION['showTop'];
			if (!empty($_SESSION['act'])) $opt["globals"]["act"] = $_SESSION['act'];
		}

		if (isset($_SESSION['password_date'])) {
			$opt["globals"]["password_date"] = $_SESSION['password_date'];
		} else {
			$opt["globals"]["password_date"] = null;
		}

		if (isset($_SESSION['openLLOFromEMIASData'])) {
			$opt["globals"]["openLLOFromEMIASData"] = $_SESSION['openLLOFromEMIASData'];
			unset($_SESSION['openLLOFromEMIASData']);
		} else {
			$opt["globals"]["openLLOFromEMIASData"] = null;
		}

		if ( isset($opt['defaultARM']) && is_array($opt['defaultARM']) && array_key_exists('session', $opt['defaultARM']) ) {
			unset($opt['defaultARM']['session']);
		}
		
		// ключ API для Google Maps API
		$opt['globals']['google_api_key'] = defined('GOOGLE_API_KEY') ? GOOGLE_API_KEY : '';

		// ключ API для Yandex Maps API
		$opt['globals']['yandex_api_key'] = defined('YANDEX_API_KEY') ? YANDEX_API_KEY : '';

		// наличие у пользователя сертификатов для ЭМД
		$isEMDEnabled = $this->config->item('EMD_ENABLE');
		$opt['globals']['enableEMD'] = !empty($isEMDEnabled);
		if (!empty($isEMDEnabled)) {
			$this->load->model('EMD_model');
			$opt['globals']['hasEMDCertificate'] = $this->EMD_model->checkUserHasEMDCertificate($data);
		} else {
			$opt['globals']['hasEMDCertificate'] = false;
		}

		if($opt['recepts'] && $opt['recepts']['print_extension'] && $opt['recepts']['print_extension']==3)
			$opt['recepts']['print_extension'] = 1;
		echo(@json_encode($opt));
	}

	/**
	 * Возвращает хост для подключения к NodeJS
	 */
	public static function getNodeJSSmpSocketConnectHost() {
		list($host) = explode(':', $_SERVER['HTTP_HOST']);
		if (defined('NODEJS_SMP_SOCKETSERVER_HOST')) {
			$host = NODEJS_SMP_SOCKETSERVER_HOST;
		}
		if (defined('NODEJS_SMP_SOCKETSERVER_PORT')) {
			$host .= ':' . NODEJS_SMP_SOCKETSERVER_PORT;
			//без порта жизнь не та
			//нет порта - нет соединения к ноду, и как следствие - взрывается консоль
			return $host;
		}

		return false;
	}

	/**
	 * Возвращает хост для подключения к NodeJS для портала
	 */
	public static function getNodeJSPortalProxyConnectionHost() {
		
		// по умолчанию включено
		$nodejs_portal_enable = true;
		if (defined('NODEJS_PORTAL_ENABLE')) {
			$nodejs_portal_enable = NODEJS_PORTAL_ENABLE;
		}
		
		if (defined('NODEJS_PORTAL_PROXY_HOSTNAME') && 
			defined('NODEJS_PORTAL_PROXY_SOCKETPORT') 
			&& $nodejs_portal_enable
		) {
			return NODEJS_PORTAL_PROXY_HOSTNAME.':'.NODEJS_PORTAL_PROXY_SOCKETPORT;
		}

		return false;
	}

	/**
	 * Возвращает хост для подключения к NodeJS
	 */
	public static function getNodeJSSocketConnectHost() {
		list($host) = explode(':', $_SERVER['HTTP_HOST']);
		if (defined('NODEJS_PROMED_SOCKET_HOST')) {
			$host = NODEJS_PROMED_SOCKET_HOST;
		}
		if (defined('NODEJS_PROMED_SOCKET_PORT')) {
			$host .= ':' . NODEJS_PROMED_SOCKET_PORT;
		}

		return $host;
	}

	/**
	 * Возвращает хост для подключения к серверу видеосвяхи
	 */
	public static function getVideoChatSocketConnectHost(){
		list($host) = explode(':', $_SERVER['HTTP_HOST']);

		if (defined('NODEJS_VIDEOCHAT_SOCKET_HOST')) {
			$host = NODEJS_VIDEOCHAT_SOCKET_HOST;
		}
		if (defined('NODEJS_VIDEOCHAT_SOCKET_PORT')) {
			$host .= ':' . NODEJS_VIDEOCHAT_SOCKET_PORT;
		}

		return $host;
	}

	/**
	 * Получение глобальных настроек для формирования страниц с настройками, параметры по умолчанию
	 */
	function getGlobalOptionsForm()
	{
		$this->load->model("Options_model", "opmodel");

		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		$node = $_POST['node'];
		$global_options = $this->opmodel->getOptionsGlobals($data);
		/**
		 * @param $arr
		 * @param $func_arr
		 * @param $node
		 * @param $options
		 * @param $maxnum
		 */
		function process_global_options(&$arr, &$func_arr, $node, $options, $maxnum)
		{
			foreach ($arr as $key=>$value)
			{
				if (is_array($arr[$key]))
					process_global_options($arr[$key], $func_arr, $node, $options, $maxnum);
				else
				{
					if (array_key_exists('vfield', $arr) && array_key_exists('name', $arr))
					{
						$name = $arr['name'];
						$field = $arr['vfield'];
						$arr[$field] = $options['globals'][$name];

						//обработка listeners
						if ($key == 'name' && isset($arr['listeners'])) {
							$listeners = $arr['listeners'];
							foreach($arr['listeners'] as $lstn => $func_txt){
								if (!empty($func_txt)) {
									$func_arr[] = $func_txt;
									$arr['listeners'][$lstn] = '*function_'.(count($func_arr)-1).'*';
								}
							}
						}

						if( isset($arr['xtype']) && $arr['xtype'] == 'radio' ) {
							$arr[$field] = $arr['inputValue'] == $options['globals'][$name] ? true : false;
						}
					}
				}
			}
		}
		$options[$node] = $this->default_global_options[$node];
		$maxnum = 0;
		$func_arr = array();
		//var_dump($options[$node]);die;
		process_global_options($options[$node], $func_arr, $node, $global_options, $maxnum);
		
		$jsn = json_encode($options);

		for($i = 0; $i < count($func_arr); $i++) {
			$jsn = str_replace('"*function_'.$i.'*"', $func_arr[$i], $jsn);
		}

		echo($jsn);
	}

	/**
	 * Получение глобального списка категорий настроек в виде дерева
	 */
	function getGlobalOptionsTree()
	{
		$this->ReturnData($this->global_options_tree);
	}
	
	
	/**
	 * Получение настроек для формирования страниц с настройками, параметры по умолчанию
	 */
	function getOptionsForm()
	{
		if ( havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('FarmacyAdmin') || havingGroup('FarmacyNetAdmin') )
		{
			$this->admin_field_disabled = false;
		} else {
			$this->admin_field_disabled = true;
		}

		$node = $_POST['node'];
		$data = array();
		// Получаем сессионные переменные
		$data = array_merge($data, getSessionParams());

		// некоторые настройки берем из базы
		$this->load->model("Options_model", "opmodel");

		// Генерация опций по умолчанию, в зависимости от прав пользователя
		$this->default_options = $this->opmodel->getDefaultOptions($data);
		
		$opt = $this->opmodel->getOptionsAll($data);

		switch ( $node ) {
			case 'appearance':
				if ( empty($opt['appearance']['menu_type']) || $opt['appearance']['menu_type'] == 'ribbon' ) {
					$opt['appearance']['menu_type'] = 'simple';
				}
				break;

			case 'costprint':
				$opt["costprint"]["next_num"] = $this->opmodel->getFirstResultFromQuery("select MAX(ISNULL(EvnCostPrint_Number, 0)) + 1 as next_num from dbo.EvnCostPrint with (nolock)");
				break;

			case 'medpers':
				if ( !empty($opt['medpers']['allowed_medpersonal_ev']) ) {
					$vizitVals = explode(',', $opt['medpers']['allowed_medpersonal_ev']);
					foreach ($vizitVals as $vizitVal) {
						$opt['medpers']['vizitpost'.$vizitVal] = true;
					}
				} else {
					$opt['medpers']['vizitpost1'] = true;
					$opt['medpers']['vizitpost6'] = true;
				}
				if ( !empty($opt['medpers']['allowed_medpersonal_es']) ) {
					$vizitVals = explode(',', $opt['medpers']['allowed_medpersonal_es']);
					foreach ($vizitVals as $vizitVal) {
						$opt['medpers']['sectionpost'.$vizitVal] = true;
					}
				} else {
					$opt['medpers']['sectionpost1'] = true;
					$opt['medpers']['sectionpost6'] = true;
				}
				break;

			case 'polka':
				$opt['polka']['next_card_code'] = $this->opmodel->getPMGenValue($data, 'PersonCard');
				$opt['polka']['evnpl_numcard_next_num'] = $this->opmodel->getPMGenValue($data, 'EvnPL');
				$opt['polka']['evnplstom_numcard_next_num'] = $this->opmodel->getPMGenValue($data, 'EvnPLStom');
				break;

			case 'stac':
				$opt['stac']['evnps_numcard_next_num'] = $this->opmodel->getPMGenValue($data, 'EvnPS');
				break;
		}

		$options[$node] = $this->default_options[$node];
		$maxnum = 0;

		// Загрузка расписания вызова на дом
		$this->load->model('HomeVisit_model', 'hvmodel');
		$cdata = array();
		$cdata['Lpu_id'] = $_SESSION['lpu_id'];
		$cresult = $this->hvmodel->getHomeVisitWorkMode($cdata);
		if (count($cresult) > 0) {
			foreach ($cresult as $item) {
				if ($item['CalendarWeek_id'] > 0)  {
					$opt['homevizit']['homevizit_day'.$item['CalendarWeek_id']] = 1;
					$opt['homevizit']['homevizit_begtime'.$item['CalendarWeek_id']] = ConvertDateFormat($item['HomeVisitWorkMode_begDate'],'H:i');
					$opt['homevizit']['homevizit_endtime'.$item['CalendarWeek_id']] = ConvertDateFormat($item['HomeVisitWorkMode_endDate'],'H:i');
				}
			}
		}

		//var_dump($opt);
		/**
		 * Рекурсивная робработка настроек
		 */
		function process_options(&$arr, $node, $opt, $maxnum)
		{
			foreach ($arr as $key=>$value)
			{
				if ( is_array($arr[$key]) )
					process_options($arr[$key], $node, $opt, $maxnum);
				else
				{
					if ( array_key_exists('vfield', $arr) && array_key_exists('name', $arr) ) {
						$name = $arr['name'];
						$field = $arr['vfield'];
						if ( isset($opt[$node]) && array_key_exists($name, $opt[$node]) )
						{
							$arr[$field] = $opt[$node][$name];
							// для радиобаттонов
							if( isset($arr['xtype']) && $arr['xtype'] == 'radio' ) {
								$arr[$field] = $arr['inputValue'] == $opt[$node][$name] ? true : false;
							}
						}
					}
				}
			}
		}

		process_options($options[$node], $node, $opt, $maxnum);

		//print_r($options[$node]); die();
		
		$this->ReturnData($options);
	}

	/**
	 * Формирование списка категорий настроек в виде дерева
	 */
	function getOptionsTree()
	{
		if ( havingGroup('SuperAdmin') || havingGroup('LpuAdmin') || havingGroup('FarmacyAdmin') || havingGroup('FarmacyNetAdmin') )
		{
			$this->admin_field_disabled = false;
		} else {
			$this->admin_field_disabled = true;
		}

		$session = getSessionParams();

		$isLpu = false;
		if (!empty($session['Lpu_id'])) {
			$isLpu = true;
		}

		$canEditIndividualPeriod = false;
		if ($isLpu && havingGroup('LpuAdmin')) {
			$canEditIndividualPeriod = $this->opmodel->checkLpuIndividualPeriod($session['Lpu_id']);
		}

		$val = array(
			array(
				'text' => toUtf('Поликлиника'),
				'id' => 'polka',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || getRegionNick() == 'saratov'
			),
            array(
                'text'  => toUtf('Диспансеризация/Профосмотры'),
                'id'    => 'dispprof',
                'leaf'  => true,
                'cls'   => 'file',
                'hidden' => !$isLpu || $this->admin_field_disabled,
            ),
			array(
				'text' => toUtf('ЛЛО'),
				'id' => 'recepts',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Внешний вид'),
				'id' => 'appearance',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUtf('Адрес'),
				'id' => 'address',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Стационар'),
				'id' => 'stac',
				'hidden' => !$isLpu || (getRegionNick() != 'ufa' && (($this->admin_field_disabled) || (getRegionNick() == 'saratov'))),
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUtf('Реестры'),
				'id' => 'registry',
				'hidden' => !$isLpu || ((!isSuperadmin() && !havingGroup('LpuAdmin')) || (getRegionNick() == 'saratov')),
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUtf('М. свидетельства'),
				'id' => 'medsvid',
				'hidden' => !$isLpu || (($this->admin_field_disabled) || (getRegionNick() == 'saratov')),
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUtf('ЛВН'),
				'id' => 'evnstick',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || getRegionNick() == 'saratov'
			),
			array(
				'text' => toUtf('Услуга'),
				'id' => 'usluga',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || getRegionNick() == 'saratov'
			),
			array(
				'text' => toUtf('Глоссарий'),
				'id' => 'glossary',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || getRegionNick() == 'saratov'
			),
			array(
				'text' => toUtf('Закуп медикаментов'),
				'id' => 'drugpurchase',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => ($isLpu ? toUtf('Учет медикаментов в МО') : toUtf('Учет медикаментов')),
				'id' => 'drugcontrol',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => false//!$isLpu
			),
			array(
				'text' => toUtf('Разное'),
				'id' => 'others',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => (getRegionNick() == 'saratov')
			),
			array(
				'text' => toUtf('ЭМК'),
				'id' => 'emk',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Назначения'),
				'id' => 'prescription',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Вызов врача на дом'),
				'id' => 'homevizit',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Лаборатория'),
				'id' => 'lis',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Уведомления'),
				'id' => 'notice',
				'leaf' => true,
				'cls' => 'file'
			),
			array(
				'text' => toUtf('Печать'),
				'id' => 'print',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Справка о стоимости МП'),
				'id' => 'costprint',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || (getRegionNick() != 'khak')
			),
			array(
				'text' => toUtf('Сервис передачи в РМИС'),
				'id' => 'rmis',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || (getRegionNick() != 'ekb')
			),
			array(
				'text' => toUtf('Фильтрация мед. персонала в документах'),
				'id' => 'medpers',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('T9'),
				'id' => 'editorT9',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || !$this->opmodel->checkAllowT9($session) || (getRegionNick() != 'vologda')
			),
			array(
				'text' => toUtf('Запись пациентов'),
				'id' => 'EditIndividualPeriod',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$canEditIndividualPeriod
			),
			array(
				'text' => toUtf('Электронная очередь'),
				'id' => 'electronicqueue',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu || (!isSuperadmin() && !havingGroup('LpuAdmin'))
			),
			array(
				'text' => toUtf('ЭКГ'),
				'id' => 'ecg',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => !$isLpu
			),
			array(
				'text' => toUtf('Мониторинг беременных'),
				'id' => 'pregnantMonitor',
				'leaf' => true,
				'cls' => 'file',
				'hidden' => getRegionNick() != 'khak'
			)
		);

        $this->ReturnData($val);
	}

	/**
	 * Получение списка МО с запретом формирования реестров
	 */
	function loadCheckRegistryAccessGrid() {
		$data = array();

		$this->load->model("Options_model", "opmodel");
		$response = $this->opmodel->loadCheckRegistryAccessGrid($data);

		$this->ProcessModelMultiList($response, true, true)->ReturnData();
	}

	/**
	 * Сохранение запрета формирования реестров для МО
	 */
	function saveCheckRegistryAccess() {
		$data = array(
			'Lpu_id' => $_POST['LimitLpu_id'],
			'pmUser_id' => $_SESSION['pmuser_id']
		);

		$this->load->model("Options_model", "opmodel");
		$response = $this->opmodel->saveCheckRegistryAccess($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Удаление запрета формирования реестров для МО
	 */
	function deleteCheckRegistryAccess() {
		$data = array('DataStorage_id' => $_POST['DataStorage_id']);

		$this->load->model("Options_model", "opmodel");
		$response = $this->opmodel->deleteCheckRegistryAccess($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Загрузка списка для настройки автоматического влючения в регистры
	 */
	function loadPersonRegisterAutoIncludeGrid() {
		$this->load->model("Options_model", "opmodel");
		$data = array();
		$response = $this->opmodel->loadPersonRegisterAutoIncludeGrid($data);

		$this->ProcessModelSave($response)->ReturnData();
	}

	/**
	 * Сохранение настроек автоматического влючения в регистры
	 */
	function savePersonRegisterAutoIncludeOptions() {
		$this->load->model("Options_model", "opmodel");
		$data = $_POST;
		$data['session'] = array('pmuser_id' => $_SESSION['pmuser_id']);
		$response = $this->opmodel->saveOptionsGlobals($data);

		if (!$response) {
			$val = array('success' => false);
		} else {
			$val = array('success' => true);
		}

		$this->ReturnData($val);
	}
	
	/**
	 *	Загрузка lpu_buildings для работы с ними #85307
	 */
	public function getLpuBuildingsWorkAccess(){
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);
		
		
		if ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) ) {
			$result = array(
				"lpuBuildingsWorkAccess" => ( isset($settings['lpuBuildingsWorkAccess']) && is_array($settings['lpuBuildingsWorkAccess']) )?$settings['lpuBuildingsWorkAccess']:null,
				"success"=>true
			);
			
			$this->ReturnData($result);
		} else {
			$this->ReturnData(array('success'=>false));	
		}
	}
	
	
	/**
	 *	Сохранение lpu_buildings для работы с ними #85307
	 */
	public function saveLpuBuildingsWorkAccess(){		
		$lpuWA =  explode(",", $_POST['lpuBuildingsWorkAccess']);		

		$user = pmAuthUser::find($_SESSION['login']);

		$settings = @unserialize($user->settings);
		if (is_array($lpuWA)) {			
			$settings['lpuBuildingsWorkAccess'] = $lpuWA;
		} else {
			return false;
		}

		$user->settings = serialize($settings);
		$_SESSION['settings'] = $user->settings;
		
		$user->post();
		$this->load->model("User_model", "umodel");
		$this->umodel->ReCacheUserData($user);
		$this->ReturnData(array('success'=>true));
	}

	/**
	 *	Сохранение состояния грида
	 */
	public function saveGridState(){
		$gridState = $_POST['gridState'];
		$gridRefId = $_POST['gridRefId'];

		$user = pmAuthUser::find($_SESSION['login']);

		$settings = @unserialize($user->settings);

		if(!empty($gridState) && !empty($gridRefId)){
			$settings['gridStates'][$gridRefId] = $gridState;

			$user->settings = serialize($settings);
			$_SESSION['settings'] = $user->settings;

			$user->post();
			$this->load->model("User_model", "umodel");
			$this->umodel->ReCacheUserData($user);
			$this->ReturnData(array('success'=>true));
		}
	}

	/**
	 *	Загрузка состояния грида
	 */
	public function getGridState(){
		$gridRefId = $_POST['gridRefId'];

		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);

		if ( isset($settings) && isset($settings['gridStates'][$gridRefId]) ) {
			$result = array(
				"gridState" => ( isset($settings['gridStates'][$gridRefId]) )?$settings['gridStates'][$gridRefId]:null,
				"success"=>true
			);
			$this->ReturnData($result);
		} else {
			$this->ReturnData(array('success'=>false));
		}
	}

	/**
	 *	Загрузка МО для управления
	 */
	public function getLpuWorkAccess(){
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);


		if ( isset($settings['lpuWorkAccess']) && is_array($settings['lpuWorkAccess']) ) {
			$result = array(
				"lpuWorkAccess" => ( isset($settings['lpuWorkAccess']) && is_array($settings['lpuWorkAccess']) )?$settings['lpuWorkAccess']:null,
				"success"=>true
			);

			$this->ReturnData($result);
		} else {
			$this->ReturnData(array('success'=>false));
		}
	}

	/**
	 *	Сохранение МО для управления в настройки
	 */
	public function saveLpuWorkAccess(){
		$lpuWA =  explode(",", $_POST['lpuWorkAccess']);

		$user = pmAuthUser::find($_SESSION['login']);

		$settings = @unserialize($user->settings);
		if (is_array($lpuWA)) {
			$settings['lpuWorkAccess'] = $lpuWA;
		} else {
			return false;
		}

		$user->settings = serialize($settings);
		$_SESSION['settings'] = $user->settings;

		$user->post();
		$this->load->model("User_model", "umodel");
		$this->umodel->ReCacheUserData($user);
		$this->ReturnData(array('success'=>true));
	}

	/**
	 * Сохранение 
	 */
	public function saveLpuBuildingForTimingCmk () {

		$LpuBuilding_id = $_POST['LpuBuilding_id'];

		$user = pmAuthUser::find($_SESSION['login']);

		$settings = @unserialize($user->settings);
		if ($LpuBuilding_id) {
			$settings['cmkTimingLpuBuilding'] = $LpuBuilding_id;
		} else {
			return false;
		}

		$user->settings = serialize($settings);
		$_SESSION['settings'] = $user->settings;

		$user->post();
		$this->load->model("User_model", "umodel");
		$this->umodel->ReCacheUserData($user);
		$this->ReturnData(array('success'=>true));

	}

	/**
	 *	Загрузка МО для управления
	 */
	public function getLpuBuildingForTimingCmk(){
		$user = pmAuthUser::find($_SESSION['login']);
		$settings = @unserialize($user->settings);


		if ( !empty($settings['cmkTimingLpuBuilding'])  ) {
			$result = array(
				"LpuBuilding_id" => $settings['cmkTimingLpuBuilding'],
				"success" => true
			);

			$this->ReturnData($result);
		} else {
			$this->ReturnData(array('success'=>false));
		}
	}
}

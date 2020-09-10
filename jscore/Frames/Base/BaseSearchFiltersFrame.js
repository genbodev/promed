/**
 * sw.Promed.BaseSearchFiltersPanel - Класс базового набора фильтров для форм поиска
 *
 *
 * @project  PromedWeb
 * @copyright  (c) Swan Ltd, 2009
 * @package frames
 * @author  Петухов Иван
 * @class sw.Promed.BaseSearchFiltersPanel
 * @extends Ext.form.FormPanel
 * @version 07.12.2009
 */

/**
 * Подключение фрейма фильтров
 */
 /**
 * @config {String} id Идентификатор самой панели, для последующего обращения к ней
 */
 /**
 * @config {Object} ownerWindow Окно, на которое добавляется панель фильтров
 */
 /**
 * @config {String} searchFormType Тип формы поиска, на которой используется панель фильтров
 */
 /**
 * @config {Integer} tabIndexBase Число, с которого начинаются табиндексы на панели
 */
 /**
 * @config {String} tabPanelId Идентификатор табпанели, для последующего обращения к ней
 */
 /**
 * @config {Array} tabs Массив, содержащий панели с дополнительными фильтрами. 1 элемент - одна панель
 */
 /**
 * @config {Array} hiddenFields Массив, содержащий дополнительные скрытые поля формы
 */
 /**
 * @config {Object} ownerWindowWizardPanel Панель гридов, которые переключаются в зависимости от выбора объекта поиска (используется в поиске ТАП)
 */

function getBaseSearchFiltersFrame( config ) {
	return new sw.Promed.BaseSearchFiltersFrame( config );
}

/**
 * Сам фрейм с фильтрами
 */
sw.Promed.BaseSearchFiltersFrame = Ext.extend(Ext.form.FormPanel, {
	/**
	 * После рендеринга добавляем события поиска по нажатию Enter в любом поле формы
	 */
	afterRender : function() {
		var personIdentSelectMenu = this.findById(this.tabPanelId).find('name', 'Person_isIdentified_form');
		if (this.searchFormType == 'CmpCallCard') {
			personIdentSelectMenu[0].show();
		} else {
			personIdentSelectMenu[0].hide();
		}

		var refusePanel = this.findById(this.tabPanelId + '_RefusePanel');
		if (!getRegionNick().inlist(['kz'])) {
			refusePanel.show();
		} else {
			refusePanel.hide();
		}

		var PrivilegegTypeField = this.getForm().findField('PrivilegeType_id');
		if (getRegionNick().inlist(['kz'])) {
			PrivilegegTypeField.setFieldLabel(langs('Категория / Нозология'));
		}

		var SubCategoryPrivTypePanel = this.findById(this.tabPanelId + '_SubCategoryPrivTypePanel');
		if (getRegionNick().inlist(['kz'])) {
			SubCategoryPrivTypePanel.show();
		} else {
			SubCategoryPrivTypePanel.hide();
		}

		var map = new Ext.KeyMap(this.getEl(), [{
			key: [13],
			fn: function() {
				this.getOwnerWindow().doSearch();
			},
			scope: this
		}]);
	},
	useArchive: 0,
	allowPersonPeriodicSelect: false,
	ownerWindowWizardPanel: null,
	ownerWindowPSWizardPanel: null,
	autoScroll: true,
	bodyBorder: false,
	border: false,
	buttonAlign: 'left',
	frame: false,
	//height: 250,
		//split: true,
//        collapsible: true,
//        collapsed: false,
//        floatable: false,
//        titleCollapse: false,
//        title: '<div>Фильтры</div>',
//        plugins: [ Ext.ux.PanelCollapsedTitle ],
//        autoHide: false,
//        forceLayout: true,
//        split: true,
//        autoWidth: true,
	/**
	 * Получение ссылки на окно, на котором находится форма
	 * @return {Ext.Window}
	 */
	getDataFromBdz: function(bdzData, person_data) {
		this.getForm().findField('Person_Firname').setValue(bdzData.firName);
		this.getForm().findField('Person_Secname').setValue(bdzData.secName);
		this.getForm().findField('Person_Surname').setValue(bdzData.surName);
		this.getForm().findField('Person_Birthday').setValue(bdzData.birthDay);
		if (getRegionNick().inlist(['ufa'])) {
				this.getForm().findField('Polis_Num').setValue(bdzData.polisNum);
			} else {
				this.getForm().findField('Person_Code').setValue(bdzData.polisNum);
			}
		this.getOwnerWindow().doSearch();
	},
	getDataFromUec: function(uecData, person_data) {
		this.getForm().findField('Person_Firname').setValue(uecData.firName);
		this.getForm().findField('Person_Secname').setValue(uecData.secName);
		this.getForm().findField('Person_Surname').setValue(uecData.surName);
		this.getForm().findField('Person_Birthday').setValue(uecData.birthDay);
		if (getRegionNick().inlist(['ufa'])) {
				this.getForm().findField('Polis_Num').setValue(uecData.polisNum);
			} else {
				this.getForm().findField('Person_Code').setValue(uecData.polisNum);
			}
		this.getOwnerWindow().doSearch();
	},
	getOwnerWindow: function () {
		return this.ownerWindow
	},
	/**
	 * Обновление размера элементов при изменени контента вкладок
	 */
	resetLayout: function(){
		this.Panel.syncSize();
		this.Panel.doLayout();
		this.getOwnerWindow().syncSize();
		this.getOwnerWindow().doLayout();
		return true;
	},
	/**
	 * Панель с фильтрами по пользователям и датам внесения
	 */
	getRecordsCount: function() {
		var form = this;

		if ( !form.getForm().isValid() ) {
			sw.swMsg.alert(lang['poisk'], lang['proverte_pravilnost_zapolneniya_poley_na_forme_poiska']);
			return false;
		}

		var loadMask = new Ext.LoadMask(form.getOwnerWindow().getEl(), { msg: "Подождите, идет подсчет записей..." });
		loadMask.show();

		var post = getAllFormFieldValues(form);

		if ( post.PersonCardStateType_id == null ) {
			post.PersonCardStateType_id = 1;
		}

		if ( post.PrivilegeStateType_id == null ) {
			post.PrivilegeStateType_id = 1;
		}

		// Надо добавить передачу параметров по умолчанию для специфических вкладок

		Ext.Ajax.request({
			callback: function(options, success, response) {
				loadMask.hide();

				if ( success ) {
					var response_obj = Ext.util.JSON.decode(response.responseText);

					if ( response_obj.Records_Count != undefined ) {
						sw.swMsg.alert(lang['podschet_zapisey'], lang['naydeno_zapisey'] + response_obj.Records_Count);
					}
					else {
						sw.swMsg.alert(lang['podschet_zapisey'], response_obj.Error_Msg);
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_podschete_kolichestva_zapisey_proizoshli_oshibki']);
				}
			},
			params: post,
			url: C_SEARCH_RECCNT
		});
	},
	getUserFiltersPanel: function( num ) {
		return {
			autoHeight: true,
			bodyStyle: 'margin-top: 5px;',
			border: false,
			layout: 'form',
			listeners: {
				'activate': function(panel) {
					if (!isAdmin) {
						this.getForm().findField('onlySQL').hideContainer();
					}
					this.getForm().findField('pmUser_insID').focus(250, true);
				}.createDelegate(this)
			},
			title: '<u>' + num + '</u>. ' + lang['polzovatel'],
			id: this.tabPanelId + 'filterUser',

			// tabIndexStart: this.tabIndexBase + 78
			items: [{
				autoHeight: true,
				style: 'padding: 0px;',
				title: lang['dobavlenie'],
				width: 755,
				xtype: 'fieldset',

				items: [ new sw.Promed.SwProMedUserCombo({
					hiddenName: 'pmUser_insID',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								// Переход к последней кнопке в окне
								this.getOwnerWindow().buttons[this.getOwnerWindow().buttons.length-1].focus();
							}
						}.createDelegate(this)
					},
					tabIndex: this.tabIndexBase + 91,
					width: 300
				}), {
					fieldLabel: lang['data'],
					name: 'InsDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
					tabIndex: this.tabIndexBase + 92,
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['diapazon_dat'],
					name: 'InsDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: this.tabIndexBase + 93,
					width: 170,
					xtype: 'daterangefield'
				}]
			}, {
				autoHeight: true,
				style: 'padding: 0px;',
				title: lang['izmenenie'],
				width: 755,
				xtype: 'fieldset',

				items: [ new sw.Promed.SwProMedUserCombo({
					hiddenName: 'pmUser_updID',
					tabIndex: this.tabIndexBase + 94,
					width: 300
				}), {
					fieldLabel: lang['data'],
					name: 'UpdDate',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
					tabIndex: this.tabIndexBase + 95,
					width: 100,
					xtype: 'swdatefield'
				}, {
					fieldLabel: lang['diapazon_dat'],
					enableKeyEvents: true,
					listeners: {
						'keydown': function(inp, e) {
							if (!e.shiftKey && e.getKey() == e.TAB)
							{
								if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
								{
									var grid_id = this.tabGridId;
									Ext.TaskMgr.start({
										run : function() {
											Ext.TaskMgr.stopAll();
											Ext.getCmp(grid_id).focus();													
										},
										interval : 200
									});
									e.stopEvent();
								}								
							}
						}.createDelegate(this)
					},
					name: 'UpdDate_Range',
					plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
					tabIndex: this.tabIndexBase + 96,
					width: 170,
					xtype: 'daterangefield'
				}]
			}, {
				xtype  : 'panel',
				border : false,	
				layout: 'form',
				autoheight: true,
				items: [{
					fieldLabel: lang['sql-zapros'],
					name: 'onlySQL',
					tabIndex: this.tabIndexBase + 97,
					width: 180,
					xtype: 'checkbox'
				}]
			}]
		};
	},
	initComponent: function() {
		var searchFiltersPanel = this;

					this.topFilter = {
						hidden: (this.searchFormType == 'PersonDopDispPlan'),
						border: false,
						layout: 'column',
						items: [{
								border: false,
								layout: 'form',
                                labelWidth: 150,
								items: [{
										allowBlank: true,
										codeField: 'PersonPeriodicType_Code',
										disabled: !this.allowPersonPeriodicSelect,
										displayField: 'PersonPeriodicType_Name',
										editable: false,
										fieldLabel: lang['tip_poiska_cheloveka'],
										hiddenName: 'PersonPeriodicType_id',
										hideEmptyRow: true,
										ignoreIsEmpty: true,
										listeners: {
												'blur': function(combo)  {
														if ( combo.value == '' )
																combo.setValue(1);
												}
										},
										store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
														[ 1, 1, lang['po_tekuschemu_sostoyaniyu'] ],
														[ 2, 2, lang['po_sostoyaniyu_na_moment_sluchaya'] ],
														[ 3, 3, lang['po_vsem_periodikam'] ]
												],
												fields: [
														{ name: 'PersonPeriodicType_id', type: 'int'},
														{ name: 'PersonPeriodicType_Code', type: 'int'},
														{ name: 'PersonPeriodicType_Name', type: 'string'},
														{ name: 'ReceptFinance_id', type: 'int'}
												],
												key: 'PersonPeriodicType_id',
												sortInfo: { field: 'PersonPeriodicType_Code' }
										}),
										tabIndex: -1,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{PersonPeriodicType_Code}</font>&nbsp;{PersonPeriodicType_Name}',
												'</div></tpl>'
										),
										value: 1,
										valueField: 'PersonPeriodicType_id',
                                        width: 300,
										xtype: 'swbaselocalcombo'
								}]
						}, {
								border: false,
								layout: 'form',
                                labelWidth: 150,
								items: [{
										allowBlank: true,										
										disabled: (!this.allowCmpSearchType),
										hidden: (!this.allowCmpSearchType),
										hideLabel: (!this.allowCmpSearchType),										
										displayField: 'SearchType_Name',
										editable: false,
										fieldLabel: lang['dokument_poiska'],
										//fieldLabel: ((this.allowCmpSearchType)?'Документ поиска':''),
										hiddenName: 'SearchType_id',
										hideEmptyRow: true,
										ignoreIsEmpty: true,
										listeners: {
												'blur': function(combo)  {
														if ( combo.value == '' )
																combo.setValue(1);
												}
										},
										store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [														
														[ 1, lang['karta_vyizova'] ],
														[ 2, lang['talon_vyizova'] ]														
												],
												fields: [
														{ name: 'SearchType_id', type: 'int'},								
														{ name: 'SearchType_Name', type: 'string'}
												],
												key: 'SearchType_id'												
										}),
										tabIndex: -1,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{SearchType_Name}',
												'</div></tpl>'
										),
										value: 1,
										valueField: 'SearchType_id',
                                        width: 300,
										xtype: 'swbaselocalcombo'
								}]
						}, {
								border: false,
								layout: 'form',
								style: 'padding-left: 10px',
								items: [{
										allowDepress: false,
										pressed: true,
										text: lang['poisk_talonov'],
										disabled: (!this.ownerWindowWizardPanel),
										hidden: (!this.ownerWindowWizardPanel),
										toggleGroup: 'search_setting_group',
										listeners:
										{
												toggle: function(button, check)
												{
														if (check)
														{
																this.ownerWindowWizardPanel.layout.setActiveItem(0);

																switch ( this.getForm().findField('SearchFormType').getValue() ) {
																	case 'EvnVizitPL':
																		this.getForm().findField('SearchFormType').setValue('EvnPL');
																		this.getForm().findField('EvnVizitPL_isPaid').fireEvent('change', this.getForm().findField('EvnVizitPL_isPaid'), this.getForm().findField('EvnVizitPL_isPaid').getValue());
																	break;

																	case 'EvnVizitPLStom':
																		this.getForm().findField('SearchFormType').setValue('EvnPLStom');
																	break;
																}
														}
												}.createDelegate(this)
										},
										enableToggle: true,
										xtype: 'button'
								}]
						}, {
								border: false,
								layout: 'form',
								style: 'padding-left: 5px',
								items: [{
										allowDepress: false,
										pressed: false,
										text: lang['poisk_posescheniy'],
										disabled: (!this.ownerWindowWizardPanel),
										hidden: (!this.ownerWindowWizardPanel),
										toggleGroup: 'search_setting_group',
										listeners:
										{
												toggle: function(button, check)
												{
														if (check)
														{
																this.ownerWindowWizardPanel.layout.setActiveItem(1);

																switch ( this.getForm().findField('SearchFormType').getValue() ) {
																	case 'EvnPL':
																		this.getForm().findField('SearchFormType').setValue('EvnVizitPL');
																		this.getForm().findField('EvnVizitPL_isPaid').fireEvent('change', this.getForm().findField('EvnVizitPL_isPaid'), this.getForm().findField('EvnVizitPL_isPaid').getValue());
																	break;

																	case 'EvnPLStom':
																		this.getForm().findField('SearchFormType').setValue('EvnVizitPLStom');
																	break;
																}
														}
												}.createDelegate(this)
										},
										enableToggle: true,
										xtype: 'button'
								}]
						}, {
								border: false,
								layout: 'form',
								style: 'padding-left: 10px',
								items: [{
										allowDepress: false,
										pressed: true,
										text: lang['poisk_kvs'],
										disabled: (!this.ownerWindowPSWizardPanel),
										hidden: (!this.ownerWindowPSWizardPanel),
										toggleGroup: 'search_setting_group',
										listeners:
										{
												toggle: function(button, check)
												{
														if (check)
														{
															this.ownerWindowPSWizardPanel.layout.setActiveItem(0);
															this.getForm().findField('SearchFormType').setValue('EvnPS');
															
														}
												}.createDelegate(this)
										},
										enableToggle: true,
										xtype: 'button'
								}]
						}, {
								border: false,
								layout: 'form',
								style: 'padding-left: 5px',
								items: [{
										allowDepress: false,
										pressed: false,
										text: lang['poisk_dvijeniy'],
										disabled: (!this.ownerWindowPSWizardPanel),
										hidden: (!this.ownerWindowPSWizardPanel),
										toggleGroup: 'search_setting_group',
										listeners:
										{
												toggle: function(button, check)
												{
														if (check)
														{
															this.ownerWindowPSWizardPanel.layout.setActiveItem(1);
															this.getForm().findField('SearchFormType').setValue('EvnSection');
															
														}
												}.createDelegate(this)
										},
										enableToggle: true,
										xtype: 'button'
								}]
						}, {
							border: false,
							layout: 'form',
							style: 'padding-left: 5px',
							hidden: !(getGlobalOptions().archive_database_enable && searchFiltersPanel.useArchive),
							labelWidth: 0,
							items: [{
								allowBlank: true,
								name: 'autoLoadArchiveRecords',
								boxLabel: lang['uchityivat_arhivnyie_dannyie'],
								hideLabel: true,
								xtype: 'checkbox'
							}]
						}]
				};

                if (this.isDisplayPersonRegisterRecordTypeField) {
                    this.topFilter.items.push({
                        border: false,
                        layout: 'form',
                        style: 'padding-left: 5px',
                        items: [{
                            fieldLabel: lang['zapisi_registra'],
                            hiddenName: 'PersonRegisterRecordType_id',
                            valueField: 'PersonRegisterRecordType_id',
                            displayField: 'PersonRegisterRecordType_Name',
                            storeKey: 'PersonRegisterRecordType_id',
                            comboData: [
                                [1,lang['vse']],
                                [2,lang['vse_sostoyaschie_na_uchete']],
                                [3,lang['vse_vyiehavshie']],
                                [4,lang['vse_u_kotoryih_diagnoz_ne_podtverdilsya']],
                                [5,lang['vse_snyatyie_po_bazaliome']],
                                [6,lang['vse_umershie']]
                            ],
                            comboFields: [
                                {name: 'PersonRegisterRecordType_id', type:'int'},
                                {name: 'PersonRegisterRecordType_Name', type:'string'}
                            ],
                            width: 200,
                            xtype: 'swstoreinconfigcombo'
                        }]
                    });
                }

				this.mainFilters = new Ext.TabPanel({
						activeTab: 0,
						autoWidth: true,
						autoHeight: true,
						width: 1000, // исправление бага в IE7
						//height: this.tabPanelHeight,
						defaults: { bodyStyle: 'padding: 0px;' },
						id: this.tabPanelId,
						layoutOnTabChange: true,
						autoScroll: true,
						listeners: {
							'tabchange': function(panel, tab) {
								this.resetLayout();
								if  (getRegionNick() == 'kz') {
									this.getForm().findField("KLCountry_id").hideContainer();
									this.getForm().findField("Person_citizen").hideContainer();
								}
								if (this.getForm().findField("Person_citizen").getValue()!= 3) { //если значение "Иное" то скрывать не будет
									this.getForm().findField("PDKLCountry_id").hideContainer();
								}
							}.createDelegate(this)
						},
						//plain: true,
						border: false,
						region: 'north',
						items: [{
					autoHeight: true,
								//height: 100,
								autoScroll: true,
								bodyStyle: 'margin-top: 5px;',
								border: false,
								layout: 'form',
								listeners: {
										'activate': function(panel) {
												this.getForm().findField('Person_Surname').focus(250, true);

										}.createDelegate(this)
								},
								title: lang['1_patsient'],
								id: this.tabPanelId + 'filterPatient',

								items: [{
										border: false,
										layout: 'column',
										items: [{
												name: 'SearchFormType',
												value: this.searchFormType,
												xtype: 'hidden'
										}, {
												border: false,
												layout: 'form',
												items: [{
														fieldLabel: lang['familiya'],
														listeners: {
																'keydown': function (inp, e) {
																		if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
																				e.stopEvent();
																				// Переход к последней кнопке в окне
																				this.getOwnerWindow().buttons[this.ownerWindow.buttons.length-1].focus();
																		}
																}.createDelegate(this)
														},
														name: 'Person_Surname',
														maskRe: /[^%]/,
														tabIndex: this.tabIndexBase + 1,
														width: 200,
														xtype: 'textfieldpmw'
												}, {
														fieldLabel: lang['imya'],
														maskRe: /[^%]/,
														name: 'Person_Firname',
														tabIndex: this.tabIndexBase + 2,
														width: 200,
														xtype: 'textfieldpmw'
												}, {
														fieldLabel: lang['otchestvo'],
														maskRe: /[^%]/,
														name: 'Person_Secname',
														tabIndex: this.tabIndexBase + 3,
														width: 200,
														xtype: 'textfieldpmw'
												}]
										}, {
												border: false,
												labelWidth: 160,
												layout: 'form',
												items: [{
														fieldLabel: lang['data_rojdeniya'],
														name: 'Person_Birthday',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 5,
														width: 100,
														xtype: 'swdatefield'
												}, {
														fieldLabel: lang['diapazon_dat_rojdeniya'],
														name: 'Person_Birthday_Range',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 6,
														width: 170,
														xtype: 'daterangefield'
												}, {
														fieldLabel: lang['nomer_amb_kartyi'],
														maskRe: /[^%]/,
														name: 'PersonCard_Code',
														tabIndex: this.tabIndexBase + 7,
														width: 100,
														xtype: 'textfield'
												}, {
														fieldLabel: langs('Регистрационный номер'),//поле только для регистра онкологии
														name: 'MorbusOnkoBase_NumCard', 
														width: 100,
														maxLength: 10,
														hidden: (this.id != 'OnkoRegistryFilterForm'),
														hideLabel: (this.id != 'OnkoRegistryFilterForm'),
														xtype: 'textfield',														
												}]
										}, {
												border: false,
												width: 65,
												layout: 'form',
												style: 'margin-left: 40px',
												items: [{
														xtype: 'button',
														hidden: !getGlobalOptions()['card_reader_is_enable'],
														cls: 'x-btn-large',
														iconCls: 'idcard32',
														tooltip: lang['identifitsirovat_po_karte_i_nayti'],
														handler: function() {
															var win = this;
															// 1. пробуем считать с эл. полиса
															sw.Applets.AuthApi.getEPoliceData({callback: function(bdzData, person_data) {
																if (bdzData) {
																	win.getDataFromBdz(bdzData, person_data);
																} else {
																	// 2. пробуем считать с УЭК
																	var successRead = false;
																	if (sw.Applets.uec.checkPlugin()) {
																		successRead = sw.Applets.uec.getUecData({callback: this.getDataFromUec.createDelegate(this), onErrorRead: function() {
																			sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
																			return false;
																		}});
																	}
																	// 3. если не считалось, то "Не найден плагин для чтения данных картридера либо не возможно прочитать данные с карты"
																	if (!successRead) {
																		sw.swMsg.alert('Ошибка', 'Не найден плагин для чтения данных картридера, либо не возможно прочитать данные с карты');
																		return false;
																	}
																}
															}});
														}.createDelegate(this)
												}]
										}]
								}, {
										border: false,
										layout: 'column',
										items: [{
												border: false,
												layout: 'form',
												items: [{
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['god_rojdeniya'],
														name: 'PersonBirthdayYear',
														tabIndex: this.tabIndexBase + 4,
														width: 60,
														xtype: 'numberfield'
												}, {
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['vozrast'],
														name: 'PersonAge',
														tabIndex: this.tabIndexBase + 10,
														width: 60,
														xtype: 'numberfield'
												}]
										}, {
												border: false,
												layout: 'form',
												items: [{
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['god_rojdeniya_s'],
														name: 'PersonBirthdayYear_Min',
														tabIndex: this.tabIndexBase + 8,
														width: 61,
														xtype: 'numberfield'
												}, {
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['vozrast_s'],
														name: 'PersonAge_Min',
														tabIndex: this.tabIndexBase + 11,
														width: 61,
														xtype: 'numberfield'
												}]
										}, {
												border: false,
												labelWidth: 40,
												layout: 'form',
												items: [{
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['po'],
														name: 'PersonBirthdayYear_Max',
														tabIndex: this.tabIndexBase + 9,
														width: 61,
														xtype: 'numberfield'
												}, {
														allowNegative: false,
														// allowDecimals: false,
														fieldLabel: lang['po'],
														name: 'PersonAge_Max',
														tabIndex: this.tabIndexBase + 12,
														width: 61,
														xtype: 'numberfield'
												}]
										}, {
												border: false,
												labelWidth: 120,
												layout: 'form',
												items: [{
													xtype: 'combo',
													tabIndex: this.tabIndexBase + 12.5,
													hidden: (this.searchFormType != 'PersonDopDispPlan'),
													hideLabel: (this.searchFormType != 'PersonDopDispPlan'),
													hiddenName: 'PersonBirthdayMonth',
													fieldLabel: 'Месяц рождения',
													editable: false,
													width: 135,
													triggerAction: 'all',
													store: [
														[1, lang['yanvar']],
														[2, lang['fevral']],
														[3, lang['mart']],
														[4, lang['aprel']],
														[5, lang['may']],
														[6, lang['iyun']],
														[7, lang['iyul']],
														[8, lang['avgust']],
														[9, lang['sentyabr']],
														[10, lang['oktyabr']],
														[11, lang['noyabr']],
														[12, lang['dekabr']]
													]
												}]
										}]
								}, {
										autoHeight: true,
										autoScroll: true,
										hidden: (getRegionNick() == 'kz'),
										style: 'padding: 0px;',
										title: lang['polis'],
										width: 755,
										xtype: 'fieldset',
										items: [{
												border: false,
												layout: 'column',
												items: [{
														border: false,
														layout: 'form',
														items: [{
																fieldLabel: lang['seriya'],
																maskRe: /[^%]/,
																name: 'Polis_Ser',
																tabIndex: this.tabIndexBase + 13,
																width: 100,
																xtype: 'textfield'
														}]
												}, {
														border: false,
														labelWidth: 100,
														layout: 'form',
														items: [{
																allowNegative: false,
																allowLeadingZeroes: true,
																// allowDecimals: false,
																fieldLabel: lang['nomer'],
																maskRe: /[^%]/,
																name: 'Polis_Num',
																tabIndex: this.tabIndexBase + 14,
																width: 100,
																xtype: 'numberfield'
														}]
												}, {
														border: false,
														labelWidth: 130,
														layout: 'form',
														items: [{
																xtype: 'textfield',
																maskRe: /\d/,
																fieldLabel: lang['edinyiy_nomer'],
																width: 130,
																name: 'Person_Code',
																tabIndex: this.tabIndexBase + 15
														}]
												}]
										}, {
												border: false,
												layout: 'column',
												items: [{
														border: false,
														layout: 'form',
														items: [{
																tabIndex: this.tabIndexBase + 16,
																width: 100,
																xtype: 'swpolistypecombo'
														}]
												}, {
														border: false,
														labelWidth: 100,
														layout: 'form',
														items: [{
																enableKeyEvents: true,
																forceSelection: false,
																hiddenName: 'OrgSmo_id',
																listeners: {
																		'blur': function(combo) {
																				if ( combo.getRawValue() == '' ) {
																						combo.clearValue();
																				}

																				if ( combo.getStore().findBy(function(rec) { return rec.get(combo.displayField) == combo.getRawValue(); }) < 0 ) {
																						combo.clearValue();
																				}
																		},
																		'keydown': function( inp, e ) {
																				if ( e.F4 == e.getKey() ) {
																						if ( inp.disabled ) {
																								return;
																						}

																						if ( e.browserEvent.stopPropagation )
																								e.browserEvent.stopPropagation();
																						else
																								e.browserEvent.cancelBubble = true;

																						if ( e.browserEvent.preventDefault )
																								e.browserEvent.preventDefault();
																						else
																								e.browserEvent.returnValue = false;

																						e.returnValue = false;

																						if ( Ext.isIE )  {
																								e.browserEvent.keyCode = 0;
																								e.browserEvent.which = 0;
																						}

																						inp.onTrigger2Click();
																						inp.collapse();

																						return false;
																				}
																		},
																		'keyup': function(inp, e) {
																				if ( e.F4 == e.getKey() ) {
																						if ( e.browserEvent.stopPropagation )
																								e.browserEvent.stopPropagation();
																						else
																								e.browserEvent.cancelBubble = true;

																						if ( e.browserEvent.preventDefault )
																								e.browserEvent.preventDefault();
																						else
																								e.browserEvent.returnValue = false;

																						e.returnValue = false;

																						if ( Ext.isIE ) {
																								e.browserEvent.keyCode = 0;
																								e.browserEvent.which = 0;
																						}

																						return false;
																				}
																		}
																},
																listWidth: 400,
																minChars: 1,
																queryDelay: 1,
																tabIndex: this.tabIndexBase + 17,
																tpl: new Ext.XTemplate(
																		'<tpl for="."><div class="x-combo-list-item">',
																		'{OrgSMO_Nick}',
																		'</div></tpl>'
																),
																typeAhead: true,
																typeAheadDelay: 1,
																width: 280,
																xtype: 'sworgsmocombo'
														}]
												}, {
														border: false,
														style: 'padding-left: 10px',
														//labelWidth: 100,
														layout: 'form',
														items: [{
																boxLabel: lang['smo_ne_ukazana'],
																hideLabel: true,
																listeners: {
																		'check': function(checkbox, checked) {
																				var base_form = this.getForm();
																				if ( checked ) {
																					base_form.findField('OrgSmo_id').clearValue();
																					base_form.findField('OrgSmo_id').disable();
																				}
																				else {
																					base_form.findField('OrgSmo_id').enable();
																				}
																		}.createDelegate(this)
																},
																name: 'Person_NoOrgSMO',
																tabIndex: this.tabIndexBase + 18,
																width: 120,
																xtype: 'checkbox'
														}]
												}]
										}, {
												border: false,
												layout: 'column',
												items: [{
														border: false,
														//width: 180,
														//labelWidth: 100,
														layout: 'form',
														items: [{
																fieldLabel: lang['bez_polisa'],
																listeners: {
																		'check': function(checkbox, checked) {
																				var base_form = this.getForm();
																				if ( checked ) {
																					base_form.findField('OrgSmo_id').clearValue();
																					base_form.findField('Polis_Num').setValue('');
																					base_form.findField('Person_Code').setValue('');
																					base_form.findField('Polis_Ser').setValue('');
																					base_form.findField('PolisType_id').clearValue();
																					base_form.findField('OMSSprTerr_id').clearValue();
																					base_form.findField('Person_NoOrgSMO').setValue(false);
																					base_form.findField('OrgSmo_id').disable();
																					base_form.findField('Polis_Num').disable();
																					base_form.findField('Person_Code').disable();
																					base_form.findField('Polis_Ser').disable();
																					base_form.findField('PolisType_id').disable();
																					base_form.findField('OMSSprTerr_id').disable();
																					base_form.findField('Person_NoOrgSMO').disable();
																				}
																				else {
																					base_form.findField('OrgSmo_id').enable();
																					base_form.findField('Polis_Num').enable();
																					base_form.findField('Person_Code').enable();
																					base_form.findField('Polis_Ser').enable();
																					base_form.findField('PolisType_id').enable();
																					base_form.findField('OMSSprTerr_id').enable();
																					base_form.findField('Person_NoOrgSMO').enable();
																				}
																		}.createDelegate(this)
																},
																name: 'Person_NoPolis',
																tabIndex: this.tabIndexBase + 18,
																width: 100,
																xtype: 'checkbox'
														}]
												}, {
														border: false,
														layout: 'form',
														labelWidth: 100,
														items: [{
																enableKeyEvents: true,
																listeners: {
																		'keypress': function(inp, e) {
																				if (!e.shiftKey && e.getKey() == e.TAB)
																				{
																						if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
																						{
																								var grid_id = this.tabGridId;
																								Ext.TaskMgr.start({
																										run : function() {
																												Ext.TaskMgr.stopAll();
																												Ext.getCmp(grid_id).focus();
																										},
																										interval : 200
																								});
																								e.stopEvent();
																						}
																				}
																		}.createDelegate(this)
																},
																additionalRecord: {
																		value: 100500,
																		text: lang['inyie_territorii'],
																		code: 0
																},
																tabIndex: this.tabIndexBase + 19,
																width: 400,
																xtype: 'swomssprterradditcombo'
														}]
												}]
										}]
								}]
						}, {
								autoHeight: true,
								//height: 100,
								autoScroll: true,
	//						autoScroll: true,
								bodyStyle: 'margin-top: 5px;',
								border: false,
								layout: 'form',
								listeners: {
									'activate': function(panel) {
										this.getForm().findField('Sex_id').focus(250, true);
									}.createDelegate(this)
								},
								title: lang['2_patsient_dop'],
								id: this.tabPanelId + 'filterPatientDop',
								//hiddenName: 'filterDop',
								// tabIndexStart: this.tabIndexBase + 21
								items: [{
									border: false,
									layout: 'column',
									items: [{
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: lang['pol'],
											hiddenName: 'Sex_id',
											listeners: {
												'keydown': function (inp, e) {
													if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
														e.stopEvent();
														// Переход к последней кнопке в окне
														this.getOwnerWindow().buttons[this.getOwnerWindow().buttons.length-1].focus();
													}
												}
											},
											tabIndex: this.tabIndexBase + 21,
											width: 150,
											xtype: 'swpersonsexcombo'
										}, {
											border: false,
											hidden: (getRegionNick() == 'kz'),
											layout: 'form',
											items: [{
												fieldLabel: lang['snils'],
												name: 'Person_Snils',
												tabIndex: this.tabIndexBase + 23,
												width: 150,
												xtype: 'textfieldpmw'
											}]
										}, {
											id: this.tabPanelId + '_PersonInnPanel',
											hidden: (getRegionNick() != 'kz'),
											border: false,
											layout: 'form',
											style: 'padding: 0px;',
											items: [{
												allowBlank: true,
												autoCreate: {tag: "input", type: "text", size: "30", maxLength: "12", autocomplete: "off"},
												fieldLabel: langs('ИИН'),
												maskRe: /\d/,
												maxLength: 12,
												minLength: 12,
												name: 'Person_Inn',
												tabIndex: this.tabIndexBase + 12,
												width: 150,
												xtype: 'textfield'
											}]
										}]
									}, {
										border: false,
										layout: 'form',
										items: [{
											fieldLabel: langs('Соц. статус'),
											hiddenName: 'SocStatus_id',
											tabIndex: this.tabIndexBase + 22,
											width: 265,
											xtype: 'swsocstatuscombo'
										}, {
											disabled: false,
											fieldLabel: lang['dispansernyiy_uchet'],
											hiddenName: 'Person_IsDisp',
											tabIndex: this.tabIndexBase + 24,
											width: 80,
											xtype: 'swyesnocombo'
										}]
									}]
								}, {
										autoHeight: true,
										autoScroll: true,
										labelWidth: 114,
										layout: 'form',
										style: 'margin: 0 5px 5px; padding: 5px 10px;',
										title: lang['dokument'],
										width: 755,
										xtype: 'fieldset',
										items: [{
												border: false,
												layout: 'column',
												items: [{
														border: false,
														layout: 'form',
														items: [{
																fieldLabel: lang['tip'],
																editable: false,
																forceSelection: true,
																hiddenName: 'DocumentType_id',
																listWidth: 500,
																tabIndex: this.tabIndexBase + 25,
																width: 200,
																xtype: 'swdocumenttypecombo'
														}]
												}, {
														border: false,
														labelWidth: 80,
														layout: 'form',
														items: [{
																fieldLabel: lang['seriya'],
																maskRe: /[^%]/,
																name: 'Document_Ser',
																tabIndex: this.tabIndexBase + 26,
																width: 80,
																xtype: 'textfield'
														}]
												}, {
														border: false,
														labelWidth: 80,
														layout: 'form',
														items: [{
																allowNegative: false,
																// allowDecimals: false,
																fieldLabel: lang['nomer'],
																name: 'Document_Num',
																tabIndex: this.tabIndexBase + 27,
																width: 100,
																xtype: 'numberfield'
														}]
												}]
										}, {
												editable: false,
												enableKeyEvents: true,
												hiddenName: 'OrgDep_id',
												listeners: {
														'keydown': function( inp, e ) {
																if ( inp.disabled ) {
																		return;
																}

																if ( e.F4 == e.getKey() ) {
																		if ( e.browserEvent.stopPropagation )
																				e.browserEvent.stopPropagation();
																		else
																				e.browserEvent.cancelBubble = true;

																		if ( e.browserEvent.preventDefault )
																				e.browserEvent.preventDefault();
																		else
																				e.browserEvent.returnValue = false;

																		e.returnValue = false;

																		if ( Ext.isIE ) {
																				e.browserEvent.keyCode = 0;
																				e.browserEvent.which = 0;
																		}

																		inp.onTrigger1Click();

																		return false;
																}
														},
														'keyup': function(inp, e) {
																if ( e.F4 == e.getKey() ) {
																		if ( e.browserEvent.stopPropagation )
																				e.browserEvent.stopPropagation();
																		else
																				e.browserEvent.cancelBubble = true;

																		if ( e.browserEvent.preventDefault )
																				e.browserEvent.preventDefault();
																		else
																				e.browserEvent.returnValue = false;

																		e.returnValue = false;

																		if ( Ext.isIE ) {
																				e.browserEvent.keyCode = 0;
																				e.browserEvent.which = 0;
																		}

																		return false;
																}
														}
												},
												listWidth: 400,
												onTrigger1Click: function() {
														if ( this.disabled ) {
																return;
														}

														var combo = this;

														getWnd('swOrgSearchWindow').show({

																onSelect: function(orgData) {
																		if ( orgData.Org_id > 0 ) {
																				combo.getStore().load({
																						callback: function() {
																								combo.setValue(orgData.Org_id);
																								combo.focus(true, 250);
																								combo.fireEvent('change', combo);
																						},
																						params: {
																								Object: 'OrgDep',
																								OrgDep_id: orgData.Org_id,
																								OrgDep_Name: ''
																						}
																				});
																		}
																		getWnd('swOrgSearchWindow').hide();
																},
																onClose: function() {
																		combo.focus(true, 200)
																},
																object: 'dep'
														});
												},
												tabIndex: this.tabIndexBase + 28,
												width: 550,
												xtype: 'sworgdepcombo'
										},
											{

												fieldLabel: langs('Гражданство'),
												enableKeyEvents: true,
												hiddenName: 'Person_citizen',
												listeners: {
													'select': function (combo, value) {
														var Person_Country = this.getForm().findField("PDKLCountry_id");

														value.data.value == 3 ? Person_Country.showContainer(): Person_Country.hideContainer();
														value.data.value == 2 ? Person_Country.setValue(643) : Person_Country.setValue('');

														this.resetLayout();

													}.createDelegate(this),
													'render': function(combo)  {
														if ( combo.value == undefined )
															combo.setValue(1);
													}
												},
												listWidth: 400,
												tabIndex: this.tabIndexBase + 28,
												width: 550,
												triggerAction: 'all',
												forceSelection: true,
												xtype: 'combo',
												store: [
													[1, langs('Все')],
													[2, langs('Россия')],
													[3, langs('Иное')],
												],
												editable: false,
												value: 1
											},
											{
												editable: false,
												enableKeyEvents: true,
												xtype: 'swklcountrycombo',
												fieldLabel: langs('Страна'),
												listeners: {
													'expand': function (combo) {
														combo.getStore().clearFilter();
														combo.getStore().filterBy(function (rec) {
															return (
																rec.get('KLCountry_id') != 643
															);
														});
													},
												},
												hiddenName: 'PDKLCountry_id',
												listWidth: 400,
												tabIndex: this.tabIndexBase + 28,
												width: 550,
											},
										]
								}, {
										autoHeight: true,
										autoScroll: true,
										labelWidth: 114,
										layout: 'form',
										style: 'margin: 5px; padding: 5px 10px;',
										title: lang['mesto_rabotyi_uchebyi'],
										width: 755,
										xtype: 'fieldset',
										items: [{
												editable: false,
												enableKeyEvents: true,
												fieldLabel: lang['organizatsiya'],
												hiddenName: 'Org_id',
												listeners: {
														'keydown': function( inp, e ) {
																if (!e.shiftKey && e.getKey() == e.TAB)
																{
																		if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
																		{
																				var grid_id = this.tabGridId;
																				Ext.TaskMgr.start({
																						run : function() {
																								Ext.TaskMgr.stopAll();
																								Ext.getCmp(grid_id).focus();
																						},
																						interval : 200
																				});
																				e.stopEvent();
																		}
																}
																if ( e.F4 == e.getKey() ) {
																		if ( e.browserEvent.stopPropagation )
																				e.browserEvent.stopPropagation();
																		else
																				e.browserEvent.cancelBubble = true;

																		if ( e.browserEvent.preventDefault )
																				e.browserEvent.preventDefault();
																		else
																				e.browserEvent.returnValue = false;

																		e.returnValue = false;

																		if ( Ext.isIE ) {
																				e.browserEvent.keyCode = 0;
																				e.browserEvent.which = 0;
																		}

																		inp.onTrigger1Click();
																		return false;
																}
														}.createDelegate(this),
														'keyup': function(inp, e) {
																if ( e.F4 == e.getKey() ) {
																		if ( e.browserEvent.stopPropagation )
																				e.browserEvent.stopPropagation();
																		else
																				e.browserEvent.cancelBubble = true;

																		if ( e.browserEvent.preventDefault )
																				e.browserEvent.preventDefault();
																		else
																				e.browserEvent.returnValue = false;

																		e.returnValue = false;

																		if ( Ext.isIE ) {
																				e.browserEvent.keyCode = 0;
																				e.browserEvent.which = 0;
																		}

																		return false;
																}
														}
												},
												onTrigger1Click: function() {
														var combo = this;

														getWnd('swOrgSearchWindow').show({

																onSelect: function(orgData) {
																		if ( orgData.Org_id > 0 ) {
																				combo.getStore().load({
																						callback: function() {
																								combo.setValue(orgData.Org_id);
																								combo.focus(true, 500);
																								combo.fireEvent('change', combo);
																						},
																						params: {
																								Object: 'Org',
																								Org_id: orgData.Org_id,
																								Org_Name: ''
																						}
																				});
																		}
																		getWnd('swOrgSearchWindow').hide();
																},
																onClose: function() { combo.focus(true, 200) }
														});
												},
												tabIndex: this.tabIndexBase + 29,
												triggerAction: 'none',
												width: 550,
												xtype: 'sworgcombo'
										}/*, {
												forceSelection: false,
												hiddenName: 'Post_id',
												minChars: 0,
												queryDelay: 1,
												selectOnFocus: true,
												tabIndex: this.tabIndexBase + 30,
												typeAhead: true,
												typeAheadDelay: 1,
												width: 500,
												xtype: 'swpostcombo'
										}*/]
								}, {
										border: false,
										layout: 'column',
										items: [{
											border: false,
											layout: 'form',
											items: [{
												comboSubject: 'YesNo',
												fieldLabel: lang['bdz'],
												tabIndex: this.tabIndexBase + 30,
												hiddenName: 'Person_IsBDZ',
												width: 100,
												xtype: 'swcommonsprcombo'		
											}]
										}, {
											border: false,
											layout: 'form',
											hidden: (this.id != 'EvnPSSearchFilterForm'),
											items: [{
												disable: (this.id != 'EvnPSSearchFilterForm'),
												comboSubject: 'YesNo',
												fieldLabel: lang['besprizornyiy'],
												width: 100,
												tabIndex: this.tabIndexBase + 31,
												hiddenName: 'EvnPS_IsWaif',
												xtype: 'swcommonsprcombo'
											}]
										}, {
											border: false,
											layout: 'form',
											hidden: true,
											name: 'Person_isIdentified_form',
											items: [{
												comboSubject: 'YesNo',
												fieldLabel: lang['identifitsirovan'],
												width: 100,
												tabIndex: this.tabIndexBase + 31,
												hiddenName: 'Person_isIdentified',
												xtype: 'swcommonsprcombo'
											}]
										}]
								}]
						}, {
								autoHeight: true,
								autoScroll: true,
								bodyStyle: 'margin-top: 5px;',
								border: false,
								labelWidth: 150,
								layout: 'form',
								listeners: {
										'activate': function(panel) {
												if ( this.getForm().findField('LpuRegion_id').getStore().getCount() == 0 ) {
														this.getForm().findField('LpuRegion_id').getStore().load({
																callback: function(records, options, success) {
																		if ( !success ) {
																				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_uchastkov']);
																				return false;
																		}
																},
																params: {
																		'add_without_region_line': true
																}
														});
												}

												/*
												if ( this.getForm().findField('MedPersonal_id').getStore().getCount() == 0 ) {
														this.getForm().findField('MedPersonal_id').getStore().load({
																callback: function(records, options, success) {
																		if ( !success ) {
																				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_med_personala']);
																				return false;
																		}
																}
														});
												}
												*/

												if ( this.getForm().findField('AttachLpu_id').getStore().getCount() == 0 ) {
														this.getForm().findField('AttachLpu_id').getStore().load({
																callback: function(records, options, success) {
																		if ( !success ) {
																				form.getForm().findField('AttachLpu_id').getStore().removeAll();
																				sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_spravochnika_mo']);
																				return false;
																		}
																}
														});
												}

												if (this.getForm().findField('PersonCardStateType_id').getValue() == '')
														this.getForm().findField('PersonCardStateType_id').setValue(1);
												
												if ( !this.getForm().findField('AttachLpu_id').disabled ) {
														this.getForm().findField('AttachLpu_id').focus(250, true);
												}
												else {
														this.getForm().findField('LpuAttachType_id').focus(250, true);
												}
										}.createDelegate(this)
								},
								title: lang['3_prikreplenie'],
								id: this.tabPanelId + 'filterPrikrep',

								// tabIndexStart: this.tabIndexBase + 33
								items: [ new sw.Promed.SwLpuSearchCombo({
										additionalRecord: {
												value: 100500,
												text: lang['drugaya_mo']
										},
										fieldLabel: lang['mo_prikrepleniya'],
										hiddenName: 'AttachLpu_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( (newValue > 0) && newValue != getGlobalOptions().lpu_id) {
																this.getForm().findField('LpuRegion_id').clearValue();
																this.getForm().findField('LpuRegion_id').disable();
																// this.getForm().findField('LpuRegionType_id').clearValue();
																// this.getForm().findField('LpuRegionType_id').disable();
														}
														else {
																this.getForm().findField('LpuRegion_id').enable();
																// this.getForm().findField('LpuRegionType_id').enable();
														}
													if((getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) && newValue > 0)
													{ //https://redmine.swan.perm.ru/issues/78988
														var params = new Object();
														if(!Ext.isEmpty(this.getForm().findField('LpuRegion_Fapid').getValue()))
															this.getForm().findField('LpuRegion_Fapid').clearValue();
														this.getForm().findField('LpuRegion_Fapid').getStore().removeAll();
														params.Lpu_id = newValue;
														this.getForm().findField('LpuRegion_Fapid').getStore().load({
															params: params
														});
													}
												}.createDelegate(this),
												'keydown': function (inp, e) {
														if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
																e.stopEvent();
																// Переход к последней кнопке в окне
																this.getOwnerWindow().buttons[this.getOwnerWindow().buttons.length-1].focus();
														}
												}.createDelegate(this)
										},
										listWidth: 400,
										tabIndex: this.tabIndexBase + 33,
										width: 310
								}), {
									hiddenName: 'LpuAttachType_id',
									listeners: {
										'change': function(combo, newValue, oldValue) {
											var lpu_region_type_combo = this.getForm().findField('LpuRegionType_id');
											var lpu_region_type_id = lpu_region_type_combo.getValue();

											lpu_region_type_combo.clearValue();
											lpu_region_type_combo.getStore().clearFilter();

											if ( newValue ) {
												var LpuRegionTypeArray = [];

												switch ( newValue ) {
													case 1:
														LpuRegionTypeArray = [ 'ter', 'ped', 'vop' ];

														if ( getRegionNick() == 'perm' ) {
															LpuRegionTypeArray = [ 'ter', 'ped', 'vop', 'comp', 'prip', 'feld' ];
														}
													break;

													case 2:
														LpuRegionTypeArray = [ 'gin' ];
													break;

													case 3:
														LpuRegionTypeArray = [ 'stom' ];
													break;

													case 4:
														LpuRegionTypeArray = [ 'slug' ];
													break;
												}

												lpu_region_type_combo.getStore().filterBy(function(rec) {
													return (!Ext.isEmpty(rec.get('LpuRegionType_SysNick')) && rec.get('LpuRegionType_SysNick').inlist(LpuRegionTypeArray));
												});
											}

											var record = lpu_region_type_combo.getStore().getById(lpu_region_type_id);

											if ( newValue != 4 || getRegionNick() != 'ufa' ) {
												if ( record ) {
													lpu_region_type_combo.setValue(lpu_region_type_id);
													lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_id, null);
												}
												else if ( lpu_region_type_combo.getStore().getCount() == 1 ) {
													lpu_region_type_combo.setValue(lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'));
													lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, lpu_region_type_combo.getStore().getAt(0).get('LpuRegionType_id'), null);
												}
												else {
													lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
												}
											}
											else {
												lpu_region_type_combo.fireEvent('change', lpu_region_type_combo, null, lpu_region_type_id);
											}
										}.createDelegate(this)
									},
									tabIndex: this.tabIndexBase + 34,
									width: 170,
									xtype: 'swlpuattachtypecombo'
								}, {
										hiddenName: 'LpuRegionType_id',
										fieldLabel:(getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','penza','vologda']))?langs('Тип основного участка'):langs('Тип участка'),
										listeners: {
												'change': function(combo, newValue, oldValue) {
														var lpu_attach_type_id = this.getForm().findField('LpuAttachType_id').getValue();
														var lpu_region_combo = this.getForm().findField('LpuRegion_id');
														var lpu_region_id = lpu_region_combo.getValue();

														lpu_region_combo.clearValue();
														lpu_region_combo.getStore().clearFilter();

														if ( newValue ) {
																lpu_region_combo.getStore().filterBy(function(record) {
																		if ( record.get('LpuRegionType_id') == newValue ) {
																				return true;
																		}
																		else {
																				return false;
																		}
																});
														}
														else if ( lpu_attach_type_id ) {
																lpu_region_combo.getStore().filterBy(function(record) {
																		if ( record.get('LpuRegion_id') == -1 ) {
																				return true;
																		}

																		switch ( lpu_attach_type_id ) {
																				case 1:
																						if ( record.get('LpuRegionType_id') == 1 || record.get('LpuRegionType_id') == 2 || record.get('LpuRegionType_id') == 4 ) {
																								return true;
																						}
																						else {
																								return false;
																						}
																				break;

																				case 2:
																						if ( record.get('LpuRegionType_id') == 3 ) {
																								return true;
																						}
																						else {
																								return false;
																						}
																				break;

																				case 3:
																						if ( record.get('LpuRegionType_id') == 5 ) {
																								return true;
																						}
																						else {
																								return false;
																						}
																				break;

																				case 4:
																						if ( record.get('LpuRegionType_id') == 6 ) {
																								return true;
																						}
																						else {
																								return false;
																						}
																				break;
																		}
																});
														}


														var attach_lpu_id = this.getForm().findField('AttachLpu_id').getValue();
														var record = lpu_region_combo.getStore().getById(lpu_region_id);

														if ( !attach_lpu_id || attach_lpu_id == getGlobalOptions().lpu_id ) {
																if ( record ) {
																		lpu_region_combo.setValue(lpu_region_id);
																}
																else if ( lpu_region_combo.getStore().getCount() == 1 ) {
																		lpu_region_combo.setValue(lpu_region_combo.getStore().getAt(0).get('LpuRegion_id'));
																}
														}
												}.createDelegate(this)
										},
										tabIndex: this.tabIndexBase + 35,
										width: 170,
										xtype: 'swlpuregiontypecombo'
								}, {
										displayField: 'LpuRegion_Name',
										editable: true,
										fieldLabel: (getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda'])) ? langs('Основной участок') : langs('Участок'),
										forceSelection: true,
										typeAhead: true,
										hiddenName: 'LpuRegion_id',
										tabIndex: this.tabIndexBase + 36,
										triggerAction: 'all',
										valueField: 'LpuRegion_id',
										width: 310,
										xtype: 'swlpuregioncombo'
								},
									{
										allowBlank: true,
										displayField: 'LpuRegion_FapName',
										fieldLabel: lang['fap_uchastok'],
										forceSelection: true,
										editable: true,
										hiddenName: 'LpuRegion_Fapid',
										hideLabel: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),
										hidden: !getRegionNick().inlist(['perm','buryatiya','kareliya','khak','krym','ekb','ufa','penza','vologda']),
										minChars: 1,
										mode: 'local',
										queryDelay: 1,
										setValue: function(v) {
											var text = v;
											if(this.valueField){
												var r = this.findRecord(this.valueField, v);
												if(r){
													text = r.data[this.displayField];
													if ( !(String(r.data['LpuRegion_FapDescr']).toUpperCase() == "NULL" || String(r.data['LpuRegion_FapDescr']) == "") )
													{
														if (r.data['LpuRegion_FapDescr']) {
															text = text + ' ( '+ r.data['LpuRegion_FapDescr'] + ' )';
														}
													}
												} else if(this.valueNotFoundText !== undefined){
													text = this.valueNotFoundText;
												}
											}
											this.lastSelectionText = text;
											if(this.hiddenField){
												this.hiddenField.value = v;
											}
											Ext.form.ComboBox.superclass.setValue.call(this, text);
											this.value = v;
										},
										lastQuery: '',
										store: new Ext.data.Store({
											autoLoad: false,
											reader: new Ext.data.JsonReader({
												id: 'LpuRegion_Fapid'
											}, [
												{name: 'LpuRegion_FapName', mapping: 'LpuRegion_FapName'},
												{name: 'LpuRegion_Fapid', mapping: 'LpuRegion_Fapid'},
												{name: 'LpuRegion_FapDescr', mapping: 'LpuRegion_FapDescr'}
											]),
											url: '/?c=LpuRegion&m=getLpuRegionListFeld'
										}),
										tabIndex: 2106,
										//tpl: '<tpl for="."><div class="x-combo-list-item">{LpuRegion_FapName}</div></tpl>',
										
										tpl: '<tpl for="."><div class="x-combo-list-item"><table height="18" style="border: 0;"><tr>'+
										'<td>{LpuRegion_FapName}</td>'+
										'</tr></table></div></tpl>',
										triggerAction: 'all',
										typeAhead: true,
										typeAheadDelay: 1,
										valueField: 'LpuRegion_Fapid',
										width : 310,
										xtype: 'combo'
									}/*, {
										codeField: 'MedPersonal_Code',
										disabled: true,
										displayField: 'MedPersonal_Fio',
										enableKeyEvents: true,
										editable: false,
										fieldLabel: lang['vrach_uchastka'],
										hiddenName: 'MedPersonal_id',
										listWidth: 350,
										mode: 'local',
										resizable: true,
										store: new Ext.data.Store({
												autoLoad: false,
												reader: new Ext.data.JsonReader({
														id: 'MedPersonal_id'
												}, [
														{ name: 'MedPersonal_Fio', mapping: 'MedPersonal_Fio' },
														{ name: 'MedPersonal_id', mapping: 'MedPersonal_id' },
														{ name: 'MedPersonal_Code', mapping: 'MedPersonal_Code' },
														{ name: 'ReceptFinance_id', type: 'int'}
												]),
												sortInfo: {
														field: 'MedPersonal_Fio'
												},
												url: C_MP_LOADLIST
										}),
										tabIndex: this.tabIndexBase + 37,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<table style="border: 0;"><td style="width: 40px"><font color="red">{MedPersonal_Code}</font></td><td><h3>{MedPersonal_Fio}</h3></td></tr></table>',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'MedPersonal_id',
										width: 310,
										xtype: 'swbaselocalcombo'
								}*/, {
										allowBlank: true,
										codeField: 'PersonCardStateType_Code',
										displayField: 'PersonCardStateType_Name',
										editable: false,
										fieldLabel: lang['aktualnost_prikr-ya'],
										ignoreIsEmpty: true,
										hiddenName: 'PersonCardStateType_id',
										hideEmptyRow: true,
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue == 1) {
																this.getForm().findField('PersonCard_endDate').setValue(null);
																this.getForm().findField('PersonCard_endDate').disable();
																this.getForm().findField('PersonCard_endDate_Range').setValue(null);
																this.getForm().findField('PersonCard_endDate_Range').disable();
														}
														else {
																this.getForm().findField('PersonCard_endDate').enable();
																this.getForm().findField('PersonCard_endDate_Range').enable();
														}
												}.createDelegate(this),
												'blur': function(combo)  {
														if ( combo.value == '' )
																combo.setValue(1);
												}
										},
										store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
														[ 1, 1, lang['aktualnyie_prikrepleniya'] ],
														[ 2, 2, lang['vsya_istoriya_prikrepleniy'] ]
												],
												fields: [
														{ name: 'PersonCardStateType_id', type: 'int'},
														{ name: 'PersonCardStateType_Code', type: 'int'},
														{ name: 'PersonCardStateType_Name', type: 'string'},
														{name: 'ReceptFinance_id', type: 'int'}
												],
												key: 'PersonCardStateType_id',
												sortInfo: { field: 'PersonCardStateType_Code' }
										}),
										tabIndex: this.tabIndexBase + 38,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{PersonCardStateType_Code}</font>&nbsp;{PersonCardStateType_Name}',
												'</div></tpl>'
										),
										value: 1,
										valueField: 'PersonCardStateType_id',
										width: 310,
										xtype: 'swbaselocalcombo'
								}, {
										border: false,
										layout: 'column',
										items: [{
												border: false,
												layout: 'form',
												items: [{
														fieldLabel: lang['data_prikrepleniya'],
														name: 'PersonCard_begDate',
														plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
														tabIndex: this.tabIndexBase + 39,
														width: 100,
														xtype: 'swdatefield'
												}, {
														fieldLabel: lang['data_otkrepleniya'],
														name: 'PersonCard_endDate',
														plugins: [ new Ext.ux.InputTextMask('99.99.9999', false)],
														tabIndex: this.tabIndexBase + 41,
														width: 100,
														xtype: 'swdatefield'
												},
												new sw.Promed.SwYesNoCombo({										
														fieldLabel: lang['uslovn_prikr'],
														hiddenName: 'PersonCard_IsAttachCondit',
														tabIndex: this.tabIndexBase + 43,
														width: 100
												})]
										}, {
												border: false,
												labelWidth: 220,
												layout: 'form',
												items: [{
														fieldLabel: lang['diapazon_dat_prikrepleniya'],
														name: 'PersonCard_begDate_Range',
														plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
														tabIndex: this.tabIndexBase + 40,
														width: 170,
														xtype: 'daterangefield'
												}, {
														fieldLabel: lang['diapazon_dat_otkrepleniya'],
														name: 'PersonCard_endDate_Range',
														plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
														tabIndex: this.tabIndexBase + 42,
														width: 170,
														xtype: 'daterangefield'
												},
												new sw.Promed.SwYesNoCombo({
														enableKeyEvents: true,
														listeners: {
															'keydown': function(inp, e) {
																if (!e.shiftKey && e.getKey() == e.TAB)
																{
																	if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
																	{
																		var grid_id = this.tabGridId;
																		Ext.TaskMgr.start({
																			run : function() {
																					Ext.TaskMgr.stopAll();
																					Ext.getCmp(grid_id).focus();
																			},
																			interval : 200
																		});
																		e.stopEvent();
																	}
																}
															}.createDelegate(this)
														},
														fieldLabel: lang['dms_prikreplenie'],
														hiddenName: 'PersonCard_IsDms',
														tabIndex: this.tabIndexBase + 43,
														width: 170
												})]
										}]
								}]
						}, {
								autoHeight: true,
								autoScroll: true,
	//						autoScroll: true,
								bodyStyle: 'margin-top: 5px;',
								border: false,
								layout: 'form',
								listeners: {
										'activate': function(panel) {
												this.getForm().findField('KLAreaStat_id').focus(250, true);
											
												if (this.getForm().findField('AddressStateType_id').getValue() == '')
													this.getForm().findField('AddressStateType_id').setValue(1);
										}.createDelegate(this)
								},
								title: lang['4_adres'],
								id: this.tabPanelId + 'filterAdr',

								// tabIndexStart: this.tabIndexBase + 46
								items: [{
										border: false,
										layout: 'column',

										items: [{
												border: false,
												layout: 'form',
												items: [{
														allowBlank: true,
														ignoreIsEmpty: true,
														codeField: 'AddressStateType_Code',
														displayField: 'AddressStateType_Name',
														editable: false,
														fieldLabel: lang['tip_adresa'],
														hiddenName: 'AddressStateType_id',
														ignoreIsEmpty: true,
														listeners: {
																'keydown': function (inp, e) {
																		if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
																				e.stopEvent();
																				// Переход к последней кнопке в окне
																				this.getOwnerWindow().buttons[this.getOwnerWindow().buttons.length-1].focus();
																		}
																}.createDelegate(this)
														},
														store: new Ext.data.SimpleStore({
																autoLoad: true,
																data: [
																		[ 1, 1, lang['adres_registratsii'] ],
																		[ 2, 2, lang['adres_projivaniya'] ]
																],
																fields: [
																		{ name: 'AddressStateType_id', type: 'int'},
																		{ name: 'AddressStateType_Code', type: 'int'},
																		{ name: 'AddressStateType_Name', type: 'string'},
																		{name: 'ReceptFinance_id', type: 'int'}
																],
																key: 'AddressStateType_id',
																sortInfo: { field: 'AddressStateType_Code' }
														}),
														tabIndex: this.tabIndexBase + 46,
														tpl: new Ext.XTemplate(
																'<tpl for="."><div class="x-combo-list-item">',
																'<font color="red">{AddressStateType_Code}</font>&nbsp;{AddressStateType_Name}',
																'</div></tpl>'
														),
														value: 1,
														valueField: 'AddressStateType_id',
														width: 180,
														xtype: 'swbaselocalcombo'
												}]
										}, {
												border: false,
												labelWidth: 10,
												layout: 'form',

												items: [{
														boxLabel: lang['bez_adresa'],
														labelSeparator: '',
														listeners: {
																'check': function(checkbox, checked) {
																		var base_form = this.getForm();

																		if ( checked ) {
																				base_form.findField('KLAreaStat_id').disable();
																				base_form.findField('KLCountry_id').disable();
																				base_form.findField('KLRgn_id').disable();
																				base_form.findField('KLSubRgn_id').disable();
																				base_form.findField('KLCity_id').disable();
																				base_form.findField('KLTown_id').disable();
																				base_form.findField('KLStreet_id').disable();
																				base_form.findField('Address_House').disable();
																				base_form.findField('KLAreaType_id').disable();
																		}
																		else {
																				base_form.findField('KLAreaStat_id').enable();
																				base_form.findField('KLCountry_id').enable();
																				base_form.findField('KLRgn_id').enable();
																				base_form.findField('KLSubRgn_id').enable();
																				base_form.findField('KLCity_id').enable();
																				base_form.findField('KLTown_id').enable();
																				base_form.findField('KLStreet_id').enable();
																				base_form.findField('Address_House').enable();
																				base_form.findField('KLAreaType_id').enable();
																		}
																}.createDelegate(this)
														},
														name: 'Person_NoAddress',
														tabIndex: this.tabIndexBase + 47,
														width: 150,
														xtype: 'checkbox'
												}]
										}]
								}, {
										codeField: 'KLAreaStat_Code',
										disabled: false,
										displayField: 'KLArea_Name',
										editable: true,
										enableKeyEvents: true,
										fieldLabel: lang['territoriya'],
										hiddenName: 'KLAreaStat_id',
										listeners: {
												'change': function (combo, newValue) {
														if ( newValue == '' )
														{
																var form = this;
																var country_combo = form.getForm().findField('KLCountry_id');
																var region_combo = form.getForm().findField('KLRgn_id');
																var sub_region_combo = form.getForm().findField('KLSubRgn_id');
																var city_combo = form.getForm().findField('KLCity_id');
																var town_combo = form.getForm().findField('KLTown_id');
																var street_combo = form.getForm().findField('KLStreet_id');

																country_combo.enable();
																region_combo.enable();
																sub_region_combo.enable();
																city_combo.enable();
																town_combo.enable();
																street_combo.enable();
														}
												}.createDelegate(this),
												'select': function(combo, record) {
														var newValue = record.get('KLAreaStat_id');
														var current_window = this.getOwnerWindow();
														var current_record = combo.getStore().getById(newValue);
														var form = this;

														var country_combo = form.getForm().findField('KLCountry_id');
														var region_combo = form.getForm().findField('KLRgn_id');
														var sub_region_combo = form.getForm().findField('KLSubRgn_id');
														var city_combo = form.getForm().findField('KLCity_id');
														var town_combo = form.getForm().findField('KLTown_id');
														var street_combo = form.getForm().findField('KLStreet_id');

														country_combo.enable();
														region_combo.enable();
														sub_region_combo.enable();
														city_combo.enable();
														town_combo.enable();
														street_combo.enable();

														if ( !current_record ) {
																return false;
														}

														var country_id = current_record.get('KLCountry_id');
														var region_id = current_record.get('KLRGN_id');
														var subregion_id = current_record.get('KLSubRGN_id');
														var city_id = current_record.get('KLCity_id');
														var town_id = current_record.get('KLTown_id');
														var klarea_pid = 0;
														var level = 0;

														clearAddressCombo(
																country_combo.areaLevel,
																{
																		'Country': country_combo,
																		'Region': region_combo,
																		'SubRegion': sub_region_combo,
																		'City': city_combo,
																		'Town': town_combo,
																		'Street': street_combo
																}
														);

														if ( country_id != null ) {
																country_combo.setValue(country_id);
																// country_combo.disable();
														}
														else {
																return false;
														}

														region_combo.getStore().load({
																callback: function() {
																		region_combo.setValue(region_id);
																},
																params: {
																		country_id: country_id,
																		level: 1,
																		value: 0
																}
														});

														if ( region_id.toString().length > 0 ) {
																klarea_pid = region_id;
																level = 1;
														}

														sub_region_combo.getStore().load({
																callback: function() {
																		sub_region_combo.setValue(subregion_id);
																},
																params: {
																		country_id: 0,
																		level: 2,
																		value: klarea_pid
																}
														});

														if ( subregion_id.toString().length > 0 ) {
																klarea_pid = subregion_id;
																level = 2;
														}

														city_combo.getStore().load({
																callback: function() {
																		city_combo.setValue(city_id);
																},
																params: {
																		country_id: 0,
																		level: 3,
																		value: klarea_pid
																}
														});

														if ( city_id.toString().length > 0 ) {
																klarea_pid = city_id;
																level = 3;
														}

														town_combo.getStore().load({
																callback: function() {
																		town_combo.setValue(town_id);
																},
																params: {
																		country_id: 0,
																		level: 4,
																		value: klarea_pid
																}
														});

														if ( town_id.toString().length > 0 ) {
																klarea_pid = town_id;
																level = 4;
														}

														street_combo.getStore().load({
																params: {
																		country_id: 0,
																		level: 5,
																		value: klarea_pid
																}
														});
	/*
														switch ( level ) {
																case 1:
																		region_combo.disable();
																		break;

																case 2:
																		region_combo.disable();
																		sub_region_combo.disable();
																		break;

																case 3:
																		region_combo.disable();
																		sub_region_combo.disable();
																		city_combo.disable();
																		break;

																case 4:
																		region_combo.disable();
																		sub_region_combo.disable();
																		city_combo.disable();
																		town_combo.disable();
																		break;
														}
	*/
												}.createDelegate(this)
										},
										store: new Ext.db.AdapterStore({
												autoLoad: false,
												dbFile: 'Promed.db',
												fields: [
														{ name: 'KLAreaStat_id', type: 'int' },
														{ name: 'KLAreaStat_Code', type: 'int' },
														{ name: 'KLArea_Name', type: 'string' },
														{ name: 'KLCountry_id', type: 'int' },
														{ name: 'KLRGN_id', type: 'int' },
														{ name: 'KLSubRGN_id', type: 'int' },
														{ name: 'KLCity_id', type: 'int' },
														{ name: 'KLTown_id', type: 'int' }
												],
												key: 'KLAreaStat_id',
												sortInfo: {
														field: 'KLAreaStat_Code',
														direction: 'ASC'
												},
												tableName: 'KLAreaStat'
										}),
										tabIndex: this.tabIndexBase + 48,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{KLAreaStat_Code}</font>&nbsp;{KLArea_Name}',
												'</div></tpl>'
										),
										valueField: 'KLAreaStat_id',
										width: 300,
										xtype: 'swbaselocalcombo'
								}, {
										areaLevel: 0,
										codeField: 'KLCountry_Code',
										disabled: false,
										displayField: 'KLCountry_Name',
										editable: true,
										fieldLabel: lang['strana'],
										hiddenName: 'KLCountry_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
																loadAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		},
																		combo.getValue(),
																		combo.getValue(),
																		true
																);
														}
														else {
																clearAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		}
																);
														}
												}.createDelegate(this),
												'keydown': function(combo, e) {
														if ( e.getKey() == e.DELETE ) {
																if ( combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
																		combo.fireEvent('change', combo, null, combo.getValue());
																}
														}
												},
												'select': function(combo, record, index) {
														if ( record.get('KLCountry_id') == combo.getValue() ) {
																combo.collapse();
																return false;
														}
														combo.fireEvent('change', combo, record.get('KLArea_id'), null);
												}
										},
										store: new Ext.db.AdapterStore({
												autoLoad: false,
												dbFile: 'Promed.db',
												fields: [
														{ name: 'KLCountry_id', type: 'int' },
														{ name: 'KLCountry_Code', type: 'int' },
														{ name: 'KLCountry_Name', type: 'string' }
												],
												key: 'KLCountry_id',
												sortInfo: {
														field: 'KLCountry_Name'
												},
												tableName: 'KLCountry'
										}),
										tabIndex: this.tabIndexBase + 49,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{KLCountry_Code}</font>&nbsp;{KLCountry_Name}',
												'</div></tpl>'
										),
										valueField: 'KLCountry_id',
										width: 300,
										xtype: 'swbaselocalcombo'
								}, {
										areaLevel: 1,
										disabled: false,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['region'],
										hiddenName: 'KLRgn_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
																loadAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		},
																		0,
																		combo.getValue(),
																		true
																);
														}
														else {
																clearAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		}
																);
														}
												}.createDelegate(this),
												'keydown': function(combo, e) {
														if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
																combo.fireEvent('change', combo, null, combo.getValue());
														}
												},
												'select': function(combo, record, index) {
														if ( record.get('KLArea_id') == combo.getValue() ) {
																combo.collapse();
																return false;
														}
														combo.fireEvent('change', combo, record.get('KLArea_id'));
												}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
														{ name: 'KLArea_id', type: 'int' },
														{ name: 'KLArea_Name', type: 'string' }
												],
												key: 'KLArea_id',
												sortInfo: {
														field: 'KLArea_Name'
												},
												url: C_LOAD_ADDRCOMBO
										}),
										tabIndex: this.tabIndexBase + 50,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{KLArea_Name}',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'KLArea_id',
										width: 300,
										xtype: 'combo'
								}, {
										areaLevel: 2,
										disabled: false,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['rayon'],
										hiddenName: 'KLSubRgn_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
																loadAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		},
																		0,
																		combo.getValue(),
																		true
																);
														}
														else {
																clearAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		}
																);
														}
												}.createDelegate(this),
												'keydown': function(combo, e) {
														if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
																combo.fireEvent('change', combo, null, combo.getValue());
														}
												},
												'select': function(combo, record, index) {
														if ( record.get('KLArea_id') == combo.getValue() ) {
																combo.collapse();
																return false;
														}
														combo.fireEvent('change', combo, record.get('KLArea_id'));
												}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
														{ name: 'KLArea_id', type: 'int' },
														{ name: 'KLArea_Name', type: 'string' }
												],
												key: 'KLArea_id',
												sortInfo: {
														field: 'KLArea_Name'
												},
												url: C_LOAD_ADDRCOMBO
										}),
										tabIndex: this.tabIndexBase + 51,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{KLArea_Name}',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'KLArea_id',
										width: 300,
										xtype: 'combo'
								}, {
										areaLevel: 3,
										disabled: false,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['gorod'],
										hiddenName: 'KLCity_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
																loadAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		},
																		0,
																		combo.getValue(),
																		true
																);
														}
														else {
																clearAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		}
																);
														}
												}.createDelegate(this),
												'keydown': function(combo, e) {
														if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
																combo.fireEvent('change', combo, null, combo.getValue());
														}
												},
												'select': function(combo, record, index) {
														if ( record.get('KLArea_id') == combo.getValue() ) {
																combo.collapse();
																return false;
														}
														combo.fireEvent('change', combo, record.get('KLArea_id'));
												}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
														{ name: 'KLArea_id', type: 'int' },
														{ name: 'KLArea_Name', type: 'string' }
												],
												key: 'KLArea_id',
												sortInfo: {
														field: 'KLArea_Name'
												},
												url: C_LOAD_ADDRCOMBO
										}),
										tabIndex: this.tabIndexBase + 52,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{KLArea_Name}',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'KLArea_id',
										width: 300,
										xtype: 'combo'
								}, {
										areaLevel: 4,
										disabled: false,
										displayField: 'KLArea_Name',
										enableKeyEvents: true,
										fieldLabel: lang['naselennyiy_punkt'],
										hiddenName: 'KLTown_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue != null && combo.getRawValue().toString().length > 0 ) {
																loadAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		},
																		0,
																		combo.getValue(),
																		true
																);
														}
														else {
																clearAddressCombo(
																		combo.areaLevel,
																		{
																				'Country': this.getForm().findField('KLCountry_id'),
																				'Region': this.getForm().findField('KLRgn_id'),
																				'SubRegion': this.getForm().findField('KLSubRgn_id'),
																				'City': this.getForm().findField('KLCity_id'),
																				'Town': this.getForm().findField('KLTown_id'),
																				'Street': this.getForm().findField('KLStreet_id')
																		}
																);
														}
												}.createDelegate(this),
												'keydown': function(combo, e) {
														if ( e.getKey() == e.DELETE && combo.getRawValue().toString().length > 0 && combo.getValue().toString().length > 0 ) {
																combo.fireEvent('change', combo, null, combo.getValue());
														}
												},
												'select': function(combo, record, index) {
														if ( record.get('KLArea_id') == combo.getValue() ) {
																combo.collapse();
																return false;
														}
														combo.fireEvent('change', combo, record.get('KLArea_id'));
												}
										},
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
														{ name: 'KLArea_id', type: 'int' },
														{ name: 'KLArea_Name', type: 'string' }
												],
												key: 'KLArea_id',
												sortInfo: {
														field: 'KLArea_Name'
												},
												url: C_LOAD_ADDRCOMBO
										}),
										tabIndex: this.tabIndexBase + 53,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{KLArea_Name}',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'KLArea_id',
										width: 300,
										xtype: 'combo'
								}, {
										disabled: false,
										displayField: 'KLStreet_Name',
										enableKeyEvents: true,
										fieldLabel: lang['ulitsa'],
										hiddenName: 'KLStreet_id',
										minChars: 0,
										mode: 'local',
										queryDelay: 250,
										store: new Ext.data.JsonStore({
												autoLoad: false,
												fields: [
														{ name: 'KLStreet_id', type: 'int' },
														{ name: 'KLStreet_Name', type: 'string' }
												],
												key: 'KLStreet_id',
												sortInfo: {
														field: 'KLStreet_Name'
												},
												url: C_LOAD_ADDRCOMBO
										}),
										tabIndex: this.tabIndexBase + 54,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'{KLStreet_Name}',
												'</div></tpl>'
										),
										triggerAction: 'all',
										valueField: 'KLStreet_id',
										width: 300,
										xtype: 'combo'
								}, {
										border: false,
										layout: 'column',
										items: [{
												border: false,
												layout: 'form',
												items: [{
														disabled: false,
														maskRe: /[^%]/,
														fieldLabel: lang['dom'],
														name: 'Address_House',
														tabIndex: this.tabIndexBase + 55,
														width: 100,
														xtype: 'textfield'
												}]
										}, {
												border: false,
												labelWidth: 220,
												layout: 'form',
												items: [{
														enableKeyEvents: true,
														listeners: {
																'keydown': function(inp, e) {
																		if (!e.shiftKey && e.getKey() == e.TAB)
																		{
																				if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
																				{
																						var grid_id = this.tabGridId;
																						Ext.TaskMgr.start({
																								run : function() {
																										Ext.TaskMgr.stopAll();
																										Ext.getCmp(grid_id).focus();
																								},
																								interval : 200
																						});
																						e.stopEvent();
																				}
																		}
																}.createDelegate(this)
														},
														tabIndex: this.tabIndexBase + 56,
														width: 100,
														xtype: 'swklareatypecombo'
												}]
										}]
								}]
						}, {
								autoHeight: true,
								autoScroll: true,
	//						autoScroll: true,
								bodyStyle: 'margin-top: 5px;',
								border: false,
								layout: 'form',
								listeners: {
										'activate': function(panel) {
												if ( getRegionNick().inlist([ 'kz' ]) ) {
													this.getForm().findField('RegisterSelector_id').setContainerVisible(false);
													this.getForm().findField('PrivilegeType_id').focus(250, true);
												}
												else {
													this.getForm().findField('RegisterSelector_id').focus(250, true);
												}
										}.createDelegate(this)
								},
								title: lang['5_lgota'],
								id: this.tabPanelId + 'filterLgota',
								labelWidth: 150,
								// tabIndexStart: this.tabIndexBase + 57
								items: [{
										codeField: 'RegisterSelector_Code',
										displayField: 'RegisterSelector_Name',
										editable: false,
										fieldLabel: lang['registr'],
										hiddenName: 'RegisterSelector_id',
										listeners: {
												'change': function(combo, newValue, oldValue) {
														var privilege_type_combo = this.getForm().findField('PrivilegeType_id');

														privilege_type_combo.getStore().filterBy(function(record, id) {
																if ( newValue == 1 ) {
																		privilege_type_combo.clearValue();

																		if ( record.get('ReceptFinance_id') == 1 ) {
																				return true;
																		}
																		else {
																				return false;
																		}
																}
																else if ( newValue == 2 ) {
																		privilege_type_combo.clearValue();

																		if ( record.get('ReceptFinance_id') == 2 ) {
																				return true;
																		}
																		else {
																				return false;
																		}
																}
																else {
																		return true;
																}
														});
												}.createDelegate(this),
												'keydown': function (inp, e) {
														if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
																e.stopEvent();
																// Переход к последней кнопке в окне
																this.getOwnerWindow().buttons[this.getOwnerWindow().buttons.length-1].focus();
														}
												}.createDelegate(this)
										},
										store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
														[ 1, 1, lang['federalnyiy'] ],
														[ 2, 2, lang['regionalnyiy'] ]
												],
												fields: [
														{ name: 'RegisterSelector_id', type: 'int'},
														{ name: 'RegisterSelector_Code', type: 'int'},
														{ name: 'RegisterSelector_Name', type: 'string'},
														{ name: 'ReceptFinance_id', type: 'int'}
												],
												key: 'RegisterSelector_id',
												sortInfo: { field: 'RegisterSelector_Code' }
										}),
										tabIndex: this.tabIndexBase + 57,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{RegisterSelector_Code}</font>&nbsp;{RegisterSelector_Name}',
												'</div></tpl>'
										),
										valueField: 'RegisterSelector_id',
										width: 250,
										xtype: 'swbaselocalcombo'
								},
								new sw.Promed.SwPrivilegeTypeCombo({
										listWidth: 350,
										tabIndex: this.tabIndexBase + 58,
										width: 250
								}),{
									id: this.tabPanelId + '_SubCategoryPrivTypePanel',
									layout: 'form',
									border: false,
									items: [{
										xtype: 'swcommonsprcombo',
										comboSubject: 'SubCategoryPrivType',
										hiddenName: 'SubCategoryPrivType_id',
										fieldLabel: 'Подкатегория',
										width: 250
									}]
								}, {
										allowBlank: true,
										ignoreIsEmpty: true,
										codeField: 'PrivilegeStateType_Code',
										displayField: 'PrivilegeStateType_Name',
										editable: false,
										fieldLabel: lang['aktualnost_lgotyi'],
										hiddenName: 'PrivilegeStateType_id',
	/*
										listeners: {
												'change': function(combo, newValue, oldValue) {
														if ( newValue == 1) {
																this.getForm().findField('Privilege_endDate').setValue(null);
																this.getForm().findField('Privilege_endDate').disable();
																this.getForm().findField('Privilege_endDate_Range').setValue(null);
																this.getForm().findField('Privilege_endDate_Range').disable();
														}
														else {
																this.getForm().findField('Privilege_endDate').enable();
																this.getForm().findField('Privilege_endDate_Range').enable();
														}
												}.createDelegate(this),
												'blur': function(combo)  {
														if ( combo.value == '' )
																combo.setValue(1);
												}
										},
	*/
										store: new Ext.data.SimpleStore({
												autoLoad: true,
												data: [
														[ 1, 1, lang['deystvuyuschie_lgotyi'] ],
														[ 2, 2, lang['vklyuchaya_nedeystvuyuschie_lgotyi'] ]
												],
												fields: [
														{ name: 'PrivilegeStateType_id', type: 'int'},
														{ name: 'PrivilegeStateType_Code', type: 'int'},
														{ name: 'PrivilegeStateType_Name', type: 'string'},
														{ name: 'ReceptFinance_id', type: 'int'}
												],
												key: 'PrivilegeStateType_id',
												sortInfo: { field: 'PrivilegeStateType_Code' }
										}),
										tabIndex: this.tabIndexBase + 59,
										tpl: new Ext.XTemplate(
												'<tpl for="."><div class="x-combo-list-item">',
												'<font color="red">{PrivilegeStateType_Code}</font>&nbsp;{PrivilegeStateType_Name}',
												'</div></tpl>'
										),
										value: '',
										valueField: 'PrivilegeStateType_id',
										width: 250,
										xtype: 'swbaselocalcombo'
								}, {
										border: false,
										layout: 'column',
										items: [{
												border: false,
												layout: 'form',
												items: [{
														fieldLabel: lang['data_nachala'],
														name: 'Privilege_begDate',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 60,
														width: 100,
														xtype: 'swdatefield'
												}, {
														fieldLabel: lang['data_okonchaniya'],
														name: 'Privilege_endDate',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 62,
														width: 100,
														xtype: 'swdatefield'
												}]
										}, {
												border: false,
												labelWidth: 220,
												layout: 'form',
												items: [{
														fieldLabel: lang['diapazon_dat_nachala'],
														name: 'Privilege_begDate_Range',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 61,
														width: 170,
														xtype: 'daterangefield'
												}, {
														fieldLabel: lang['diapazon_dat_okonchaniya'],
														name: 'Privilege_endDate_Range',
														plugins: [
																new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
														],
														tabIndex: this.tabIndexBase + 63,
														width: 170,
														xtype: 'daterangefield'
												}]
										}]
								},{
									id: this.tabPanelId + '_RefusePanel',
									xtype  : 'panel',
									border : false,	
									layout: 'form',
									autoheight: true,	
									items: [{
										comboSubject: 'YesNo',
										fieldLabel: lang['otkaznik'],
										tabIndex: this.tabIndexBase + 30,
										hiddenName: 'Refuse_id',
										width: 100,
										xtype: 'swcommonsprcombo'		
									}, {
										comboSubject: 'YesNo',
										fieldLabel: lang['otkaz_na_sled_god'],
										tabIndex: this.tabIndexBase + 30,
										hiddenName: 'RefuseNextYear_id',
										width: 100,
										listeners: {
											'keydown': function(inp, e) {
													if (!e.shiftKey && e.getKey() == e.TAB)
													{
															if ( this.tabGridId && Ext.getCmp(this.tabGridId) )
															{
																	var grid_id = this.tabGridId;
																	Ext.TaskMgr.start({
																			run : function() {
																					Ext.TaskMgr.stopAll();
																					Ext.getCmp(grid_id).focus();
																			},
																			interval : 200
																	});
																	e.stopEvent();
															}
													}
											}.createDelegate(this)
										},
										xtype: 'swcommonsprcombo'		
									}]
								}]
						}]
				});

				 var viewportwidth;
				 var viewportheight;

				 // the more standards compliant browsers (mozilla/netscape/opera/IE7) use window.innerWidth and window.innerHeight

				 if (typeof window.innerWidth != 'undefined')
				 {
					  viewportwidth = window.innerWidth,
					  viewportheight = window.innerHeight
				 }

				// IE6 in standards compliant mode (i.e. with a valid doctype as the first line in the document)

				else if (typeof document.documentElement != 'undefined'
					&& typeof document.documentElement.clientWidth !=
					'undefined' && document.documentElement.clientWidth != 0)
				{
					viewportwidth = document.documentElement.clientWidth,
					viewportheight = document.documentElement.clientHeight
				}

				// older versions of IE

				else
				{
					viewportwidth = document.getElementsByTagName('body')[0].clientWidth,
					viewportheight = document.getElementsByTagName('body')[0].clientHeight
				}

				var pan = this;

				this.Panel = new sw.Promed.Panel({
					autoHeight: true,
					//height: this.tabPanelHeight + 55,
					border: true,
					collapsible: true,
					title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
					region: 'center',
					width: viewportwidth,
					items: [
						this.topFilter,
						this.mainFilters
					],
					listeners: {
						collapse: function(p) {
							//pan.setHeight('25px');
							pan.ownerWindow.doLayout();
							pan.ownerWindow.syncSize();
						},
						expand: function(p) {
							//pan.setHeight(pan.tabPanelHeight + 55);
							pan.ownerWindow.doLayout();
							pan.ownerWindow.syncSize();
						}
					}
				});

				Ext.apply(this, {
					items: [
						this.Panel
					]
				});

		sw.Promed.BaseSearchFiltersFrame.superclass.initComponent.apply(this, arguments);

				if ( this.tabs )
		{

			var num = 0;
			for ( var i = 0; i < this.tabs.length; i++ ) {
				Ext.getCmp(this.tabPanelId).add(this.tabs[i]);
				if(!this.tabs[i].hidden){
					num++
				}
			}

			Ext.getCmp(this.tabPanelId).add(this.getUserFiltersPanel(5 + num + 1));
		}
		else
		{
			Ext.getCmp(this.tabPanelId).add(this.getUserFiltersPanel(6));
		}

		if ( this.hiddenFields ) {
			var i = 0;

			for ( i = 0; i < this.hiddenFields.length; i++ ) {
				this.add(this.hiddenFields[i]);
			}
		}

		if ( isUserGroup('RzhdRegistry')
			&& inlist(this.id, ['EPLStomSW_EvnPLSearchFilterForm', 'EvnPLSearchFilterForm'])
			&& getRegionNick() == 'ufa') {

			this.findById(this.tabPanelId +'filterPatientDop').add(
				new Ext.form.FieldSet({
					xtype: 'fieldset',
					title: langs('Регистр РЖД'),
					width: 755,
					style: 'margin: 0 5px 5px; padding: 5px 10px;',
					autoHeight: true,
					items: [
						new sw.Promed.SwRzhdOrgCombo({
							hiddenName: 'RzhdOrg_id',
							anchor: '100%',
							editable: false
						}),

						{
							border: false,
							layout: 'column',
							defaults: {
								layout: 'form',
								border: false,
								columnWidth: 0.5
							},
							items: [
								{
									items: new sw.Promed.SwRzhdWorkerCategoryCombo({
										hiddenName: 'RzhdWorkerCategory_id',
										anchor: '100%'
									})
								},
								{
									items: new sw.Promed.SwRzhdWorkerGroupCombo({
										hiddenName: 'RzhdWorkerGroup_id',
										anchor: '100%'
									})
								}
							]
						}
					]
				})
			);
		}
	},
	labelAlign: 'right',
	labelWidth: 130,
	tabPanelHeight: 250,
		region: 'north'
});

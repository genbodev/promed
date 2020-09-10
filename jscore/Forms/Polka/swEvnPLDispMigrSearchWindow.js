/**
* swEvnPLDispMigrSearchWindow - окно поиска талона по осмотрам мигрантов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2009 - 2016 Swan Ltd.
*
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispMigrSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispMigrSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispMigrSearchWindow.js',
	addEvnPLDM: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispMigrantEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dispanserizatsii_teen14ti_letnih_uje_otkryito']);
			return false;
		}
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDMSW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				current_window.refreshEvnPLDMList();
			},
			onSelect: function(person_data) {
				getWnd('swEvnPLDispMigrantEditWindow').show({
					action: 'add',
					Person_id: person_data.Person_id,
					PersonEvn_id: person_data.PersonEvn_id,
					Server_id: person_data.Server_id
				});
				return;			
			}
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLDM: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDMSW_EvnPLDispMigrSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispMigrant_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_DT14_id = record.get('EvnPLDispMigrant_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi']);
								}
								else {
									grid.getStore().remove(record);

									if ( grid.getStore().getCount() == 0 ) {
										LoadEmptyRow(grid);
									}
								}

								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_voznikli_oshibki']);
							}
						},
						params: {
							EvnPLDispMigrant_id: evn_pl_DT14_id
						},
						url: '/?c=EvnPLDispMigrant&m=deleteEvnPLDispMigrant'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: 'Удалить случай?',
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispMigrSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDMSW_EvnPLDispMigrSearchGrid').getGrid().getStore().removeAll();
		current_window.findById('EPLDMSW_EvnPLDispMigrSearchGrid').getAction('action_print').setDisabled(false); // печать всегда доступна
	},
	searchInProgress: false,
	getFilterForm: function() {
		if ( this.filterForm == undefined ) {
			this.filterForm = this.findById('EvnPLDispMigrSearchFilterForm');
		}
		return this.filterForm;
	},
	doSearch: function(params) {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;

		if ( params && params['soc_card_id'] )
			var soc_card_id = params['soc_card_id'];
		
		if ( this.isStream )
		{
			this.doStreamInputSearch();
			return true;
		}
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispMigrSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispMigrant_grid = current_window.findById('EPLDMSW_EvnPLDispMigrSearchGrid').ViewGridPanel;

		var vals = filter_form.getForm().getValues();
		var flag = true;
		for ( var value in vals )
		{
			if ( vals[value] != "" )
			flag = false;
		}
		if ( flag )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { filter_form.getForm().findField(0).focus()});
			return false;
		}

		var arr = filter_form.find('disabled', true);
		var params = filter_form.getForm().getValues();

		for (i = 0; i < arr.length; i++)
		{
			if (arr[i].getValue)
			{
				if (arr[i].hiddenName != undefined)
					params[arr[i].hiddenName] = arr[i].getValue();
				else if (arr[i].name != undefined)
					params[arr[i].name] = arr[i].getValue();
			}
		}

		if (filter_form.getForm().isValid())
		{
			if ( soc_card_id )
			{
				var params = {
					soc_card_id: soc_card_id,
					SearchFormType: params.SearchFormType
				};
			}			
			params.start = 0;
			params.limit = 100;

			params.SearchFormType = "EvnPLDispMigrant",
			EvnPLDispMigrant_grid.getStore().removeAll();
			EvnPLDispMigrant_grid.getStore().baseParams = params;
			EvnPLDispMigrant_grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
		else {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert('Поиск', 'Проверьте правильность заполнения полей на форме поиска');
		}
	},
	doStreamInputSearch: function() {
		var grid = this.findById('EPLDMSW_EvnPLDispMigrSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispMigrSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispMigrantStream_begDate = this.begDate;
		params.EvnPLDispMigrantStream_begTime = this.begTime;
		if ( !params.EvnPLDispMigrantStream_begDate && !params.EvnPLDispMigrantStream_begTime ) {
			this.getBegDateTime();
			thisWindow.searchInProgress = false;
		}
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispMigrantStream",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var current_window = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					var response_obj = Ext.util.JSON.decode(response.responseText);

					current_window.begDate = response_obj.begDate;
					current_window.begTime = response_obj.begTime;
					if ( current_window.isStream ) {
						current_window.doStreamInputSearch();
					}
					//current_window.findById('EPLDMSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispMigrSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDMSW_SearchButton');
	},
	initComponent: function() {
		var win = this;

		// стандартные фильтры не подходят, форма сильно отличается
		this.mainFilters = new Ext.TabPanel({
			activeTab: 0,
			autoWidth: true,
			autoHeight: true,
			width: 1000, // исправление бага в IE7
			//height: this.tabPanelHeight,
			defaults: { bodyStyle: 'padding: 0px;' },
			id: 'EPLDMSW_SearchFilterTabbar',
			layoutOnTabChange: true,
			autoScroll: true,
			listeners: {
				'tabchange': function(panel, tab) {
					//this.Panel.syncSize();
					//this.Panel.doLayout();
					//this.getOwnerWindow().syncSize();
					//this.getOwnerWindow().doLayout();
				}.createDelegate(this)
			},
			//plain: true,
			border: false,
			region: 'north',
			items: [{
				autoHeight: true,
				autoScroll: true,
				bodyStyle: 'margin: 10px 5px;',
				border: false,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						//this.getForm().findField('Person_Surname').focus(250, true);
					}.createDelegate(this)
				},
				title: lang['1_patsient'],
				id: 'filterPatient',
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
					}]
				}, {
					autoHeight: true,
					autoScroll: true,
					labelWidth: 114,
					layout: 'form',
					style: 'margin: 0px 5px 0px 5px; padding: 5px 7px;',
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
									width: 100,
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
						width: 500,
						xtype: 'sworgdepcombo'
					}]
				}]
			}, {
				autoHeight: true,
				autoScroll: true,
				bodyStyle: 'margin: 10px 5px;',
				border: false,
				labelWidth: 70,
				layout: 'form',
				listeners: {
					'activate': function(panel) {
						//this.getForm().findField('Person_Surname').focus(250, true);
					}.createDelegate(this)
				},
				title: '2. Результат',
				id: 'filterResult',
				items: [{
					border: false,
					layout: 'column',
					items: [{
						border: false,
						layout: 'form',
						items: [{
							comboSubject: 'ResultDispMigrant',
							fieldLabel: 'Результат',
							hiddenName: 'ResultDispMigrant_id',
							tabIndex: this.tabIndexBase + 29,
							width: 350,
							xtype: 'swcommonsprcombo'
				
						}, {
							autoHeight: true,
							autoScroll: true,
							labelWidth: 70,
							layout: 'form',
							style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
							title: 'Сертификат об обследовании на ВИЧ',
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
										items: [{
											fieldLabel: 'Номер',
											name: 'EvnPLDispMigran_SertHIVNumber',
											tabIndex: this.tabIndexBase + 30,
											width: 150,
											xtype: 'textfield'
										}]
									}, {
										border: false,
										labelWidth: 70,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата',
											name: 'EvnPLDispMigran_SertHIVDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: this.tabIndexBase + 5,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 120,
										layout: 'form',
										items: [{
											fieldLabel: 'Диапазон дат',
											name: 'EvnPLDispMigran_SertHIVDateRange',
											plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
											tabIndex: this.tabIndexBase + 6,
											width: 170,
											xtype: 'daterangefield'
										}]
									}]
							}]
						}, {
							autoHeight: true,
							autoScroll: true,
							labelWidth: 70,
							layout: 'form',
							style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
							title: 'Мед. заключение об инфекционных заболеваниях',
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
										items: [{
											fieldLabel: 'Номер',
											name: 'EvnPLDispMigran_SertInfectNumber',
											tabIndex: this.tabIndexBase + 30,
											width: 150,
											xtype: 'textfield'
										}]
									}, {
										border: false,
										labelWidth: 70,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата',
											name: 'EvnPLDispMigran_SertInfectDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: this.tabIndexBase + 5,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 120,
										layout: 'form',
										items: [{
											fieldLabel: 'Диапазон дат',
											name: 'EvnPLDispMigran_SertInfectDateRange',
											plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
											tabIndex: this.tabIndexBase + 6,
											width: 170,
											xtype: 'daterangefield'
										}]
									}]
							}]
						}, {
							autoHeight: true,
							autoScroll: true,
							labelWidth: 70,
							layout: 'form',
							style: 'margin: 10px 5px 0px 5px; padding: 5px 7px;',
							title: 'Мед заключение о наркомании',
							width: 755,
							xtype: 'fieldset',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',
										items: [{
											fieldLabel: 'Номер',
											name: 'EvnPLDispMigran_SertNarcoNumber',
											tabIndex: this.tabIndexBase + 30,
											width: 150,
											xtype: 'textfield'
										}]
									}, {
										border: false,
										labelWidth: 70,
										layout: 'form',
										items: [{
											fieldLabel: 'Дата',
											name: 'EvnPLDispMigran_SertNarcoDate',
											plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
											tabIndex: this.tabIndexBase + 5,
											width: 100,
											xtype: 'swdatefield'
										}]
									}, {
										border: false,
										labelWidth: 120,
										layout: 'form',
										items: [{
											fieldLabel: 'Диапазон дат',
											name: 'EvnPLDispMigran_SertNarcoDateRange',
											plugins: [new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)],
											tabIndex: this.tabIndexBase + 6,
											width: 170,
											xtype: 'daterangefield'
										}]
									}]
							}]
						}]
					}]
				}]
			}]
		});
		
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 250,
				autoHeight: true,
				id: 'EPLDMSW_SearchFilterPanel',
				region: 'north',
				items: [
					new sw.Promed.Panel({
						autoHeight: true,
						border: false,
						collapsible: true,
						title: lang['najmite_na_zagolovok_chtobyi_svernut_razvernut_panel_filtrov'],
						region: 'center',
						items: [
						new Ext.form.FormPanel({
							afterRender : function() {
								var map = new Ext.KeyMap(this.getEl(), [{
									key: [13],
									fn: function() {
										win.doSearch();
									},
									scope: this
								}]);
							},
							autoScroll: true,
							bodyBorder: false,
							labelAlign: 'right',
							labelWidth: 130,
							id: 'EvnPLDispMigrSearchFilterForm',
							items: [
								this.mainFilters
							]
						})],
						listeners: {
							collapse: function(p) {
								win.doLayout();
								win.syncSize();
							},
							expand: function(p) {
								win.doLayout();
								win.syncSize();
							}
						}
					})
				]
			}),
			new sw.Promed.ViewFrame({
				useArchive: 0,
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispMigrSearchWindow').addEvnPLDM(); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispMigrSearchWindow').openEvnPLDMEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispMigrSearchWindow').openEvnPLDMEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispMigrSearchWindow').deleteEvnPLDM(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispMigrSearchWindow').refreshEvnPLDMList(); } },
					{ name: 'action_print', menu: new Ext.menu.Menu({
						items: [{
							text: 'Журнал учета мигрантов, прошедших мед. освидетельствование',
							handler: function() {
								getWnd('swEvnPLDispMigrListPrintWindow').show({
									template: 'DispMigrant_JournalUchet.rptdesign',
									title: 'Журнал учета иностранных граждан и лиц без гражданства, прошедших медицинское освидетельствование'
								});
							}
						}, {
							text: 'Список мигрантов, прошедших мед. освидетельствование',
							handler: function() {
								getWnd('swEvnPLDispMigrListPrintWindow').show({
									template: 'DispMigrant_PersonList.rptdesign',
									title: 'Список иностранных граждан и лиц без гражданства, прошедших мед. освидетельствование'
								});
							}
						}, {
							text: 'Количество мигрантов, прошедших мед. освидетельствование',
							handler: function() {
								getWnd('swEvnPLDispMigrListPrintWindow').show({
									template: 'EvnPLDispMigrant_kolvo.rptdesign',
									title: 'Количество иностранных граждан и лиц без гражданства, прошедших медицинское освидетельствование, и количество выявленных инфекционных заболеваний'
								});
							}
						}]
					})}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EPLDMSW_SearchButton', type: 'field'
				},
				id: 'EPLDMSW_EvnPLDispMigrSearchGrid',
				layout: 'fit',
				object: 'EvnPLDM',
				pageSize: 100,
				paging: true,
				region: 'center',
				root: 'data',
				totalProperty: 'totalCount', 
				onBeforeLoadData: function() {
					this.getButtonSearch().disable();
				}.createDelegate(this),
				onLoadData: function() {
					this.getButtonSearch().enable();
				}.createDelegate(this),
				title: '',
				toolbar: true,
				stringfields: [
					{ name: 'EvnPLDispMigrant_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: 'Дата рождения' },
					{ name: 'PersonPAddress', type: 'string', header: 'Адрес проживания', width: 150 },
					{ name: 'PersonUAddress', type: 'string', header: 'Адрес регистрации', width: 150 },
					{ name: 'EvnPLDispMigrant_setDate', type: 'date', format: 'd.m.Y', header: 'Дата начала мед. освид.' },
					{ name: 'EvnPLDispMigrant_disDate', type: 'date', format: 'd.m.Y', header: 'Дата окончания мед. освид.' },
					{ name: 'ResultDispMigrant_Name', type: 'string', header: 'Результат', width: 100 }
				],
				onRowSelect: function(sm, index, record) {
					if (win.viewOnly == true)
					{
						this.getAction('action_view').setDisabled(false);
						this.getAction('action_edit').setDisabled(true);
						this.getAction('action_delete').setDisabled(true);
					}
					else
					{
						// Запретить редактирование/удаление архивных записей
						if (getGlobalOptions().archive_database_enable) {
							this.getAction('action_edit').setDisabled(record.get('archiveRecord') == 1);
							this.getAction('action_delete').setDisabled(record.get('archiveRecord') == 1);
						}
					}
				}
			})],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDMSW_SearchButton',
				tabIndex: TABINDEX_EPLDMSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDMSW+91,
				text: BTN_FRMRESET
			},
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDMSW+94,
				text: BTN_FRMCANCEL
			}
			]
		});
		sw.Promed.swEvnPLDispMigrSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispMigrSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDMSW_SearchFilterTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					current_window.doReset();
					break;					
				case Ext.EventObject.J:
					current_window.hide();
					break;					

				case Ext.EventObject.NUM_ONE:
				case Ext.EventObject.ONE:
					search_filter_tabbar.setActiveTab(0);
					break;

				case Ext.EventObject.NUM_TWO:
				case Ext.EventObject.TWO:
					search_filter_tabbar.setActiveTab(1);
					break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.FIVE,
			Ext.EventObject.FOUR,
			Ext.EventObject.J,
			Ext.EventObject.NUM_FIVE,
			Ext.EventObject.NUM_FOUR,
			Ext.EventObject.NUM_ONE,
			Ext.EventObject.NUM_SIX,
			Ext.EventObject.NUM_TWO,
			Ext.EventObject.NUM_THREE,
			Ext.EventObject.ONE,
			Ext.EventObject.SIX,
			Ext.EventObject.TWO,
			Ext.EventObject.THREE
		],
		stopEvent: true
	}, {
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispMigrSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					current_window.addEvnPLDM();
					break;									
			}
		},
		key: [
			Ext.EventObject.INSERT	
		],
		stopEvent: true
	}],
	layout: 'border',
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDMEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispMigrant_grid = current_window.findById('EPLDMSW_EvnPLDispMigrSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispMigrantEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}

		if (!EvnPLDispMigrant_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var EvnPLDispMigrant_id = EvnPLDispMigrant_grid.getSelectionModel().getSelected().data.EvnPLDispMigrant_id;
		var person_id = EvnPLDispMigrant_grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = EvnPLDispMigrant_grid.getSelectionModel().getSelected().data.Server_id;

		if (EvnPLDispMigrant_id > 0 && person_id > 0 && server_id >= 0)
		{
			getWnd('swEvnPLDispMigrantEditWindow').show({
				action: action,
				EvnPLDispMigrant_id: EvnPLDispMigrant_id,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDMList();
				},
				Person_id: person_id,
				Server_id: server_id
			});
		}
	},
	plain: true,
	refreshEvnPLDMList: function(action) {
		this.doSearch();
	},
	resizable: true,
	show: function() {
		sw.Promed.swEvnPLDispMigrSearchWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.viewOnly = false;
		if(arguments[0])
		{
			if(arguments[0].viewOnly)
				this.viewOnly = arguments[0].viewOnly;
		}
		this.findById('EPLDMSW_EvnPLDispMigrSearchGrid').setActionDisabled('action_add', this.viewOnly);
		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();
		
		var form = this.findById('EvnPLDispMigrSearchFilterForm');
		var base_form = form.getForm();
		
		/*base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;*/

		this.findById('EPLDMSW_SearchFilterTabbar').setActiveTab('EPLDT14_FirstTab');
		this.findById('EPLDMSW_EvnPLDispMigrSearchGrid').getAction('action_print').setDisabled(false); // печать всегда доступна
	},
	title: 'Медицинское освидетельствование мигрантов: Поиск',
	width: 800
});

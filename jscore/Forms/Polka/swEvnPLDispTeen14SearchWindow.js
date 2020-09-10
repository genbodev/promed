/**
* swEvnPLDispTeen14SearchWindow - окно поиска талона по диспансеризации Teen14ти-летних подростков
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2009 - 2011 Swan Ltd.
* @author		Ivan Pshenitcyn aka IVP (ipshon@gmail.com)
* @version		01.08.2011
* @comment		Префикс для id компонентов EPLDT14SW (EvnPLDispTeen14SearchWindow)
*				tabIndex: 2301
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispTeen14EditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispTeen14SearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispTeen14SearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispTeen14SearchWindow.js',
	addEvnPLDT14: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispTeen14EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dispanserizatsii_teen14ti_letnih_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDT14SW_YearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDT14SW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				current_window.refreshEvnPLDT14List();
			},
			onSelect: function(person_data) {
				// сначала проверим, можем ли мы добавлять талон на этого человека
				Ext.Ajax.request({
					url: '/?c=EvnPLDispTeen14&m=checkIfEvnPLDispTeen14Exists',
					callback: function(opt, success, response) {
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.isEvnPLDispTeen14Exists == false )
							{
								getWnd('swEvnPLDispTeen14EditWindow').show({
									action: 'add',
									Person_id: person_data.Person_id,
									PersonEvn_id: person_data.PersonEvn_id,
									Server_id: person_data.Server_id,
									Year: Year
								});
								return;
							}
							else
							{
								sw.swMsg.alert("Ошибка", "На данного подростка уже заведена карта диспансеризации подростка 14 лет",
									function () {
										getWnd('swPersonSearchWindow').hide();
										current_window.addEvnPLDT14();
									}
								);
								return;
							}
						}
					},
					params: { Person_id: person_data.Person_id }
				});				
			},
			searchMode: 'DT14',
			Year: Year
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLDT14: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispTeen14_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_DT14_id = record.get('EvnPLDispTeen14_id');

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
							EvnPLDispTeen14_id: evn_pl_DT14_id
						},
						url: '/?c=EvnPLDispTeen14&m=deleteEvnPLDispTeen14'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispTeen14SearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').getGrid().getStore().removeAll();
	},
	searchInProgress: false,
	getFilterForm: function() {
		if ( this.filterForm == undefined ) {
			this.filterForm = this.findById('EvnPLDispTeen14SearchFilterForm');
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
		var filter_form = current_window.findById('EvnPLDispTeen14SearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var evnpldispTeen14_grid = current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').ViewGridPanel;

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
		
		var Year = Ext.getCmp('EPLDT14SW_YearCombo').getValue();
		if (Year>0)
		{
			if (filter_form.getForm().findField('EvnPLDispTeen14_setDate_Range').getValue1()=='')
			{
				params['EvnPLDispTeen14_setDate_Range'] = ('01.01.'+Year+' - '+'31.12.'+Year);
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

			if (!Ext.isEmpty(params.autoLoadArchiveRecords)) {
				current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispTeen14",
			evnpldispTeen14_grid.getStore().removeAll();
			evnpldispTeen14_grid.getStore().baseParams = params;
			evnpldispTeen14_grid.getStore().load({
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
		var grid = this.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispTeen14SearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispTeen14Stream_begDate = this.begDate;
		params.EvnPLDispTeen14Stream_begTime = this.begTime;
		if ( !params.EvnPLDispTeen14Stream_begDate && !params.EvnPLDispTeen14Stream_begTime ) {
			this.getBegDateTime();
			thisWindow.searchInProgress = false;
		}
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispTeen14Stream",
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
					current_window.findById('EPLDT14SW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispTeen14SearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDT14SW_SearchButton');
	},
	initComponent: function() {
		var win = this;
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
                                autoHeight: true,
				id: 'EPLDT14SW_SearchFilterPanel',
				region: 'north',
				//layout: 'border',
				items: [
					{
						border: false,
						region: 'center',
						layout: 'column',
						height: 25,
						items: [
						{
							columnWidth: 0.50,
							border: false,
							layout: 'form',
							items: [{
								xtype: 'swbaselocalcombo',
								mode: 'local',
								triggerAction: 'all',
								fieldLabel: lang['god'],
								store: new Ext.data.JsonStore(
								{
									key: 'EvnPLDispTeen14_Year',
									autoLoad: false,
									fields:
									[
										{name:'EvnPLDispTeen14_Year',type: 'int'},
										{name:'count', type: 'int'}
									],
									url: '/?c=EvnPLDispTeen14&m=getEvnPLDispTeen14Years'
								}),
								id: 'EPLDT14SW_YearCombo',
								hiddenName: 'EvnPLDispTeen14_Year',
								tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDispTeen14_Year}</td>'+
									'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
									'</div></tpl>',
								region: 'north',
								valueField: 'EvnPLDispTeen14_Year',
								displayField: 'EvnPLDispTeen14_Year',
								editable: false,
								tabIndex: 2036,
								enableKeyEvents: true,
								listeners: {
									'keydown': function(combo, e)
									{
										if ( e.getKey() == Ext.EventObject.ENTER )
										{											
											e.stopEvent();
											var current_window = Ext.getCmp('EvnPLDispTeen14SearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispTeen14SearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDT14SW_EvnPLDispTeen14SearchGrid').focus();													
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDT14SW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDT14SW+56
							}]
						}, {
							columnWidth: 0.50,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDT14SW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDT14SW+57
							}]
						}]
				},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispTeen14SearchFilterForm',
						ownerWindow: this,
						listeners: {
							'collapse': function(p) {
								p.ownerWindow.doLayout();
							},
							'expand': function(p) {
								p.ownerWindow.doLayout();
							}
						},
						region: 'north',
						searchFormType: 'EvnPLDispTeen14',
						tabIndexBase: TABINDEX_EPLDT14SW,
						tabPanelId: 'EPLDT14SW_SearchFilterTabbar',
						tabGridId: 'EPLDT14SW_EvnPLDispTeen14SearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 180,
							id: 'EPLDT14_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispTeen14SearchFilterForm');
									form.getForm().findField('EvnPLDispTeen14_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: '<u>6</u>. Дисп. 14-ти лет',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispTeen14_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDT14SW + 59,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispTeen14_setDate_Range',
										tabIndex: TABINDEX_EPLDT14SW + 60,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_okonchaniya'],
										name: 'EvnPLDispTeen14_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDT14SW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispTeen14_disDate_Range',
										tabIndex: TABINDEX_EPLDT14SW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy'],
								name: 'EvnPLDispTeen14_VizitCount',
								tabIndex: TABINDEX_EPLDT14SW + 63,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_ot'],
								name: 'EvnPLDispTeen14_VizitCount_From',
								tabIndex: TABINDEX_EPLDT14SW + 64,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_do'],
								name: 'EvnPLDispTeen14_VizitCount_To',
								tabIndex: TABINDEX_EPLDT14SW + 65,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispTeen14_IsFinish',
								tabIndex: TABINDEX_EPLDT14SW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								id: 'EPLDT14SW_HealthKindCombo',
								listeners: {
									/*'render': function(combo) {
										combo.getStore().load();
									},*/
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDT14SW_EvnPLDispTeen14SearchGrid').focus();													
												},
												interval : 200
											});
										}
									}
								},
								hiddenName: 'EvnPLDispTeen14_HealthKind_id',
								tabIndex: TABINDEX_EPLDT14SW + 66,
								validateOnBlur: false,
								width: 100,
								xtype: 'swhealthkindcombo'
							}]
						}]
					})]
			}),
			new sw.Promed.ViewFrame({
				useArchive: 1,
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispTeen14SearchWindow').addEvnPLDT14(); } },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispTeen14SearchWindow').openEvnPLDT14EditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispTeen14SearchWindow').openEvnPLDT14EditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispTeen14SearchWindow').deleteEvnPLDT14(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispTeen14SearchWindow').refreshEvnPLDT14List(); } },
					{name: 'action_print',
						menuConfig: {
							printObjectListFull: { handler: function() {
								var base_form = this.findById('EvnPLDispTeen14SearchFilterForm').getForm();
								base_form.submit();
							}.createDelegate(this) }
						}
					},
					{
						hidden: false,
						name:'action_printpassport',
						tooltip: lang['napechatat_pasport_zdorovya'],
						icon: 'img/icons/print16.png',
						handler: function() {
							var grid = Ext.getCmp('EPLDT14SW_EvnPLDispTeen14SearchGrid').getGrid();
							var record = grid.getSelectionModel().getSelected();							
							if (!record) {
								Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
								return false;
							}
							var evn_pl_id = record.get('EvnPLDispTeen14_id');
							var server_id = record.get('Server_id');							
							if (!evn_pl_id)
								return false;
							var id_salt = Math.random();
							var win_id = 'print_passport' + Math.floor(id_salt * 10000);
							var win = window.open('/?c=EvnPLDispTeen14&m=printEvnPLDispTeen14Passport&EvnPLDispTeen14_id=' + evn_pl_id + '&Server_id=' + server_id, win_id);
						}, 
						text: lang['pechat_pasporta_zdorovya']
					}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EPLDT14SW_SearchButton', type: 'field'
				},
				id: 'EPLDT14SW_EvnPLDispTeen14SearchGrid',
				layout: 'fit',
				object: 'EvnPLDT14',
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
					{ name: 'EvnPLDispTeen14_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
					{ name: 'EvnPLDispTeen14_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
					{ name: 'EvnPLDispTeen14_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
					{ name: 'EvnPLDispTeen14_VizitCount', type: 'int', header: lang['posescheniy'] },
					{ name: 'EvnPLDispTeen14_IsFinish', type: 'string', header: lang['zakonch'], width:50 }
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
				id: 'EPLDT14SW_SearchButton',
				tabIndex: TABINDEX_EPLDT14SW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDT14SW+91,
				text: BTN_FRMRESET
			}, {
				id: 'mode_button',
				handler: function() {
					if ( this.ownerCt.isStream == false )
					{
						this.ownerCt.setStreamInputMode();
					}
					else
					{
						this.ownerCt.setSearchMode();
					}
				},
				tabIndex: TABINDEX_EPLDT14SW+92,
				text: "Режим потокового ввода"
			}, /*{
				handler: function() {
					var base_form = this.findById('EvnPLDispTeen14SearchFilterForm').getForm();
					base_form.submit();					
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EPLSW + 111,
				text: lang['pechat_spiska']
			},*/
			{
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				tabIndex: TABINDEX_EPLDT14SW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDT14SW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('mode_button').focus(true, 200);
				}
			}
			]
		});
		sw.Promed.swEvnPLDispTeen14SearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispTeen14SearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDT14SW_SearchFilterTabbar');

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

				case Ext.EventObject.NUM_THREE:
				case Ext.EventObject.THREE:
					search_filter_tabbar.setActiveTab(2);
					break;

				case Ext.EventObject.NUM_FOUR:
				case Ext.EventObject.FOUR:
					search_filter_tabbar.setActiveTab(3);
					break;

				case Ext.EventObject.NUM_FIVE:
				case Ext.EventObject.FIVE:
					search_filter_tabbar.setActiveTab(4);
					break;

				case Ext.EventObject.NUM_SIX:
				case Ext.EventObject.SIX:
					search_filter_tabbar.setActiveTab(5);
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
			var current_window = Ext.getCmp('EvnPLDispTeen14SearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					current_window.addEvnPLDT14();
					break;									
			}
		},
		key: [
			Ext.EventObject.INSERT	
		],
		stopEvent: true
	}],
	layout: 'border',
	loadYearsCombo: function () {
		var years_combo = this.findById('EPLDT14SW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				url: C_EPLDT14_LOAD_YEARS,
				callback: function() {
					var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
				}
			});
		}
		else
		{
			var date = new Date();
					var year = date.getFullYear();
					years_combo.setValue(year);
					years_combo.focus(true, 500);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDT14EditWindow: function(action) {
		var current_window = this;
		var evnpldispTeen14_grid = current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispTeen14EditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}

		if (!evnpldispTeen14_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var evnpldispTeen14_id = evnpldispTeen14_grid.getSelectionModel().getSelected().data.EvnPLDispTeen14_id;
		var person_id = evnpldispTeen14_grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = evnpldispTeen14_grid.getSelectionModel().getSelected().data.Server_id;

		if (evnpldispTeen14_id > 0 && person_id > 0 && server_id >= 0)
		{
			getWnd('swEvnPLDispTeen14EditWindow').show({
				action: action,
				EvnPLDispTeen14_id: evnpldispTeen14_id,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDT14List();
				},
				Person_id: person_id,
				Server_id: server_id
			});
		}
	},
	plain: true,
	refreshEvnPLDT14List: function(action) {
		var current_window = this;
		var evnpldispTeen14_grid = current_window.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').ViewGridPanel;
		if ( this.isStream ) {
			this.doStreamInputSearch();
			this.loadYearsCombo();
		}
		else {
			if ( evnpldispTeen14_grid.getStore().getCount() > 0 && evnpldispTeen14_grid.getStore().getAt(0).get('EvnPLDispTeen14_id') > 0 )
				this.doSearch();
		}
	},
	resizable: true,
	setSearchMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].enable();
		this.buttons[1].enable();
		button.setText(lang['rejim_potokovogo_vvoda']);
		this.setTitle(WND_POL_EPLDT14SEARCH);
		Ext.getCmp('EvnPLDispTeen14SearchFilterForm').setHeight(280);
		this.findById('EvnPLDispTeen14SearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDT14SW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDT14SW_YearCombo').focus(true, 100);

	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDT14STREAM);
		this.findById('EvnPLDispTeen14SearchFilterForm').hide();
		Ext.getCmp('EvnPLDispTeen14SearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDT14SW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDT14SW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispTeen14SearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDT14SW_SearchFilterTabbar').setActiveTab('EPLDT14_FirstTab');
				this.setStreamInputMode();
			}
			else
				this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

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
		this.findById('EPLDT14SW_EvnPLDispTeen14SearchGrid').setActionDisabled('action_add', this.viewOnly);
		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();
		
		var form = this.findById('EvnPLDispTeen14SearchFilterForm');
		var base_form = form.getForm();
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;

		//EvnPLDispTeen14_HealthKind_id
		base_form.findField('EvnPLDispTeen14_HealthKind_id').getStore().clearFilter();
		base_form.findField('EvnPLDispTeen14_HealthKind_id').lastQuery = '';
		base_form.findField('EvnPLDispTeen14_HealthKind_id').getStore().filterBy(function(rec) {
			if(!rec.get('HealthKind_Code').inlist(['6','7']))
			{
				return true;
			}
			return false;
		});
		this.loadYearsCombo();
		this.findById('EPLDT14SW_SearchFilterTabbar').setActiveTab('EPLDT14_FirstTab');
	},
	title: WND_POL_EPLDT14SEARCH,
	width: 800
});

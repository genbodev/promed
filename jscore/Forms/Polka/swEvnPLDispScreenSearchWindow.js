/**
* swEvnPLDispScreenSearchWindow - окно поиска скрининговых исследований
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Dmitry Vlasenko
* @comment		Префикс для id компонентов EPLDSSW (EvnPLDispScreenSearchWindow)
*
*
* Использует: окно редактирования исследования (swEvnPLDispScreenEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispScreenSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispScreenSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispScreenSearchWindow.js',
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	checkScreenAge: function(age) {
		if (age < 18) {
			Ext.Msg.alert(langs('Сообщение'), langs('Неверный возраст для скрининга'));
			return false;
		}
		return true;
	},
	onEvnPLDispScreenSaved: function(data)
	{
		var win = this;
		var evnpldispdop_grid = this.findById('EPLDSSW_EvnPLDispScreenSearchGrid').getGrid();

		if ( !data || !data.evnPLDispScreenData ) {
			return false;
		}
		// Обновить запись в grid
		var record = evnpldispdop_grid.getStore().getById(data.evnPLDispScreenData.EvnPLDispScreen_id);
		if ( record ) {
			var grid_fields = new Array();
			var i = 0;
			evnpldispdop_grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});
			for ( i = 0; i < grid_fields.length; i++ ) {
				record.set(grid_fields[i], data.evnPLDispScreenData[grid_fields[i]]);
			}
			record.commit();

			win.checkPrintCost();
		}
		else {
			if ( evnpldispdop_grid.getStore().getCount() == 1 && !evnpldispdop_grid.getStore().getAt(0).get('EvnPLDispScreen_id') ) {
				evnpldispdop_grid.getStore().removeAll();
			}
			evnpldispdop_grid.getStore().loadData({'data': [ data.evnPLDispScreenData ]}, true);
		}
	},
	addEvnPLDP: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispScreenEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_issledovaniya_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDSSW_YearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDSSW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
			},
			onSelect: function(person_data) {
				if (current_window.checkScreenAge(person_data.Person_Age)) {
					getWnd('swEvnPLDispScreenEditWindow').show({
						action: 'add',
						DispClass_id: 13,
						Year: Year,
						EvnPLDispScreen_id: null,
						onHide: Ext.emptyFn,
						callback: function(data) {
							current_window.onEvnPLDispScreenSaved(data);
						},
						Person_id: person_data.Person_id,
						Server_id: person_data.Server_id
					});
				}
			},
			searchMode: 'evnpldispscreen',
			Year: Year
		});
	},
	deleteEvnPLDP: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispScreen_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispScreen_id');

		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						callback: function(options, success, response) {
							if ( success ) {
								var response_obj = Ext.util.JSON.decode(response.responseText);

								if ( response_obj.success == false ) {
									sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_talona_dd']);
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
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_talona_dd_voznikli_oshibki']);
							}
						},
						params: {
							EvnPLDispScreen_id: evn_pl_dd_id
						},
						url: '/?c=EvnPLDispScreen&m=deleteEvnPLDispScreen'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon_dd'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var current_window = this;
		var filter_form = current_window.findById('EvnPLDispScreenSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').getGrid().getStore().removeAll();
		filter_form.getForm().findField('LpuAttachType_id').setValue(1);
	},
	searchInProgress: false,
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
		var filter_form = current_window.findById('EvnPLDispScreenSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispScreen_grid = current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').ViewGridPanel;

		var vals = filter_form.getForm().getValues();
		var flag = true;
		for ( value in vals )
		{
			if ( vals[value] != "" )
			flag = false;
		}
		if ( flag )
		{
			sw.swMsg.alert("Внимание", "Заполните хотя бы одно поле для поиска.",
			function () { filter_form.getForm().findField(0).focus()});
			thisWindow.searchInProgress = false;
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
		
		var Year = Ext.getCmp('EPLDSSW_YearCombo').getValue();
		if (Year>0)
		{
			params['PersonDopDisp_Year'] = Year;
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
				current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispScreen",
			EvnPLDispScreen_grid.getStore().removeAll();
			EvnPLDispScreen_grid.getStore().baseParams = params;
			EvnPLDispScreen_grid.getStore().load({
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
		var grid = this.findById('EPLDSSW_EvnPLDispScreenSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispScreenSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispScreenStream_begDate = this.begDate;
		params.EvnPLDispScreenStream_begTime = this.begTime;
		if ( !params.EvnPLDispScreenStream_begDate && !params.EvnPLDispScreenStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispScreenStream",
			grid.getStore().removeAll();
			grid.getStore().baseParams = params;
			grid.getStore().load({
				callback: function(){
					thisWindow.searchInProgress = false;
				},
				params: params
			});
		}
	},
	draggable: true,
	getBegDateTime: function() {
		var current_window = this;
		/*Ext.Ajax.request({
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
					current_window.findById('EPLDSSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});*/
	},
	height: 550,
	id: 'EvnPLDispScreenSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDSSW_SearchButton');
	},
	printCost: function() {
		var grid = this.EvnPLDispScreenSearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispScreen_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispScreen_id'),
				type: 'EvnPLDispScreen',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.EvnPLDispScreenSearchGrid.getGrid();
		var menuPrint = this.EvnPLDispScreenSearchGrid.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispScreen_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispScreen_IsEndStage') != lang['da']);
			}
		}
	},
	initComponent: function() {
		var win = this;
		
		win.EvnPLDispScreenSearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispScreenSearchWindow').addEvnPLDP(); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispScreenSearchWindow').openEvnPLDPEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispScreenSearchWindow').openEvnPLDPEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispScreenSearchWindow').deleteEvnPLDP(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispScreenSearchWindow').refreshEvnPLDPList(); } },
				{ name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), text: langs('Справка о стоимости лечения'), handler: function () { win.printCost() }}
				}},
				{
					hidden: false,
					name:'action_printpassport',
					tooltip: lang['napechatat_pasport_zdorovya'],
					icon: 'img/icons/print16.png',
					handler: function() {
						var grid = Ext.getCmp('EPLDSSW_EvnPLDispScreenSearchGrid').getGrid();
						var record = grid.getSelectionModel().getSelected();							
						if (!record) {
							Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
							return false;
						}
						var evn_pl_id = record.get('EvnPLDispScreen_id');
						var server_id = record.get('Server_id');							
						if (!evn_pl_id)
							return false;
						var id_salt = Math.random();
						var win_id = 'print_passport' + Math.floor(id_salt * 10000);
						var win = window.open('/?c=EvnPLDispScreen&m=printEvnPLDispScreenPassport&EvnPLDispScreen_id=' + evn_pl_id + '&Server_id=' + server_id, win_id);
					}, 
					text: lang['pechat_pasporta_zdorovya']
				}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				var disabled = false;
				if (getGlobalOptions().archive_database_enable) {
					disabled = disabled || (record.get('archiveRecord') == 1);
				}
				if (Ext.isEmpty(record.get('EvnPLDispScreen_id'))) {
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_view', true);
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_edit', true);
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_delete', true);
				} else {
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_view', false);
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_edit', disabled);
					win.EvnPLDispScreenSearchGrid.setActionDisabled('action_delete', disabled);
				}

				win.checkPrintCost();
			},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLDSSW_SearchButton', type: 'field'
			},
			id: 'EPLDSSW_EvnPLDispScreenSearchGrid',
			layout: 'fit',
			object: 'EvnPLDP',
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
				{ name: 'EvnPLDispScreen_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'AisError', type: 'string', hidden: true },
				{ name: 'Person_Surname', header: langs('Фамилия'), width: 150 },
				{ name: 'Person_Firname', header: langs('Имя'), width: 150 },
				{ name: 'Person_Secname', header: langs('Отчество'), width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: langs('Д/р') },
				{ name: 'Sex_Name', header: langs('Пол'), width: 80 },
				//{ name: 'AgeGroupDisp_Name', header: langs('Возрастная группа'), width: 150 },
				{ name: 'EvnPLDispScreen_setDate', type: 'date', format: 'd.m.Y', header: langs('Дата начала') },
				{ name: 'EvnPLDispScreen_disDate', type: 'date', format: 'd.m.Y', header: langs('Дата окончания') },
				{ name: 'EvnPLDispScreen_IsEndStage', type: 'string', header: langs('Закончен') }
			]
		});
			
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
				autoHeight: true,
				id: 'EPLDSSW_SearchFilterPanel',
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
							width: 300,
							border: false,
							layout: 'form',
							items: [{
								allowBlank: false,
								xtype: 'swbaselocalcombo',
								mode: 'local',
								triggerAction: 'all',
								fieldLabel: lang['god'],
								store: new Ext.data.JsonStore(
								{
									key: 'EvnPLDisp_Year',
									autoLoad: false,
									fields:
									[
										{name:'EvnPLDisp_Year',type: 'int'},
										{name:'count', type: 'int'}
									],
									url: C_EPLD_LOAD_YEARS
								}),
								id: 'EPLDSSW_YearCombo',
								hiddenName: 'EvnPLDisp_Year',
								tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDisp_Year}</td>'+
									'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
									'</div></tpl>',
								region: 'north',
								valueField: 'EvnPLDisp_Year',
								displayField: 'EvnPLDisp_Year',
								editable: false,
								tabIndex: 2036,
								enableKeyEvents: true,
								listeners: {
									'keydown': function(combo, e)
									{
										if ( e.getKey() == Ext.EventObject.ENTER )
										{											
											e.stopEvent();
											var current_window = Ext.getCmp('EvnPLDispScreenSearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispScreenSearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDSSW_EvnPLDispScreenSearchGrid').focus();
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDSSW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDSSW+56
							}]
						}/*, {
							width: 400,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDSSW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDSSW+57
							}]
						}*/]
					},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispScreenSearchFilterForm',
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
						searchFormType: 'EvnPLDispScreen',
						tabIndexBase: TABINDEX_EPLDSSW,
						tabPanelId: 'EPLDSSW_SearchFilterTabbar',
						tabGridId: 'EPLDSSW_EvnPLDispScreenSearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 200,
							id: 'EPLDSSW_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispScreenSearchFilterForm');
									form.getForm().findField('EvnPLDispScreen_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: '<u>6</u>. Скрининговое исследование',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispScreen_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDSSW + 59,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispScreen_setDate_Range',
										tabIndex: TABINDEX_EPLDSSW + 60,
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
										name: 'EvnPLDispScreen_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDSSW + 61,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispScreen_disDate_Range',
										tabIndex: TABINDEX_EPLDSSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispScreen_IsEndStage',
								tabIndex: TABINDEX_EPLDSSW + 63,
								width: 100,
								xtype: 'swyesnocombo'
							}/*, {
								comboSubject: 'AgeGroupDisp',
								fieldLabel: lang['vozrastnaya_gruppa'],
								tabIndex: TABINDEX_EPLDSSW + 64,
								loadParams: {params: {where: "where DispType_id = 5"}},
								hiddenName: 'AgeGroupDisp_id',
								lastQuery: '',
								width: 300,
								xtype: 'swcommonsprcombo'
							}*/]
							}]
					})]
			}),
			win.EvnPLDispScreenSearchGrid
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDSSW_SearchButton',
				tabIndex: TABINDEX_EPLDSSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDSSW+91,
				text: BTN_FRMRESET
			}, {
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
				hidden: true,
				tabIndex: TABINDEX_EPLDSSW+92,
				text: "Режим потокового ввода"
			},/* {
				handler: function() {
					var base_form = this.findById('EvnPLDispScreenSearchFilterForm').getForm();
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
				tabIndex: TABINDEX_EPLDSSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDSSW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					this.buttons[0].focus(true, 200);
				}.createDelegate(this)
			}
			]
		});
		sw.Promed.swEvnPLDispScreenSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispScreenSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDSSW_SearchFilterTabbar');

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
	}],
	layout: 'border',
	loadYearsCombo: function () {
		var years_combo = this.findById('EPLDSSW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 13
				},
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
	openEvnPLDPEditWindow: function(action) {
		var current_window = this;
		var EvnPLDispScreen_grid = current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispScreenEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_issledovaniya_uje_otkryito']);
			return false;
		}

		var record = EvnPLDispScreen_grid.getSelectionModel().getSelected();
		if (!record)
		{
			return false;
		}
		
		var EvnPLDispScreen_id = record.data.EvnPLDispScreen_id;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (/*EvnPLDispScreen_id > 0 &&*/ person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				DispClass_id: 13,
				EvnPLDispScreen_id: EvnPLDispScreen_id,
				onHide: Ext.emptyFn,
				callback: function(data) {
					current_window.onEvnPLDispScreenSaved(data);
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			var age = swGetPersonAge(new Date(record.get('Person_Birthday')), new Date());
			if (this.checkScreenAge(age)) {
				getWnd('swEvnPLDispScreenEditWindow').show(params);
			}
		}
	},
	plain: true,
	refreshEvnPLDPList: function(action) {
		var current_window = this;
		var EvnPLDispScreen_grid = current_window.findById('EPLDSSW_EvnPLDispScreenSearchGrid').ViewGridPanel;
		if ( this.isStream ) {
			this.doStreamInputSearch();
			this.loadYearsCombo();
		}
		else {
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
		this.setTitle(WND_POL_EPLDSSEARCH);
		Ext.getCmp('EvnPLDispScreenSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispScreenSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDSSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDSSW_YearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EvnPLDispScreenSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDSSW_EvnPLDispScreenSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDSSTREAM);
		this.findById('EvnPLDispScreenSearchFilterForm').hide();
		Ext.getCmp('EvnPLDispScreenSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDSSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDSSW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispScreenSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDSSW_SearchFilterTabbar').setActiveTab(2);
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
		
		var form = this.findById('EvnPLDispScreenSearchFilterForm');
		var base_form = form.getForm();
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;

		this.loadYearsCombo();
		this.findById('EPLDSSW_SearchFilterTabbar').setActiveTab(0);
	},
	title: WND_POL_EPLDSSEARCH,
	width: 800
});

/**
* swEvnPLDispScreenChildSearchWindow - окно поиска скрининговых исследований
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright	Copyright (c) 2015 Swan Ltd.
* @author		Dmitry Vlasenko
* @comment		Префикс для id компонентов EPLDSCSW (EvnPLDispScreenChildSearchWindow)
*
*
* Использует: окно редактирования исследования (swEvnPLDispScreenChildEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispScreenChildSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispScreenChildSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispScreenChildSearchWindow.js',
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	checkScreenAge: function(age) {
		if (age >= 18) {
			Ext.Msg.alert(langs('Сообщение'), langs('Неверный возраст для скрининга'));
			return false;
		}
		return true;
	},
	onEvnPLDispScreenChildSaved: function(data)
	{
		var win = this;
		var evnpldispdop_grid = this.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').getGrid();

		if ( !data || !data.evnPLDispScreenChildData ) {
			return false;
		}
		// Обновить запись в grid
		var record = evnpldispdop_grid.getStore().getById(data.evnPLDispScreenChildData.EvnPLDispScreenChild_id);
		if ( record ) {
			var grid_fields = new Array();
			var i = 0;
			evnpldispdop_grid.getStore().fields.eachKey(function(key, item) {
				grid_fields.push(key);
			});
			for ( i = 0; i < grid_fields.length; i++ ) {
				record.set(grid_fields[i], data.evnPLDispScreenChildData[grid_fields[i]]);
			}
			record.commit();

			win.checkPrintCost();
		}
		else {
			if ( evnpldispdop_grid.getStore().getCount() == 1 && !evnpldispdop_grid.getStore().getAt(0).get('EvnPLDispScreenChild_id') ) {
				evnpldispdop_grid.getStore().removeAll();
			}
			evnpldispdop_grid.getStore().loadData({'data': [ data.evnPLDispScreenChildData ]}, true);
		}
	},
	addEvnPLDP: function() {
		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispScreenChildEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_issledovaniya_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDSCSW_YearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDSCSW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
			},
			onSelect: function(person_data) {
				if (current_window.checkScreenAge(person_data.Person_Age)) {
					var params = {
						action: 'add',
						DispClass_id: 15,
						Year: Year,
						EvnPLDispScreenChild_id: null,
						onHide: Ext.emptyFn,
						callback: function (data) {
							current_window.onEvnPLDispScreenChildSaved(data);
						},
						Person_id: person_data.Person_id,
						Server_id: person_data.Server_id
					};

					// проверяем возможность добавления карты
					current_window.getLoadMask(langs('Проверка возможности добавления скринингового исследования')).show();
					Ext.Ajax.request({
						params: {
							Person_id: person_data.Person_id
						},
						callback: function (options, success, response) {
							current_window.getLoadMask().hide();

							if (success) {
								var response_obj = Ext.util.JSON.decode(response.responseText);
								if (Ext.isEmpty(response_obj.Error_Msg)) {
									getWnd('swEvnPLDispScreenChildEditWindow').show(params);
								} else {
									// Вопрос
									sw.swMsg.show({
										buttons: {ok: lang['sozdat_kartu'], cancel: lang['otmena']},
										fn: function (buttonId, text, obj) {
											if (buttonId == 'ok') {
												getWnd('swEvnPLDispScreenChildEditWindow').show(params);
											}
										},
										icon: Ext.MessageBox.QUESTION,
										msg: response_obj.Error_Msg,
										title: lang['vnimanie']
									});
								}
							}
						},
						url: '/?c=EvnPLDispScreenChild&m=checkAddAvailability'
					});
				}
			},
			searchMode: 'evnpldispscreenchild',
			Year: Year
		});
	},
	deleteEvnPLDP: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispScreenChild_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispScreenChild_id');

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
							EvnPLDispScreenChild_id: evn_pl_dd_id
						},
						url: '/?c=EvnPLDispScreenChild&m=deleteEvnPLDispScreenChild'
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
		var filter_form = current_window.findById('EvnPLDispScreenChildSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').getGrid().getStore().removeAll();
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
		var filter_form = current_window.findById('EvnPLDispScreenChildSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var EvnPLDispScreenChild_grid = current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').ViewGridPanel;

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
		
		var Year = Ext.getCmp('EPLDSCSW_YearCombo').getValue();
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
				current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').showArchive = true;
			} else {
				current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').showArchive = false;
			}

			params.SearchFormType = "EvnPLDispScreenChild",
			EvnPLDispScreenChild_grid.getStore().removeAll();
			EvnPLDispScreenChild_grid.getStore().baseParams = params;
			EvnPLDispScreenChild_grid.getStore().load({
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
		var grid = this.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispScreenChildSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispScreenChildStream_begDate = this.begDate;
		params.EvnPLDispScreenChildStream_begTime = this.begTime;
		if ( !params.EvnPLDispScreenChildStream_begDate && !params.EvnPLDispScreenChildStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispScreenChildStream",
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
					current_window.findById('EPLDSCSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});*/
	},
	height: 550,
	id: 'EvnPLDispScreenChildSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDSCSW_SearchButton');
	},
	printCost: function() {
		var grid = this.EvnPLDispScreenChildSearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispScreenChild_id')) {
			getWnd('swCostPrintWindow').show({
				Evn_id: selected_record.get('EvnPLDispScreenChild_id'),
				type: 'EvnPLDispScreenChild',
				callback: function() {
					grid.getStore().reload();
				}
			});
		}
	},
	printEvnPLDispScreenChild: function(print_blank) {
		var grid = this.EvnPLDispScreenChildSearchGrid.getGrid();
		var selected_record = grid.getSelectionModel().getSelected();
		if (selected_record && selected_record.get('EvnPLDispScreenChild_id')) {
			var template = 'han_DispScreenChild_f025_07u.rptdesign';
			printBirt({
				'Report_FileName': template,
				'Report_Params': '&paramEvnPLDispScreenChild_id=' + selected_record.get('EvnPLDispScreenChild_id'),
				'Report_Format': 'pdf'
			});
		}
	},
	checkPrintCost: function() {
		// Печать справки только для закрытых случаев
		var grid = this.EvnPLDispScreenChildSearchGrid.getGrid();
		var menuPrint = this.EvnPLDispScreenChildSearchGrid.getAction('action_print').menu;
		if (menuPrint && menuPrint.printCost) {
			menuPrint.printCost.setDisabled(true);
			var selected_record = grid.getSelectionModel().getSelected();
			if (selected_record && selected_record.get('EvnPLDispScreenChild_id')) {
				menuPrint.printCost.setDisabled(selected_record.get('EvnPLDispScreenChild_IsEndStage') != lang['da']);
			}
		}
	},
	initComponent: function() {
		var win = this;
		
		win.EvnPLDispScreenChildSearchGrid = new sw.Promed.ViewFrame({
			useArchive: 1,
			actions: [
				{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispScreenChildSearchWindow').addEvnPLDP(); } },
				{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispScreenChildSearchWindow').openEvnPLDPEditWindow('edit'); } },
				{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispScreenChildSearchWindow').openEvnPLDPEditWindow('view'); } },
				{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispScreenChildSearchWindow').deleteEvnPLDP(); } },
				{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispScreenChildSearchWindow').refreshEvnPLDPList(); } },
				{ name: 'action_print', menuConfig: {
					printCost: {name: 'printCost', text: langs('Справка о стоимости лечения'), hidden: ! getRegionNick().inlist(['perm', 'kz', 'ufa']), handler: function () { win.printCost() }},
					printEvnPLDispScreenChild: {name: 'printCost', text: langs('Печать "Статистическая карта (форма 025-07/у)"'), handler: function () { win.printEvnPLDispScreenChild() }}
				}}
			],
			onRowSelect: function(sm,rowIdx,record)
			{
				var disabled = false;
				if (getGlobalOptions().archive_database_enable) {
					disabled = disabled || (record.get('archiveRecord') == 1);
				}
				if (Ext.isEmpty(record.get('EvnPLDispScreenChild_id'))) {
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_view', true);
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_edit', true);
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_delete', true);
				} else {
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_view', false);
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_edit', disabled);
					win.EvnPLDispScreenChildSearchGrid.setActionDisabled('action_delete', disabled);
				}

				var menuPrint = win.EvnPLDispScreenChildSearchGrid.getAction('action_print').menu;
				if (menuPrint && menuPrint.printEvnPLDispScreenChild) {
					if (record && record.get('EvnPLDispScreenChild_IsEndStage') == 'Да') {
						menuPrint.printEvnPLDispScreenChild.setDisabled(false);
					} else {
						menuPrint.printEvnPLDispScreenChild.setDisabled(true);
					}
				}

				win.checkPrintCost();
			},
			autoExpandColumn: 'autoexpand',
			autoLoadData: false,
			dataUrl: C_SEARCH,
			focusOn: {
				name: 'EPLDSCSW_SearchButton', type: 'field'
			},
			id: 'EPLDSCSW_EvnPLDispScreenChildSearchGrid',
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
				{ name: 'EvnPLDispScreenChild_id', type: 'int', header: 'ID', key: true },
				{ name: 'Person_id', type: 'int', hidden: true },
				{ name: 'Server_id', type: 'int', hidden: true },
				{ name: 'Person_Surname', header: lang['familiya'], width: 150 },
				{ name: 'Person_Firname', header: lang['imya'], width: 150 },
				{ name: 'Person_Secname', header: lang['otchestvo'], width: 150 },
				{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
				{ name: 'Sex_Name', header: lang['pol'], width: 80 },
				{ name: 'AgeGroupDisp_Name', header: lang['vozrastnaya_gruppa'], width: 150 },
				{ name: 'EvnPLDispScreenChild_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
				{ name: 'EvnPLDispScreenChild_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
				{ name: 'EvnPLDispScreenChild_IsEndStage', type: 'string', header: lang['zakonchen'] }
			]
		});
			
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
				autoHeight: true,
				id: 'EPLDSCSW_SearchFilterPanel',
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
								id: 'EPLDSCSW_YearCombo',
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
											var current_window = Ext.getCmp('EvnPLDispScreenChildSearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispScreenChildSearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDSCSW_EvnPLDispScreenChildSearchGrid').focus();
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDSCSW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDSCSW+56
							}]
						}/*, {
							width: 400,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDSCSW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDSCSW+57
							}]
						}*/]
					},
					getBaseSearchFiltersFrame({
						useArchive: 1,
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispScreenChildSearchFilterForm',
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
						searchFormType: 'EvnPLDispScreenChild',
						tabIndexBase: TABINDEX_EPLDSCSW,
						tabPanelId: 'EPLDSCSW_SearchFilterTabbar',
						tabGridId: 'EPLDSCSW_EvnPLDispScreenChildSearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 200,
							id: 'EPLDSCSW_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispScreenChildSearchFilterForm');
									form.getForm().findField('EvnPLDispScreenChild_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: lang['6_skriningovoe_issledovanie'],
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispScreenChild_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDSCSW + 59,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispScreenChild_setDate_Range',
										tabIndex: TABINDEX_EPLDSCSW + 60,
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
										name: 'EvnPLDispScreenChild_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDSCSW + 61,
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispScreenChild_disDate_Range',
										tabIndex: TABINDEX_EPLDSCSW + 62,
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
										],
										width: 170,
										xtype: 'daterangefield'
									}]
								}]
							}, {
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispScreenChild_IsEndStage',
								tabIndex: TABINDEX_EPLDSCSW + 63,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								comboSubject: 'AgeGroupDisp',
								fieldLabel: lang['vozrastnaya_gruppa'],
								tabIndex: TABINDEX_EPLDSCSW + 64,
								loadParams: {params: {where: "where DispType_id = 6"}},
								hiddenName: 'AgeGroupDisp_id',
								lastQuery: '',
								width: 300,
								xtype: 'swcommonsprcombo'
							}]
						}]
					})]
			}),
			win.EvnPLDispScreenChildSearchGrid
			],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDSCSW_SearchButton',
				tabIndex: TABINDEX_EPLDSCSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDSCSW+91,
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
				tabIndex: TABINDEX_EPLDSCSW+92,
				text: "Режим потокового ввода"
			},/* {
				handler: function() {
					var base_form = this.findById('EvnPLDispScreenChildSearchFilterForm').getForm();
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
				tabIndex: TABINDEX_EPLDSCSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDSCSW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					this.buttons[0].focus(true, 200);
				}.createDelegate(this)
			}
			]
		});
		sw.Promed.swEvnPLDispScreenChildSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispScreenChildSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDSCSW_SearchFilterTabbar');

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
		var years_combo = this.findById('EPLDSCSW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				params: {
					DispClass_id: 15
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
		var EvnPLDispScreenChild_grid = current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispScreenChildEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_issledovaniya_uje_otkryito']);
			return false;
		}

		var record = EvnPLDispScreenChild_grid.getSelectionModel().getSelected();
		if (!record)
		{
			return false;
		}
		
		var EvnPLDispScreenChild_id = record.data.EvnPLDispScreenChild_id;
		var person_id = record.data.Person_id;
		var server_id = record.data.Server_id;

		if (/*EvnPLDispScreenChild_id > 0 &&*/ person_id > 0 && server_id >= 0)
		{
			var params = {
				action: action,
				DispClass_id: 15,
				EvnPLDispScreenChild_id: EvnPLDispScreenChild_id,
				onHide: Ext.emptyFn,
				callback: function(data) {
					current_window.onEvnPLDispScreenChildSaved(data);
				},
				Person_id: person_id,
				Server_id: server_id
			};
			if (getGlobalOptions().archive_database_enable) {
				params.archiveRecord = record.get('archiveRecord');
			}
			var age = swGetPersonAge(new Date(record.get('Person_Birthday')), new Date());
			if (this.checkScreenAge(age)) {
				getWnd('swEvnPLDispScreenChildEditWindow').show(params);
			}
		}
	},
	plain: true,
	refreshEvnPLDPList: function(action) {
		var current_window = this;
		var EvnPLDispScreenChild_grid = current_window.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').ViewGridPanel;
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
		this.setTitle(WND_POL_EPLDSCSEARCH);
		Ext.getCmp('EvnPLDispScreenChildSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispScreenChildSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDSCSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDSCSW_YearCombo').focus(true, 100);

	},
	getFilterForm: function() {
		return this.findById('EvnPLDispScreenChildSearchFilterForm');
	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDSCSW_EvnPLDispScreenChildSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDSCSTREAM);
		this.findById('EvnPLDispScreenChildSearchFilterForm').hide();
		Ext.getCmp('EvnPLDispScreenChildSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDSCSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDSCSW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispScreenChildSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDSCSW_SearchFilterTabbar').setActiveTab(2);
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
		
		var form = this.findById('EvnPLDispScreenChildSearchFilterForm');
		var base_form = form.getForm();
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;

		this.loadYearsCombo();
		this.findById('EPLDSCSW_SearchFilterTabbar').setActiveTab(2);
	},
	title: WND_POL_EPLDSCSEARCH,
	width: 800
});

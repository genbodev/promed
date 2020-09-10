/**
* swEvnPLDispDopSearchWindow - окно поиска талона по дополнительной диспансеризации
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package		Polka
* @access		public
* @copyright		Copyright (c) 2009 Swan Ltd.
* @author		Ivan Petukhov aka Lich (megatherion@list.ru)
* @originalauthor	Stas Bykov aka Savage (savage1981@gmail.com)
* @version		24.06.2009
* @comment		Префикс для id компонентов EPLDDSW (EvnPLDispDopSearchWindow)
*				tabIndex: 2301
*
*
* Использует: окно редактирования талона по доп. диспансеризации (swEvnPLDispDopEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispDopSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispDopSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispDopSearchWindow.js',
	addEvnPLDD: function() {
		return false;

		var current_window = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispDopEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dopolnitelnoy_dispanserizatsii_uje_otkryito']);
			return false;
		}
		var Year = this.findById('EPLDDSW_YearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('EPLDDSW_SearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				current_window.refreshEvnPLDDList();
			},
			onSelect: function(person_data) {
				// сначала проверим, можем ли мы добавлять талон на этого человека
				Ext.Ajax.request({
					url: '/?c=EvnPLDispDop&m=checkIfEvnPLDispDopExists',
					callback: function(opt, success, response) {
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.isEvnPLDispDopExists == false )
							{
								getWnd('swEvnPLDispDopEditWindow').show({
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
								sw.swMsg.alert("Ошибка", "На этого человека уже был заведен талон в этом году.",
									function () {
										getWnd('swPersonSearchWindow').hide();
										current_window.addEvnPLDD();
									}
								);
								return;
							}
						}
					},
					params: { Person_id: person_data.Person_id }
				});				
			},
			searchMode: 'dd',
			Year: Year
		});
	},
	begDate: null,
	begTime: null,
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	deleteEvnPLDD: function() {
		var current_window = this;
		var grid = current_window.findById('EPLDDSW_EvnPLDispDopSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispDop_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispDop_id');

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
							EvnPLDispDop_id: evn_pl_dd_id
						},
						url: '/?c=EvnPLDispDop&m=deleteEvnPLDispDop'
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
		var filter_form = current_window.findById('EvnPLDispDopSearchFilterForm');
		filter_form.getForm().reset();
		current_window.findById('EPLDDSW_EvnPLDispDopSearchGrid').getGrid().getStore().removeAll();
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
		var filter_form = current_window.findById('EvnPLDispDopSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			thisWindow.searchInProgress = false;
			return false;
		}
		
		var evnpldispdop_grid = current_window.findById('EPLDDSW_EvnPLDispDopSearchGrid').ViewGridPanel;

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
		
		var Year = Ext.getCmp('EPLDDSW_YearCombo').getValue();
		if (Year>0)
		{
			if (filter_form.getForm().findField('EvnPLDispDop_setDate_Range').getValue1()=='')
			{
				params['EvnPLDispDop_setDate_Range'] = ('01.01.'+Year+' - '+'31.12.'+Year);
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
			params.SearchFormType = "EvnPLDispDop",
			evnpldispdop_grid.getStore().removeAll();
			evnpldispdop_grid.getStore().baseParams = params;
			evnpldispdop_grid.getStore().load({
				params: params,
				callback: function (){
					thisWindow.searchInProgress = false;
				}
			});
		}
	},
	doStreamInputSearch: function() {
		return false;

		var grid = this.findById('EPLDDSW_EvnPLDispDopSearchGrid').ViewGridPanel;
		var form = this.findById('EvnPLDispDopSearchFilterForm');
		var thisWindow = this;
		var params = {};
		params.EvnPLDispDopStream_begDate = this.begDate;
		params.EvnPLDispDopStream_begTime = this.begTime;
		if ( !params.EvnPLDispDopStream_begDate && !params.EvnPLDispDopStream_begTime )
			this.getBegDateTime();
		else
		{
			params.start = 0;
			params.limit = 100;
			params.SearchFormType = "EvnPLDispDopStream",
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
					current_window.findById('EPLDDSW_Stream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispDopSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('EPLDDSW_SearchButton');
	},
	initComponent: function() {
		Ext.apply(this, {
			items: [
			new Ext.Panel({
				height: 280,
                                autoHeight: true,
				id: 'EPLDDSW_SearchFilterPanel',
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
									key: 'EvnPLDispDop_Year',
									autoLoad: false,
									fields:
									[
										{name:'EvnPLDispDop_Year',type: 'int'},
										{name:'count', type: 'int'}
									],
									url: C_EPLDD_LOAD_YEARS
								}),
								id: 'EPLDDSW_YearCombo',
								hiddenName: 'EvnPLDispDop_Year',
								tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDispDop_Year}</td>'+
									'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
									'</div></tpl>',
								region: 'north',
								valueField: 'EvnPLDispDop_Year',
								displayField: 'EvnPLDispDop_Year',
								editable: false,
								tabIndex: 2036,
								enableKeyEvents: true,
								listeners: {
									'keydown': function(combo, e)
									{
										if ( e.getKey() == Ext.EventObject.ENTER )
										{											
											e.stopEvent();
											var current_window = Ext.getCmp('EvnPLDispDopSearchWindow');
											current_window.doSearch();
										}
										if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
										{
											e.stopEvent();
											if ( Ext.getCmp('EvnPLDispDopSearchWindow').isStream )
											{
												Ext.TaskMgr.start({
													run : function() {
														Ext.TaskMgr.stopAll();
														Ext.getCmp('EPLDDSW_EvnPLDispDopSearchGrid').focus();													
													},
													interval : 200
												});
												return true;
											}
											var panel = Ext.getCmp('EPLDDSW_SearchFilterTabbar').getActiveTab();
											var els=panel.findByType('textfield', false);
											if (els==undefined)
												els=panel.findByType('combo', false);
											var el=els[0];
											if (el!=undefined && el.focus)
												el.focus(true, 200);
										}
									}
								},
								tabIndex: TABINDEX_EPLDDSW+56
							}]
						}, {
							columnWidth: 0.50,
							border: false,
							labelWidth: 130,
							layout: 'form',
							items: [ {
								disabled: true,
								fieldLabel: lang['data_nachala_vvoda'],
								id: 'EPLDDSW_Stream_begDateTime',
								width: 165,								
								xtype: 'textfield',
								tabIndex: TABINDEX_EPLDDSW+57
							}]
						}]
				},
					getBaseSearchFiltersFrame({
						allowPersonPeriodicSelect: true,
						id: 'EvnPLDispDopSearchFilterForm',
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
						searchFormType: 'EvnPLDispDop',
						tabIndexBase: TABINDEX_EPLDDSW,
						tabPanelId: 'EPLDDSW_SearchFilterTabbar',
						tabGridId: 'EPLDDSW_EvnPLDispDopSearchGrid',
						tabs: [{
							autoHeight: true,
							bodyStyle: 'margin-top: 5px;',
							border: false,
							labelWidth: 180,
							id: 'EPLDD_FirstTab',
							layout: 'form',
							listeners: {
								'activate': function(panel) {
									var form = this.findById('EvnPLDispDopSearchFilterForm');
									form.getForm().findField('EvnPLDispDop_setDate').focus(400, true);									
								}.createDelegate(this)
							},								
							title: '<u>6</u>. Доп. дисп.',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispDop_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDDSW + 59,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispDop_setDate_Range',
										tabIndex: TABINDEX_EPLDDSW + 60,
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
										name: 'EvnPLDispDop_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDDSW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispDop_disDate_Range',
										tabIndex: TABINDEX_EPLDDSW + 62,
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
								name: 'EvnPLDispDop_VizitCount',
								tabIndex: TABINDEX_EPLDDSW + 63,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_ot'],
								name: 'EvnPLDispDop_VizitCount_From',
								tabIndex: TABINDEX_EPLDDSW + 64,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_do'],
								name: 'EvnPLDispDop_VizitCount_To',
								tabIndex: TABINDEX_EPLDDSW + 65,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispDop_IsFinish',
								tabIndex: TABINDEX_EPLDDSW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}, {
								allowBlank: true,
								enableKeyEvents: true,
								fieldLabel: lang['gruppa_zdorovya'],
								id: 'EPLDDSW_HealthKindCombo',
								listeners: {
									'render': function(combo) {
										combo.getStore().load();
									},
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('EPLDDSW_EvnPLDispDopSearchGrid').focus();													
												},
												interval : 200
											});
										}
									}
								},
								hiddenName: 'EvnPLDispDop_HealthKind_id',
								tabIndex: TABINDEX_EPLDDSW + 66,
								validateOnBlur: false,
								width: 100,
								xtype: 'swhealthkindcombo'
							}]
						}]
					})]
			}),
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { Ext.getCmp('EvnPLDispDopSearchWindow').addEvnPLDD(); }, disabled: true, hidden: true },
					{ name: 'action_edit', handler: function() { Ext.getCmp('EvnPLDispDopSearchWindow').openEvnPLDDEditWindow('edit'); } },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispDopSearchWindow').openEvnPLDDEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { Ext.getCmp('EvnPLDispDopSearchWindow').deleteEvnPLDD(); } },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispDopSearchWindow').refreshEvnPLDDList(); } },
					{ name: 'action_print'},
					{
						hidden: false,
						name:'action_printpassport',
						tooltip: lang['napechatat_pasport_zdorovya'],
						icon: 'img/icons/print16.png',
						handler: function() {
							var grid = Ext.getCmp('EPLDDSW_EvnPLDispDopSearchGrid').getGrid();
							var record = grid.getSelectionModel().getSelected();							
							if (!record) {
								Ext.Msg.alert(lang['oshibka'], lang['ne_vyibran_ni_odin_talon']);
								return false;
							}
							var evn_pl_id = record.get('EvnPLDispDop_id');
							var server_id = record.get('Server_id');							
							if (!evn_pl_id)
								return false;
							var id_salt = Math.random();
							var win_id = 'print_passport' + Math.floor(id_salt * 10000);
							var win = window.open('/?c=EvnPLDispDop&m=printEvnPLDispDopPassport&EvnPLDispDop_id=' + evn_pl_id + '&Server_id=' + server_id, win_id);
						}, 
						text: lang['pechat_pasporta_zdorovya']
					}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'EPLDDSW_SearchButton', type: 'field'
				},
				id: 'EPLDDSW_EvnPLDispDopSearchGrid',
				layout: 'fit',
				object: 'EvnPLDD',
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
					{ name: 'EvnPLDispDop_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'Person_Surname', type: 'string', header: lang['familiya'], width: 150 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
					{ name: 'EvnPLDispDop_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
					{ name: 'EvnPLDispDop_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
					{ name: 'EvnPLDispDop_VizitCount', type: 'int', header: lang['posescheniy'] },
					{ name: 'EvnPLDispDop_IsFinish', type: 'string', header: lang['zakonch'], width:50 }
				]
			})],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'EPLDDSW_SearchButton',
				tabIndex: TABINDEX_EPLDDSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDDSW+91,
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
				tabIndex: TABINDEX_EPLDDSW+92,
				text: "Режим потокового ввода"
			}, {
				handler: function() {
					var base_form = this.findById('EvnPLDispDopSearchFilterForm').getForm();
					base_form.submit();					
				}.createDelegate(this),
				iconCls: 'print16',
				tabIndex: TABINDEX_EPLSW + 111,
				text: lang['pechat_spiska']
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
				tabIndex: TABINDEX_EPLDDSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('EPLDDSW_YearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('mode_button').focus(true, 200);
				}
			}
			]
		});
		sw.Promed.swEvnPLDispDopSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	isStream: null, // потоковый ввод или поиск?
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPLDispDopSearchWindow');
			var search_filter_tabbar = current_window.findById('EPLDDSW_SearchFilterTabbar');

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
			var current_window = Ext.getCmp('EvnPLDispDopSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					current_window.addEvnPLDD();
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
		var years_combo = this.findById('EPLDDSW_YearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				url: C_EPLDD_LOAD_YEARS,
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
	openEvnPLDDEditWindow: function(action) {
		var current_window = this;
		var evnpldispdop_grid = current_window.findById('EPLDDSW_EvnPLDispDopSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispDopEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dop_dispanserizatsii_uje_otkryito']);
			return false;
		}

		if (!evnpldispdop_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var evnpldispdop_id = evnpldispdop_grid.getSelectionModel().getSelected().data.EvnPLDispDop_id;
		var person_id = evnpldispdop_grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = evnpldispdop_grid.getSelectionModel().getSelected().data.Server_id;

		if (evnpldispdop_id > 0 && person_id > 0 && server_id >= 0)
		{
			getWnd('swEvnPLDispDopEditWindow').show({
				action: action,
				EvnPLDispDop_id: evnpldispdop_id,
				onHide: Ext.emptyFn,
				callback: function() {
					current_window.refreshEvnPLDDList();
				},
				Person_id: person_id,
				Server_id: server_id
			});
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		var current_window = this;
		var evnpldispdop_grid = current_window.findById('EPLDDSW_EvnPLDispDopSearchGrid').ViewGridPanel;
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
		this.setTitle(WND_POL_EPLDDSEARCH);
		Ext.getCmp('EvnPLDispDopSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispDopSearchFilterForm').show();	
		this.doLayout();		
		this.isStream = false;
		if ( this.findById('EPLDDSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDDSW_YearCombo').focus(true, 100);

	},
	setStreamInputMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].disable();
		this.buttons[1].disable();		
		button.setText('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Режим поиска&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
		var grid = this.findById('EPLDDSW_EvnPLDispDopSearchGrid').ViewGridPanel;
		grid.getStore().removeAll();
		this.setTitle(WND_POL_EPLDDSTREAM);
		this.findById('EvnPLDispDopSearchFilterForm').hide();
		Ext.getCmp('EvnPLDispDopSearchFilterForm').setHeight(25);
		this.doLayout();
		this.isStream = true;
		this.doStreamInputSearch();
		if ( this.findById('EPLDDSW_YearCombo').getStore().getCount() > 0 )
			this.findById('EPLDDSW_YearCombo').focus(true, 100);
	},
	show: function() {
		sw.Promed.swEvnPLDispDopSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			if ( arguments[0].mode == 'stream' )
			{
				this.setSearchMode();
				this.findById('EPLDDSW_SearchFilterTabbar').setActiveTab('EPLDD_FirstTab');
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
		//this.findById('EPLDDSW_EvnPLDispDopSearchGrid').setActionDisabled('action_add', this.viewOnly);
		this.findById('EPLDDSW_EvnPLDispDopSearchGrid').setActionDisabled('action_edit', this.viewOnly);
		this.findById('EPLDDSW_EvnPLDispDopSearchGrid').setActionDisabled('action_delete', this.viewOnly);
		if(this.viewOnly == true)
			this.buttons[2].hide();
		else
			this.buttons[2].show();
		var form = this.findById('EvnPLDispDopSearchFilterForm');
		var base_form = form.getForm();
		
		base_form.getEl().dom.action = "/?c=Search&m=printSearchResults";
		base_form.getEl().dom.method = "post";
		base_form.getEl().dom.target = "_blank";
		base_form.standardSubmit = true;
		
		Ext.getCmp('EPLDDSW_HealthKindCombo').getStore().clearFilter();
		
		this.loadYearsCombo();
		this.findById('EPLDDSW_SearchFilterTabbar').setActiveTab('EPLDD_FirstTab');
	},
	title: WND_POL_EPLDDSEARCH,
	width: 800
});

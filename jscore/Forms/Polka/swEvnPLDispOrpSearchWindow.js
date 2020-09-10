/**
* swEvnPLDispOrpSearchWindow - окно поиска талона по диспасеризации детей-сирот
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    Polka
* @access     public
* @copyright  Copyright (c) 2009 Swan Ltd.
* @author     Марков 
* @version    май 2010
* @comment    Префикс для id компонентов epldosw (EvnPLDispOrpSearchWindow)
*             tabIndex: TABINDEX_EPLDOSW = 9200;
*
*
* Использует: окно редактирования талона по диспасеризации детей-сирот (swEvnPLDispOrpEditWindow)
*             окно поиска человека (swPersonSearchWindow)
*/
/*NO PARSE JSON*/
sw.Promed.swEvnPLDispOrpSearchWindow = Ext.extend(sw.Promed.BaseForm, 
{
	/* */
	codeRefresh: true,
	objectName: 'swEvnPLDispOrpSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnPLDispOrpSearchWindow.js',
	addEvnPLDD: function() 
	{
		var frm = this;

		if (getWnd('swPersonSearchWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_poiska_cheloveka_uje_otkryito']);
			return false;
		}

		if (getWnd('swEvnPLDispOrpEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dispaserizatsii_detey-sirot_uje_otkryito']);
			return false;
		}
		var Year = this.findById('epldoswYearCombo').getValue();
		
		getWnd('swPersonSearchWindow').show({
			onClose: function() {
				// Установить фокус на первое поле текущей вкладки
				var panel = Ext.getCmp('epldoswSearchFilterTabbar').getActiveTab();
				var els=panel.findByType('textfield', false);
				if (els==undefined)
					els=panel.findByType('combo', false);
				var el=els[0];
				if (el!=undefined && el.focus)
					el.focus(true, 200);
				frm.refreshEvnPLDDList();
			},
			onSelect: function(person_data) {
				// сначала проверим, можем ли мы добавлять талон на этого человека
				Ext.Ajax.request({
					url: '/?c=EvnPLDispOrp&m=checkIfEvnPLDispOrpExists',
					callback: function(opt, success, response) {
						if (success && response.responseText != '')
						{
							var response_obj = Ext.util.JSON.decode(response.responseText);

							if ( response_obj.isEvnPLDispOrpExists == false )
							{
								getWnd('swEvnPLDispOrpEditWindow').show({
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
										frm.addEvnPLDD();
									}
								);
								return;
							}
						}
					},
					params: { Person_id: person_data.Person_id }
				});				
			},
			searchMode: 'ddorp',
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
		var frm = this;
		var grid = frm.findById('epldoswEvnPLDispOrpSearchGrid').getGrid();

		if ( !grid || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('EvnPLDispOrp_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();
		var evn_pl_dd_id = record.get('EvnPLDispOrp_id');

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
							EvnPLDispOrp_id: evn_pl_dd_id
						},
						url: '/?c=EvnPLDispOrp&m=deleteEvnPLDispOrp'
					});
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_talon_dd'],
			title: lang['vopros']
		});
	},
	doReset: function() {
		var frm = this;
		var filter_form = frm.findById('EvnPLDispOrpSearchFilterForm');
		filter_form.getForm().reset();
		frm.findById('epldoswEvnPLDispOrpSearchGrid').getGrid().getStore().removeAll();
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
	
		var frm = this;
		var filter_form = frm.findById('EvnPLDispOrpSearchFilterForm');
		
		if ( filter_form.isEmpty() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolneno_ni_odno_pole'], function() {
			});
			return false;
		}
		
		var evnpldispdop_grid = frm.findById('epldoswEvnPLDispOrpSearchGrid').ViewGridPanel;

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
		
		// фильтрация по году 
		var Year = Ext.getCmp('epldoswYearCombo').getValue();
		if (Year>0)
		{
			if (filter_form.getForm().findField('EvnPLDispOrp_setDate_Range').getValue1()=='')
			{
				params['EvnPLDispOrp_setDate_Range'] = ('01.01.'+Year+' - '+'31.12.'+Year);
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
			params.SearchFormType = "EvnPLDispOrpOld",
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
	draggable: true,
	getBegDateTime: function() {
		var frm = this;
		Ext.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				if (success && response.responseText != '')
				{
					frm.searchInProgress = false;
					var response_obj = Ext.util.JSON.decode(response.responseText);

					frm.begDate = response_obj.begDate;
					frm.begTime = response_obj.begTime;
					frm.findById('epldoswStream_begDateTime').setValue(response_obj.begDate + ' ' + response_obj.begTime);
				}
			}
		});
	},
	height: 550,
	id: 'EvnPLDispOrpSearchWindow',
	getButtonSearch: function() {
		// TODO: правильно юзать scope кнопки
		return Ext.getCmp('epldoswSearchButton');
	},
	initComponent: function() 
	{
		this.EditForm = new Ext.Panel(
		{
			autoHeight: true,
			id: 'epldoswSearchFilterPanel',
			region: 'north',
			layout: 'form',
			border: false,
			labelWidth: 120,
			items: 
			[{
				bodyStyle:'padding:3px; padding-left:5px;',
				layout: 'form',
				xtype: 'panel',
				border: false,
				//frame: true,
				labelWidth: 125,
				items:
				[{
					xtype: 'swbaselocalcombo',
					mode: 'local',
					fieldLabel: lang['god'],
					store: new Ext.data.JsonStore(
					{
						key: 'EvnPLDispOrp_Year',
						autoLoad: false,
						fields:
						[
							{name:'EvnPLDispOrp_Year',type: 'int'},
							{name:'count', type: 'int'}
						],
						url: C_EPLDO_LOAD_YEARS
					}),
					id: 'epldoswYearCombo',
					hiddenName: 'EvnPLDispOrp_Year',
					tpl:'<tpl for="."><div class="x-combo-list-item"><table border="0" width="100%"><tr><td style="width: 40px; color: red">{EvnPLDispOrp_Year}</td>'+
							'<td style="text-align: right"><b>{count}<b></td></tr></table>'+
							'</div></tpl>',
					region: 'north',
					valueField: 'EvnPLDispOrp_Year',
					displayField: 'EvnPLDispOrp_Year',
					editable: false,
					tabIndex: TABINDEX_EPLDOSW+95,
					enableKeyEvents: true,
					listeners: 
					{
						'keydown': function(combo, e)
						{
							if ( e.getKey() == Ext.EventObject.ENTER )
							{
								e.stopEvent();
								var frm = Ext.getCmp('EvnPLDispOrpSearchWindow');
								frm.doSearch();
							}
							if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								var panel = Ext.getCmp('epldoswSearchFilterTabbar').getActiveTab();
								var els=panel.findByType('textfield', false);
								if (els==undefined)
									els=panel.findByType('combo', false);
								var el=els[0];
								if (el!=undefined && el.focus)
									el.focus(true, 200);
							}
						}
					}
				},
				{
					disabled: true,
					fieldLabel: lang['data_nachala_vvoda'],
					id: 'epldoswStream_begDateTime',
					width: 165,
					xtype: 'textfield',
					tabIndex: TABINDEX_EPLDOSW+57
				}]
			},
			getBaseSearchFiltersFrame(
			{
				id: 'EvnPLDispOrpSearchFilterForm',
				ownerWindow: this,
				region: 'north',
				searchFormType: 'EvnPLDispOrpOld',
				tabIndexBase: TABINDEX_EPLDOSW,
				tabPanelId: 'epldoswSearchFilterTabbar',
				tabGridId: 'epldoswEvnPLDispOrpSearchGrid',
				tabs: 
				[{
					autoHeight: true,
					bodyStyle: 'margin-top: 5px;',
					border: false,
					labelWidth: 180,
					id: 'EPLDO_FirstTab',
					layout: 'form',
					listeners: 
					{
						'activate': function(panel) 
						{
							var form = this.findById('EvnPLDispOrpSearchFilterForm');
							form.getForm().findField('EvnPLDispOrp_setDate').focus(400, true);									
						}.createDelegate(this)
					},
							title: '<u>6</u>. Дисп. детей-сирот',
							items: [{
								border: false,
								layout: 'column',
								items: [{
									border: false,
									layout: 'form',									
									items: [{
										fieldLabel: lang['data_nachala'],
										name: 'EvnPLDispOrp_setDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDOSW + 59,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_nachala'],
										name: 'EvnPLDispOrp_setDate_Range',
										tabIndex: TABINDEX_EPLDOSW + 60,
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
										name: 'EvnPLDispOrp_disDate',
										plugins: [
											new Ext.ux.InputTextMask('99.99.9999', false)
										],
										tabIndex: TABINDEX_EPLDOSW + 61,										
										width: 100,
										xtype: 'swdatefield'
									}]
								}, {
									border: false,
									layout: 'form',
									labelWidth: 225,									
									items: [{
										fieldLabel: lang['diapazon_dat_okonchaniya'],
										name: 'EvnPLDispOrp_disDate_Range',
										tabIndex: TABINDEX_EPLDOSW + 62,
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
								name: 'EvnPLDispOrp_VizitCount',
								tabIndex: TABINDEX_EPLDOSW + 63,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_ot'],
								name: 'EvnPLDispOrp_VizitCount_From',
								tabIndex: TABINDEX_EPLDOSW + 64,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								fieldLabel: lang['kolichestvo_posescheniy_do'],
								name: 'EvnPLDispOrp_VizitCount_To',
								tabIndex: TABINDEX_EPLDOSW + 65,
								width: 100,
								xtype: 'numberfield'
							}, {
								allowDecimal: false,
								allowNegative: false,
								enableKeyEvents: true,
								fieldLabel: lang['sluchay_zakonchen'],
								hiddenName: 'EvnPLDispOrp_IsFinish',
								listeners: {
									'keydown': function(combo, e) {
										if ( !e.shiftKey && e.getKey() == e.TAB )
										{
											Ext.TaskMgr.start({
												run : function() {
													Ext.TaskMgr.stopAll();
													Ext.getCmp('epldoswEvnPLDispOrpSearchGrid').focus();													
												},
												interval : 200
											});
										}
									}
								},
								tabIndex: TABINDEX_EPLDOSW + 66,
								width: 100,
								xtype: 'swyesnocombo'
							}]
						}]
					})]
		});
	
		Ext.apply(this, 
		{
			items: 
			[
				this.EditForm,
			new sw.Promed.ViewFrame({
				actions: [
					{ name: 'action_add', handler: function() { /*Ext.getCmp('EvnPLDispOrpSearchWindow').addEvnPLDD();*/ }, disabled: true, hiden: true },
					{ name: 'action_edit', handler: function() { /*Ext.getCmp('EvnPLDispOrpSearchWindow').openEvnPLDDEditWindow('edit');*/ }, disabled: true, hiden: true },
					{ name: 'action_view', handler: function() { Ext.getCmp('EvnPLDispOrpSearchWindow').openEvnPLDDEditWindow('view'); } },
					{ name: 'action_delete', handler: function() { /*Ext.getCmp('EvnPLDispOrpSearchWindow').deleteEvnPLDD();*/ }, disabled: true, hiden: true },
					{ name: 'action_refresh', handler: function() { Ext.getCmp('EvnPLDispOrpSearchWindow').refreshEvnPLDDList(); } },
					{ name: 'action_print'}
				],
				autoExpandColumn: 'autoexpand',
				autoLoadData: false,
				dataUrl: C_SEARCH,
				focusOn: {
					name: 'epldoswSearchButton', type: 'field'
				},
				id: 'epldoswEvnPLDispOrpSearchGrid',
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
					{ name: 'EvnPLDispOrp_id', type: 'int', header: 'ID', key: true },
					{ name: 'Person_id', type: 'int', hidden: true },
					{ name: 'Server_id', type: 'int', hidden: true },
					{ name: 'Person_Surname', id: 'autoexpand', type: 'string', header: lang['familiya'], width: 150 },
					{ name: 'Person_Firname', type: 'string', header: lang['imya'], width: 150 },
					{ name: 'Person_Secname', type: 'string', header: lang['otchestvo'], width: 150 },
					{ name: 'Person_Birthday', type: 'date', format: 'd.m.Y', header: lang['d_r'] },
					{ name: 'EvnPLDispOrp_setDate', type: 'date', format: 'd.m.Y', header: lang['data_nachala'] },
					{ name: 'EvnPLDispOrp_disDate', type: 'date', format: 'd.m.Y', header: lang['data_okonchaniya'] },
					{ name: 'EvnPLDispOrp_VizitCount', type: 'int', header: lang['posescheniy'] },
					{ name: 'EvnPLDispOrp_IsFinish', type: 'string', header: lang['zakonch'], width:50 }
				]
			})],
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),				
				iconCls: 'search16',
				id: 'epldoswSearchButton',
				tabIndex: TABINDEX_EPLDOSW+90,
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				tabIndex: TABINDEX_EPLDOSW+91,
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
				tabIndex: TABINDEX_EPLDOSW+94,
				text: BTN_FRMCANCEL,
				onTabAction: function() {
					Ext.getCmp('epldoswYearCombo').focus(true, 200);
				},
				onShiftTabAction: function() {
					Ext.getCmp('mode_button').focus(true, 200);
				}
			}
			]
		});
		sw.Promed.swEvnPLDispOrpSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var frm = Ext.getCmp('EvnPLDispOrpSearchWindow');
			var search_filter_tabbar = frm.findById('epldoswSearchFilterTabbar');

			switch (e.getKey())
			{
				case Ext.EventObject.C:
					frm.doReset();
					break;					
				case Ext.EventObject.J:
					frm.hide();
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
			var frm = Ext.getCmp('EvnPLDispOrpSearchWindow');
			switch (e.getKey())
			{
				case Ext.EventObject.INSERT:
					frm.addEvnPLDD();
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
		var years_combo = this.findById('epldoswYearCombo');
		if ( years_combo.getStore().getCount() == 0 ) {
			years_combo.getStore().load({
				url: C_EPLDO_LOAD_YEARS,
				callback: function() {
					years_combo.setValue(2012);
					years_combo.focus(true, 500);
				}
			});
		}
		else
		{
			var date = new Date();
			years_combo.setValue(2012);
			years_combo.focus(true, 500);
		}
	},
	maximizable: true,
	minHeight: 550,
	minWidth: 800,
	modal: false,
	openEvnPLDDEditWindow: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('epldoswEvnPLDispOrpSearchGrid').ViewGridPanel;

		if (getWnd('swEvnPLDispOrpEditWindow').isVisible())
		{
			Ext.Msg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_talona_po_dispaserizatsii_detey-sirot_uje_otkryito']);
			return false;
		}

		if (!evnpldispdop_grid.getSelectionModel().getSelected())
		{
			return false;
		}

		var evnpldispdop_id = evnpldispdop_grid.getSelectionModel().getSelected().data.EvnPLDispOrp_id;
		var person_id = evnpldispdop_grid.getSelectionModel().getSelected().data.Person_id;
		var server_id = evnpldispdop_grid.getSelectionModel().getSelected().data.Server_id;

		if (evnpldispdop_id > 0 && person_id > 0 && server_id >= 0)
		{
			getWnd('swEvnPLDispOrpEditWindow').show({
				action: action,
				EvnPLDispOrp_id: evnpldispdop_id,
				onHide: Ext.emptyFn,
				callback: function() {
					frm.refreshEvnPLDDList();
				},
				Person_id: person_id,
				Server_id: server_id
			});
		}
	},
	plain: true,
	refreshEvnPLDDList: function(action) {
		var frm = this;
		var evnpldispdop_grid = frm.findById('epldoswEvnPLDispOrpSearchGrid').ViewGridPanel;
		this.doSearch();
	},
	resizable: true,
	setSearchMode: function()
	{
		var button = this.buttons[2];
		this.buttons[0].enable();
		this.buttons[1].enable();
		button.setText(lang['rejim_potokovogo_vvoda']);
		this.setTitle(WND_POL_EPLDOSEARCH);
		Ext.getCmp('EvnPLDispOrpSearchFilterForm').setHeight(280);
		this.findById('EvnPLDispOrpSearchFilterForm').show();	
		this.doLayout();		
		if ( this.findById('epldoswYearCombo').getStore().getCount() > 0 )
			this.findById('epldoswYearCombo').focus(true, 100);

	},
	show: function() {
		sw.Promed.swEvnPLDispOrpSearchWindow.superclass.show.apply(this, arguments);
		if ( arguments[0] != undefined && arguments[0].mode != undefined )
		{
			this.setSearchMode();
		}
		else
			this.setSearchMode();
		this.getBegDateTime();

		this.restore();
		this.center();
		this.maximize();
		this.doReset();

		this.loadYearsCombo();
		var form = this.findById('EvnPLDispOrpSearchFilterForm');
		this.findById('epldoswSearchFilterTabbar').setActiveTab('EPLDO_FirstTab');
	},
	title: WND_POL_EPLDOSEARCH,
	width: 800
});

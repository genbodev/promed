/**
* swDrugOstatBySkladViewWindow - окно просмотра и редактирования остатков по аптечному складу.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      12.10.2009
*/

sw.Promed.swDrugOstatBySkladViewWindow = Ext.extend(sw.Promed.BaseForm, {
	layout: 'border',
	maximized: true,
	modal: true,
	resizable: false,
	draggable: false,
	closeAction :'hide',
	id: 'DrugOstatBySkladViewWindow',
	plain: true,
	title: WND_DLO_MEDSKLAD,
	refreshDrugOstatUpdateTime: function() {
		/*var current_window = this;
		var loadMask = new Ext.LoadMask(Ext.get('DrugOstatBySkladViewWindow'), { msg: "Получение последней даты обновления остатков..." });
		loadMask.show();
		Ext.Ajax.request({
			url: C_DRUG_RAS_UPD_TIME,
			callback: function(opt, success, resp) {
				loadMask.hide();
				Ext.getCmp('DOBSVW_MnnFilter').focus();
				if (resp.responseText != '')
				{
					var response_data = Ext.util.JSON.decode(resp.responseText);
					if (response_data && response_data[0]['DrugOstatUpdateTime'])
						current_window.setTitle(WND_DLO_MEDSKLAD + ' (Обновлено: ' + response_data[0]['DrugOstatUpdateTime'] + ')');
				}
			}
		});*/
	},
	show: function() {
		sw.Promed.swDrugOstatBySkladViewWindow.superclass.show.apply(this, arguments);
		// проверка даты остатков
		this.refreshDrugOstatUpdateTime();
		Ext.getCmp('DOBSVW_ResetFilterButton').handler();
		Ext.getCmp('DOBSVW_MnnFilter').focus();
	},
	initComponent: function() {
		Ext.apply(this, {
			items: [
				new Ext.grid.GridPanel({
					region: 'center',
					id: 'DrugOstatBySkladGridDetail',
					autoExpandColumn: 'autoexpand',
					autoExpandMax: 2000,
					loadMask: true,
					title: lang['medikament'],
					stripeRows: true,
					tbar: new sw.Promed.Toolbar({
						autoHeight: true,
						items: [{
							xtype: 'label',
							text: lang['mnn'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
							xtype: 'textfield',
							id: 'DOBSVW_MnnFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').handler();
									}
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_TorgFilter').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										Ext.getCmp('DOBSVW_OrgFarmFilter').focus();
									}
								}
							}
						},{
							xtype: 'label',
							text: lang['torg_naim'],
							style: 'margin-left: 5px; font-weight: bold'
						},{
							xtype: 'textfield',
							id: 'DOBSVW_TorgFilter',
							style: 'margin-left: 5px',
							width: 120,
							enableKeyEvents: true,
							listeners: {
								'keydown': function (inp, e) {
									if (e.getKey() == Ext.EventObject.ENTER)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').handler();
									}
									if (e.shiftKey == false && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										inp.ownerCt.items.item('DOBSVW_FindAction').focus();
									}
									if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
									{
										e.stopEvent();
										Ext.getCmp('DOBSVW_MnnFilter').focus();
									}
								}
							}
						}, {
							xtype: 'button',
							style: 'margin-left: 5px',
							text: BTN_FRMFILTER,
							iconCls: 'search16',
							id: 'DOBSVW_FindAction',
							handler: function() {
								Ext.getCmp('DrugOstatBySkladViewWindow').loadDrugOstatGrid();
							},
							onTabElement: 'DOBSVW_ResetFilterButton',
							onShiftTabElement: 'DOBSVW_TorgFilter'
						}, {
							xtype: 'button',
							iconCls: 'resetsearch16',
							id: 'DOBSVW_ResetFilterButton',
							style: 'margin-left: 5px',
							text: BTN_FRMRESET,
							handler: function() {
								this.ownerCt.items.item('DOBSVW_MnnFilter').setValue('');
								this.ownerCt.items.item('DOBSVW_TorgFilter').setValue('');
								this.ownerCt.items.item('DOBSVW_FindAction').handler();
							},
							onTabAction: function(field){
								var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
								if ( grid.getStore().getCount() > 0 )
								{
									grid.getSelectionModel().selectFirstRow();
									grid.getView().focusRow(0);
								}
								else
								{
									Ext.getCmp('DOBSVW_CloseButton').focus();
								}
							},
							onShiftTabElement: 'DOBSVW_FindAction'
						}, {
							xtype: 'tbfill'
						}, {
							id: 'DOBSVW_DrugGridCounter',
							text: '0 / 0',
							xtype: 'label'
						}]
					}),
					enableKeyEvents: true,
					keys: [{
						key: [
							Ext.EventObject.ENTER,
//	                        Ext.EventObject.DELETE,
							Ext.EventObject.F3,
							Ext.EventObject.F4,
							Ext.EventObject.F5,
							Ext.EventObject.F9,
							Ext.EventObject.INSERT,
							Ext.EventObject.TAB,
							Ext.EventObject.PAGE_UP,
							Ext.EventObject.PAGE_DOWN,
							Ext.EventObject.HOME,
							Ext.EventObject.END
						],
						fn: function(inp, e) {
							
							// События для грида работают, только когда мы стоим на гриде
							// Странный код, означает что если мы стоим не на элементе TD или А,
							// что является признаком того, что мы стоим на гриде,
							// то переходим к стандартным обработчикам, 
							if ( e.target.nodeName != 'TD' && e.target.nodeName != 'A' ) {
								return true;
							}
							
							e.stopEvent();

							if ( e.browserEvent.stopPropagation )
								e.browserEvent.stopPropagation();
							else
								e.browserEvent.cancelBubble = true;

							if ( e.browserEvent.preventDefault )
								e.browserEvent.preventDefault();
							else
								e.browserEvent.returnValue = false;

							e.browserEvent.returnValue = false;
							e.returnValue = false;

							if (Ext.isIE)
							{
								e.browserEvent.keyCode = 0;
								e.browserEvent.which = 0;
							}

							var current_window = Ext.getCmp('DrugOstatBySkladViewWindow');
							var drug_grid = current_window.findById('DrugOstatBySkladGridDetail');
							switch (e.getKey())
							{
								case Ext.EventObject.END:
									GridEnd(drug_grid);
								break;

								case Ext.EventObject.ENTER:
								case Ext.EventObject.F4:
								break;
								case Ext.EventObject.F3:
								break;
								case Ext.EventObject.F5:

								break;
								
								case Ext.EventObject.F9:
									Ext.getCmp('DOBSVW_OstatPrintButton').handler();
								break;

								case Ext.EventObject.HOME:
									GridHome(drug_grid);
								break;

								case Ext.EventObject.INSERT:
								break;

								case Ext.EventObject.DELETE:
								break;

								case Ext.EventObject.PAGE_DOWN:
									GridPageDown(drug_grid, 'Drug_id');
								break;

								case Ext.EventObject.PAGE_UP:
									GridPageUp(drug_grid, 'Drug_id');
								break;

								case Ext.EventObject.TAB:
									Ext.getCmp('DOBSVW_CloseButton').focus();
								break;
							}
						},
						stopEvent: false
					}],
					store: new Ext.data.JsonStore({
						autoLoad: false,
						url: C_DRUG_OSTAT_FARM_LIST,
						fields: [
							'DrugOstat_id',
							'OrgFarmacy_id',
							'DrugMnn_Name',
							'Drug_id',
							'Drug_Name',
							'Drug_CodeG',
							{name: 'setDate', type: 'date', dateFormat:'d.m.Y'},
							{name: 'godnDate', type: 'date', dateFormat:'d.m.Y'},
							'DrugOstat_Fed',
							'DrugOstat_Reg',
							'DrugOstat_7Noz'
						],
						listeners: {
							'load': function(store) {
								var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
								Ext.getCmp('DOBSVW_DrugGridCounter').setText('0 / ' + store.getCount());
							}
						}
					}),
					columns: [
						{dataIndex: 'DrugOstat_id', hidden: true, hideable: false},
						{dataIndex: 'OrgFarmacy_id', hidden: true, hideable: false},
						{dataIndex: 'Drug_id', hidden: true, hideable: false},
						{header: lang['mnn'], dataIndex: 'DrugMnn_Name', sortable: true, width: 450},
						{header: lang['kod_ges'], dataIndex: 'Drug_CodeG', sortable: true, width: 120},
						{id: 'autoexpand', header: lang['torgovoe_naimenovanie'], dataIndex: 'Drug_Name', sortable: true},
						{header: lang['ostatki_fed'], dataIndex: 'DrugOstat_Fed', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80},
						{header: lang['ostatki_reg'], dataIndex: 'DrugOstat_Reg', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80},
						{header: lang['ostatki_7_noz'], dataIndex: 'DrugOstat_7Noz', /*renderer: sw.Promed.Format.checkColumnForRas,*/ sortable: true, width: 80}
					],
					sm: new Ext.grid.RowSelectionModel({
						singleSelect: true,
						listeners: {
							'rowselect': function(sm, rowIdx, r) {
								Ext.getCmp('DOBSVW_DrugGridCounter').setText((rowIdx + 1) + ' / ' + this.grid.getStore().getCount());
							}
						}
					})
				})
			],
			enableKeyEvents: true,
			buttonAlign: 'left',
			buttons: [
			{
		        iconCls: 'print16',
				id: 'DOBSVW_PrintButton',
                text: BTN_FRMPRINTALL,
                handler: function() {
					var drug_grid = Ext.getCmp('DrugOstatBySkladGridDetail');
					var date = Ext.util.Format.date(new Date(), 'd.m.Y');
					Ext.ux.GridPrinter.print(drug_grid, {tableHeaderText: lang['ostatki_medikamentov_na_regionalnom_aptechnom_sklade_na'] + date});
				},
				onShiftTabAction: function(field) {
                    var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
					else
					{
                       	Ext.getCmp('DOBSVW_CloseButton').focus();
					}
				},
				onTabElement: 'DOBSVW_CloseButton'
			},{
		        iconCls: 'print16',
				id: 'DOBSVW_PrintButton',
                text: BTN_FRMPRINTCUR,
                handler: function() {
					var drug_grid = Ext.getCmp('DrugOstatBySkladGridDetail'),
                        rec = drug_grid.getSelectionModel().getSelected();

                    if (!rec) return true;

					var date = Ext.util.Format.date(new Date(), 'd.m.Y');
					Ext.ux.GridPrinter.print(drug_grid, {rowId: rec.id,tableHeaderText: lang['ostatki_medikamentov_na_regionalnom_aptechnom_sklade_na'] + date});
				},
				onShiftTabAction: function(field) {
                    var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
					else
					{
                       	Ext.getCmp('DOBSVW_CloseButton').focus();
					}
				},
				onTabElement: 'DOBSVW_CloseButton'
			}, '-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event) {
					ShowHelp(WND_DLO_MEDSKLAD);
				}.createDelegate(self)
			},
			{
				iconCls: 'close16',
				id: 'DOBSVW_CloseButton',
				text: BTN_FRMCLOSE,
				handler: function() { this.ownerCt.hide() },
				onShiftTabAction: function(field) {
					var grid = Ext.getCmp('DrugOstatBySkladGridDetail');
					if ( grid.getStore().getCount() > 0 )
					{
						grid.getSelectionModel().selectFirstRow();
						grid.getView().focusRow(0);
					}
					else
					{
						Ext.getCmp('DOBSVW_ResetMNNFilterButton').focus();
					}
				},
				onTabElement: 'DOBSVW_OrgFarmFilter'
			}
			],
			keys: [{
		    	alt: true,
		        fn: function(inp, e) {
					switch ( e.getKey() )
					{
						case Ext.EventObject.P:
							Ext.getCmp('DrugOstatBySkladViewWindow').hide();
						break;
						case Ext.EventObject.G:
							Ext.getCmp('DOBSVW_PrintButton').handler();
						break;
					}
		        },
		        key: [ Ext.EventObject.P, Ext.EventObject.G ],
		        stopEvent: true
		    }]			
		});
		sw.Promed.swDrugOstatBySkladViewWindow.superclass.initComponent.apply(this, arguments);
	},
	loadDrugOstatGrid: function() {
		var detail_grid = Ext.getCmp('DrugOstatBySkladGridDetail');
		var mnn_filter = Ext.getCmp('DOBSVW_MnnFilter').getValue();
		var torg_filter = Ext.getCmp('DOBSVW_TorgFilter').getValue();
		detail_grid.getStore().removeAll();
		detail_grid.getStore().load({
			params: {
				OrgFarmacy_id: 1,
				mnn: mnn_filter,
				torg: torg_filter
			},
			callback: function()
			{
				Ext.getCmp('DOBSVW_DrugGridCounter').setText('0 / ' + detail_grid.getStore().getCount());
			}
		});
	}
});
/**
* swDrugTorgViewWindow - окно поиска и просмотра торговых наименований медикаментов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      06.10.2009
*/

sw.Promed.swDrugTorgViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function() {
		this.findById('DTVW_DrugTorgFilterForm').getForm().reset();
		this.findById('DTVW_DrugTorgGrid').getStore().removeAll();
		this.findById('DTVW_DrugTorgGrid').getTopToolbar().items.items[0].disable();
		this.findById('DTVW_DrugTorgGrid').getTopToolbar().items.items[2].el.innerHTML = '0 / 0';
		this.findById('DTVW_DrugTorg_Name').focus(true, 250);
	},
	doSearch: function() {
		var form = this.findById('DTVW_DrugTorgFilterForm');
		var grid = this.findById('DTVW_DrugTorgGrid');
		var Mask = new Ext.LoadMask(Ext.get('DrugTorgViewWindow'), { msg: SEARCH_WAIT });

		var params = form.getForm().getValues();

		if ( !params.DrugTorg_Name ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DTVW_DrugTorg_Name').focus(true, 250); });
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();

		grid.getStore().baseParams = form.getForm().getValues();

		params.limit = 100;
		params.start = 0;

		grid.getStore().load({
			callback: function() {
				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				Mask.hide();
			},
			params: params
		});
	},
	draggable: true,
	height: 500,
	id: 'DrugTorgViewWindow',
	layout: 'border',
	maximizable: true,
	minHeight: 500,
	minWidth: 800,
	modal: false,
	openDrugTorgEditWindow: function() {
		var current_window = this;

		if ( getWnd('swDrugTorgEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_latinskogo_naimenovaniya_medikamenta_uje_otkryito']);
			return false;
		}

		var grid = current_window.findById('DTVW_DrugTorgGrid');
		var params = new Object();

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		var drug_torg_code = selected_record.get('DrugTorg_Code');
		var drug_torg_id = selected_record.get('DrugTorg_id');
		var drug_torg_name = selected_record.get('DrugTorg_Name');
		var drug_torg_name_lat = selected_record.get('DrugTorg_NameLat');

		if ( !drug_torg_id ) {
			return false;
		}

		params.callback = function(data) {
			if (!data || !data.DrugTorgData) {
				return false;
			}

			// Обновить запись в grid
			var record = grid.getStore().getById(data.DrugTorgData.DrugTorg_id);

			if ( record ) {
				record.set('DrugTorg_id', data.DrugTorgData.DrugTorg_id);
				record.set('DrugTorg_Code', data.DrugTorgData.DrugTorg_Code);
				record.set('DrugTorg_Name', data.DrugTorgData.DrugTorg_Name);
				record.set('DrugTorg_NameLat', data.DrugTorgData.DrugTorg_NameLat);

				record.commit();
			}
		};
		params.DrugTorg_Code = drug_torg_code;
		params.DrugTorg_id = drug_torg_id;
		params.DrugTorg_Name = drug_torg_name;
		params.DrugTorg_NameLat = drug_torg_name_lat;
		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		getWnd('swDrugTorgEditWindow').show( params );
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swDrugTorgViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.onHide = Ext.emptyFn;

		if ( arguments[0] && arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.doReset();
	},
	title: WND_DLO_DRUGTORGLATINEDIT,
	width: 800,
	initComponent: function() {
		var drugTorgGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = Ext.getCmp('DTVW_DrugTorgGrid');

					if ( store.getCount() > 0 ) {
						grid.getTopToolbar().items.items[2].el.innerHTML = '0 / ' + store.getCount();
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
					else {
						Ext.getCmp('DTVW_DrugTorg_Name').focus(true);
					}
				}
			},
			reader: new Ext.data.JsonReader({
				id: 'DrugTorg_id',
				root: 'data',
				totalProperty: 'count'
			}, [{
				mapping: 'DrugTorg_id',
				name: 'DrugTorg_id',
				type: 'int'
			}, {
				mapping: 'DrugTorg_Code',
				name: 'DrugTorg_Code',
				type: 'string'
			}, {
				mapping: 'DrugTorg_Name',
				name: 'DrugTorg_Name',
				type: 'string'
			}, {
				mapping: 'DrugTorg_NameLat',
				name: 'DrugTorg_NameLat',
				type: 'string'
			}]),
			url: C_DRUG_TORG_VIEW
		});

		Ext.apply(this, {
			buttons: [{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'DTVW_DrugTorg_Name',
				text: BTN_FRMCLOSE
			}],
 			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				buttons: [{
					handler: function() {
						this.ownerCt.ownerCt.doSearch();
					},
					iconCls: 'search16',
					text: BTN_FRMSEARCH
				}, {
					handler: function() {
						this.ownerCt.ownerCt.doReset();
					},
					iconCls: 'resetsearch16',
					text: BTN_FRMRESET
				}],
				frame: true,
				id: 'DTVW_DrugTorgFilterForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DTVW_DrugTorg_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[1].focus();
							}
						}
					},
					name: 'DrugTorg_Name',
					xtype: 'textfield'
				}],
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e) {
						Ext.getCmp('DrugTorgViewWindow').doSearch();
					},
					stopEvent: true
				}],
           		labelAlign: 'top',
				region: 'north',
				style: 'padding: 5px;'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				bbar: new Ext.PagingToolbar({
					displayInfo: true,
					displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
					emptyMsg: "Нет записей для отображения",
					pageSize: 100,
					store: drugTorgGridStore
				}),
				border: false,
				columns: [{
					dataIndex: 'DrugTorg_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'DrugTorg_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}, {
					dataIndex: 'DrugTorg_NameLat',
					header: lang['latinskoe_naimenovanie'],
					sortable: true,
					width: 300
				}],
				id: 'DTVW_DrugTorgGrid',
				keys: [{
					key: [
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.F4,
						Ext.EventObject.HOME,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP,
						Ext.EventObject.TAB
					],
					fn: function(inp, e) {
						e.stopEvent();

						if ( e.browserEvent.stopPropagation ) {
							e.browserEvent.stopPropagation();
						}
						else {
							e.browserEvent.cancelBubble = true;
						}

						if ( e.browserEvent.preventDefault ) {
							e.browserEvent.preventDefault();
						}

						e.browserEvent.returnValue = false;
						e.returnValue = false;

						if ( Ext.isIE ) {
							e.browserEvent.keyCode = 0;
							e.browserEvent.which = 0;
						}

						var grid = Ext.getCmp('DTVW_DrugTorgGrid');

						switch ( e.getKey() ) {
							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F4:
								grid.ownerCt.openDrugTorgEditWindow();
							break;

							case Ext.EventObject.HOME:
								GridHome(grid);
							break;

							case Ext.EventObject.PAGE_DOWN:
								GridPageDown(grid);
							break;

							case Ext.EventObject.PAGE_UP:
								GridPageUp(grid);
							break;
 
							case Ext.EventObject.TAB:
								grid.ownerCt.buttons[1].focus(false, 100);
							break;
						}
					},
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function( grid, rowIndex ) {
						this.ownerCt.openDrugTorgEditWindow();
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIdx, r) {
							if ( sm.getSelected().get('DrugTorg_id') ) {
								this.grid.getTopToolbar().items.items[0].enable();
							}
							else {
								this.grid.getTopToolbar().items.items[0].disable();
							}

							this.grid.getTopToolbar().items.items[2].el.innerHTML = String(rowIdx + 1) + ' / ' + this.grid.getStore().getCount();
						}
					},
					singleSelect: true
				}),
				store: drugTorgGridStore,
				stripeRows: true,
				tbar: new sw.Promed.Toolbar({
					buttons: [{
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.openDrugTorgEditWindow();
						},
						iconCls: 'edit16',
						text: BTN_GRIDEDIT,
                        hidden: (isUserGroup('LpuUser') || isUserGroup('OrgUser'))
					}, {
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				})
			})]
		});
		sw.Promed.swDrugTorgViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
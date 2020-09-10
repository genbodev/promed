/**
* swDrugMnnViewWindow - окно поиска и просмотра МНН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      23.09.2009
*/

sw.Promed.swDrugMnnViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closable: true,
	closeAction: 'hide',
	collapsible: true,
	doReset: function() {
		this.findById('DMVW_DrugMnnFilterForm').getForm().reset();
		this.findById('DMVW_DrugMnnGrid').getStore().baseParams = new Object();
		this.findById('DMVW_DrugMnnGrid').getStore().removeAll();
		this.findById('DMVW_DrugMnnGrid').getTopToolbar().items.items[0].disable();
		this.findById('DMVW_DrugMnnGrid').getTopToolbar().items.items[2].el.innerHTML = '0 / 0';
		this.findById('DMVW_DrugMnn_Name').focus(true, 250);
	},
	doSearch: function() {
		var form = this.findById('DMVW_DrugMnnFilterForm');
		var grid = this.findById('DMVW_DrugMnnGrid');
		var Mask = new Ext.LoadMask(Ext.get('DrugMnnViewWindow'), { msg: SEARCH_WAIT });

		var params = form.getForm().getValues();
		params.privilegeType = this.privilegeType;

		if ( !params.DrugMnn_Name ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DMVW_DrugMnn_Name').focus(true, 250); });
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();

		grid.getStore().baseParams = form.getForm().getValues();
		grid.getStore().baseParams.privilegeType = this.privilegeType;

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
	id: 'DrugMnnViewWindow',
	layout: 'border',
	maximizable: true,
	minHeight: 500,
	minWidth: 800,
	modal: false,
	openDrugMnnEditWindow: function() {
		var current_window = this;

		if ( getWnd('swDrugMnnEditWindow').isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_latinskogo_naimenovaniya_mnn_uje_otkryito']);
			return false;
		}

		var drug_mnn_grid = current_window.findById('DMVW_DrugMnnGrid');
		var params = new Object();

		if ( !drug_mnn_grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = drug_mnn_grid.getSelectionModel().getSelected();

		var drug_mnn_code = selected_record.get('DrugMnn_Code');
		var drug_mnn_id = selected_record.get('DrugMnn_id');
		var drug_mnn_name = selected_record.get('DrugMnn_Name');
		var drug_mnn_name_lat = selected_record.get('DrugMnn_NameLat');

		if ( !drug_mnn_id ) {
			return false;
		}

		params.callback = function(data) {
			if ( !data || !data.DrugMnnData ) {
				return false;
			}

			// Обновить запись в drug_mnn_grid
			var record = drug_mnn_grid.getStore().getById(data.DrugMnnData.DrugMnn_id);

			if ( record ) {
				record.set('DrugMnn_id', data.DrugMnnData.DrugMnn_id);
				record.set('DrugMnn_Code', data.DrugMnnData.DrugMnn_Code);
				record.set('DrugMnn_Name', data.DrugMnnData.DrugMnn_Name);
				record.set('DrugMnn_NameLat', data.DrugMnnData.DrugMnn_NameLat);

				record.commit();
			}
		};
		params.DrugMnn_Code = drug_mnn_code;
		params.DrugMnn_id = drug_mnn_id;
		params.DrugMnn_Name = drug_mnn_name;
		params.DrugMnn_NameLat = drug_mnn_name_lat;
		params.onHide = function() {
			drug_mnn_grid.getView().focusRow(drug_mnn_grid.getStore().indexOf(selected_record));
		};

		getWnd('swDrugMnnEditWindow').show( params );
	},
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swDrugMnnViewWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();
		this.maximize();

		this.onHide = Ext.emptyFn;
		this.privilegeType = null;

		if ( !arguments[0] ) {
			this.hide();
			return false;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].privilegeType ) {
			this.privilegeType = arguments[0].privilegeType;
		}

		this.doReset();
	},
	title: WND_DLO_DRUGMNNLATINEDIT,
	width: 800,
	initComponent: function() {
		var drugMnnGridStore = new Ext.data.Store({
			autoLoad: false,
			listeners: {
				'load': function(store, records, options) {
					var grid = Ext.getCmp('DMVW_DrugMnnGrid');

					if ( store.getCount() > 0 ) {
						grid.getTopToolbar().items.items[2].el.innerHTML = '0 / ' + store.getCount();
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
					else {
						Ext.getCmp('DMVW_DrugMnn_Name').focus(true);
					}
				}
			},
			reader: new Ext.data.JsonReader({
				id: 'DrugMnn_id',
				root: 'data',
				totalProperty: 'count'
			}, [{
				mapping: 'DrugMnn_id',
				name: 'DrugMnn_id',
				type: 'int'
			}, {
				mapping: 'DrugMnn_Code',
				name: 'DrugMnn_Code',
				type: 'string'
			}, {
				mapping: 'DrugMnn_Name',
				name: 'DrugMnn_Name',
				type: 'string'
			}, {
				mapping: 'DrugMnn_NameLat',
				name: 'DrugMnn_NameLat',
				type: 'string'
			}]),
			url: C_DRUG_MNN_VIEW
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
				onTabElement: 'DMVW_DrugMnn_Name',
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
				id: 'DMVW_DrugMnnFilterForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DMVW_DrugMnn_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[1].focus();
							}
						}
					},
					name: 'DrugMnn_Name',
					xtype: 'textfield'
				}],
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e) {
						Ext.getCmp('DrugMnnViewWindow').doSearch();
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
					store: drugMnnGridStore
				}),
				border: false,
				columns: [{
					dataIndex: 'DrugMnn_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'DrugMnn_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}, {
					dataIndex: 'DrugMnn_NameLat',
					header: lang['latinskoe_naimenovanie'],
					sortable: true,
					width: 300
				}],
				id: 'DMVW_DrugMnnGrid',
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

						var grid = Ext.getCmp('DMVW_DrugMnnGrid');

						switch ( e.getKey() ) {
							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
							case Ext.EventObject.F4:
								grid.ownerCt.openDrugMnnEditWindow();
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
						this.ownerCt.openDrugMnnEditWindow();
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIdx, r) {
							if ( sm.getSelected().get('DrugMnn_id') ) {
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
				store: drugMnnGridStore,
				stripeRows: true,
				tbar: new sw.Promed.Toolbar({
					buttons: [{
						handler: function() {
							this.ownerCt.ownerCt.ownerCt.openDrugMnnEditWindow();
						},
						iconCls: 'edit16',
						text: BTN_GRIDEDIT,
                        hidden:(isUserGroup('LpuUser') || isUserGroup('OrgUser'))
					}, {
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}]
				})
			})]
		});
		sw.Promed.swDrugMnnViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
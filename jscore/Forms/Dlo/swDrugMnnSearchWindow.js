/**
* swDrugMnnSearchWindow - окно поиска наименований медикаментов.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Pshenicyn Ivan aka IVP (ipshon@rambler.ru)
* @version      17.04.2009
*/

sw.Promed.swDrugMnnSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction: 'hide',
	doReset: function() {
		this.findById('DrugMnnSearchGrid').getStore().removeAll();
		this.findById('DrugMnnSearchForm').getForm().reset();
		this.findById('DMSW_DrugMnn_Name').focus(true, 250);
		this.findById('DrugMnnSearchGrid').getTopToolbar().items.items[1].el.innerHTML = '0 / 0';
	},
	doSearch: function() {
		var grid = this.findById('DrugMnnSearchGrid');
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
		var params = new Object();

		params.Date = this.EvnRecept_setDate;
		params.EvnRecept_Is7Noz_Code = this.EvnRecept_Is7Noz_Code;
		params.mode = 'any';
		params.query = this.findById('DrugMnnSearchForm').getForm().findField('DrugMnn_Name').getValue();
		params.ReceptFinance_Code = this.ReceptFinance_Code;
		params.ReceptType_Code = this.ReceptType_Code;
		params.PrivilegeType_id = this.PrivilegeType_id;

		if ( this.searchFull == true ) {
			params.searchFull = 'searchFull';
		}

		if ( !params.query ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { this.findById('DrugMnnSearchForm').getForm().findField('DrugMnn_Name').focus(true, 250); }.createDelegate(this) );
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();

		grid.getStore().load({
			callback: function() {
				Mask.hide();

				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			},
			params: params
		});
	},
	draggable: false,
	EvnRecept_Is7Noz_Code: 0,
	EvnRecept_setDate: null,
	height: 500,
	id: 'DrugMnnSearchWindow',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onClose();
		}
	},
	modal: true,
	onClose: Ext.emptyFn,
	onDrugMnnSelect: Ext.emptyFn,
	onOkButtonClick: function() {
		if ( this.findById('DrugMnnSearchGrid').getSelectionModel().getSelected() ) {
			this.onDrugMnnSelect({
				DrugMnn_id: this.findById('DrugMnnSearchGrid').getSelectionModel().getSelected().get('DrugMnn_id'),
				DrugMnn_Code: this.findById('DrugMnnSearchGrid').getSelectionModel().getSelected().get('DrugMnn_Code'),
				DrugMnn_Name: this.findById('DrugMnnSearchGrid').getSelectionModel().getSelected().get('DrugMnn_Name')
			});
		}
		else {
			this.hide();
		}
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDrugMnnSearchWindow.superclass.show.apply(this, arguments);

		this.EvnRecept_Is7Noz_Code = 0;
		this.EvnRecept_setDate = null;
		this.onClose = Ext.emptyFn;
		this.onDrugMnnSelect = Ext.emptyFn;
		this.ReceptFinance_Code = null;
		this.ReceptType_Code = null;
		this.PrivilegeType_id = null;
		this.searchFull = false;

		if ( !arguments[0] ) {
			this.hide();
			return false;
		}

		if ( arguments[0].EvnRecept_Is7Noz_Code && Number(arguments[0].EvnRecept_Is7Noz_Code) == 1 ) {
			this.EvnRecept_Is7Noz_Code = arguments[0].EvnRecept_Is7Noz_Code;
		}

		if ( arguments[0].EvnRecept_setDate ) {
			this.EvnRecept_setDate = arguments[0].EvnRecept_setDate;
		}

		if ( arguments[0].onClose ) {
			this.onClose = arguments[0].onClose;
		}

		if ( arguments[0].onSelect ) {
			this.onDrugMnnSelect = arguments[0].onSelect;
		}

		if ( arguments[0].ReceptFinance_Code ) {
			this.ReceptFinance_Code = arguments[0].ReceptFinance_Code;
		}

		if ( arguments[0].ReceptType_Code ) {
			this.ReceptType_Code = arguments[0].ReceptType_Code;
		}
		
		if ( arguments[0].PrivilegeType_id ) {
			this.PrivilegeType_id = arguments[0].PrivilegeType_id;
		}

		if ( arguments[0].searchFull ) {
			this.searchFull = arguments[0].searchFull;
		}

		this.doReset();
	},
	title: WND_SEARCH_DRUGMNN,
	width: 800,
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSearch();
				}.createDelegate(this),
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.doReset();
				}.createDelegate(this),
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.onOkButtonClick();
				}.createDelegate(this),
		        iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabElement: 'DMSW_DrugMnn_Name',
				text: BTN_FRMCANCEL
			}],
 			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DrugMnnSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DMSW_DrugMnn_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'DrugMnn_Name',
					xtype: 'textfield'
				}],
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e) {
						this.doSearch();
					}.createDelegate(this),
					stopEvent: true
				}],
				labelAlign: 'top',
				region: 'north',
				style: 'padding: 0px;'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				border: false,
				columns: [{
					dataIndex: 'DrugMnn_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'DrugMnnSearchGrid',
				keys: [{
					key: [
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
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

						var grid = this.findById('DrugMnnSearchGrid');

						switch ( e.getKey() ) {
							case Ext.EventObject.END:
								GridEnd(grid);
							break;

							case Ext.EventObject.ENTER:
								if ( !grid.getSelectionModel().getSelected() ) {
									return false;
								}

								this.onOkButtonClick();
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
								// TODO: getWnd
								getWnd('swDrugMnnSearchWindow').buttons[0].focus(false, 100);
							break;
						}
					},
					scope: this,
					stopEvent: true
				}],
				listeners: {
					'rowdblclick': function( grid, rowIndex ) {
						this.onOkButtonClick();
					}.createDelegate(this)
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					listeners: {
						'rowselect': function(sm, rowIndex, record) {
							this.grid.getTopToolbar().items.items[1].el.innerHTML = String(rowIndex + 1) + ' / ' + this.grid.getStore().getCount();
						}
					},
					singleSelect: true
				}),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						'DrugMnn_id',
						'DrugMnn_Code',
						'DrugMnn_Name'
					],
					listeners: {
						'load': function(store, records, options) {
							var grid = this.findById('DrugMnnSearchGrid');
							
							if ( store.getCount() > 0 ) {
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this)
					},
					url: C_DRUG_MNN_LIST
				}),
				stripeRows: true,
				tbar: new sw.Promed.Toolbar({
					buttons: [{
						xtype: 'tbfill'
					}, {
						text: '0 / 0',
						xtype: 'tbtext'
					}],
					style: 'padding: 5px;'
				})
			})]
		});
		sw.Promed.swDrugMnnSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});
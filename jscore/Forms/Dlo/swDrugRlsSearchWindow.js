/**
* swDrugRlsSearchWindow - окно поиска медикаментов по торговому наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      05.04.2013
*/

sw.Promed.swDrugRlsSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	isKardio: null,
	doReset: function() {
		this.findById('DRSW_DrugRlsSearchGrid').getStore().removeAll();
		this.findById('DrugRlsSearchForm').getForm().reset();
		this.findById('DRSW_Drug_Name').focus(true, 250);
		this.findById('DRSW_DrugRlsSearchGrid').getTopToolbar().items.items[1].el.innerHTML = '0 / 0';
	},
	doSearch: function() {
		var grid = this.findById('DRSW_DrugRlsSearchGrid');
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
		var params = new Object();

		grid.getStore().removeAll();

		if (this.fixed_search_params) {
			Ext.apply(params, this.fixed_search_params);
		} else {
			params.Date = this.EvnRecept_setDate;
			params.ReceptType_Code = this.ReceptType_Code;
			params.DrugOstatRegistry_id = this.DrugOstatRegistry_id;
			params.EvnRecept_IsMnn = this.EvnRecept_IsMnn;
			params.EvnRecept_IsKEK = this.EvnRecept_IsKEK;
			params.MorbusType_id = this.MorbusType_id;
			params.Person_id = this.Person_id;
			params.is_mi_1 = this.is_mi_1;
			params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
			params.PersonRegisterType_id = this.PersonRegisterType_id;

			if (this.isKardio) {
				grid.getStore().baseParams.recept_drug_ostat_control = 1;
				grid.getStore().baseParams.recept_empty_drug_ostat_allow = 1;
				grid.getStore().baseParams.select_drug_from_list = 'jnvlp';
				this.searchFull == false;
			} else {
				params.mode = 'all';
			}

			if (this.searchFull == true) {
				params.searchFull = 'searchFull';
			}
		}

		params.query = this.findById('DrugRlsSearchForm').getForm().findField('Drug_Name').getValue();

		if ( Ext.isEmpty(params.query) ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DRSW_Drug_Name').focus(true, 250); });
			return false;
		}


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
	draggable: true,
	EvnRecept_setDate: null,
	height: 500,
	id: 'DrugRlsSearchWindow',
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
				onTabElement: 'DRSW_Drug_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DrugRlsSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DRSW_Drug_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'Drug_Name',
					xtype: 'textfield'
				}],
				keys: [{
					fn: function(e) {
						this.doSearch();
					}.createDelegate(this),
					key: Ext.EventObject.ENTER,
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
					dataIndex: 'Drug_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'DRSW_DrugRlsSearchGrid',
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

						var grid = this.findById('DRSW_DrugRlsSearchGrid');

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
								Ext.getCmp('DrugRlsSearchWindow').buttons[0].focus(false, 100);
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
						'Drug_Name',
						'Drug_Code',
						'Drug_rlsid',
						'DrugComplexMnn_id',
						'Drug_Price',
						'Drug_IsKEK',
						'DrugOstat_Flag'
					],
					listeners: {
						'load': function(store, records, options) {
							var grid = this.findById('DRSW_DrugRlsSearchGrid');
							
							if ( store.getCount() > 0 ) {
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this)
					},
					url: C_DRUG_RLS_LIST
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
		sw.Promed.swDrugRlsSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onDrugRlsSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if ( !this.findById('DRSW_DrugRlsSearchGrid').getSelectionModel().getSelected() ) {
			this.hide();
			return false;
		}

		this.onDrugRlsSelect(this.findById('DRSW_DrugRlsSearchGrid').getSelectionModel().getSelected().data);
	},
	plain: true,
	ReceptFinance_Code: null,
	ReceptType_Code: null,
	PrivilegeType_id: null,
	resizable: false,
	show: function() {
		sw.Promed.swDrugRlsSearchWindow.superclass.show.apply(this, arguments);

		this.fixed_search_params = null;
		this.EvnRecept_setDate = null;
		this.onDrugRlsSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.ReceptType_Code = null;
		this.searchFull = false;
		this.WhsDocumentCostItemType_id = null;
		this.DrugOstatRegistry_id = null;
		this.EvnRecept_IsMnn = null;
		this.EvnRecept_IsKEK = null;
		this.MorbusType_id = null;
		this.Person_id = null;
        this.is_mi_1 = false;
		if ( !arguments[0] ) {
			this.hide();
			return false;
		}

		if ( arguments[0].fixed_search_params ) {
			this.fixed_search_params = arguments[0].fixed_search_params;
		}

		if ( arguments[0].EvnRecept_setDate ) {
			this.EvnRecept_setDate = arguments[0].EvnRecept_setDate;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].onSelect ) {
			this.onDrugRlsSelect = arguments[0].onSelect;
		}

		if ( arguments[0].ReceptType_Code ) {
			this.ReceptType_Code = arguments[0].ReceptType_Code;
		}
		
		if ( arguments[0].searchFull ) {
			this.searchFull = arguments[0].searchFull;
		}

		if ( arguments[0].WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}

		if ( arguments[0].DrugOstatRegistry_id ) {
			this.DrugOstatRegistry_id = arguments[0].DrugOstatRegistry_id;
		}

		if ( arguments[0].EvnRecept_IsMnn ) {
			this.EvnRecept_IsMnn = arguments[0].EvnRecept_IsMnn;
		}

		if ( arguments[0].EvnRecept_IsKEK ) {
			this.EvnRecept_IsKEK = arguments[0].EvnRecept_IsKEK;
		}

		if ( arguments[0].MorbusType_id ) {
			this.MorbusType_id = arguments[0].MorbusType_id;
		}

		if ( arguments[0].Person_id ) {
			this.Person_id = arguments[0].Person_id;
		}

        if ( arguments[0].is_mi_1) {
            this.is_mi_1 = arguments[0].is_mi_1;
        }

		if ( arguments[0].isKardio ) {
			this.isKardio = arguments[0].isKardio;
		}

		if ( arguments[0].WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}

		if ( arguments[0].PersonRegisterType_id ) {
			this.PersonRegisterType_id = arguments[0].PersonRegisterType_id;
		}

		this.doReset();
	},
	title: WND_SEARCH_DRUGTORG,
	width: 800
});
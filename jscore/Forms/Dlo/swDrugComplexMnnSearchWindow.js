/**
* swDrugComplexMnnSearchWindow - окно поиска наименований медикаментов.
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

sw.Promed.swDrugComplexMnnSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction: 'hide',
	isKardio: null,
	doReset: function() {
		this.findById('DrugComplexMnnSearchGrid').getStore().removeAll();
		this.findById('DrugComplexMnnSearchForm').getForm().reset();
		this.findById('DCMSW_DrugComplexMnn_Name').focus(true, 250);
		this.findById('DrugComplexMnnSearchGrid').getTopToolbar().items.items[1].el.innerHTML = '0 / 0';
	},
	doSearch: function() {
		var grid = this.findById('DrugComplexMnnSearchGrid');
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
		var params = new Object();

		grid.getStore().removeAll();

		if (this.fixed_search_params) {
			Ext.apply(params, this.fixed_search_params);
		} else {
			params.Date = this.EvnRecept_setDate;
			params.ReceptType_Code = this.ReceptType_Code;
			params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
			params.MorbusType_id = this.MorbusType_id;
			params.Person_id = this.Person_id;
			params.EvnRecept_IsMnn = this.EvnRecept_IsMnn;
			params.EvnRecept_IsKEK = this.EvnRecept_IsKEK;
			params.DrugRequestProperty_id = this.DrugRequestProperty_id;

			if (this.isKardio){
				grid.getStore().baseParams.recept_drug_ostat_control = 1;
				grid.getStore().baseParams.recept_empty_drug_ostat_allow = 1;
				grid.getStore().baseParams.select_drug_from_list = 'jnvlp';
				this.searchFull == false;
			} else {
				params.mode = 'any';
				params.fromReserve = 1;
			}

			if ( this.searchFull == true ) {
				params.searchFull = 'searchFull';
			}
		}

		params.query = this.findById('DrugComplexMnnSearchForm').getForm().findField('DrugComplexMnn_Name').getValue();
		params.paging = true;

		/*if ( Ext.isEmpty(params.query) ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { this.findById('DrugComplexMnnSearchForm').getForm().findField('DrugComplexMnn_Name').focus(true, 250); }.createDelegate(this) );
			return false;
		}*/

		Mask.show();

		grid.getStore().baseParams = Ext.apply(grid.getStore().baseParams, params);
		grid.getStore().load({
			callback: function() {
				Mask.hide();

				if ( grid.getStore().getCount() > 0 ) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
			}
		});
	},
	draggable: false,
	EvnRecept_setDate: null,
	height: 500,
	id: 'DrugComplexMnnSearchWindow',
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onClose();
		}
	},
	modal: true,
	onClose: Ext.emptyFn,
	onDrugComplexMnnSelect: Ext.emptyFn,
	onOkButtonClick: function() {
		if ( this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected() ) {
			this.onDrugComplexMnnSelect({
				DrugComplexMnn_id: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('DrugComplexMnn_id'),
				DrugComplexMnn_Code: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('DrugComplexMnn_Code'),
				DrugComplexMnn_Name: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('DrugComplexMnn_Name'),
				Actmatters_id: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('Actmatters_id'),
				DrugOstatRegistry_id: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('DrugOstatRegistry_id'),
				DrugRequestRow_id: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('DrugRequestRow_id'),
				WhsDocumentSupply_id: this.findById('DrugComplexMnnSearchGrid').getSelectionModel().getSelected().get('WhsDocumentSupply_id')
			});
		}
		else {
			this.hide();
		}
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDrugComplexMnnSearchWindow.superclass.show.apply(this, arguments);

		var grid = this.findById('DrugComplexMnnSearchGrid');

		this.params = null;
		this.fixed_search_params = null;
		this.EvnRecept_setDate = null;
		this.onClose = Ext.emptyFn;
		this.onDrugComplexMnnSelect = Ext.emptyFn;
		this.ReceptType_Code = null;
		this.searchFull = false;
		this.WhsDocumentCostItemType_id = false;
		this.MorbusType_id = null;
		this.Person_id = null;
		this.searchUrl = C_DRUG_COMPLEX_MNN_LIST;
		this.forRecept = false;
		this.EvnRecept_IsMnn = null;
		this.EvnRecept_IsKEK = null;
		this.DrugRequestProperty_id = null;

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

		if ( arguments[0].onClose ) {
			this.onClose = arguments[0].onClose;
		}

		if ( arguments[0].onSelect ) {
			this.onDrugComplexMnnSelect = arguments[0].onSelect;
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

		if ( arguments[0].MorbusType_id ) {
			this.MorbusType_id = arguments[0].MorbusType_id;
		}

		if ( arguments[0].Person_id ) {
			this.Person_id = arguments[0].Person_id;
		}

		if ( arguments[0].searchUrl ) {
			this.searchUrl = arguments[0].searchUrl;
		}

		if ( arguments[0].forRecept ) {
			this.forRecept = arguments[0].forRecept;
		}

		if ( arguments[0].EvnRecept_IsMnn ) {
			this.EvnRecept_IsMnn = arguments[0].EvnRecept_IsMnn;
		}

		if ( arguments[0].EvnRecept_IsKEK ) {
			this.EvnRecept_IsKEK = arguments[0].EvnRecept_IsKEK;
		}

		if ( arguments[0].DrugRequestProperty_id ) {
			this.DrugRequestProperty_id = arguments[0].DrugRequestProperty_id;
		}

		if ( arguments[0].isKardio ) {
			this.isKardio = arguments[0].isKardio;
		}
		var cm = grid.getColumnModel();
		if (
			getGlobalOptions().select_drug_from_list
			&& getGlobalOptions().select_drug_from_list.inlist(['request','request_and_allocation'])
			&& this.forRecept
		) {
			cm.setHidden(cm.getIndexById('Person_Fio'), false);
		} else {
			cm.setHidden(cm.getIndexById('Person_Fio'), true);
		}

		this.doReset();
		grid.getStore().proxy.conn.url = this.searchUrl;
	},
	title: WND_SEARCH_DRUGMNN,
	WhsDocumentCostItemType_id: null,
	width: 800,
	initComponent: function() {
		this.gridStore = new Ext.data.JsonStore({
			autoLoad: false,
			root: 'data',
			totalProperty: 'totalCount',
			fields: [
				'DrugComplexMnn_id',
				'DrugComplexMnn_Code',
				'DrugComplexMnn_Name',
				'Actmatters_id',
				'DrugOstatRegistry_id',
				'DrugRequestRow_id',
				'WhsDocumentSupply_id',
				'Person_Fio'
			],
			listeners: {
				'load': function(store, records, options) {
					var grid = this.findById('DrugComplexMnnSearchGrid');

					if ( store.getCount() > 0 ) {
						grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
						grid.getView().focusRow(0);
						grid.getSelectionModel().selectFirstRow();
					}
				}.createDelegate(this)
			},
			url: C_DRUG_COMPLEX_MNN_LIST
		});

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
				onTabElement: 'DCMSW_DrugComplexMnn_Name',
				text: BTN_FRMCANCEL
			}],
 			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				bodyBorder: false,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DrugComplexMnnSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DCMSW_DrugComplexMnn_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'DrugComplexMnn_Name',
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
				bbar: new Ext.PagingToolbar ({
					store: this.gridStore,
					pageSize: 100,
					displayInfo: true,
					displayMsg: lang['otobrajaemyie_stroki_{0}_-_{1}_iz_{2}'],
					emptyMsg: "Нет записей для отображения"
				}),
				columns: [{
					id: 'Person_Fio',
					dataIndex: 'Person_Fio',
					header: lang['fio'],
					width: 180,
					hidden: true,
					sortable: true
				}, {
					dataIndex: 'DrugComplexMnn_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'DrugComplexMnnSearchGrid',
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

						var grid = this.findById('DrugComplexMnnSearchGrid');

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
								getWnd('swDrugComplexMnnSearchWindow').buttons[0].focus(false, 100);
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
				store: this.gridStore,
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

		sw.Promed.swDrugComplexMnnSearchWindow.superclass.initComponent.apply(this, arguments);
	}
});
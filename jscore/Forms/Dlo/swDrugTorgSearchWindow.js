/**
* swDrugTorgSearchWindow - окно поиска медикаментов по торговому наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-26.05.2009
*/

sw.Promed.swDrugTorgSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	doReset: function() {
		this.findById('DTSW_DrugTorgSearchGrid').getStore().removeAll();
		this.findById('DrugTorgSearchForm').getForm().reset();
		this.findById('DTSW_DrugTorg_Name').focus(true, 250);
		this.findById('DTSW_DrugTorgSearchGrid').getTopToolbar().items.items[1].el.innerHTML = '0 / 0';
	},
	doSearch: function() {
		var grid = this.findById('DTSW_DrugTorgSearchGrid');
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
		var params = new Object();

		params.Date = this.EvnRecept_setDate;
		params.EvnRecept_Is7Noz_Code = this.EvnRecept_Is7Noz_Code;
		params.mode = this.mode;
		params.query = this.findById('DrugTorgSearchForm').getForm().findField('DrugTorg_Name').getValue();
		params.Drug_CodeG = this.findById('DrugTorgSearchForm').getForm().findField('Drug_CodeG').getValue();
		params.ReceptFinance_Code = this.ReceptFinance_Code;
		params.ReceptType_Code = this.ReceptType_Code;
		params.PrivilegeType_id = this.PrivilegeType_id;
		params.WhsDocumentCostItemType_id = this.WhsDocumentCostItemType_id;
        params.is_mi_1 = this.is_mi_1;

		if ( !params.query && !params.Drug_CodeG ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DTSW_DrugTorg_Name').focus(true, 250); });
			return false;
		}

		if ((params.query.length < 3) && (params.Drug_CodeG.length < 3)) {
			sw.swMsg.alert(lang['oshibka'], lang['dlya_poiska_vvedite_ne_menee_3_simvolov'], function() { grid.ownerCt.findById('DTSW_DrugTorg_Name').focus(true, 250); });
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
	draggable: true,
	EvnRecept_Is7Noz_Code: 0,
	EvnRecept_setDate: null,
	height: 500,
	id: 'DrugTorgSearchWindow',
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
				onTabElement: 'DTSW_DrugTorg_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DrugTorgSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DTSW_DrugTorg_Name',
					listeners: {
						'keydown': function(inp, e) {
							if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this)
					},
					name: 'DrugTorg_Name',
					xtype: 'textfield'
				},
					{
						anchor: '20%',
						enableKeyEvents: true,
						fieldLabel: 'ГЕС',
						id: 'DTSW_Drug_CPodeG',
						listeners: {
							'keydown': function(inp, e) {
								if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this)
						},
						name: 'Drug_CodeG',
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
					sortable: true,
					renderer: function(v,p){
						p.attr = 'ext:qtip="<b><font size=2px>' + v + '</font></b>" ext:qwidth="450"';
						return v;
					}
				},
					{
						dataIndex: 'Drug_CodeG',
						header: 'ГЕС',
						id: 'autoexpand',
						sortable: true
					}],
				id: 'DTSW_DrugTorgSearchGrid',
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

						var grid = this.findById('DTSW_DrugTorgSearchGrid');

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
								Ext.getCmp('DrugTorgSearchWindow').buttons[0].focus(false, 100);
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
						'Drug_id',
						'DrugRequestRow_id',
						'DrugMnn_id',
						'DrugFormGroup_id',
						'Drug_IsKEK',
						'Drug_Name',
						'Drug_DoseCount',
						'Drug_DoseQ',
						'Drug_DoseUEEi',
						'Drug_Fas',
						'Drug_Price',
						'Drug_IsKEK_Code',
						'DrugOstat_Flag',
						'Drug_CodeG'
					],
					listeners: {
						'load': function(store, records, options) {
							var grid = this.findById('DTSW_DrugTorgSearchGrid');
							
							if ( store.getCount() > 0 ) {
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this)
					},
					url: '/?c=EvnRecept&m=loadDrugList'
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
		sw.Promed.swDrugTorgSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	onDrugTorgSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if ( !this.findById('DTSW_DrugTorgSearchGrid').getSelectionModel().getSelected() ) {
			this.hide();
			return false;
		}

		this.onDrugTorgSelect(this.findById('DTSW_DrugTorgSearchGrid').getSelectionModel().getSelected().data);
	},
	plain: true,
	ReceptFinance_Code: null,
	ReceptType_Code: null,
	PrivilegeType_id: null,
	resizable: false,
	show: function() {
		sw.Promed.swDrugTorgSearchWindow.superclass.show.apply(this, arguments);

		this.EvnRecept_Is7Noz_Code = 0;
		this.EvnRecept_setDate = null;
		this.onDrugTorgSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.ReceptFinance_Code = null;
		this.ReceptType_Code = null;
		this.PrivilegeType_id = null;
		this.WhsDocumentCostItemType_id = null;
        this.is_mi_1 = false;
        this.mode = 'all';

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

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].onSelect ) {
			this.onDrugTorgSelect = arguments[0].onSelect;
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

		if ( arguments[0].WhsDocumentCostItemType_id ) {
			this.WhsDocumentCostItemType_id = arguments[0].WhsDocumentCostItemType_id;
		}

        if ( arguments[0].is_mi_1 ) {
            this.is_mi_1 = arguments[0].is_mi_1;
        }

        if ( arguments[0].mode ) {
            this.mode = arguments[0].mode;
        }
		this.doReset();
	},
	title: WND_SEARCH_DRUGTORG,
	width: 800
});
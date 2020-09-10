/**
* swRlsDrugTorgSearchWindow - окно поиска медикаментов по торговому наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      	DLO
* @access      		public
* @copyright	    Copyright (c) 2012 Swan Ltd.
* @author  	    	Salakhov R.
* @originalauthor   Stas Bykov aka Savage (savage1981@gmail.com)
* @version      	23.12.2012
*/

sw.Promed.swRlsDrugTorgSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	doReset: function() {
        var form = this.findById('RlsDrugTorgSearchForm').getForm().reset();

        form.reset();
		this.findById('RDTSW_RlsDrugTorgSearchGrid').getStore().removeAll();
		this.findById('RDTSW_DrugTorg_Name').focus(true, 250);
		this.findById('RDTSW_RlsDrugTorgSearchGrid').getTopToolbar().items.items[1].el.innerHTML = '0 / 0';

        if (this.EanFilterEnabled) {
            form.findField('Drug_Ean').showContainer();
        } else {
            form.findField('Drug_Ean').hideContainer();
        }
        this.doLayout();
	},
	doSearch: function() {
		var grid = this.findById('RDTSW_RlsDrugTorgSearchGrid');
		var Mask = new Ext.LoadMask(this.getEl(), { msg: SEARCH_WAIT });
		var params = new Object();
        var form = this.findById('RlsDrugTorgSearchForm').getForm();

		params.query = form.findField('DrugTorg_Name').getValue();
		params.Drug_Ean = form.findField('Drug_Ean').getValue();
		if (this.FormValues && this.FormValues.DrugComplexMnn_id) {
			params.DrugComplexMnn_id = this.FormValues.DrugComplexMnn_id;
		}

		if (Ext.isEmpty(params.query) && Ext.isEmpty(params.Drug_Ean)) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('RDTSW_DrugTorg_Name').focus(true, 250); });
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
	height: 500,
	id: 'RlsDrugTorgSearchWindow',
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
				text: langs('Выбрать'),
				id: 'RDTSW_btSelect'
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabElement: 'RDTSW_DrugTorg_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'RlsDrugTorgSearchForm',
				items: [{
                    layout: 'form',
                    items: [{
                        fieldLabel : lang['kod_ean'],
                        anchor: '100%',
                        name: 'Drug_Ean',
                        xtype: 'textfield'
                    }]
                }, {
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'RDTSW_DrugTorg_Name',
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
					dataIndex: 'Drug_Code',
					header: langs('Код'),
					hidden: !(getRegionNick() == 'ufa' || getGlobalOptions().pmuser_id.inlist(['255436352404', '256064768703', '257186302627']))
				}, {
					dataIndex: 'Drug_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'RDTSW_RlsDrugTorgSearchGrid',
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

						var grid = this.findById('RDTSW_RlsDrugTorgSearchGrid');

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
								Ext.getCmp('RlsDrugTorgSearchWindow').buttons[0].focus(false, 100);
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
						'Drug_Code',
						'Drug_Name',
						'DrugMnn_id',
						'DrugComplexMnn_id'
					],
					listeners: {
						'load': function(store, records, options) {
							var grid = this.findById('RDTSW_RlsDrugTorgSearchGrid');
							
							if ( store.getCount() > 0 ) {
								grid.getTopToolbar().items.items[1].el.innerHTML = '0 / ' + store.getCount();
								grid.getView().focusRow(0);
								grid.getSelectionModel().selectFirstRow();
							}
						}.createDelegate(this)
					},
					url: '/?c=RlsDrug&m=loadDrugList'
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
		sw.Promed.swRlsDrugTorgSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	modal: true,
	maximized: true,
	onDrugTorgSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if ( !this.findById('RDTSW_RlsDrugTorgSearchGrid').getSelectionModel().getSelected() ) {
			this.hide();
			return false;
		}

		this.onDrugTorgSelect(this.findById('RDTSW_RlsDrugTorgSearchGrid').getSelectionModel().getSelected().data);
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swRlsDrugTorgSearchWindow.superclass.show.apply(this, arguments);

		this.onDrugTorgSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.EanFilterEnabled = false;
		this.FormValues = new Object();
		this.parent = '';

		if ( !arguments[0] ) {
			this.hide();
			return false;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].onSelect ) {
			this.onDrugTorgSelect = arguments[0].onSelect;
		}

		if ( arguments[0].EanFilterEnabled ) {
			this.EanFilterEnabled = arguments[0].EanFilterEnabled;
		}

		if ( arguments[0].FormValues ) {
			this.FormValues = arguments[0].FormValues;
		}
		
		if ( arguments[0].parent ) {
			this.parent = arguments[0].parent;
		}
		if (this.parent == 'swAdminWorkPlaceWindow')
			Ext.getCmp('RDTSW_btSelect').hide();
		else
			Ext.getCmp('RDTSW_btSelect').show();

		this.doReset();
        this.findById('RlsDrugTorgSearchForm').getForm().setValues(this.FormValues);
	},
	title: WND_SEARCH_DRUGTORG,
	width: 800
});
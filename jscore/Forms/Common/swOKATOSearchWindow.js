/**
* swOKATOSearchWindow - окно поиска OKATO
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @access       public
* @copyright    Copyright (c) 2015 Swan Ltd.
* @author       Abakhri Samir
* @version      02.09.2015
*/

sw.Promed.swOKATOSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	doReset: function() {
		this.findById('DSW_OKATOSearchGrid').getStore().removeAll();
		this.findById('OKATOSearchForm').getForm().reset();
		this.findById('DSW_OKATO_Name').focus(true, 250);
	},
	doSearch: function() {
		var grid = this.findById('DSW_OKATOSearchGrid');
		var Mask = new Ext.LoadMask(Ext.get('swOKATOSearchWindow'), { msg: SEARCH_WAIT });
		var params = this.findById('OKATOSearchForm').getForm().getValues();

		/*if ( !params.OKATO_Name )
		{
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DSW_OKATO_Name').focus(true, 250); });
			return false;
		}*/
		
		/*if ( params.OKATO_Name.length < 2 ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_ne_menee_dvuh_simvolov'], function() { grid.ownerCt.findById('DSW_OKATO_Name').focus(true, 250); });
			return false;
		}*/

		grid.getStore().removeAll();
		Mask.show();

		this.findById('DSW_Above100Text').hide();
		grid.getStore().load({
			params: params,
			callback: function(r, opt ) {
			
				var len = r.length;
				if ( len > 100 ) { // опа! 101 запись!
					// вывести напдись под "Найдено более 100 записей, необходимо уточнить критерии поиска"
					this.findById('DSW_Above100Text').show();
					grid.getStore().removeAt(len - 1);
					len--;
				}
				
				if (grid.getStore().getCount() > 0)
				{
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				Mask.hide();
			}.createDelegate(this)
		});
	},
	draggable: true,
	height: 500,
	id: 'swOKATOSearchWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.ownerCt.doSearch();
				},
				iconCls: 'search16',
				text: BTN_FRMSEARCH
			}, {
				handler: function() {
					this.ownerCt.doReset();
				},
				iconCls: 'resetsearch16',
				text: BTN_FRMRESET
			}, {
				handler: function() {
					this.ownerCt.onOkButtonClick();
				},
		        iconCls: 'ok16',
				text: lang['vyibrat']
			}, {
				text: '-'
			},
			HelpButton(this),
			{
				handler: function() {
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'DSW_OKATO_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'OKATOSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DSW_OKATO_Name',
					listeners: {
						'keydown': function(inp, e) {
							if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true)
							{
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[5].focus();
							}
						}
					},
					name: 'OKATO_Name',
					xtype: 'textfield'
				},{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['kod_okato'],
					id: 'DSW_OKATO_Code',
					listeners: {
						'keydown': function(inp, e) {
							if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true)
							{
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[5].focus();
							}
						}
					},
					name: 'OKATO_Code',
					xtype: 'textfield'
				},{
						id: 'DSW_Above100Text',
						height: 20,
						xtype:'label',
						html: lang['naydeno_bolee_100_zapisey_neobhodimo_utochnit_kriterii_poiska']
				}],
				keys: [{
					fn: function(e) {
						Ext.getCmp('swOKATOSearchWindow').doSearch();
					},
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}],
				labelAlign: 'top',
				region: 'north'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				border: false,
				columns: [{
					dataIndex: 'OKATO_Code',
					header: lang['kod_okato'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'OKATO_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'DSW_OKATOSearchGrid',
				keys: {
					key: [
						Ext.EventObject.END,
						Ext.EventObject.ENTER,
						Ext.EventObject.HOME,
						Ext.EventObject.PAGE_DOWN,
						Ext.EventObject.PAGE_UP
					],
					fn: function(inp, e) {
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

						var grid = Ext.getCmp('DSW_OKATOSearchGrid');

						switch (e.getKey())
						{
							case Ext.EventObject.END:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(grid.getStore().getCount() - 1);
									grid.getSelectionModel().selectLastRow();
								}
								break;

							case Ext.EventObject.ENTER:
								grid.ownerCt.onOkButtonClick();
								break;

							case Ext.EventObject.HOME:
								if (grid.getStore().getCount() > 0)
								{
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
								break;

							case Ext.EventObject.PAGE_DOWN:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && grid.getSelectionModel().getSelected())
								{
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

									if (index + 10 <= records_count - 1)
									{
										index = index + 10;
									}
									else
									{
										index = records_count - 1;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;

							case Ext.EventObject.PAGE_UP:
								var records_count = grid.getStore().getCount();

								if (records_count > 0 && grid.getSelectionModel().getSelected())
								{
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

									if (index - 10 >= 0)
									{
										index = index - 10;
									}
									else
									{
										index = 0;
									}

									grid.getView().focusRow(index);
									grid.getSelectionModel().selectRow(index);
								}
								break;
						}
					},
					stopEvent: true
				},
				listeners: {
					'rowdblclick': function( grid, rowIndex ) {
						this.ownerCt.onOkButtonClick();
					}
				},
				region: 'center',
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'rowselect': function(sm, rowIdx, r) {
							//
						}
					}
				}),
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{name: 'OKATO_id', type: 'int'},
						{name: 'OKATO_Name', type: 'string'},
						{name: 'OKATO_Code', type: 'string'}
					],
					key: 'OKATO_id',
					sortInfo: {
						field: 'OKATO_Code'
					},
					url: C_OKATO_LIST
				}),
		       	stripeRows: true
			})]
		});
		sw.Promed.swOKATOSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
    modal: true,
	onDiagSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if (!this.findById('DSW_OKATOSearchGrid').getSelectionModel().getSelected())
        {
        	this.hide();
        	return false;
        }

		var selected_record = this.findById('DSW_OKATOSearchGrid').getSelectionModel().getSelected();

		this.onOKATOSelect({
			OKATO_Code: selected_record.data.OKATO_Code,
			OKATO_id: selected_record.data.OKATO_id,
			OKATO_Name: selected_record.data.OKATO_Name
		});

		this.hide();
	},
    plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swOKATOSearchWindow.superclass.show.apply(this, arguments);
		this.onOKATOSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.findById('DSW_Above100Text').hide();
		if ( !arguments[0] )
		{
			this.hide();
			return false;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].callback)
		{
			this.onOKATOSelect = arguments[0].callback;
		}

		this.doReset();
	},
	title: WND_SEARCH_OKATO,
	width: 800
});
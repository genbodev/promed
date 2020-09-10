/**
* swUslugaSearchWindow - окно поиска услуги по наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-25.06.2009
*/
/*NO PARSE JSON*/

sw.Promed.swUslugaSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swUslugaSearchWindow',
	objectSrc: '/jscore/Forms/Polka/swUslugaSearchWindow.js',

	buttonAlign: 'left',
    closeAction: 'hide',
	doReset: function() {
		this.findById('USW_UslugaSearchGrid').getStore().removeAll();
		this.findById('UslugaSearchForm').getForm().reset();
		this.findById('USW_Usluga_Code').focus(true, 250);
	},
	doSearch: function() {
		var grid = this.findById('USW_UslugaSearchGrid');
  		var Mask = new Ext.LoadMask(Ext.get('UslugaSearchWindow'), { msg: SEARCH_WAIT });
		var params = this.findById('UslugaSearchForm').getForm().getValues();

		if ( this.allowedCatCode )
			params.allowedCatCode = this.allowedCatCode;
			
		if ( this.allowedCodeList )
			params.allowedCodeList = this.allowedCodeList;

		if ( !params.Usluga_Name && !params.Usluga_Code ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('USW_Usluga_Name').focus(true, 250); });
			return false;
		}

		/*
		if ( !this.Usluga_date ) {
			sw.swMsg.alert(lang['soobschenie'], lang['otsutstvuet_parametr_s_datoy_okazaniya_uslugi_pri_poiske_data_okazaniya_uslugi_ne_budet_uchityivatsya'], function() {});
		}
		*/
		grid.getStore().removeAll();
		Mask.show();

		params.Usluga_date = this.Usluga_date;
		grid.getStore().load({
			params: params,
			callback: function() {
				if (grid.getStore().getCount() > 0)
				{
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
                Mask.hide();
			}
		});
	},
	draggable: true,
	height: 500,
	id: 'UslugaSearchWindow',
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
				onTabElement: 'USW_Usluga_Code',
				text: BTN_FRMCANCEL
			}],
 			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'UslugaSearchForm',
				items: [{
					enableKeyEvents: true,
					fieldLabel: lang['kod'],
					id: 'USW_Usluga_Code',
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[5].focus();
							}
						}
					},
					name: 'Usluga_Code',
					width: 200,
					xtype: 'textfield'
				}, {
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'USW_Usluga_Name',
					listeners: {
						'keydown': function (inp, e) {
							if (e.shiftKey == true && e.getKey() == Ext.EventObject.TAB)
							{
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[5].focus();
							}
						}
					},
					name: 'Usluga_Name',
					width: 	500,
					xtype: 'textfield'
				}],
				keys: [{
					key: Ext.EventObject.ENTER,
					fn: function(e) {
						Ext.getCmp('UslugaSearchWindow').doSearch();
					},
					stopEvent: true
				}],
           		// labelAlign: 'top',
				region: 'north',
				style: 'padding: 5px;'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				autoExpandMin: 300,
				border: false,
				columns: [{
					dataIndex: 'Usluga_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Usluga_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true//,
                    //width: 100
				},  {
                    dataIndex: 'Usluga_Price',
                    header: lang['stoimost'],
                    id: 'autoexpand',
                    sortable: true
                    }],
				id: 'USW_UslugaSearchGrid',
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

                    	var grid = Ext.getCmp('USW_UslugaSearchGrid');

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
					url: C_USLUGA_LIST,
					fields: [
						'Usluga_Code',
						'Usluga_id',
						'Usluga_Name',
                        'Usluga_Price'
					]
				}),
		       	stripeRows: true
			})]
		});
		sw.Promed.swUslugaSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
    modal: true,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if (!this.findById('USW_UslugaSearchGrid').getSelectionModel().getSelected())
        {
        	this.hide();
        	return false;
        }

		var selected_record = this.findById('USW_UslugaSearchGrid').getSelectionModel().getSelected();

		this.onUslugaSelect({
			Usluga_Code: selected_record.get('Usluga_Code'),
			Usluga_id: selected_record.get('Usluga_id'),
			Usluga_Name: selected_record.get('Usluga_Name'),
            Usluga_Price: selected_record.get('Usluga_Price')
		});
	},
	onUslugaSelect: Ext.emptyFn,
	Usluga_date: null,
    plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaSearchWindow.superclass.show.apply(this, arguments);

		var current_window = this;

		current_window.onHide = Ext.emptyFn;
		current_window.onUslugaSelect = Ext.emptyFn;
		current_window.Usluga_date = null;

		if ( !arguments[0] )
		{
			sw.swMsg.alert(lang['oshibka'], lang['ne_zadanyi_obyazatelnyie_parametryi'], function() { current_window.hide(); } );
			return false;
		}

		if (arguments[0].onHide)
		{
			current_window.onHide = arguments[0].onHide;
		}

		if (arguments[0].onSelect)
		{
			current_window.onUslugaSelect = arguments[0].onSelect;
		}

		if (arguments[0].Usluga_date)
		{
			current_window.Usluga_date = arguments[0].Usluga_date;
		}
		
		if (arguments[0].allowedCatCode)
		{
			current_window.allowedCatCode = arguments[0].allowedCatCode;
		}		
		
		if (arguments[0].allowedCodeList)
		{
			current_window.allowedCodeList = arguments[0].allowedCodeList;
		}

		current_window.doReset();
	},
	title: WND_SEARCH_USLUGA,
	width: 800
});
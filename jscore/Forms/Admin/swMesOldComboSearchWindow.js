/**
* swMesOldComboSearchWindow - окно поиска МЭС по диагнозу
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2010 Swan Ltd.
* @author       Rustam Salakhov
* @version      15.10.2010
*/

sw.Promed.swMesOldComboSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	doReset: function() {
		this.findById('MCSW_MesOldComboSearchGrid').getStore().removeAll();
		this.findById('MesOldComboSearchForm').getForm().reset();
		this.findById('MCSW_Diag_Name').focus(true, 250);
	},
	searchInProgress: false,
	doSearch: function() {
		if (this.searchInProgress) {
			log(lang['poisk_uje_vyipolnyaetsya']);
			return false;
		} else {
			this.searchInProgress = true;
		}
		var thisWindow = this;
		var grid = this.findById('MCSW_MesOldComboSearchGrid');
		var Mask = new Ext.LoadMask(Ext.get('MesOldComboSearchWindow'), { msg: SEARCH_WAIT });
		var params = this.findById('MesOldComboSearchForm').getForm().getValues();

		if ( !params.Diag_Name ) {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('MCSW_Diag_Name').focus(true, 250); });
			return false;
		}
		
		if (params.Diag_Name && params.Diag_Name.length < 3) {
			thisWindow.searchInProgress = false;
			sw.swMsg.alert(lang['oshibka'], lang['dlya_uspeshnogo_poiska_neobhodimo_vvesti_ne_menee_treh_simvolov'], function() { grid.ownerCt.findById('MCSW_Diag_Name').focus(true, 250); });
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();

		grid.getStore().load({
			params: params,
			callback: function() {
				thisWindow.searchInProgress = false;
				if (grid.getStore().getCount() > 0) {
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				Mask.hide();
			}
		});
	},
	draggable: true,
	height: 500,
	id: 'MesOldComboSearchWindow',
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
				onTabElement: 'MCSW_Diag_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'MesOldComboSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'MCSW_Diag_Name',
					listeners: {
						'keydown': function(inp, e) {
							if (e.getKey() == Ext.EventObject.TAB && e.shiftKey == true)
							{
								e.stopEvent();
								inp.ownerCt.ownerCt.buttons[5].focus();
							}
						}
					},
					name: 'Diag_Name',
					xtype: 'textfield'
				}],
				keys: [{
					fn: function(e) {
						Ext.getCmp('MesOldComboSearchWindow').doSearch();
					},
					key: Ext.EventObject.ENTER,
					stopEvent: true
				}],
				labelAlign: 'top',
				region: 'north',
				style: 'padding: 5px;'
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				border: false,
				columns: [{
					dataIndex: 'Mes_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Diag_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'MCSW_MesOldComboSearchGrid',
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

                        if (Ext.isIE) {
                            e.browserEvent.keyCode = 0;
                            e.browserEvent.which = 0;
                        }

                    	var grid = Ext.getCmp('MCSW_MesOldComboSearchGrid');

                        switch (e.getKey()) {
                            case Ext.EventObject.END:
                                if (grid.getStore().getCount() > 0) {
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

                        	    if (records_count > 0 && grid.getSelectionModel().getSelected()) {
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

									if (index + 10 <= records_count - 1) {
                        	    		index = index + 10;
                                    } else {
                        	    		index = records_count - 1;
                                    }

                                    grid.getView().focusRow(index);
                                    grid.getSelectionModel().selectRow(index);
                                }
                                break;

                            case Ext.EventObject.PAGE_UP:
                                var records_count = grid.getStore().getCount();

                        	    if (records_count > 0 && grid.getSelectionModel().getSelected()) {
									var index = grid.getStore().indexOf(grid.getSelectionModel().getSelected());

                        	    	if (index - 10 >= 0){
                        	    		index = index - 10;
                                    } else {
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
						'Mes_Code',
						'Mes_id',
						'Diag_Name'
					],
					url: '/?c=Mes&m=loadMesOldComboSearchList'
				}),
		       	stripeRows: true
			})]
		});
		sw.Promed.swMesOldComboSearchWindow.superclass.initComponent.apply(this, arguments);
	},
	layout: 'border',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
    modal: true,
	onMesSelect: Ext.emptyFn,
	onHide: Ext.emptyFn,
	onOkButtonClick: function() {
		if (!this.findById('MCSW_MesOldComboSearchGrid').getSelectionModel().getSelected())
        {
        	this.hide();
        	return false;
        }

		var selected_record = this.findById('MCSW_MesOldComboSearchGrid').getSelectionModel().getSelected();

		this.onMesSelect({
			Mes_Code: selected_record.data.Mes_Code,
			Mes_id: selected_record.data.Mes_id,
			Diag_Name: selected_record.data.Diag_Name
		});
	},
    plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swMesOldComboSearchWindow.superclass.show.apply(this, arguments);

		this.onMesSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] ) {
			this.hide();
			return false;
		}

		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		if (arguments[0].onSelect) {
			this.onMesSelect = arguments[0].onSelect;
		}

		this.doReset();
	},
	title: getMESAlias() + lang['poisk'],
	width: 800
});
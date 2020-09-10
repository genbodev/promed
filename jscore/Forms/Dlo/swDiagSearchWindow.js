/**
* swDiagSearchWindow - окно поиска диагноза по наименованию
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      DLO
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-01.06.2009
*/

sw.Promed.swDiagSearchWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'left',
	closeAction : 'hide',
	MKB:null,
	doReset: function() {
		this.mainGrid.getStore().removeAll();
		this.findById('DiagSearchForm').getForm().reset();
		this.findById('DSW_Diag_Name').focus(true, 250);
	},
	doSearch: function() {
		var grid = this.mainGrid;
		var Mask = new Ext.LoadMask(Ext.get('DiagSearchWindow'), { msg: SEARCH_WAIT });
		var params = this.findById('DiagSearchForm').getForm().getValues();

		if ( !params.Diag_Name )
		{
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_usloviya_poiska'], function() { grid.ownerCt.findById('DSW_Diag_Name').focus(true, 250); });
			return false;
		}
		
		if ( params.Diag_Name.length < 2 ) {
			sw.swMsg.alert(lang['oshibka'], lang['vvedite_ne_menee_dvuh_simvolov'], function() { grid.ownerCt.findById('DSW_Diag_Name').focus(true, 250); });
			return false;
		}

		grid.getStore().removeAll();
		Mask.show();
		
		if (this.isEvnDiagDopDispDiag == true)
		{
			params.isEvnDiagDopDispDiag = 1;
		}

		if (this.isHeredityDiag == true)
		{
			params.isHeredityDiag = 1;
		}

		if (this.isInfectionAndParasiteDiag == true)
		{
			params.isInfectionAndParasiteDiag = 1;
		}

		if (this.PersonRegisterType_SysNick && this.PersonRegisterType_SysNick.length > 0)
		{
			params.PersonRegisterType_SysNick = this.PersonRegisterType_SysNick;
		}

		if (this.MorbusType_SysNick && this.MorbusType_SysNick.length > 0)
		{
			params.MorbusType_SysNick = this.MorbusType_SysNick;
		}

		if (this.MorbusProfDiag_id > 0)
		{
			params.MorbusProfDiag_id = this.MorbusProfDiag_id;
		}
		
		if (this.MKB)
		{
			if(this.MKB.query){
				params.MKB = this.MKB.query;
			}
			if(this.MKB.isMain){
				params.isMain =this.MKB.isMain; 
			}
		}
		
		if (this.withGroups) {
			params.withGroups = 1;
		}
		
		if (this.formMode) {
			params.formMode = this.formMode;
		}

        if (this.registryType) {
            params.registryType = this.registryType;
        }

		if (this.checkAccessRights) {
			params.checkAccessRights = this.checkAccessRights;
		}

		if (this.filterDate) {
			params.filterDate = this.filterDate;
		}

		if (this.filterDiag) {
            params.filterDiag = Ext.util.JSON.encode(this.filterDiag);
		}

		if (this.deathDiag) {
            params.deathDiag = Ext.util.JSON.encode(this.deathDiag);
		}

		this.findById('DSW_Above100Text').hide();
		grid.setHeight(365);
		grid.getStore().load({
			params: params,
			callback: function(r, opt ) {
			
				var len = r.length;
				if ( len > 100 ) { // опа! 101 запись!
					// вывести напдись под "Найдено более 100 записей, необходимо уточнить критерии поиска"
					this.findById('DSW_Above100Text').show();
					grid.setHeight(350);
					grid.getStore().removeAt(len - 1);
					len--;
				}
				
				if (grid.getStore().getCount() > 0)
				{
					grid.getView().focusRow(0);
					grid.getSelectionModel().selectFirstRow();
				}
				Mask.hide();
				grid.ownerCt.doLayout();
			}.createDelegate(this)
		});
	},
	draggable: true,
	height: 500,
	id: 'DiagSearchWindow',
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
				onTabElement: 'DSW_Diag_Name',
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				autoHeight: true,
				border: false,
				buttonAlign: 'left',
				frame: true,
				id: 'DiagSearchForm',
				items: [{
					anchor: '100%',
					enableKeyEvents: true,
					fieldLabel: lang['naimenovanie'],
					id: 'DSW_Diag_Name',
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
				},
				{
						id: 'DSW_Above100Text',
						height: 20,
						xtype:'label',
						html: lang['naydeno_bolee_100_zapisey_neobhodimo_utochnit_kriterii_poiska']
				}],
				keys: [{
					fn: function(e) {
						Ext.getCmp('DiagSearchWindow').doSearch();
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
				hidden: true,
				columns: [{
					dataIndex: 'Diag_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Diag_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}],
				id: 'DSW_DiagSearchGrid',
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

                    	var grid = Ext.getCmp('DSW_DiagSearchGrid');

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
						'Diag_Code',
						'Diag_id',
						'Diag_Name',
						'DiagFinance_IsOms',
                        'PersonRegisterType_List',
                        'MorbusType_List',
                        'DeathDiag_IsLowChance'
					],
					url: C_DIAG_LIST
				}),
		       	stripeRows: true
			}),
			new Ext.grid.GridPanel({
				autoExpandColumn: 'autoexpand',
				border: false,
				hidden: true,
				height: 365,
				columns: [{
					dataIndex: 'Diag_Code',
					header: lang['kod'],
					sortable: true,
					width: 100
				}, {
					dataIndex: 'Diag_Name',
					header: lang['naimenovanie'],
					id: 'autoexpand',
					sortable: true
				}, {
					dataIndex: 'DeathDiag_IsLowChance',
					hidden: true,
					sortable: true
				}],
				id: 'DSW_DeathDiagSearchGrid',
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

                    	var grid = Ext.getCmp('DSW_DeathDiagSearchGrid');

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
				region: 'south',
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true,
					listeners: {
						'rowselect': function(sm, rowIdx, r) {
							//
						}
					}
				}),
				store: new Ext.data.GroupingStore({
					autoLoad: false,
					fields: [
						'Diag_Code',
						'Diag_id',
						'Diag_Name',
						'DiagFinance_IsOms',
                        'PersonRegisterType_List',
                        'MorbusType_List',
                        'DeathDiag_IsLowChance'
					],
					sortInfo: {
						field: 'Diag_Code',
						direction: 'ASC'
					},
					groupField: 'DeathDiag_IsLowChance',
					reader: new Ext.data.JsonReader({
						id: 'Diag_id',
					}, [{
						mapping: 'Diag_id',
						name: 'Diag_id'
					}, {
						mapping: 'Diag_id',
						name: 'Diag_id'
					}, {
						mapping: 'Diag_Code',
						name: 'Diag_Code'
					}, {
						mapping: 'Diag_Name',
						name: 'Diag_Name'
					}, {
						mapping: 'DiagFinance_IsOms',
						name: 'DiagFinance_IsOms'
					}, {
						mapping: 'PersonRegisterType_List',
						name: 'PersonRegisterType_List'
					}, {
						mapping: 'MorbusType_List',
						name: 'MorbusType_List'
					}, {
						mapping: 'DeathDiag_IsLowChance',
						name: 'DeathDiag_IsLowChance'
					}]),
					url: C_DIAG_LIST
				}),
				view: new Ext.grid.GroupingView({
					groupTextTpl: '{[values.gvalue == 2 ? "Маловероятные диагнозы" : "Основные диагнозы"]}'
				}),
		       	stripeRows: true
			})]
		});
		sw.Promed.swDiagSearchWindow.superclass.initComponent.apply(this, arguments);
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
		if (!this.mainGrid.getSelectionModel().getSelected())
        {
        	this.hide();
        	return false;
        }

		var selected_record = this.mainGrid.getSelectionModel().getSelected();

		this.onDiagSelect({
			Diag_Code: selected_record.data.Diag_Code,
			Diag_id: selected_record.data.Diag_id,
			Diag_Name: selected_record.data.Diag_Name,
			DiagFinance_IsOms: selected_record.data.DiagFinance_IsOms,
            PersonRegisterType_List: selected_record.data.PersonRegisterType_List,
			MorbusType_List: selected_record.data.MorbusType_List,
			DeathDiag_IsLowChance: selected_record.data.DeathDiag_IsLowChance
		});
	},
    plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swDiagSearchWindow.superclass.show.apply(this, arguments);
		this.MKB=null;
		this.isEvnDiagDopDispDiag = false;
		this.isHeredityDiag = false;
		this.isInfectionAndParasiteDiag = false;
		this.onDiagSelect = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.checkAccessRights = false;
		this.filterDate = null;
		this.filterDiag = null;
		this.deathDiag = null;
		this.mainGrid = null;
		this.findById('DSW_Above100Text').hide();
		this.findById('DSW_DiagSearchGrid').hide();
		this.findById('DSW_DeathDiagSearchGrid').hide();
		
		if ( !arguments[0] )
		{
			this.hide();
			return false;
		}

		if (arguments[0].filterDate)
		{
			this.filterDate = arguments[0].filterDate;
		}

		if (arguments[0].isEvnDiagDopDispDiag === true)
		{
			this.isEvnDiagDopDispDiag = true;
		}

		if (arguments[0].isHeredityDiag === true)
		{
			this.isHeredityDiag = true;
		}

		if (arguments[0].isInfectionAndParasiteDiag === true)
		{
			this.isInfectionAndParasiteDiag = true;
		}

		if (arguments[0].onHide)
		{
			this.onHide = arguments[0].onHide;
		}
		
		if (arguments[0].MKB)
		{
			this.MKB = arguments[0].MKB;
		}

		if (arguments[0].onSelect)
		{
			this.onDiagSelect = arguments[0].onSelect;
		}

		this.PersonRegisterType_SysNick = arguments[0].PersonRegisterType_SysNick || '';
		this.MorbusType_SysNick = arguments[0].MorbusType_SysNick || '';

		if (arguments[0].MorbusProfDiag_id)
		{
			this.MorbusProfDiag_id = arguments[0].MorbusProfDiag_id;
		}
		else
		{
			this.MorbusProfDiag_id = null;
		}
		
		if (arguments[0].withGroups)
		{
			this.withGroups = arguments[0].withGroups;
		}
		else
		{
			this.withGroups = false;
		}
		
		if (arguments[0].formMode)
		{
			this.formMode = arguments[0].formMode;
		}
		else
		{
			this.formMode = '';
		}

        if (arguments[0].registryType)
        {
            this.registryType = arguments[0].registryType;
        }

        if (arguments[0].checkAccessRights)
        {
            this.checkAccessRights = arguments[0].checkAccessRights;
        }

		if (arguments[0].filterDiag)
		{
			this.filterDiag = arguments[0].filterDiag;
		}

		if (arguments[0].deathDiag){
			this.deathDiag = arguments[0].deathDiag;	
			this.findById('DSW_DeathDiagSearchGrid').show();
			this.findById('DSW_DeathDiagSearchGrid').ownerCt.doLayout();
			this.mainGrid = this.findById('DSW_DeathDiagSearchGrid');
		} else {
			this.findById('DSW_DiagSearchGrid').show();
			this.findById('DSW_DiagSearchGrid').ownerCt.doLayout();
			this.mainGrid = this.findById('DSW_DiagSearchGrid');
		}

		this.doReset();
	},
	title: WND_SEARCH_DIAG,
	width: 800
});
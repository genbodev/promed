/**
* swSmpWaybillsEditWindow Учет путевых листов и ГСМ
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      21.01.2013
*/

sw.Promed.swSmpWaybillsViewWindow = Ext.extend(sw.Promed.BaseForm,{
	
	width: 900,
	
	height: 400,
	
	modal: true,
	
	resizable: true,
	
	//autoHeight: true,
	
	plain: false,
	
	onCancel: Ext.emptyFn,
	
	callback: Ext.emptyFn,
	
	listeners: {},
	
	waybillPanelAutoLoad: true, // Автозагрузка waybillGridPanel
	
	id: 'swSmpWaybillsViewWindow',
	
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event){ ShowHelp(this.ownerCt.title); }
		}, {
			text      : BTN_FRMCLOSE,
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function(){ this.ownerCt.hide(); }
		}
	],
	
	initActions: function(){
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: lang['period'],
			plugins: [ new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false) ]
		});
		
		this.dateMenu.addListener('keydown',function(inp,e){
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.doSearch('period');
			}
		}.createDelegate(this));
		
		this.dateMenu.addListener('select',function(){
			this.doSearch('period');
		}.createDelegate(this));
		
		this.formActions = new Array();

		this.formActions.selectDate = new Ext.Action({ text: '' });
		
		// Один период назад
		this.formActions.prev = new Ext.Action({
			text: lang['predyiduschiy'],
			xtype: 'button',
			iconCls: 'arrow-previous16',
			handler: function(){
				this.prevDay();
				this.doSearch('range');
			}.createDelegate(this)
		});
		
		// Один период вперед
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function(){
				this.nextDay();
				this.doSearch('range');
			}.createDelegate(this)
		});
		
		// Период за день
		this.formActions.day = new Ext.Action({
			text: lang['den'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-day16',
			handler: function(){
				this.currentDay();
				this.doSearch('day');
			}.createDelegate(this)
		});
		
		// Период неделя
		this.formActions.week = new Ext.Action({
			text: lang['nedelya'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-week16',
			pressed: true,
			handler: function(){
				this.currentWeek();
				this.doSearch('week');
			}.createDelegate(this)
		});
		
		// Период месяц
		this.formActions.month = new Ext.Action({
			text: lang['mesyats'],
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-month16',
			handler: function(){
				this.currentMonth();
				this.doSearch('month');
			}.createDelegate(this)
		});
		
		// Период
		this.formActions.range = new Ext.Action({
			text: lang['period'],
			disabled: true,
			xtype: 'button',
			toggleGroup: 'periodToggle',
			iconCls: 'datepicker-range16',
			handler: function(){
				this.doSearch('range');
			}.createDelegate(this)
		});
	},
	stepDay: function(day){
		var frm = this;
		var date1 = (this.dateMenu.getValue1() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (this.dateMenu.getValue2() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	prevDay: function(){
		this.stepDay(-1);
	},
	nextDay: function(){
		this.stepDay(1);
	},
	currentDay: function(){
		var date1 = Date.parseDate(this.curDate, 'd.m.Y');
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentWeek: function(){
		var date1 = (Date.parseDate(this.curDate, 'd.m.Y'));
		var dayOfWeek = (date1.getDay() + 6) % 7;
		date1 = date1.add(Date.DAY, -dayOfWeek).clearTime();
		var date2 = date1.add(Date.DAY, 6).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	currentMonth: function(){
		var date1 = (Date.parseDate(frm.curDate, 'd.m.Y')).getFirstDateOfMonth();
		var date2 = date1.getLastDateOfMonth();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	getPeriodToggle: function(mode){
		switch(mode){
			case 'day':
				return this.WindowToolbar.items.items[9];
			break;
			case 'week':
				return this.WindowToolbar.items.items[10];
			break;
			case 'month':
				return this.WindowToolbar.items.items[11];
			break;
			case 'range':
				return this.WindowToolbar.items.items[12];
			break;
		}
		return null;
	},
	doSearch: function(mode){
		var btn = this.getPeriodToggle(mode);
		if ( btn ) {
			if ( mode == 'range') {
				btn.toggle(true);
				this.mode = mode;
			} else if ( this.mode == mode ) {
				btn.toggle(true);
				// чтобы при повторном открытии тоже происходила загрузка списка записанных на эту неделю
				if ( mode != 'week' ) {
					return false;
				}
			} else {
				this.mode = mode;
			}
		}
		var params = {};
		params.dateStart = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.dateFinish = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		this.findById( this.id + 'GridPanel' ).removeAll({clearAll: true});
		this.findById( this.id + 'GridPanel' ).loadData({globalFilters: params});
	},
	getCurrentDateTime: function(){
		if ( getGlobalOptions().date ) {
			this.curDate = getGlobalOptions().date;
			this.mode = 'week';
			this.currentWeek();
			if ( this.gridPanelAutoLoad ) {
				this.doSearch( this.mode );
			}
		} else {
			var obj = this;
			obj.getLoadMask( LOAD_WAIT ).show();
			Ext.Ajax.request({
				url: C_LOAD_CURTIME,
				callback: function(opt, success, response){
					if ( success && response.responseText != '' ) {
						var result  = Ext.util.JSON.decode(response.responseText);
						obj.curDate = result.begDate;
						obj.mode = 'week';
						obj.currentWeek();
						if ( obj.gridPanelAutoLoad ) {
							obj.doSearch( obj.mode );
						}
					}
					obj.getLoadMask().hide();
				}.createDelegate(this)
			});
		}
	},
	
	focusSelectedRow: function(){
		var GridPanel = this.findById( this.id + 'GridPanel' );
		var record = GridPanel.getSelectionModel().getSelected();
		if( !record ) {
			return false;
		}
		
		var idx = GridPanel.getStore().indexOf( record );
		var row = GridPanel.getView().getRow( idx );
		GridPanel.getView().focusRow( row );
	},
	
	printWaybill: function() {		
		var GridPanel = this.findById(this.id+'GridPanel');
		var record = GridPanel.getSelectionModel().getSelected();
		if( !record ) {
			sw.swMsg.alert( lang['oshibka'], lang['ne_vyibran_putevoy_list'] );
			return false;
		}
		
		var Waybill_id = record.get('Waybill_id');
		if ( !Waybill_id ) {
			sw.swMsg.alert( lang['oshibka'], lang['ne_udalos_poluchit_identifikator_putevogo_lista'] );
			return false;
		}
		
		window.open('/?c=Waybill&m=printWaybill&Waybill_id='+Waybill_id, 'print_waybill_'+Waybill_id);
	},

	initComponent: function(){
		
		this.initActions();
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev,
				{ xtype: 'tbseparator' },
				this.dateMenu,
				{ xtype: 'tbseparator' },
				this.formActions.next, 
				//{ xtype: 'tbfill' },
				{ xtype: 'tbseparator' },
				this.formActions.range,
				this.formActions.day,
				this.formActions.week, 
				this.formActions.month,
				{ xtype: 'tbseparator' }
			]
		});
		
		var gridFields = [
			{dataIndex: 'Waybill_id', header: 'ID', key: true, hidden: true, hideable: false},
			{dataIndex: 'Waybill_Num', header: lang['nomer'], width: 100},
			{dataIndex: 'Waybill_Date', header: lang['data'], width: 100},
			{dataIndex: 'EmergencyTeam_Driver', header: lang['voditel'], width: 300},
			{dataIndex: 'EmergencyTeam_CarNum', header: lang['nomer_mashinyi'], width: 150}
		];
		
		var storeFields = [];
		for( var i=0, cnt=gridFields.length; i<cnt; i++ ){
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex
			});
		}
		
		var gridStore = new Ext.data.Store({
			autoLoad: false,
			sortInfo: {
				field: 'Waybill_Date',
				direction: 'DESC'
			},
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=Waybill&m=loadWaybillGrid'
		});
		
		var gridActions = [
			{ name: 'action_add', iconCls: 'add16', text: lang['dobavit'], handler: this.openSmpWaybillsEditWindow.createDelegate(this,['add']) },
			{ name: 'action_view', iconCls: 'view16', text: lang['prosmotr'], handler: this.openSmpWaybillsEditWindow.createDelegate(this,['view']), disabled: true },
			{ name: 'action_edit', iconCls: 'edit16', text: lang['izmenit'], handler: this.openSmpWaybillsEditWindow.createDelegate(this, ['edit']), disabled: true },
			//{ name: 'action_delete', iconCls: 'delete16', text: 'Удалить', handler: this.deleteWaybill.createDelegate(this), disabled: true },
			{ name: 'action_refresh', iconCls: 'refresh16', text: lang['obnovit'], handler: function(){ this.autoEvent=false; this.doSearch(); }.createDelegate(this) },
			{ name: 'action_print', iconCls: 'print16', text: lang['pechat_pl'], handler: this.printWaybill.createDelegate(this) }
		];

		var GridPanel = new Ext.grid.GridPanel({
			id: this.id + 'GridPanel',
			//autoHeight: true,
			loadMask: { msg: LOAD_WAIT },
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			store: gridStore,
			loadData: function( params ){
				with(this.getStore()){
					removeAll();
					baseParams = params.globalFilters;
					load();
				}
			},
			actions: gridActions,
			getAction: function(action) {
				return this.ViewActions[action] || null;
			},
			tbar: new Ext.Toolbar(),
			listeners: {
				render: function() {
					this.contextMenu = new Ext.menu.Menu();
					this.ViewActions = {};
					for( var i=0, cnt=this.actions.length; i<cnt; i++ ){
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(this.actions[i]);
						this.getTopToolbar().add(this.ViewActions[this.actions[i]['name']]);
						this.contextMenu.add(this.ViewActions[this.actions[i]['name']]);
					}
				},
				rowcontextmenu: function(grd,num,e){
					e.stopEvent();
					this.getSelectionModel().selectRow(num);
					this.contextMenu.showAt(e.getXY());
				}
			}
		});
		
		var form = this;
		
		GridPanel.getStore().on('load', function(store, rs) {
			if( store.getCount() ) {
				this.getSelectionModel().selectFirstRow();
				form.focusSelectedRow();
			}
			this.getAction('action_view').setDisabled( !this.getSelectionModel().hasSelection() );
			this.getAction('action_edit').setDisabled( !this.getSelectionModel().hasSelection() );
		}.createDelegate(GridPanel));
	
		GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			this.getAction('action_edit').setDisabled( rec.get('Waybill_id') == null );
			//this.getAction('action_delete').setDisabled( rec.get('Waybill_id') == null );
		}.createDelegate(GridPanel));

		GridPanel.on('rowdblclick', function() {
			var noedit = this.getAction('action_edit').isDisabled();
			this.getAction( noedit ? 'action_view' : 'action_edit' ).execute();
		}.createDelegate(GridPanel));


		Ext.apply(this,{
			xtype: 'panel',
			title: lang['uchet_putevyih_listov_i_gsm'],
			layout: 'fit',
			border: false,
			hidden: false,
			items: [ GridPanel ],
			tbar: this.WindowToolbar

		});
		
		sw.Promed.swSmpWaybillsViewWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
        sw.Promed.swSmpWaybillsViewWindow.superclass.show.apply(this, arguments);
	   
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		this.getCurrentDateTime();

		this.center();
		
		this.doSearch();
	},
	
	openSmpWaybillsEditWindow: function(action){
		
		if ( !action || !action.toString().inlist(['add','edit','view']) ) {
			return false;
		}

		var wnd = 'swSmpWaybillsEditWindow';
		var wndObj = getWnd(wnd);
		
		if ( wndObj.isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_putevyih_listov_uje_otkryito']);
			return false;
		}
		
		var params = {
			action: action,
			callback: function(data){
				if ( typeof data != 'object' ) {
				//	return false;
				}
				this.doSearch();
				this.findById( this.id + 'GridPanel' ).getStore().reload();
			}.createDelegate(this)
		}
		
		if ( action == 'view' || action == 'edit' ) {
			var grid = this.findById( this.id + 'GridPanel' );
			var selection = grid.getSelectionModel();
			if ( selection.getCount() < 1 ) {
				sw.swMsg.alert( lang['oshibka'], lang['ni_odin_putevoy_list_ne_vyibran'] );
				return false;
			} else if ( selection.getCount() > 1 ) {
				sw.swMsg.alert( lang['oshibka'], lang['vyiberite_tolko_odin_putevoy_list'] );
				return false;
			}
			
			var record = selection.getSelected();
			
			params.Waybill_id = record.get('Waybill_id');
			if ( !params.Waybill_id ) {
				sw.swMsg.alert( lang['oshibka'], lang['ne_udalos_poluchit_identifikator_putevogo_lista'] );
				return false;
			}
		}
				
		wndObj.show(params);
		
	},
	
	deleteWaybills: function(){
		alert('under construction delete');
		return;
	}
});
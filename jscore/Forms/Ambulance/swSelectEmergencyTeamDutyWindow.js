/**
* swSelectEmergencyTeamDutyWindow - форма выбора бригады
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2012 Swan Ltd.
* @author       Miyusov Alexandr
* @version      17.10.2012
*/





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

sw.Promed.swSelectEmergencyTeamDutyWindow = Ext.extend(sw.Promed.BaseForm,{
	width: 900,
	height: 400,
	modal: true,
	resizable: true,
	//autoHeight: true,
	plain: false,
	onCancel: Ext.emptyFn,
	callback: Ext.emptyFn,
	listeners: {},
	//waybillPanelAutoLoad: true, // Автозагрузка waybillGridPanel
	id: 'swSelectEmergencyTeamDutyWindow',
	
	initActions: function(){
		this.dateMenu = new Ext.form.DateRangeField({
			width: 150,
			fieldLabel: '',
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
				this.prevMonth();
				this.doSearch('range');
			}.createDelegate(this)
		});
		
		// Один период вперед
		this.formActions.next = new Ext.Action({
			text: lang['sleduyuschiy'],
			xtype: 'button',
			iconCls: 'arrow-next16',
			handler: function(){
				this.nextMonth();
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
	stepMonth: function(month){
		var frm = this;
		var date1 = (this.dateMenu.getValue1() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.MONTH, month).clearTime();
		var date2 = (this.dateMenu.getValue2() || Date.parseDate(this.curDate, 'd.m.Y')).add(Date.MONTH, month).clearTime();
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},
	prevMonth: function(){
	//	this.stepDay(-1);
		this.stepMonth(-1);
	},
	nextMonth: function(){
	
		this.stepMonth(1);
	},
	currentDay: function(){
		var date1 = Date.parseDate(this.curDate, 'd.m.Y').add(Date.MONTH, -1).clearTime();
		var date2 = Date.parseDate(this.curDate, 'd.m.Y');
		this.dateMenu.setValue(Ext.util.Format.date(date1, 'd.m.Y')+' - '+Ext.util.Format.date(date2, 'd.m.Y'));
	},

	getPeriodToggle: function(mode){
		switch(mode){
			
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
			this.mode = 'range';
			this.currentDay();
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
						obj.mode = 'range';
						obj.currentDay();
						if ( obj.gridPanelAutoLoad ) {
							obj.doSearch( obj.mode );
						}
					}
					obj.getLoadMask().hide();
				}.createDelegate(this)
			});
		}
	},
	
	selectEmergencyDutyTeam: function() {
		var record = this.findById( this.id + 'GridPanel' ).getSelectionModel().getSelected();
		var parent = this;
		if(!record) return false;
		log(record);
		
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamSession',
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id')				
			},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					
					
					
					this.callback(
						record.get('EmergencyTeamDuty_DTStart')
					);
					this.hide();
				}
			}.createDelegate(this)
		});
	},	
	
	selectOnEmergencyDutyTeam: function() {
		var record = this.findById( this.id + 'GridPanel' ).getSelectionModel().getSelected();
		var parent = this;
		if(!record) return false;
				
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComing',
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
				EmergencyTeamDuty_isComesToWork: 2
			},
			callback: function(o, s, r) {				
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) parent.findById( this.id + 'GridPanel' ).getStore().load();					
				}
			}.createDelegate(this)
		});
	},	
	
	selectOffEmergencyDutyTeam: function() {
		var record = this.findById( this.id + 'GridPanel' ).getSelectionModel().getSelected();
		var parent = this;
		if(!record) return false;
				
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComing',
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
				EmergencyTeamDuty_isComesToWork: 1
			},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) parent.findById( this.id + 'GridPanel' ).getStore().load();
				}
			}.createDelegate(this)
		});
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
	
	changeRenderer: function(val) {		
        if (val == 'true') 
			return '<span style="font-size:16px;">V</span>';
        else 
			return '';
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
				{ xtype: 'tbseparator' }
			]
		});
		
		var gridFields = [
			{ header: lang['id_smenyi'], dataIndex: 'EmergencyTeamDuty_id', key: true, hidden: true, hideable: false },
			{ header: lang['id_brigadyi'], dataIndex: 'EmergencyTeam_id', hidden: true, hideable: false },
			{ header: lang['brigada_№'], dataIndex: 'EmergencyTeam_Num', width:80 },
			{ header: lang['nachalo_smenyi'], dataIndex: 'EmergencyTeamDuty_DTStartVis', width:120 },
			{ header: lang['konets_smenyi'], dataIndex: 'EmergencyTeamDuty_DTFinishVis', width:120 },
			{ header: lang['nachalo'], dataIndex: 'EmergencyTeamDuty_DTStart', hidden: true, width:120 },
			{ header: lang['konets'], dataIndex: 'EmergencyTeamDuty_DTFinish', hidden: true, width:120 },
			{ header: lang['profil'], dataIndex: 'EmergencyTeamSpec_Name', width:240 },
			{ header: lang['vyihod'],dataIndex: 'EmergencyTeamDuty_isComesToWork', renderer: this.changeRenderer, width:100},				
			{ header: lang['zakryita'],dataIndex: 'EmergencyTeamDuty_isClose', renderer: this.changeRenderer, width:100}	
		];
		
//		var gridFields = [
//			{dataIndex: 'Waybill_id', header: 'ID', key: true, hidden: true, hideable: false},
//			{dataIndex: 'Waybill_Num', header: 'Номер', width: 100},
//			{dataIndex: 'Waybill_Date', header: 'Дата', width: 100},
//			{dataIndex: 'EmergencyTeam_Driver', header: 'Водитель', width: 300},
//			{dataIndex: 'EmergencyTeam_CarNum', header: 'Номер машины', width: 150}
//		];
		
		var storeFields = [];
		for( var i=0, cnt=gridFields.length; i<cnt; i++ ){
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex
			});
		}
		
		var gridStore = new Ext.data.Store({
			autoLoad: false,
//			sortInfo: {
//				field: 'EmergencyTeamDuty_DTStart',
//				direction: 'DESC'
//			},
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			//url: '/?c=Waybill&m=loadWaybillGrid'
			url: '/?c=EmergencyTeam&m=loadEmergencyTeamByMedPersonal'
		});
		
		
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
			
			tbar: new Ext.Toolbar(),
			listeners: {
				render: function() {
					
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
		}.createDelegate(GridPanel));
	
		GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			
		}.createDelegate(GridPanel));

		GridPanel.on('rowdblclick', function() {
			
		}.createDelegate(GridPanel));


		Ext.apply(this,{
			xtype: 'panel',
			title: lang['smenyi'],
			layout: 'fit',
			border: false,
			hidden: false,
			buttons: [
			'-',
			{
				text: lang['vyibrat'],
				iconCls: 'ok16',
				handler: this.selectEmergencyDutyTeam.createDelegate(this)
			},
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
			}],
			items: [ GridPanel ],
			tbar: this.WindowToolbar

		});
		
		
		sw.Promed.swSelectEmergencyTeamDutyWindow.superclass.initComponent.apply(this, arguments);
	},
	
	show: function(){
        sw.Promed.swSelectEmergencyTeamDutyWindow.superclass.show.apply(this, arguments);
	   
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		this.getCurrentDateTime();

		this.center();
		
		this.doSearch();
	}
	
});




/*
sw.Promed.swSelectEmergencyTeamDutyWindow = Ext.extend(sw.Promed.BaseForm, {
	
	modal: true,
	width: 800,
	height: 300,	
	resizable: false,
	plain: false,
	closable: false,
	title: lang['spisok_smen_vracha'],
	callback: Ext.emptyFn,
	onDoCancel: Ext.emptyFn,
		
	// id врача
	MedPersonal_id: null,
	
	listeners: {
		hide: function() {
			this.findById( this.id + '_Grid' ).getStore().removeAll();
		},
		
		destroy: function(){
		}
	},
	
	selectOnEmergencyDutyTeam: function() {
		var record = this.findById( this.id + '_Grid' ).getSelectionModel().getSelected();
		var parent = this;
		if(!record) return false;
		log(record);
		
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComing',
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
				EmergencyTeamDuty_isComesToWork: 2
			},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) parent.findById( this.id + '_Grid' ).getStore().load();					
				}
			}.createDelegate(this)
		});
	},
	
	
	selectOffEmergencyDutyTeam: function() {
		var record = this.findById( this.id + '_Grid' ).getSelectionModel().getSelected();
		var parent = this;
		if(!record) return false;
		log(record);
		
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComing',
			params: {
				EmergencyTeam_id: record.get('EmergencyTeam_id'),
				EmergencyTeamDuty_id: record.get('EmergencyTeamDuty_id'),
				EmergencyTeamDuty_isComesToWork: 1
			},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) parent.findById( this.id + '_Grid' ).getStore().load();
				}
			}.createDelegate(this)
		});
	},
	
	
	
	onCancel: function() {
		this.onDoCancel();
		this.hide();
	},
	
	changeRenderer: function(val) {		
        if (val == 'true') 
			return '<span style="font-size:16px;">V</span>';
        else 
			return '';
	},
	
	initComponent: function() {
		this.initActions();
		
		this.WindowToolbar = new Ext.Toolbar({
			items: [
				this.formActions.prev,
				{ xtype: 'tbseparator' },
				this.dateMenu,
				{ xtype: 'tbseparator' },
				this.formActions.next, 
				{ xtype: 'tbseparator' }
			]
		});
		
		var items = [];
		var parentObject = this;
		//var params = new Object();
		//params.MedPersonal_id = this.MedPersonal_id;		
		//log(params);
		
		
		var gridFields = [
			{ header: lang['id_smenyi'], name: 'EmergencyTeamDuty_id', key: true, hidden: true, hideable: false },
			{ header: lang['id_brigadyi'], name: 'EmergencyTeam_id', hidden: true, hideable: false },
			{ header: lang['brigada_№'], name: 'EmergencyTeam_Num', width:80 },
			{ header: lang['nachalo_smenyi'], name: 'EmergencyTeamDuty_DTStart', width:120 },
			{ header: lang['konets_smenyi'], name: 'EmergencyTeamDuty_DTFinish', width:120 },
			{ header: lang['profil'], name: 'EmergencyTeamSpec_Name', width:240 },
			{ header: lang['vyihod'],name: 'EmergencyTeamDuty_isComesToWork',  renderer: this.changeRenderer, width:100},				
			{ header: lang['zakryita'],name: 'EmergencyTeamDuty_isClose',  renderer: this.changeRenderer, width:100}	
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
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=EmergencyTeam&m=loadEmergencyTeamByMedPersonal'
		});
		
		this.GridPanel = new  Ext.grid.GridPanel({
			
			
			id: this.id + '_Grid',		
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
			}
			
			
			
//			id: this.id + '_Grid',
//			toolbar: false,
//			height: 230,			
//			border: false,
//			autoLoadData: false,
//			stripeRows: true, 
//			loadData: function( params ){
//				with(this.getStore()){
//					removeAll();
//					baseParams = params.globalFilters;
//					load();
//				}
//			},
//			colModel: new Ext.grid.ColumnModel({
//				columns: gridFields
//			}),
//			stringfields: [
//				{ header: 'ID смены', name: 'EmergencyTeamDuty_id', key: true, hidden: true, hideable: false },
//				{ header: 'ID бригады', name: 'EmergencyTeam_id', hidden: true, hideable: false },
//				{ header: 'Бригада №', name: 'EmergencyTeam_Num', width:80 },
//				{ header: 'Начало смены', name: 'EmergencyTeamDuty_DTStart', width:120 },
//				{ header: 'Конец смены', name: 'EmergencyTeamDuty_DTFinish', width:120 },
//				{ header: 'Профиль', name: 'EmergencyTeamSpec_Name', width:240 },
//				{ header: 'Выход',name: 'EmergencyTeamDuty_isComesToWork',  renderer: this.changeRenderer, width:100},				
//				{ header: 'Закрыта',name: 'EmergencyTeamDuty_isClose',  renderer: this.changeRenderer, width:100}				
//			],
		//	dataUrl: '/?c=EmergencyTeam&m=loadEmergencyTeamByMedPersonal',
		//	store: gridStore
		//	totalProperty: 'totalCount'
		});
		
		items.push( this.GridPanel );
		
		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: lang['vyiyti_v_vyibrannuyu_smenu'],
				iconCls: 'ok16',
				handler: this.selectOnEmergencyDutyTeam.createDelegate(this)
			}, {
				text: lang['otmenit_vyihod'],
				iconCls: 'ok16',
				handler: this.selectOffEmergencyDutyTeam.createDelegate(this)
			}, 
			'-',
			{
				text: lang['zakryit'],
				iconCls: 'close16',
				handler: this.onCancel.createDelegate(this)
			}],
			tbar: this.WindowToolbar,
			items: [{
//						fieldLabel: 'Дата',
//						name: 'Range',
//						plugins: [
//							new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
//						],						
//						width: 200,
//						xtype: 'daterangefield'
//					},{
				layout: 'column',
				autoHeight: true,
				items: [items]							
			}]
		});
		
		
		this.findById( this.id + '_Grid' ).on('rowclick',function(){
			//parentObject.directionFromEmergencyTeamToEmergencyAddress();
		})
		
		sw.Promed.swSelectEmergencyTeamDutyWindow.superclass.initComponent.apply(this, arguments);
	},
	
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
	
	getPeriodToggle: function(mode){
		switch(mode){			
			case 'range':
				return this.WindowToolbar.items.items[12];
			break;
		}
		return null;
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
	
	getCurrentDateTime: function(){	
		this.curDate = getGlobalOptions().date;			
	},
	
	doSearch: function(mode){
		var params = {};
		params.dateStart = Ext.util.Format.date(this.dateMenu.getValue1(), 'd.m.Y');
		params.dateFinish = Ext.util.Format.date(this.dateMenu.getValue2(), 'd.m.Y');
		log(params);		
		this.findById( this.id + '_Grid' ).getStore().removeAll();
		this.findById( this.id + '_Grid' ).getStore().loadData({globalFilters: params});
	},
	
	show: function() {
        sw.Promed.swSelectEmergencyTeamDutyWindow.superclass.show.apply(this, arguments);
		
	   // this.setTitle('Выбор бригады вызова');
		
		this.doLayout();
		this.restore();
		this.center();
		
		var parentObj = this;		
		
		if( arguments[0].callback && getPrimType(arguments[0].callback) == 'function' ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].onDoCancel && getPrimType(arguments[0].onDoCancel) == 'function' ) {
			this.onDoCancel = arguments[0].onDoCancel;
		}
		this.getCurrentDateTime();
		
		
		this.findById( this.id + '_Grid' ).getStore().load();
	}
	
});





*/
/**
* swWorkPlaceSMPHeadDutyWindow АРМ старшего смены СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      09.2012
*/

sw.Promed.swWorkPlaceSMPHeadDutyWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
	useUecReader: true,
	id: 'swWorkPlaceSMPHeadDutyWindow',
	
	ARMType: null, 

	buttons: [
		'-',
		{	text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		},
		{	text      : lang['zakryit'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}	
		}
	],
	
	listeners: {
		hide: function() {
			this.stopTask();
			if( this.interval2 ) {
				clearInterval(this.interval2);
				delete this.interval2;
			}
		}
	},
	
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['pojaluysta_jdite'] });
		}

		return this.loadMask;
	},
	
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist(['view']) ) {
			return false;
		}

		var wnd = 'swCmpCallCardShortEditWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_prosmotra_kartyi_vyizova_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel;
		var params = new Object();

		params.action = action;
		params.callback = function(data){
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}

			// Обновить grid
			grid.getStore().reload();
			this.autoEvent = false;
		}.createDelegate(this);

		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}

		var selected_record = grid.getSelectionModel().getSelected();

		if ( !selected_record.get('CmpCallCard_id') ) {
			return false;
		}

		formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');

		params.onHide = function() {
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	
	startTimer: function() {	
		var topTitle = this.GridPanel;
		setInterval(function(){			
			date = new Date(), 
			d = date.getDate(),
			mo = date.getMonth()+1,
			y = date.getFullYear(),
			h = date.getHours(), 
			m = date.getMinutes(), 
			s = date.getSeconds(), 
			d = (d < 10) ? '0' + d : d, 
			mo = (mo < 10) ? '0' + mo : mo, 
			h = (h < 10) ? '0' + h : h, 
			m = (m < 10) ? '0' + m : m, 
			s = (s < 10) ? '0' + s : s,
			topTitle.setTitle('АРМ старшего смены СМП. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
		 }, 1000); 
	},		
	
	buttonPanelActions: {
		action_messages: {
			iconCls: 'messages48',
			tooltip: lang['sistema_soobscheniy'],
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}
		},
		
		action_swSmpFarmacyRegisterWindow: {
			iconCls: 'dlo32',
			tooltip: lang['uchet_lekarstvennyih_sredstv'],			
			handler: function(){
				var params = new Object;
				var wnd = getWnd('swWorkPlaceSMPHeadDutyWindow');				
				params.ARMType = wnd.ARMType;				
				//getWnd('swSmpFarmacyRegisterWindow').show({ARMType: this.ownerCt.ownerCt.ownerCt.ARMType.toString()});
				getWnd('swSmpFarmacyRegisterWindow').show(params);
			}
		},
			
		// Просмотр списка бригад: номер, СС, статус, карта
		action_emergencyteammanagment: {
			iconCls: 'emergency-list32',
			tooltip: lang['upravlenie_brigadami'],
			handler: function() {
				var params = new Object;
				var wnd = getWnd('swWorkPlaceSMPHeadDutyWindow');
				params.ARMType = wnd.ARMType;
				getWnd('swSmpEmergencyTeamOperEnvWindow').show(params);
			}
		},
		
		// Просмотр оперативной обстановки по диспетчерам
		action_dispatchoperenv: {
			iconCls: 'users32',
			tooltip: lang['operativnaya_obstanovka_po_dispetcheram'],
			handler: function() {
				getWnd('swDispatchOperEnvWindow').show();
			}
		},
		
		// Учет путевых листов и ГСМ
		action_waybill: {
			iconCls: 'reports32',
			tooltip: lang['uchet_putevyih_listov_i_gsm'],
			handler: function() {
				getWnd('swSmpWaybillsViewWindow').show();
			}
		},
		
		// Редактирование справочника неформализованных адресов СМП
		action_unformalizedaddress: {
			iconCls: 'reports32',
			tooltip: lang['redaktirovanie_spravochnika_neformalizovannyih_adresov_smp'],
			handler: function() {
				getWnd('swUnformalizedAddressDirectoryEditWindow').show();
			}
		},
		
		// Связка бригады скорой помощи
		action_emergency_team_wialon_rel: {
			iconCls: 'doubles32',
			tooltip: lang['privyazka_brigad_wialon'],
			handler: function(){
				getWnd('swSmpEmergencyTeamRelWindow').show({
					api: 'wialon'
				});
			}
		},
		
		action_reports: //http://redmine.swan.perm.ru/issues/18509
		{
			nn: 'action_Report',
			tooltip: lang['prosmotr_otchetov'],
			text: lang['prosmotr_otchetov'],
			iconCls: 'report32',
			//hidden: !this.enableDefaultActions,//( !document.getElementById('swWorkPlaceCallCenterWindow') || !this.enableDefaultActions ),
			handler: function() {
				if (sw.codeInfo.loadEngineReports)
				{
					getWnd('swReportEndUserWindow').show();
				}
				else
				{
					getWnd('reports').load(
						{
							callback: function(success)
							{
								sw.codeInfo.loadEngineReports = success;
								// здесь можно проверять только успешную загрузку
								getWnd('swReportEndUserWindow').show();
							}
						});
				}
			}
		},

		action_StoragePlacement:
		{
			nn: 'action_StoragePlacement',
			tooltip: lang['formirovanie_ukladok'],
			text: lang['formirovanie_ukladok'],
			iconCls : 'storage-place32',
			handler: function()
			{
				getWnd('swStorageZoneViewWindow').show({fromARM:'smp',smp:{LpuBuilding_id:this.LpuBuilding_id}});
			}.createDelegate(this)
		}
		
	},
	
	// <!-- КОСТЫЛЬ
	autoEvent: true,
	
	startTask: function() {
		if( !this.interval ) {
			this.interval = setInterval(this.task.run, this.task.interval);
		}
	},
	
	stopTask: function() {
		if( this.interval > 0 ) {
			clearInterval(this.interval);
			delete this.interval;
		}
	},
	
	refreshTaskTime: function() {
		if( !this.GridPanel.hideTaskTime ) {
			this.setTextTimeButton(this.task.interval/1000);
			if( this.interval2 ) {
				clearInterval(this.interval2);
				delete this.interval2;
			}
			this.interval2 = setInterval(this.setTextTimeButton.createDelegate(this), 1000);
		}
		
		if( this.autoEvent ) {
			return false;
		}
		this.autoEvent = true;
		this.stopTask();
		this.startTask();
	},
	// КОСТЫЛЬ -->
	
	setTextTimeButton: function(s) {
		var timeel = null;
		this.GridPanel.getTopToolbar().items.each(function(b) {
			if (b.id = this.GridPanel.id + '_tasktime' ) {
				timeel = b;
			}
		}.createDelegate(this));
		if( timeel.setText ) {
			timeel.seconds = s || timeel.seconds-1;
			var s = s || +timeel.seconds;
			if( s < 0 ) return;
			timeel.setText(lang['do_avtorefresha_ostalos'] + s + lang['cek']);
		}
	},
	
	getGroupName: function(id) {
		var groups = [
			'Принятые звонки', // из ДВ - все звонки которые приняты но не переданы ДН от всех диспетчеров данного ЛПУ
			'Принятые ДН', // Переданные вызовы от диспетчера вызовов status=1 && lpu_id is null
			lang['peredannyie_v_smp'], // +lang['naznachena']+ +lang['brigada']+ status=2 && lpu_id is null
			'Обслужены в СМП', // Если все хорошо и заполнен талон вызова status=4 && lpu_id is null
			'Переданные в НМП', // Вызов назначен на НМП
			'Принятые НМП', // Если в НМП обработали и приняли вызов
			'Обслужены НМП', // Если все хорошо и заполнен талон вызова
			'Отклонены НМП', // Если в НМП обработали и отказали в вызове
			lang['otkaz'],
			lang['zakryityie'],
			'Передано диспетчеру подстанции',
			'Возвращено диспетчером подстанции',
			'Дубль',
			'Решение старшего врача',
			'Отложено',
			'Передано из 112'
		];
		if( id ) {
			return groups[id-1];
		} else {
			return groups;
		}
	},
	
	getRowCount: function(group_id) {
		//log(group_id);
		var gs = [];
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpGroupName_id') == group_id && r.get('CmpCallCard_id') != null ) {
				gs.push(r.get('CmpCallCard_id'));
			}
		});

		return gs.length;
	},
	
	addEmptyRecord: function() {
		var groups = [],
			gs = [];
		
		for(var j=1; j<=this.getGroupName().length; j++) {
			groups[j] = [];
		}
		
		this.GridPanel.getStore().each(function(rec) {
			if ( typeof groups[rec.get('CmpGroup_id')] == 'object' ) {
				groups[rec.get('CmpGroup_id')].push(rec.get('CmpCallCard_id'));
			}
		});
		for(i in groups) {
			if( groups[i].length == 0 ) {
				gs.push(+i);
			}
		}
		for(var i=0; i<gs.length; i++) {
			var data = {};
			this.GridPanel.getColumnModel().getColumnsBy(function(c) { data[c.dataIndex] = null; });
			data['CmpGroup_id'] = gs[i];
			data['CmpGroupName_id'] = (!Ext.isEmpty(data['CmpGroup_id']) && data['CmpGroup_id'].toString().length > 1 ? data['CmpGroup_id'].toString() : '0' + data['CmpGroup_id']);
			this.GridPanel.getStore().add(new Ext.data.Record(data));
		}
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
		this.removeEmptyRecord();
	},
	
	removeEmptyRecord: function() {
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpCallCard_id') == null && this.getRowCount(r.get('CmpGroupName_id')) > 0 ) {
				this.GridPanel.getStore().remove(r);
			}
		}.createDelegate(this));
	},
	
	focusSelectedRow: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		
		var idx = this.GridPanel.getStore().indexOf(record);
		var row = this.GridPanel.getView().getRow(idx);
		
		this.GridPanel.getView().focusRow(row);
	},
	
	show: function() {
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;
		if ( this.ARMType.toString() != 'smpheadduty' ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], function(){ this.hide(); }.createDelegate(this) );
			return false;
		}
		this.LpuBuilding_id = arguments[0].LpuBuilding_id;
		
		var opts = getGlobalOptions();
				
		with( this.LeftPanel.actions ) {
			// Pskov only
			if ( opts.region.number != 60 ) {
				action_emergency_team_wialon_rel.setHidden(true);
			}

			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}

		this.startTask();
		this.startTimer();
		sw.Promed.swWorkPlaceSMPHeadDutyWindow.superclass.show.apply(this, arguments);
	},
	asyncLockCmpCallCard: function(data) {
		//redactStr дублируется в asyncUnlockCmpCallCard
		var redactStr = '<img src="../img/grid/lock.png">';
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx == -1) {
			return false
		}
		var record = this.GridPanel.getStore().getAt(idx)
		if(!record) return false;
		if (record.get('Person_FIO').indexOf(redactStr)== -1){
			record.set('Person_FIO', (redactStr+record.get('Person_FIO')) );
			record.set('CmpCallCard_isLocked', 1);
		}
		record.commit();
		var sm = this.GridPanel.getSelectionModel();
		if(sm.getSelected()&&sm.getSelected().get('CmpCallCard_id')==data['CmpCallCard_id'] ) {
			var ind = this.GridPanel.getStore().indexOf(record);
			this.GridPanel.getSelectionModel().fireEvent('rowselect',sm,ind,record);
		}
		
	},
	asyncUnlockCmpCallCard: function(data) {
		//redactStr дублируется в asyncLockCmpCallCard
		var redactStr = '<img src="../img/grid/lock.png">';
		console.log(data);
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx == -1) {
			if (!data['DispatchDirect_CmpGroup_id']) {
				return false;
			} else {
				this.asyncAddCmpCallCard(data);
			}
		}
		var record = this.GridPanel.getStore().getAt(idx)
		if(!record) return false;
		for (k in data) {
			if (data.hasOwnProperty(k)) {
				if (typeof record.get(k) != 'undefined') {
					record.set(k,data[k]);
				}
			}
		}

		record.set('CmpGroup_id',data['DispatchDirect_CmpGroup_id']);//HEADDUTY
		record.set('CmpGroupName_id',data['HeadDuty_CmpGroupName_id']);//HEADDUTY
		
		record.commit();
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
		this.addEmptyRecord();
		record.set('Person_FIO', (record.get('Person_FIO').substring(redactStr.length)));
		record.set('CmpCallCard_isLocked', 0);
		record.commit();
		var sm = this.GridPanel.getSelectionModel();
		if(sm.getSelected()&&sm.getSelected().get('CmpCallCard_id')==data['CmpCallCard_id'] ) {
			var ind = this.GridPanel.getStore().indexOf(record);
			this.GridPanel.getSelectionModel().fireEvent('rowselect',sm,ind,record);
		}
		
		
	},
	asyncAddCmpCallCard:function(data){
		data['CmpGroup_id'] = data['HeadDuty_CmpGroup_id'];
		data['CmpGroupName_id'] = data['HeadDuty_CmpGroupName_id'];
		if (data['CmpGroup_id']==null) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx != -1) {
			return false
		}
		
		date = {};
		date['begDate'] = this.dateMenu.getValue1();
		date['endDate'] = this.dateMenu.getValue2();
		date['endDate'].setDate(date['endDate'].getDate() + 1);
		date['prmDate'] = new Date(data['CmpCallCard_prmDate']);
		
		
		if (!((date['begDate']<=date['prmDate'])&&(date['endDate']>date['prmDate']))) {
			return false;
		}
		
		var redactStr = '<img src="../img/grid/lock.png">';
		data['Person_FIO'] = data['Person_FIO'].substring(redactStr.length);
		data['CmpCallCard_isLocked'] = 0;
		var record = new Ext.data.Record(data);
		this.GridPanel.getStore().add(record);
		this.GridPanel.getStore().commitChanges();
		this.removeEmptyRecord();
		this.addEmptyRecord();
		with(this.GridPanel.getStore()) {
			var ss = getSortState();
			sort(ss.field, ss.direction);
		}
	},
	asyncDeleteCmpCallCard: function(data) {
		if (!data || !data['CmpCallCard_id']) {
			return false;
		}
		var idx = this.GridPanel.getStore().findBy(function(rec) { return rec.get('CmpCallCard_id') == data['CmpCallCard_id']; });
		if (idx == -1) {
			return false
		}
		var record = this.GridPanel.getStore().getAt(idx);
		this.GridPanel.getStore().remove(record);
		this.addEmptyRecord();
	},
	initComponent: function() {

		var form = this;
		
		this.task = {run: this.doSearch.createDelegate(this), interval: 60*1000};
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.autoEvent = false;
				this.doSearch();
			}
		}.createDelegate(this);
	   
		var gridFields = [
			{ dataIndex: 'CmpCallCard_id', header: 'ID', key: true, hidden: true, hideable: false },
			{ dataIndex: 'Person_id', hidden: true, hideable: false },
			{ dataIndex: 'PersonEvn_id', hidden: true, hideable: false },
			{ dataIndex: 'Server_id', hidden: true, hideable: false },
			{ dataIndex: 'Person_Surname', hidden: true, hideable: false },
			{ dataIndex: 'Person_Firname', hidden: true, hideable: false },
			{ dataIndex: 'Person_Secname', hidden: true, hideable: false },
			{ dataIndex: 'pmUser_insID', hidden: true, hideable: false },
			{dataIndex: 'CmpCallCard_isLocked', hidden: true, hideable: false},
			{ dataIndex: 'PersonQuarantine_IsOn', type: 'string', hidden: true},
			{ dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya'], width: 110 },
			{ dataIndex: 'CmpCallCard_Numv', sortable: false, header: lang['№_vyizova_za_den'], width: 100 },
			{ dataIndex: 'CmpCallCard_Ngod', sortable: false, header: lang['№_vyizova_za_god'], width: 100 },
			{ dataIndex: 'Person_FIO', sortable: false, header: lang['patsient'], width: 250 },
			{ dataIndex: 'Person_Birthday', sortable: false, header: lang['data_rojdeniya'], width: 100 },
			{ dataIndex: 'CmpCallType_Name', sortable: false, header: lang['tip_vyizova'], width: 200},
			{ dataIndex: 'CmpReason_Name',sortable: false,  header: lang['povod'], width: 200 },
			{ dataIndex: 'CmpLpu_Name', sortable: false, header: lang['lpu_prikrepleniya'], width: 100 },
			{ dataIndex: 'CmpDiag_Name',sortable: false,  header: lang['diagnoz_smp'], width: 100 },
			{ dataIndex: 'StacDiag_Name',sortable: false,  header: lang['diagnoz_statsionara'], width: 130 },
			{ dataIndex: 'SendLpu_Nick', sortable: false, header: lang['lpu_peredachi'], width: 100 },
			{ dataIndex: 'PPDUser_Name', sortable: false, header: lang['prinyal'], width: 200 },
			{ dataIndex: 'ServeDT', sortable: false, header: lang['obslujeno'], width: 150 },
			{ dataIndex: 'PPDResult', sortable: false, header: lang['rezultat_obrabotki_v_nmp'], width: 300, render: function(v, p, r) {
				return '<p title="'+v+'">'+v+'</p>';
			} },
			{ dataIndex: 'CmpGroup_id', hidden: true, hideable: false },
			{ dataIndex: 'CmpGroupName_id', hidden: true, hideable: false }
		],
		storeFields = [];
		for(var i=0; i<gridFields.length; i++) {
			storeFields.push({
				mapping: gridFields[i].dataIndex,
				name: gridFields[i].dataIndex
			});
		}
		
		var gridStore = new Ext.data.GroupingStore({
			autoLoad: false,
			sortInfo: {
				field: 'CmpGroupName_id',
				direction: 'ASC'
			},
			groupField: 'CmpGroupName_id',
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=CmpCallCard&m=loadSMPHeadDutyWorkPlace'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			title: ' - ',
			id: form.id + '_Grid',
			hideTaskTime: true,
			paging: true,
			keys: [{
				fn: function(inp, e) {
					switch(e.getKey()) {
						case Ext.EventObject.ENTER:
							this.GridPanel.fireEvent('rowdblclick');
							break;
						case Ext.EventObject.DELETE:
							this.GridPanel.ViewActions.action_delete.execute();
							break;
						case Ext.EventObject.F6:
							this.openPCardHistory();
							break;
						case Ext.EventObject.F10:
							this.openPersonEdit();
							break;
						case Ext.EventObject.F11:
							this.openPCureHistory();
							break;
					}
				},
				key: [
					Ext.EventObject.ENTER,
					Ext.EventObject.DELETE,
					Ext.EventObject.F6,
					Ext.EventObject.F10,
					Ext.EventObject.F11
				],
				scope: this,
				stopEvent: true
			}],
			listeners: {
				render: function() {
					this.contextMenu = new Ext.menu.Menu();
					this.ViewActions = {};
					for(var i=0; i<this.actions.length; i++) {
						this.ViewActions[this.actions[i]['name']] = new Ext.Action(this.actions[i]);
						this.getTopToolbar().add(this.ViewActions[this.actions[i]['name']]);
						this.contextMenu.add(this.ViewActions[this.actions[i]['name']]);
					}
					
					this.getTopToolbar().addFill();
					this.getTopToolbar().addButton({
						disabled: true,
						id: this.id + '_tasktime',
						hidden: typeof this.hideTaskTime != 'undefined' ? this.hideTaskTime : true
					});
				},
				rowcontextmenu: function(grd, num, e) {
					e.stopEvent();
					this.getSelectionModel().selectRow(num);
					this.contextMenu.showAt(e.getXY());
				}
			},
			loadData: function(params) {
				with(this.getStore()) {
					removeAll();
					baseParams = params.globalFilters;
					load();
				}
			},
			tbar: new Ext.Toolbar(),
			actions: [
				{ name: 'action_view', iconCls: 'view16', text: lang['prosmotr'], tooltip: lang['smotret_kartu'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view']) },
				{ name: 'action_refresh', iconCls: 'refresh16', text: lang['obnovit'], handler: function(btn) { this.autoEvent = false; this.doSearch(); }.createDelegate(this) },
				{ name: 'action_print', iconCls: 'print16', text: lang['pechat_spiska'], 
					handler: 
						function() { 
							var params = {};
							params.notPrintEmptyRows = true;
							Ext.ux.GridPrinter.print(this.GridPanel, params);
						}.createDelegate(this) 
				}
			],
			loadMask: { msg: lang['zagruzka'] },
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			view: new Ext.grid.GroupingView({
				enableGroupingMenu: false,
				groupTextTpl: '{[values.gvalue + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({["записей "+Ext.getCmp("'+this.id+'").getRowCount(values.gvalue)]})'
			}),
			getAction: function(action) {
				return this.ViewActions[action] || null;
			},
			setParam: function(p, v) {
				this.getStore().baseParams[p] = v;
			},
			store: gridStore
		});
		this.GridPanel.getStore().on('load', function(store, rs) {
			if(store.getCount()) {
				this.getSelectionModel().selectFirstRow();
				form.focusSelectedRow();
			}
			var d = !this.getSelectionModel().hasSelection();
			this.getAction('action_view').setDisabled(d);
			
			if( Ext.get(this.getView().getGroupId(7)) != null && this.firstShow ) {
				this.getView().toggleGroup(this.getView().getGroupId(7), false);
				this.firstShow = false;
			}
			
			form.addEmptyRecord();
			if (form.socket && form.socket.connected) {
				form.refreshTaskTime();
			}
		}.createDelegate(this.GridPanel));
		this.GridPanel.getView().getRowClass = function(record, index) {
			var cls = '';
			if (record.data['CmpCallCard_isLocked']==1) {
				cls = cls + 'grid-locked-row';
			}
			if (record.get('PersonQuarantine_IsOn') == 'true') {
				cls = cls + 'x-grid-rowbackred ';
			}
			return cls;
		}
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			this.getAction('action_view').setDisabled( rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );			
		}.createDelegate(this.GridPanel));
		this.GridPanel.on('rowdblclick', function() {
			this.getAction('action_view').execute();
		});
		
		sw.Promed.swWorkPlaceSMPHeadDutyWindow.superclass.initComponent.apply(this, arguments);
	}
});
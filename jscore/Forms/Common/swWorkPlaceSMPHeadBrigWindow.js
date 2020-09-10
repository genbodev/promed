/**
* АРМ диспетчера вызовов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/

sw.Promed.swWorkPlaceSMPHeadBrigWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
	useUecReader: true,
	id: 'swWorkPlaceSMPHeadBrigWindow',
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['zakryit'],
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
			
			if(this.interval2) {
				clearInterval(this.interval2);
				delete this.interval2;
			}
		}
	},
	
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		var wnd = '';

		switch ( this.ARMType.toString() ) {
			case 'smpheadbrig':
				wnd = 'swCmpCallCardNewShortEditWindow';
			break;
		}

		if ( wnd == '' ) {
			sw.swMsg.alert(lang['soobschenie'], lang['ne_udalos_opredelit_tip_vyibrannogo_arma']);
			return false;
		}

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}

		var formParams = new Object();
		var grid = this.GridPanel;//.getGrid();
		var params = new Object();

		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}
			
			if(action == 'edit'){
				var po = this;
				if(po.socket && po.socket.connected){
					po.socket.emit('changeCmpCallCard', data.cmpCallCardData.CmpCallCard_id, 'addCall');
					log('NODE EMIT changeCmpCallCard');
					this.socket.on('changeCmpCallCard',function(data, type){
						log('NODE ON changeCmpCallCard type='+type);
					});
				}
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
		
		formParams.ARMType = this.ARMType;
		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	show: function() {
		sw.Promed.swWorkPlaceSMPHeadBrigWindow.superclass.show.apply(this, arguments);
		
		this.ARMType = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;

		if ( !this.ARMType.toString().inlist([ 'smpheadbrig' ]) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		log('connect nodejs')
		//подключение nodeJS
		connectNode(this);
		
		loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), lang['kuda_dostavlen']);
		var parentObject = this;
		Ext.Ajax.request({
			url: '/?c=EmergencyTeam&m=setEmergencyTeamWorkComingMedPersonal',
			params: {
				MedPersonal_id: getGlobalOptions().medpersonal_id				
			},
			callback: function(o, s, r) {
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);				
					
					$('.bottom-message').html(obj.Msg);
					$('.bottom-message').fadeIn(500,function(){
						setTimeout("$('.bottom-message').fadeOut(500);",2500);    
					});
					if( obj.Code == '1' || obj.Code == '2' || obj.Code == '4') {
						var po = parentObject;
						getWnd('swSelectEmergencyTeamDutyWindow').show({
							onDoCancel: function() {
							},
							callback: function(data) {
								$('.bottom-message').html("Смена установлена.");
								$('.bottom-message').fadeIn(500,function(){
									setTimeout("$('.bottom-message').fadeOut(500);",2500);    
								});	

								Ext.MessageBox.hide();
								var a = Date.parseDate(data, 'Y-m-d h:i:s');
								a = Ext.util.Format.date(a, 'd.m.Y');
								Ext.getCmp('periodField')? Ext.getCmp('periodField').setValue(a+' - '+a): null;
								var aflag = true;
								setInterval(function(){
									if (aflag == true)
										po.setDoSearch();
									aflag=false;
								}, 1000);

							}
						});
					}
					
				}
			}.createDelegate(this)
		});
		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}
		
		this.startTimer();
		this.startTask();
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
			topTitle.setTitle('АРМ старшего бригады. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
		 }, 1000); 
	},	
	
	buttonPanelActions: {
		action_messages: {
			iconCls: 'messages48',
			tooltip: lang['sistema_soobscheniy'],
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}
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
			lang['peredannyie_vyizovyi'],
			lang['obslujennyie_vyizovyi']
		];
		if( id ) {
			return groups[id-1];
		} else {
			return groups;
		}
	},
	
	setDoSearch: function() {
		this.doSearch('period');
		log('#');
	},
	
	getRowCount: function(group_id) {
		//log(group_id);
		var gs = [];
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpGroup_id') == group_id && r.get('CmpCallCard_id') != null ) {
				gs.push(r.get('CmpCallCard_id'));
			}
		});
		//var str = gs.length.toString(),
		//	smb = str.slice(str.length-1, str.length);

		return gs.length;
	},
	
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment, refuse_reason_id, callbackFn) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		this.getLoadMask().show();
		if (StatusType_id != 5 || typeof(refuse_reason_id)=='undefined')  {
			refuse_reason_id = 0;
		}
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id'),
				CmpCallCardStatusType_id: StatusType_id,
				CmpCallCardStatus_Comment: StatusComment || null,
				CmpCallCard_IsOpen: IsOpen,
				armtype: this.ARMType.toString(),
				CmpReason_id: refuse_reason_id
			},
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						if(callbackFn)callbackFn();
						/*
						record.set('CmpGroup_id', obj.CmpGroup_id);
						record.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						*/
						this.doSearch();
					}
				}
			}.createDelegate(this)
		});
	},
	
	addEmptyRecord: function() {
		var groups = {},
			gs = [];
		
		for(var j=1; j<=this.getGroupName().length; j++) {
			groups[j] = [];
		}
		
		this.GridPanel.getStore().each(function(rec) {
			groups[rec.get('CmpGroup_id')].push(rec.get('CmpCallCard_id'));
		});
		for(i in groups) {
			if( groups[i].length == 0 ) {
				gs.push(+i);
			}
		}
		for(var i=0; i<gs.length; i++) {
			var data = {};
			this.GridPanel.getColumnModel().getColumnsBy(function(c) {data[c.dataIndex] = null;});
			data['CmpGroup_id'] = gs[i];
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
			if( r.get('CmpCallCard_id') == null && this.getRowCount(r.get('CmpGroup_id')) > 0 ) {
				this.GridPanel.getStore().remove(r);
			}
		}.createDelegate(this));
	},
	
	/*
	buttonPanelActions: {
		adistest_action: {
			text: 'ADIS',
			handler: function() {
				Ext.Ajax.request({
					url: '/?c=AmbulanceCard&m=saveAmbulanceCard'
				});
			},
			iconCls: ''
		}
	},
	*/	
	// Прикрепление человека
	openPCardHistory: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onHide = this.focusSelectedRow.createDelegate(this);
		ShowWindow('swPersonCardHistoryWindow', params);
	},
	
	// Редактирование человека
	openPersonEdit: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onClose = this.focusSelectedRow.createDelegate(this);
		params.callback = this.doSearch.createDelegate(this);
		ShowWindow('swPersonEditWindow', params);
	},
	
	// История лечения
	openPCureHistory: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || record.get('Person_id') == null ) return false;
		
		var params = record.data;
		params.onHide = this.focusSelectedRow.createDelegate(this);
		ShowWindow('swPersonCureHistoryWindow', params);
	},
	
	focusSelectedRow: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record ) return false;
		
		var idx = this.GridPanel.getStore().indexOf(record);
		var row = this.GridPanel.getView().getRow(idx);
		
		this.GridPanel.getView().focusRow(row);
	},
	
	// #############################
	// #############################
	
	
	
	closeCmpCallCard: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit']) ) {
			return false;
		}
		
		// wnd = 'swCmpCallCardCloseCardWindow';
		var wnd = 'swCmpCallCardNewCloseCardWindow';
		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert(lang['soobschenie'], lang['okno_redaktirovaniya_kartyi_vyizova_uje_otkryito']);
			return false;
		}		
		var parentObject = this;
		var formParams = new Object();
		var grid = this.GridPanel;//.getGrid();
		var params = new Object();
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCloseCardData ) {
				return false;
			}
			
			
			// Назначаем тип
			this.setStatusCmpCallCard(1, 6, null, null, function(){
				if(data.cmpCloseCardData.action == 'add'){
					//var win = Ext.getCmp('swWorkPlaceSMPAdminWindow');
					parentObject.socket.emit('changeCmpCallCard', data.cmpCloseCardData.CmpCallCard_id, 'closeCard', function(data){
						log('NODE emit closeCard');
					});
				}
			});
			
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
			//parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};
		if( (params.action == 'edit') && !selected_record.get('CmpCloseCard_id') ){
			params.action = 'edit';
		}
		params.formParams = formParams;
		if(params.action=='add'){
			params.AutoBrigadeStatusChange = true;
		}
		getWnd(wnd).show(params);

		
	},
	

	 
	// #############################
	// #############################
	
	initComponent: function() {
		var form = this;
		this.showToolbar = false;
		this.task = {run: this.doSearch.createDelegate(this), interval: 60*1000};
		$('<div class="bottom-message" style="z-index:9999; padding: 20px; color: white; border-radius: 10px; background-color: #698bb8; position: absolute; bottom:60px; right:60px; display:none;"></div>').appendTo('body');
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.autoEvent = false;
				this.doSearch();
			}
		}.createDelegate(this);

		this.FilterPanel = new sw.Promed.BaseWorkPlaceFilterPanel({
			owner: form,
			labelWidth: 120,
			filter: {
				title: lang['filtryi'],
				layout: 'form',
				items: [{
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 200,
							name: 'Search_SurName',
							fieldLabel: lang['familiya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_FirName',
							fieldLabel: lang['imya'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							xtype: 'textfieldpmw',
							width: 120,
							name: 'Search_SecName',
							fieldLabel: lang['otchestvo'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}, {
						layout: 'form',
						labelWidth: 75,
						items: [{
							xtype: 'swdatefield',
							format: 'd.m.Y',
							plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
							name: 'Search_BirthDay',
							fieldLabel: lang['dr'],
							listeners: {
								'keydown': form.onKeyDown
							}
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						labelWidth: 120,
						items: [
							{
								fieldLabel: 'Куда доставлен',
								hiddenName: 'CmpLpu_id',
								listeners: {
									select: form.onKeyDown
								},
								listWidth: 400,
								width: 200,
								xtype: 'swlpucombo'
							}
						]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['№_vyizova_za_den'],
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'CmpCallCard_Numv',
							width: 120,
							xtype: 'textfield'
						}]
					}, {
						layout: 'form',
						labelWidth: 120,
						items: [{
							enableKeyEvents: true,
							fieldLabel: lang['№_vyizova_za_god'],
							listeners: {
								'keydown': form.onKeyDown
							},
							name: 'CmpCallCard_Ngod',
							width: 120,
							xtype: 'textfield'
						}]
					}]
				}, {
					layout: 'column',
					items: [{
						layout: 'form',
						items: [{
							style: "padding-left: 20px",
							xtype: 'button',
							id: form.id + 'BtnSearch',
							text: lang['nayti'],
							iconCls: 'search16',
							handler: function() {
								form.doSearch();
							}.createDelegate(form)
						}]
					}, {
						layout: 'form',
						items: [{
							style: "padding-left: 10px",
							xtype: 'button',
							id: form.id + 'BtnClear',
							text: lang['sbros'],
							iconCls: 'reset16',
							handler: function() {
								form.doReset();
							}.createDelegate(form)
						}]
					}]
				}]
			}
		});

		var gridFields = [
			{dataIndex: 'CmpCallCard_id', header: 'ID', key: true, hidden: true, hideable: false},
			{dataIndex: 'Person_id', hidden: true, hideable: false},
			{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
			{dataIndex: 'Server_id', hidden: true, hideable: false},
			{dataIndex: 'Person_Surname', hidden: true, hideable: false},
			{dataIndex: 'Person_Firname', hidden: true, hideable: false},
			{dataIndex: 'Person_Secname', hidden: true, hideable: false},
			{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: function (date) {
					if (date) {
						date = new Date(date);
						return Ext.util.Format.date(date, 'd.m.Y H:i');
					}
				}, header: langs('Дата/время'), width: 110},
			{dataIndex: 'CmpCallCard_Numv', header: langs('№ вызова за день'), width: 100},
			{dataIndex: 'CmpCallCard_Ngod', header: langs('№ вызова (за год)'), width: 100},
			{dataIndex: 'Person_FIO', header: langs('Пациент'), width: 250},
			{dataIndex: 'Person_Birthday', header: langs('Дата рождения'), width: 100},
			{dataIndex: 'CmpCallType_Name', header: langs('Тип вызова'), width: 200},
			{dataIndex: 'CmpReason_Name', header: langs('Повод'), width: 200},
			{dataIndex: 'Adress_Name', header: langs('Место вызова'), width: 300},
			{dataIndex: 'CmpDiag_Name', header: langs('Диагноз СМП'), width: 100},
			{dataIndex: 'StacDiag_Name', header: langs('Диагноз стационара'), width: 130},
			{dataIndex: 'SendLpu_Nick', header: langs('ЛПУ передачи'), width: 100},
			{dataIndex: 'PPDUser_Name', header: langs('Принял'), width: 200},
			{dataIndex: 'ServeDT', header: langs('Обслужено'), width: 150},
			{dataIndex: 'PPDResult', header: langs('Результат обработки в НМП'), width: 300, render: function(v, p, r) {
				//var res = '';
				//ext:qtip=
				return '<p title="'+v+'">'+v+'</p>';
			}},
			{dataIndex: 'CmpGroup_id', hidden: true, hideable: false}
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
				field: 'CmpGroup_id',
				direction: 'ASC'
			},
			groupField: 'CmpGroup_id',
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=CmpCallCard&m=loadSMPHeadBrigWorkPlace'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			title: ' ',
			id: form.id + '_Grid',
			hideTaskTime: true,
			paging: true,
			keys: [{
				fn: function(inp, e) {
					switch(e.getKey()) {
						case Ext.EventObject.ENTER:
							this.GridPanel.fireEvent('rowdblclick');
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
				{name: 'action_view', iconCls: 'view16', text: langs('Просмотр'), tooltip: langs('Смотреть карту'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view'])},
				{name: 'action_view', iconCls: 'edit16', hidden:(getGlobalOptions().region.nick.inlist(['ufa','kareliya'])), text: langs('Изменить'), tooltip: langs('Изменить карту'), handler: this.openCmpCallCardEditWindow.createDelegate(this, ['edit'])},
				{name: 'action_refresh', iconCls: 'refresh16', text: langs('Обновить '), handler: function(btn) {this.autoEvent = false;this.doSearch();}.createDelegate(this)},
				{name: 'action_closecard', text: langs('Закрыть карту вызова'), tooltip: langs('Закрыть карту вызова'), handler: this.closeCmpCallCard.createDelegate(this, ['add'])},
				{name: 'action_closecardview', text: langs('Редактировать карту вызова'), tooltip: langs('Редактировать карту вызова'), handler: this.closeCmpCallCard.createDelegate(this, ['edit'])}
			],
			loadMask: {msg: lang['zagruzka']},
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
			this.getAction('action_closecardview').setDisabled(d);
			this.getAction('action_closecard').setDisabled(d);
						
			form.addEmptyRecord();
			form.refreshTaskTime();
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			console.log('groupid',rec.get('CmpGroup_id'));
			this.getAction('action_closecard').setDisabled(rec.get('CmpGroup_id') != 1 || rec.get('CmpCallCard_id') == null  );
			this.getAction('action_closecardview').setDisabled(rec.get('CmpGroup_id') != 2 ) ;
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			this.getAction('action_view').execute();
		});
		Ext.apply(this, {			
			buttonPanelActions: {
				action_messages: {
					iconCls: 'messages48',
					tooltip: lang['sistema_soobscheniy'],
					handler: function() {
						getWnd('swMessagesViewWindow').show();
					}
				},

				adistest_action: {
					iconCls: 'diary-entry32',
					tooltip: lang['vyibor_smenyi'],
					handler: function() {
						var parentObject = this;		
						getWnd('swSelectEmergencyTeamDutyWindow').show({
							onDoCancel: function() {
							},
							callback: function(data) {
								$('.bottom-message').html("Смена установлена.");
								$('.bottom-message').fadeIn(500,function(){
									setTimeout("$('.bottom-message').fadeOut(500);",2500);    
								});								
								
								//Ext.MessageBox.hide();
								var a = Date.parseDate(data, 'Y-m-d h:i:s');
								a = Ext.util.Format.date(a, 'd.m.Y');
								Ext.getCmp('periodField')? Ext.getCmp('periodField').setValue(a+' - '+a): null;
								var aflag = true;
								setInterval(function(){
									if (aflag == true) parentObject.setDoSearch();
									aflag=false;
								}, 100);
								
							}
						});						
					}.createDelegate(this)				
				}
			}
		});
		
		sw.Promed.swWorkPlaceSMPHeadBrigWindow.superclass.initComponent.apply(this, arguments);
	}
});
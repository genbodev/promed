/**
* АРМ диспетчера направлений СМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dyomin Dmitry
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      апрель.2012
*/

sw.Promed.swWorkPlaceSMPDispatcherDirectWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
	useUecReader: true,
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
	
	deleteCmpCallCard: function() {
		var grid = this.GridPanel;//.getGrid();
		var parentObject = this;
		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
			return false;
		}
		
		var record = grid.getSelectionModel().getSelected();
		this.emitEditingEvent(record.get('CmpCallCard_id'),function(){
			sw.swMsg.show({
				buttons: Ext.Msg.YESNO,
				fn: function(buttonId, text, obj) {
					if ( buttonId == 'yes' ) {
						Ext.Ajax.request({
							callback: function(options, success, response) {
								if ( success ) {
									var response_obj = Ext.util.JSON.decode(response.responseText);

									if ( response_obj.success == false ) {
										sw.swMsg.alert(lang['oshibka'], response_obj.Error_Msg ? response_obj.Error_Msg : lang['oshibka_pri_udalenii_kartyi_vyizova']);
									}
									else {
										grid.getStore().remove(record);
										parentObject.emitDeletingEvent(record.get('CmpCallCard_id'));
										parentObject.addEmptyRecord();
									}

									if ( grid.getStore().getCount() > 0 ) {
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
									grid.getStore().reload();
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
									parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
								}
							},
							params: {
								CmpCallCard_id: record.get('CmpCallCard_id')
							},
							url: '/?c=CmpCallCard&m=deleteCmpCallCard'
						});
					} else {
						parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
					}
				},
				icon: Ext.MessageBox.QUESTION,
				msg: lang['udalit_kartu_vyizova'],
				title: lang['vopros']
			});
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	id: 'swWorkPlaceSMPDispatcherDirectWindow',
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		var wnd = '';
		switch ( this.ARMType.toString() ) {
			case 'smpdispatchdirect':
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
		var parentObject = this;
		params.action = action;
		params.callback = function(data) {
			if ( !data || !data.cmpCallCardData ) {
				return false;
			}
			grid.getStore().reload();
			parentObject.emitAddingEvent(data.cmpCallCardData['CmpCallCard_id']);
			this.autoEvent = false;
		}.createDelegate(this);

		if ( action == 'add' ) {
			formParams.CmpCallCard_id = 0;

			params.onHide = function() {
				
			};
			
			formParams.ARMType = this.ARMType;
			params.formParams = formParams;
			getWnd(wnd).show(params);
			
		}
		else {
			if ( !grid.getSelectionModel().getSelected() ) {
				return false;
			}

			var selected_record = grid.getSelectionModel().getSelected();

			if ( !selected_record.get('CmpCallCard_id') ) {
				return false;
			}
	
			formParams.CmpCallCard_id = selected_record.get('CmpCallCard_id');

			params.onHide = function() {
				if (action == 'edit') {
					parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
				}
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
			formParams.ARMType = this.ARMType;
			params.formParams = formParams;
			if (action == 'edit') {
				this.emitEditingEvent(selected_record.get('CmpCallCard_id'),function(){
					getWnd(wnd).show(params);
				});
			} else {
				getWnd(wnd).show(params);
			}
		}
	},
	show: function() {
		sw.Promed.swWorkPlaceSMPDispatcherDirectWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		
		this.setTimeMenuVisibility();
		
		
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		
		
		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;

		if ( !this.ARMType.toString().inlist([ 'smpdispatchdirect' ]) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		if ((this.ARMType.toString() == 'smpdispatchdirect') &&
			(Ext.globalOptions.globals.groups.indexOf('SMPCallDispath')!= -1) && 
			(Ext.globalOptions.globals.groups.indexOf('SMPDispatchDirections')!= -1) 
		){
			this.GridPanel.ViewActions.action_add.show();
		}
		else {
			this.GridPanel.ViewActions.action_add.hide();
		}

		loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), lang['kuda_dostavlen']);
		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}
		
		this.startTask();
		this.startTimer();
		sw.Promed.swWorkPlaceSMPDispatcherDirectWindow.superclass.show.apply(this, arguments);
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
		record.set('CmpGroup_id',data['DispatchDirect_CmpGroup_id']);
		
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
		data['CmpGroup_id'] = data['DispatchDirect_CmpGroup_id'];
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
			topTitle.setTitle('АРМ диспетчера направлений. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
		 }, 1000); 
	},		
	parentObj: this,
	buttonPanelActions: {
		action_messages: {
			iconCls: 'messages48',
			tooltip: lang['sistema_soobscheniy'],
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}
		},
		// Суточный рапорт
		action_report: {
			iconCls: 'reports32',
			tooltip: lang['sutochnyiy_raport'],
			handler: function() {
				var wnd = getWnd('swWorkPlaceSMPDispatcherDirectWindow');
				wnd.printReportCmpCallCard();
			//	this.printReportCmpCallCard.createDelegate(this);
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
	
	
	closeCmpCallCard: function() {
		
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
		params.action = 'add';
		params.callback = function(data) {
			if ( !data || !data.cmpCloseCardData ) {
				return false;
			}
			
			// Назначаем тип
			this.setStatusCmpCallCard(1, 6);
			
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
			parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
			grid.getView().focusRow(grid.getStore().indexOf(selected_record));
		};

		params.formParams = formParams;
		
		this.emitEditingEvent(selected_record.get('CmpCallCard_id'),function(){
			getWnd(wnd).show(params);
		});
		
	},
	
	
	printCmpCallCard: function() {		
		var grid = this.GridPanel;
		if ( !grid.getSelectionModel().getSelected() ) {
			return false;
		}
		var record = grid.getSelectionModel().getSelected();
		if (!record)
		{
			Ext.Msg.alert(lang['oshibka'], lang['ne_vyibrana_karta']);
			return false;
		}
		var CmpCallCard_id = record.get('CmpCallCard_id');
		if ( !CmpCallCard_id )
			return false;
		var id_salt = Math.random();
		var win_id = 'print_110u' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=CmpCallCard&m=printCmpCloseCard110&CmpCallCard_id=' + CmpCallCard_id, win_id);
		//var url ='';
		//window.open(url, '_blank');		
	},
	
	printReportCmpCallCard: function() {	
		console.log('!');
		var grid = this.GridPanel;
//		if ( !grid.getSelectionModel().getSelected() ) {
//			return false;
//		}
//		var record = grid.getSelectionModel().getSelected();
//		if (!record)
//		{
//			Ext.Msg.alert('Ошибка', 'Не выбрана карта.<br/>');
//			return false;
//		}
//		var CmpCallCard_id = record.get('CmpCallCard_id');
//		if ( !CmpCallCard_id )
//			return false;
		
		date1 = this.dateMenu.getValue1();
		ddate1 = Ext.util.Format.date(date1, 'd.m.Y')
		date2 = this.dateMenu.getValue2();
		ddate2 = Ext.util.Format.date(date2, 'd.m.Y')
		
		
		Daydate = this.dateMenu.getValue1();
		
		var id_salt = Math.random();
		var win_id = 'printReportCmp' + Math.floor(id_salt * 10000);
		var win = window.open('/?c=CmpCallCard&m=printReportCmp&daydate1=' + ddate1 + '&daydate2=' + ddate2, win_id);
		return false;
		//var url ='';
		//window.open(url, '_blank');		
	},
	
	getGroupName: function(id) {
		var groups = [
			'Принятые звонки', // Переданные вызовы от диспетчера вызовов status=1 && lpu_id is null
			lang['peredannyie_v_smp'], // +lang['naznachena']+ +lang['brigada']+ status=2 && lpu_id is null
			'Обслужены в СМП', // Если все хорошо и заполнен талон вызова status=4 && lpu_id is null
			'Переданные в НМП', // Вызов назначен на НМП
			'Принятые НМП', // Если в НМП обработали и приняли вызов
			'Обслужены НМП', // Если все хорошо и заполнен талон вызова
			'Отклонены НМП', // Если в НМП обработали и отказали в вызове
			lang['otkaz'],
			lang['zakryityie']
			/*
			// Old list. Will remove it soon;
			lang['dlya_peredachi_v_nmp'],
			lang['ne_mogut_byit_peredanyi_v_nmp'],
			lang['peredanyi_v_nmp'],
			lang['prinyatyi_v_nmp'],
			lang['otklonenyi_nmp'],
			lang['obslujenyi_nmp'],
			lang['zakryityie'],
			lang['otkaz']
			*/
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
			if( r.get('CmpGroup_id') == group_id && r.get('CmpCallCard_id') != null ) {
				gs.push(r.get('CmpCallCard_id'));
			}
		});
		//var str = gs.length.toString(),
		//	smb = str.slice(str.length-1, str.length);

		return gs.length;
	},
	setServedStatus: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		// this.emitEditingEvent(record.get('CmpCallCard_id'), function(){
		// 	parentObject.setStatusCmpCallCard(null, 4);
		// })
		parentObject.setStatusCmpCallCard(null, 4);
	},
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		this.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id'),
				CmpCallCardStatusType_id: StatusType_id,
				CmpCallCardStatus_Comment: StatusComment || null,
				CmpCallCard_IsOpen: IsOpen,
				armtype: this.ARMType.toString()
			},
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						
						if (!parentObject.emitEndEditingEvent(record.get('CmpCallCard_id')))
							this.doSearch();
//						/*
//						record.set('CmpGroup_id', obj.CmpGroup_id);
//						if( obj.CmpGroup_id != 5 ) {
//							record.set('PPDResult', '');
//						}
//						record.commit();
//						with(this.GridPanel.getStore()) {
//							var ss = getSortState();
//							sort(ss.field, ss.direction);
//						}
//						this.addEmptyRecord();
//						*/
//						this.doSearch();						
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
	
	selectEmergencyTeam: function(flag) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;

		var parentObject = this;
		// this.emitEditingEvent(record.get('CmpCallCard_id'),function(){
			//console.log('record', record, record.get('CmpCallCard_prmDate'));
			getWnd('swSelectEmergencyTeamWindow').show({
				CmpCallCard: record.get('CmpCallCard_id'),
				onDoCancel: function() {
					parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
				},
				callback: function(data) {
					parentObject.setEmergencyTeam(record, data, flag)
				},
				addressMap: record.get('Adress_Name'),
				CmpCallCard_id: record.get('CmpCallCard_id')
			});
		// });
	},
	setEmergencyTeam: function(selectedRecord,EmergencyTeam_data,flag) {		
		var cb = this.setStatusCmpCallCard;		
		var cb2 = this.closeCmpCallCard;			
		this.getLoadMask(lang['naznachenie']).show();
		var parentObject = this;
		var redactStr = '<img src="../img/grid/lock.png">';
		Ext.Ajax.request({
			params: {
				EmergencyTeam_id: EmergencyTeam_data.EmergencyTeam_id,
				CmpCallCard_id: selectedRecord.get('CmpCallCard_id'),
				Person_FIO: selectedRecord.get('Person_FIO').substring(redactStr.length),
				Person_Firname: selectedRecord.get('Person_Firname'),
				Person_Secname: selectedRecord.get('Person_Secname'),
				Person_Surname: selectedRecord.get('Person_Surname'),
				Person_id: selectedRecord.get('Person_id'),
				Person_Birthday: selectedRecord.get('Person_Birthday'),
				CmpCallCard_prmDate: Ext.util.Format.date(selectedRecord.get('CmpCallCard_prmDate'), 'H:i | d.m.Y') ,
				CmpReason_Name: selectedRecord.get('CmpReason_Name'),
				CmpCallType_Name: selectedRecord.get('CmpCallType_Name'),
				Adress_Name: selectedRecord.get('Adress_Name')
			},
			url: '/?c=CmpCallCard&m=setEmergencyTeam',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
						selectedRecord.set('EmergencyTeam_Num', EmergencyTeam_data.EmergencyTeam_Num);
						selectedRecord.commit();										
						if (flag == true) {
							cb2.call(this);
						} else {
							cb.call( this, null, 2 );
						}
					}
					else {
//						//TODO:Передать вызов по рации или повторить запрос
//						sw.swMsg.alert('Ошибка', resp.Err_Msg);
						Ext.Msg.show({
							title:lang['oshibka'],
							msg: resp.Err_Msg,
							buttons:  { 
								yes: "Повторить попытку",
								no: "Передать по рации",
								cancel: lang['otmena']
							},
							fn: function(param){
								
								switch (param) {
									case 'yes':
										parentObject.setEmergencyTeam(selectedRecord,EmergencyTeam_data);
									break;
									case 'no':										
										Ext.Ajax.request({
											params: {
												EmergencyTeam_id: EmergencyTeam_data.EmergencyTeam_id,
												CmpCallCard_id: selectedRecord.get('CmpCallCard_id')
											},
											url: '/?c=CmpCallCard&m=setEmergencyTeamWithoutSending',
											callback: function(o, s, r) {
												if(s) {								
													var resp = Ext.util.JSON.decode(r.responseText);
													if( resp.success ) {														
														selectedRecord.set('EmergencyTeam_Num', EmergencyTeam_data.EmergencyTeam_Num);
														selectedRecord.commit();																												
														if (flag == true) {															
															parentObject.emitEndEditingEvent(selectedRecord.get('CmpCallCard_id'));
															cb2.call( parentObject );
														} else {
															parentObject.emitEndEditingEvent(selectedRecord.get('CmpCallCard_id'));
															cb.call( parentObject, null, 2 );
														}
													} 
													else {
														sw.swMsg.alert(lang['oshibka'], resp.Error_Msg);
													}
												}
											}
										});
									break;
									case 'cancel':
										parentObject.emitEndEditingEvent(selectedRecord.get('CmpCallCard_id'));
									break;
									default:
									return false;
								}
							},
							animEl: 'elId',
							icon: Ext.MessageBox.QUESTION
						});
					}
				}
			}.createDelegate(this)
		});
	},
	
	selectLpuTransmit: function() {		
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;				
		
		if (record.get('Person_Birthday') != null) {
			var personBirthdayDate = new Date();
			var today = new Date();
			personBirthdayDate.setTime(Date.parse(record.get('Person_Birthday').replace(/(\d+).(\d+).(\d+)/, '$2/$1/$3'),'m/d/Y'));
			var personAge = today.getFullYear() - personBirthdayDate.getFullYear();
			var m = today.getMonth() - personBirthdayDate.getMonth();
			if (m < 0 || (m === 0 && today.getDate() < personBirthdayDate.getDate())) {
				personAge--;
			}


			if (personAge<1) {
				sw.swMsg.alert(lang['oshibka'], lang['patsientyi_do_goda_obslujivayutsya_v_smp']);
				return false;
			}
		}	
		var parentObject = this;
		this.emitEditingEvent( record.get('CmpCallCard_id'),function(){
			getWnd('swSelectLpuWithMedServiceWindow').show({
				MedServiceType_id: 18,
				MedServiceType_Name: lang['clujba_neotlojnoy_pomoschi'],
				callback: function(data) {
					parentObject.setLpuTransmit(record, data);
				},
				onCancel: function(){
					parentObject.emitEndEditingEvent( record.get('CmpCallCard_id'));
				}
			});
		});
	},
	setLpuTransmit: function(selrecord, lpu_data) {
		var cb = this.setStatusCmpCallCard;
		/*
		if( selrecord.get('CmpGroup_id') == 5 ) {
			var cb = this.setStatusCmpCallCard;
		}
		*/
		if ((typeof lpu_data == 'undefined')||(typeof lpu_data.Lpu_id == 'undefined')||(lpu_data==null)) {
			var selrecord = this.GridPanel.getSelectionModel().getSelected();
			if(!selrecord) return false;
		}
		
		this.getLoadMask(lang['sohranenie']).show();
		Ext.Ajax.request({
			params: {
				Lpu_ppdid: lpu_data.Lpu_id,
				CmpCallCard_id: selrecord.get('CmpCallCard_id')
			},
			url: '/?c=CmpCallCard&m=setLpuTransmit',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
						selrecord.set('SendLpu_Nick', lpu_data.Lpu_Nick);
						selrecord.set('CmpGroup_id', lpu_data.Lpu_id > 0 ? 1 : 2);
						selrecord.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						
						if( selrecord.get('CmpGroup_id') == 5 ) {
							cb.call( this, null, 0 );
						} else {
							cb.call( this, null, 1 );
						}
					}
				} else {
					this.emitEndEditingEvent(selrecord.get('CmpCallCard_id'));
				}
			}.createDelegate(this)
		});
	},
	
	deleteLpuTransmit: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		this.emitEditingEvent(record.get('CmpCallCard_id'),function(){
			parentObject.setLpuTransmit(record, {
				Lpu_id: 0,
				Lpu_Nick: ''
			});
		});
	},
	
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
	
	refuseCmpCallCard: function() {
		var parentObject = this;
		var record = this.GridPanel.getSelectionModel().getSelected();
		if( !record || !record.get('CmpCallCard_id') ) {
			return false;
		}
		var parentObject = this;
		this.emitEditingEvent( record.get('CmpCallCard_id'), function(){
			Ext.Msg.prompt(lang['prichina_otkaza'], lang['vvedite_prichinu'], function(btn, txt) {
				if( btn == 'ok' ) {
					if( txt == '' ) {
						return sw.swMsg.alert(lang['oshibka'], lang['vyi_doljnyi_vvesti_prichinu_otkaza'], parentObject.rejectCmpCallCard, parentObject);
					}
					parentObject.setStatusCmpCallCard(null, 5, txt);
				} else {
					parentObject.emitEndEditingEvent(record.get('CmpCallCard_id'));
				}
			}, parentObject, true);
		});
	},
	
	initComponent: function() {
		
		var form = this;				
		this.task = {run: this.doSearch.createDelegate(this), interval: 30*1000};
		
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
						items: [{
							comboSubject: 'CmpLpu',
							fieldLabel: lang['kuda_dostavlen'],
							hiddenName: 'CmpLpu_id',
							listeners: {
								'keydown': form.onKeyDown
							},
							listWidth: 400,
							width: 200,
							xtype: 'swcommonsprcombo'
						}]
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
			{dataIndex: 'PPD_WaitingTime', hidden: true, hideable: false},
			{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
			{dataIndex: 'Server_id', hidden: true, hideable: false},
			{dataIndex: 'Person_Surname', hidden: true, hideable: false},
			{dataIndex: 'Person_Firname', hidden: true, hideable: false},
			{dataIndex: 'Person_Secname', hidden: true, hideable: false},
			{dataIndex: 'Person_Age', hidden: true, hideable: false},
			{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_isLocked', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya'], width: 110},
			{dataIndex: 'CmpCallCard_Numv', header: lang['№_vyizova_za_den'], width: 100},
			{dataIndex: 'CmpCallCard_Ngod', header: lang['№_vyizova_za_god'], width: 100},
			{dataIndex: 'Person_FIO', header: lang['patsient'], width: 250},
			{dataIndex: 'Person_Birthday', header: lang['data_rojdeniya'], width: 100},
			{dataIndex: 'CmpCallType_Name', header: lang['tip_vyizova'], width: 200},
			{dataIndex: 'CmpReason_Name', header: lang['povod'], width: 200},
			{dataIndex: 'Adress_Name', header: lang['mesto_vyizova'], width: 300},
			{dataIndex: 'CmpLpu_Name', header: lang['lpu_prikrepleniya'], width: 100},
			{dataIndex: 'SendLpu_Nick', header: lang['lpu_peredachi'], width: 100},
			{dataIndex: 'EmergencyTeam_Num', header: lang['№_brigadyi'], width: 100},
			{dataIndex: 'CmpDiag_Name', header: lang['diagnoz_smp'], width: 100},
			{dataIndex: 'StacDiag_Name', header: lang['diagnoz_statsionara'], width: 130},
			{dataIndex: 'PPDUser_Name', header: lang['prinyal'], width: 200},
			{dataIndex: 'ServeDT', header: lang['obslujeno'], width: 150},
			{dataIndex: 'PPDResult', header: lang['rezultat_obrabotki_v_nmp'], width: 300, render: function(v, p, r) {
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
			url: '/?c=CmpCallCard&m=loadSMPDispatchDirectWorkPlace'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			title: ' - ',
			id: form.id + '_Grid',
			hideTaskTime: true,
			paging: false,
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
				{name: 'action_add', iconCls: 'add16', text: lang['dobavit'], tooltip: lang['dobavit_kartu'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['add'])},
				{name: 'action_edit', iconCls: 'edit16', text: lang['izmenit'], tooltip: lang['izmenit_kartu'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['edit'])},
				{name: 'action_view', iconCls: 'view16', text: lang['prosmotr'], tooltip: lang['smotret_kartu'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view'])},
				{name: 'action_delete', iconCls: 'delete16', text: lang['udalit'], tooltip: lang['udalit_kartu'], handler: this.deleteCmpCallCard.createDelegate(this)},
				{name: 'action_refresh', iconCls: 'refresh16', text: lang['obnovit'], handler: function(btn) {this.autoEvent = false;this.doSearch();}.createDelegate(this)},
				{name: 'action_print', iconCls: 'print16', text: lang['pechat_spiska'], 
					handler: 
						function() { 
							var params = {};
							params.notPrintEmptyRows = true;
							Ext.ux.GridPrinter.print(this.GridPanel, params);
						}.createDelegate(this) 
				},
				{name: 'action_transmit', text: lang['prinyat'], tooltip: lang['naznachit_brigadu'], handler: this.selectEmergencyTeam.createDelegate(this)},
				{name: 'action_back', text: lang['otklonit'], handler: this.deleteLpuTransmit.createDelegate(this)},
				{name: 'action_served', text: lang['obslujeno'], handler: this.setServedStatus.createDelegate(this, [null, 4])},
				{name: 'action_closecard', text: lang['zakryit_kartu_vyizova'], tooltip: lang['zakryit_kartu_vyizova'], handler:  function() {						
						var record = this.GridPanel.getSelectionModel().getSelected();
						if(!record) return false;
						//console.log(record);
						if (record.get('EmergencyTeam_Num') != null) {
							this.closeCmpCallCard();
						} else {
							this.selectEmergencyTeam(true);
						}
				}.createDelegate(this)},				
				{name: 'action_printcard', text: lang['pechat_110u'], tooltip: lang['pechat_110u'], handler: this.printCmpCallCard.createDelegate(this)},				
				{name: 'action_refuse', text: lang['otkaz'], tooltip: lang['otkaz'], handler: this.refuseCmpCallCard.createDelegate(this)},
				{name: 'action_selectlpu', text: lang['peredat_v_nmp'], tooltip: lang['vyibrat_lpu_dlya_peredachi'], handler: this.selectLpuTransmit.createDelegate(this)},
				//{name: 'action_nottransmit', text: 'Невозможно передать', tooltip: 'Невозможно передать', handler: this.deleteLpuTransmit.createDelegate(this)},
//				{name: 'action_open', hidden: true, text: 'Открыть (тест)', handler: this.setStatusCmpCallCard.createDelegate(this, [2, null])}
				
//				{name: 'action_report', text: 'Суточный рапорт', tooltip: 'Суточный рапорт', handler: this.printReportCmpCallCard.createDelegate(this)}			
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
			this.getAction('action_edit').setDisabled(d);
			this.getAction('action_view').setDisabled(d);
			this.getAction('action_delete').setDisabled(d);
			this.getAction('action_transmit').setDisabled(d);
			this.getAction('action_back').setDisabled(d);
			this.getAction('action_printcard').setDisabled(d);
			this.getAction('action_served').setDisabled(d);
			this.getAction('action_refuse').setDisabled(d);
			this.getAction('action_closecard').setDisabled(d);			
			//this.getAction('action_nottransmit').setDisabled(d);
			this.getAction('action_selectlpu').setDisabled(d);
			
			if( Ext.get(this.getView().getGroupId(7)) != null && this.firstShow ) {
				this.getView().toggleGroup(this.getView().getGroupId(7), false);
				this.firstShow = false;
			}
			
			form.addEmptyRecord();
			form.refreshTaskTime();
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getView().getRowClass = function(record, index) {
			if (record.data['CmpCallCard_isLocked']==1) {
				return 'grid-locked-row';
			}
		}
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
//			console.log(rec.get('CmpCallCard_id'))
			this.getAction('action_edit').setDisabled( !rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );			
			this.getAction('action_view').setDisabled( rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );
			this.getAction('action_delete').setDisabled( !rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1);
//			this.getAction('action_refresh').setDisabled( rec.get('CmpCallCard_id') == null );
			this.getAction('action_printcard').setDisabled( !rec.get('CmpGroup_id').inlist([9]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );			
			this.getAction('action_transmit').setDisabled(!rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_back').setDisabled(!rec.get('CmpGroup_id').inlist([4,5,7]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );			
			this.getAction('action_served').setDisabled(!rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );
			this.getAction('action_closecard').setDisabled( !rec.get('CmpGroup_id').inlist([3]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );
			this.getAction('action_refuse').setDisabled( !rec.get('CmpGroup_id').inlist([1,2,3,4]) || rec.get('CmpCallCard_id') == null|| rec.get('CmpCallCard_isLocked')==1 );
			this.getAction('action_selectlpu').setDisabled( !rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null|| rec.get('Person_id') == null|| rec.get('Person_Age') == 0|| rec.get('CmpCallCard_isLocked')==1 );
			form.setTimeMenu(rec.get('PPD_WaitingTime'));
			//this.getAction('action_nottransmit').setDisabled( !rec.get('CmpGroup_id').inlist([2,5]) || rec.get('CmpCallCard_id') == null );			
			
			
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			var noedit = this.getAction('action_edit').isDisabled();
			this.getAction( noedit ? 'action_view' : 'action_edit' ).execute();
		});
		
		
		sw.Promed.swWorkPlaceSMPDispatcherDirectWindow.superclass.initComponent.apply(this, arguments);
	}
});
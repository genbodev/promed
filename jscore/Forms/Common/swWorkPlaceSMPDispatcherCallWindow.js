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

sw.Promed.swWorkPlaceSMPDispatcherCallWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
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
		activate: function(){
			this.doSearch();
		},
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
		this.emitEditingEvent(record.get('CmpCallCard_id'), function(){
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
										grid.getStore().reload();
									}

									if ( grid.getStore().getCount() > 0 ) {
										grid.getView().focusRow(0);
										grid.getSelectionModel().selectFirstRow();
									}
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
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
	id: 'swWorkPlaceSMPDispatcherCallWindow',
	openCmpCallCardEditWindow: function(action) {		
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		var wnd = '';
		
		switch ( this.ARMType.toString() ) {
			case 'smpdispatchcall':
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
/*
			// Обновить запись в grid
			var record = grid.getStore().getById(data.cmpCallCardData.CmpCallCard_id);

			if ( record ) {
				var grid_fields = new Array();
				var i = 0;

				grid.getStore().fields.eachKey(function(key, item) {
					grid_fields.push(key);
				});

				for ( i = 0; i < grid_fields.length; i++ ) {
					record.set(grid_fields[i], data.cmpCallCardData[grid_fields[i]]);
				}

				record.commit();
			}
			else {
				if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('CmpCallCard_id') ) {
					grid.getStore().removeAll();
				}

				grid.getStore().loadData({'data': [ data.cmpCallCardData ]}, true);
			}
*/
		}.createDelegate(this);

		if ( action == 'add' ) {
			formParams.CmpCallCard_id = 0;

			params.onHide = function() {
				//grid.getView().focusRow(0);
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
		this.ARMType = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;

		//проверяем регион 63-самара		
		var opts = getGlobalOptions();
		var formParams = new Object();
		var params = new Object();
//		if ( opts.region.number == 63 ) {
//			
//			if ( getWnd('swWorkPlaceSMPDispatcherCallWindow2').isVisible() ) 
//			{			
//			sw.swMsg.alert('Сообщение', 'Окно диспетчера вызовов уже открыто');
//			return false;				
//			}
//			//alert (this.ARMType);
//			formParams.ARMType = this.ARMType;
//			params.formParams = formParams;
//			
//			getWnd('swWorkPlaceSMPDispatcherCallWindow2').show(params);
//			
//			return false;
//		}

		if ( !this.ARMType.toString().inlist([ 'smpdispatchcall' ]) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], function() {this.hide();}.createDelegate(this) );
			return false;
		}
		
		loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), lang['kuda_dostavlen']);

		switch ( this.ARMType.toString() ) {
			case 'smpdispatchcall':
				this.GridPanel.getAction('action_add').show();
			break;
		}
		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}
		
		opts = getGlobalOptions();

		this.startTask();
		this.startTimer();
		sw.Promed.swWorkPlaceSMPDispatcherCallWindow.superclass.show.apply(this, arguments);
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
			return false
		}
		var record = this.GridPanel.getStore().getAt(idx)
		if(!record) return false;
		for (k in data) {
			if (data.hasOwnProperty(k)) {
				if (typeof record.get(k) != 'undefined') {
					console.log(k);
					record.set(k,data[k]);
				}
			}
		}
		record.set('CmpGroup_id',data['DispatchCall_CmpGroup_id']);//CALL
		record.set('CmpGroupName_id',data['DispatchCall_CmpGroupName_id']);//CALL
		
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
		console.log(data);
		data['CmpGroup_id'] = data['DispatchCall_CmpGroup_id'];
		data['CmpGroupName_id'] = data['DispatchCall_CmpGroupName_id'];

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
			topTitle.setTitle('АРМ диспетчера вызовов. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
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
	
	getGroupName: function(id) {
		var groups = [
			lang['prinyatyie_zvonki'],
			lang['peredanyi_v_smp'],
			lang['prinyatyi_smp'],
			lang['obslujenyi_v_smp'],
			lang['peredannyie_v_nmp'],
			lang['prinyatyi_v_nmp'],
			lang['otklonenyi_nmp'],
			lang['obslujenyi_nmp'],
			lang['otkaz'],
			lang['zakryityie']
		];
		if( id ) {
			return groups[id-1];
		} else {
			return groups;
		}
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
		var cb = this.setStatusCmpCallCard;		/*
		if( selrecord.get('CmpGroup_id') == 5 ) {
			var cb = this.setStatusCmpCallCard;
		}
		*/			   
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
				}
			}.createDelegate(this)
		});
	},

	getRowCount: function(group_id) {
		//log(group_id);
		var gs = [];
		this.GridPanel.getStore().each(function(r) {
			if( r.get('CmpGroupName_id') == group_id && r.get('CmpCallCard_id') != null ) {
				gs.push(r.get('CmpCallCard_id'));
			}
		});
		//var str = gs.length.toString(),
		//	smb = str.slice(str.length-1, str.length);

		return gs.length;
	},
	transmitToDispatchDirect: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
		this.emitEditingEvent(record.get('CmpCallCard_id'), function(){
			parentObject.setStatusCmpCallCard(null, 1);
		})
	},
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment, refuse_reason_id) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		var parentObject = this;
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
						if (!parentObject.emitEndEditingEvent(record.get('CmpCallCard_id')))
							this.doSearch();
						/*
						record.set('CmpGroup_id', obj.CmpGroup_id);
						if( obj.CmpGroup_id != 5 ) {
							record.set('PPDResult', '');
						}
						record.commit();
						
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						*/						
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
			data['CmpGroupName_id'] = (data['CmpGroup_id']==10)?('10'):('0'+data['CmpGroup_id']+'');
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
	
	
	
	refuseCmpCallCard: function() {
		var refuse_CmpReason_id = 0;
		// Регулярное выражение для фильтрации по кодам отказа (Подборка из БД dbo.CmpReason). 
		var refuse_RegExp = /509|510|511|559|560|588|589|596|597|601|602|610|611|622|623|641|642|646|647|681|682|759|761|773/;
		var parent_object = this;
		
		var selrecord = this.GridPanel.getSelectionModel().getSelected();
		log(this.GridPanel.getStore());
		if(!selrecord) return false;
		
		this.emitEditingEvent(selrecord.get('CmpCallCard_id'), function(){
			var refuseCmpCallCardWin = new Ext.Window({
				width:400,
				heigth:300,
				title:lang['vvedite_kod_otkaza_i_kommentariy'],
				modal: true,
				draggable:false,
				resizable:false,
				closable : false,
				listeners: {
					'hide': function() {
						parent_object.emitEndEditingEvent(selrecord.get('CmpCallCard_id'));
					}
				},
				items:[{
					xtype: 'form',
					bodyStyle: {padding: '10px'},
					disabledClass: 'field-disabled',
					hiddenName: 'refuse_form',
					items:
					[{
					//comboSubject: 'CmpReason',
						disabledClass: 'field-disabled',
						fieldLabel: lang['povod'],
						allowBlank: false,
						hiddenName: 'CmpReason_id',
						// tabIndex: TABINDEX_PEF + 5,
						width: 250,
						store: new Ext.db.AdapterStore({
							dbFile: 'Promed.db',
							fields: [
								{name: 'CmpReason_id', mapping: 'CmpReason_id'},
								{name: 'CmpReason_Code', mapping: 'CmpReason_Code'},
								{name: 'CmpReason_Name', mapping: 'CmpReason_Name'}
							],
							autoLoad: true,
							key: 'CmpReason_id',
							sortInfo: {field: 'CmpReason_Code'},
							tableName: 'CmpReason'
						}),
						mode: 'local',
						triggerAction: 'all',
						listeners: {
							//render: function() { this.getStore().load(); },
							select: function(c, r, i) {
								this.setValue(r.get('CmpReason_id'));
								this.setRawValue(r.get('CmpReason_Code')+'.'+r.get('CmpReason_Name'));
								refuse_CmpReason_id = r.get('CmpReason_id');
							},
							blur: function() {
								this.collapse();
								if ( this.getRawValue() == '' ) {
									this.setValue('');
									if ( this.onChange && typeof this.onChange == 'function' ) {
										this.onChange(this, '');
									}
								} else {
									var store = this.getStore(),
									val = this.getRawValue().toString().substr(0, 5);
									val = LetterChange(val);
									if ( val.charAt(3) != '.' && val.length > 3 ) {
										val = val.slice(0,3) + '.' + val.slice(3, 4);
									}
									val = val.replace(' ', '');
									var yes = false;
									store.each(function(r){
										if ( r.get('CmpReason_Code') == val ) {
											this.setValue(r.get(this.valueField));
											this.fireEvent('select', this, r, 0);
											this.fireEvent('change', this, r.get(this.valueField), '');
											if ( this.onChange && typeof this.onChange == 'function') {
												this.onChange(this, r.get(this.valueField));
											}
											yes = true;
											return true;
										}
									}.createDelegate(this));
									/*if (!yes) {
										this.setValue(null);
										this.fireEvent('change', this, null, '');
										if ( this.onChange && typeof this.onChange == 'function') {
											this.onChange(this, null);
										}
									}*/
								}
							}
						},
						doQuery: function(q) {
							var c = this;
							this.getStore().load({
								callback: function() {
									this.filter('CmpReason_Code', q);
									this.loadData(getStoreRecords(this));
									if( this.getCount() == 0 ) {
										c.setRawValue(q.slice(0, q.length-1));
										c.doQuery(c.getRawValue());
									}
									c[ c.expanded ? 'collapse' : 'expand' ]();
									this.filter('CmpReason_id',refuse_RegExp);
								}
							});
						},
						onTriggerClick: function() {
							this.focus();
							if( this.getStore().getCount() == 0 || this.isExpanded() ) {
								this.collapse();
								return;
							}
							if(this.getValue() > 0) {
								this[ this.isExpanded() ? 'collapse' : 'expand' ]();
							} else {
								this.doQuery(this.getRawValue());
							}
						},
						tpl: new Ext.XTemplate(
							'<tpl for="."><div class="x-combo-list-item">',
							'<font color="red">{CmpReason_Code}</font>.{CmpReason_Name}',
							'</div></tpl>'
						),
						valueField: 'CmpReason_id',
						displayField: 'CmpReason_Name',
						xtype: 'swbaselocalcombo',
						style: 'padding: 0px; padding-top: 2px; margin-bottom: 5px;',
						id: 'Combobox'
					},
					{
						disabledClass: 'field-disabled',
						fieldLabel: lang['kommentariy'],
						height: 100,
						name: 'CmpCallCard_Comm',
						id: 'refuse_comment',
						// tabIndex: TABINDEX_PEF + 5,
						width: 250,
						xtype: 'textarea',
						style: 'padding: 0; padding-top: 5px; margin-bottom: 5px;'
					}]
				}],
				buttons:[{
					text:lang['ok'],
					id:'save',
					handler:function(){
						if (refuse_CmpReason_id <= 0) {
							sw.swMsg.show({
								buttons: Ext.Msg.OK,
								icon: Ext.Msg.WARNING,
								msg: lang['doljen_byit_vyibran_povod_otkaza'],
								title: ERR_INVFIELDS_TIT
							});
							return false;
						}
						else {
							var refuse_comment = Ext.getCmp('refuse_comment').getValue();
							parent_object.setStatusCmpCallCard(null, 5, refuse_comment, refuse_CmpReason_id);
							refuseCmpCallCardWin.close();
						}
					}
				},
				{
					text: lang['otmena'],
					handler: function(){
						refuseCmpCallCardWin.close();
						//this.formStatus='edit';
						//ConfirmDublicateWin.close();
						//parent_object.hide();
					}
				}]
			})
			refuseCmpCallCardWin.show();
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
			{dataIndex: 'CmpDiag_Name', header: lang['diagnoz_smp'], width: 100},
			{dataIndex: 'StacDiag_Name', header: lang['diagnoz_statsionara'], width: 130},
			{dataIndex: 'PPDUser_Name', header: lang['prinyal'], width: 200},
			{dataIndex: 'ServeDT', header: lang['obslujeno'], width: 150},
			{dataIndex: 'PPDResult', header: lang['rezultat_obrabotki_v_nmp'], width: 300, render: function(v, p, r) {
				//var res = '';
				//ext:qtip=
				return '<p title="'+v+'">'+v+'</p>';
			}},
			{dataIndex: 'CmpGroupName_id',  hidden: true, hideable: false},
			{dataIndex: 'CmpGroup_id', hidden: true, hideable: false},
			{dataIndex: 'Owner', hidden: true, hideable: false, id: 'ownerColumn'}
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
			url: '/?c=CmpCallCard&m=loadSMPDispatchCallWorkPlace'
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
							if (keys_enable)
								this.GridPanel.ViewActions.action_delete.execute();
							break;
						case Ext.EventObject.F6:
							this.openPCardHistory();
							break;
						case Ext.EventObject.F10:
							if (keys_enable)
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
				{name: 'action_transmit', text: lang['peredat_dispetcheru_napravleniy'], tooltip: lang['peredat_dispetcheru_napravleniy'], handler: this.transmitToDispatchDirect.createDelegate(this)},
				{name: 'action_refuse', text: lang['otkaz'], tooltip: lang['otkaz'], handler: this.refuseCmpCallCard.createDelegate(this)},
				{name: 'action_selectlpu', text: lang['peredat_v_nmp'], tooltip: lang['vyibrat_lpu_dlya_peredachi'], handler: this.selectLpuTransmit.createDelegate(this) },
				{name: 'action_open', hidden: true, text: lang['otkryit_test'], handler: this.setStatusCmpCallCard.createDelegate(this, [2, null])},
				{name: 'action_close', hidden: true, text: lang['zakryit_test'], handler: this.setStatusCmpCallCard.createDelegate(this, [1, null])}
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

		var keys_enable;

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
			this.getAction('action_refuse').setDisabled(d);
			this.getAction('action_selectlpu').setDisabled(d);
			keys_enable = false;
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
			if (record.data.Owner == 0) {
				return 'hidden-own';
			}
			if (record.data['CmpCallCard_isLocked']==1) {
				return 'grid-locked-row';
			}
		}
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
		keys_enable = (rec.get('Owner') == 1); // определяем возможность использования функциональных клавиш
			this.getAction('action_transmit').setDisabled(rec.get('CmpGroup_id') != 1 || rec.get('CmpCallCard_id') == null || rec.get('Owner') == 0|| rec.get('CmpCallCard_isLocked')==1 );
			this.getAction('action_refuse').setDisabled( rec.get('CmpGroup_id').inlist([4,8,9]) || rec.get('CmpCallCard_id') == null || rec.get('Owner') == 0|| rec.get('CmpCallCard_isLocked')==1);
			//this.getAction('action_nottransmit').setDisabled( !rec.get('CmpGroup_id').inlist([1,5]) || rec.get('CmpCallCard_id') == null );			
			this.getAction('action_view').setDisabled( rec.get('CmpCallCard_id') == null || rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_edit').setDisabled( rec.get('CmpGroup_id') != 1 || rec.get('CmpCallCard_id') == null || rec.get('Owner') == 0|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_delete').setDisabled( rec.get('CmpGroup_id') != 1 || rec.get('CmpCallCard_id') == null || rec.get('Owner') == 0|| rec.get('CmpCallCard_isLocked')==1);
			this.getAction('action_selectlpu').setDisabled( !rec.get('CmpGroup_id').inlist([1,7]) || rec.get('CmpCallCard_id') == null|| rec.get('Person_id') == null|| rec.get('Person_Age') == 0 || rec.get('CmpCallCard_isLocked')==1);
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			var noedit = this.getAction('action_edit').isDisabled();
			this.getAction( noedit ? 'action_view' : 'action_edit' ).execute();
		});
		
		sw.Promed.swWorkPlaceSMPDispatcherCallWindow.superclass.initComponent.apply(this, arguments);
	}
});
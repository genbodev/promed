/**
* АРМ врача/оператора СМП
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

sw.Promed.swWorkPlaceSMPWindow = Ext.extend(sw.Promed.swWorkPlaceSMPDefaultWindow, {
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

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('CmpCallCard_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

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
									this.addEmptyRecord();
								}

								if ( grid.getStore().getCount() > 0 ) {
									grid.getView().focusRow(0);
									grid.getSelectionModel().selectFirstRow();
								}
							}
							else {
								sw.swMsg.alert(lang['oshibka'], lang['pri_udalenii_kartyi_vyizova_voznikli_oshibki']);
							}
						}.createDelegate(this),
						params: {
							CmpCallCard_id: record.get('CmpCallCard_id')
						},
						url: '/?c=CmpCallCard&m=deleteCmpCallCard'
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu_vyizova'],
			title: lang['vopros']
		});
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	id: 'swWorkPlaceSMPWindow',
	
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}
		
		var wnd = 'swCmpCallCardNewShortEditWindow';

		if ( getWnd(wnd).isVisible() ) {
			sw.swMsg.alert('Сообщение', 'Окно редактирования карты вызова уже открыто');
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
				//grid.getView().focusRow(0);
			};
			formParams.ARMType = this.ARMType;
			if (selected_record) {
				formParams.CmpCloseCard_Id = selected_record.get('CmpCloseCard_id');
			}
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
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
				if (action == 'edit') {
					parentObject.emitEndEditingEvent(selected_record.get('CmpCallCard_id'));
				}
			};
			if (selected_record) {
				formParams.CmpCloseCard_Id = selected_record.get('CmpCloseCard_id');
			}
			formParams.ARMType = this.ARMType;
			params.formParams = formParams;
			if (action == 'edit') {
				//this.emitEditingEvent(selected_record.get('CmpCallCard_id'),function(){
					getWnd(wnd).show(params);
				//});
			} else {
				getWnd(wnd).show(params);
			}	
		}
	},
	
	/*
	openCmpCallCardEditWindow: function(action) {		
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		if ( this.ARMType.toString() == 'smpvr' && action == 'add' ) {
			return false;
		}

		var wnd = '';

		switch ( this.ARMType.toString() ) {
			case 'smpreg':
				wnd = 'swCmpCallCardShortEditWindow';
			break;

			case 'smpvr':
				wnd = 'swCmpCallCardEditWindow';
			break;
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

			// Обновить grid
			grid.getStore().reload();
			this.autoEvent = false;

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

		}.createDelegate(this);

		if ( action == 'add' ) {
			formParams.CmpCallCard_id = 0;
			formParams.ARMType = this.ARMType;
			params.onHide = function() {
				//grid.getView().focusRow(0);
			};
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
				grid.getView().focusRow(grid.getStore().indexOf(selected_record));
			};
		}

		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	*/
	show: function() {
		sw.Promed.swWorkPlaceSMPWindow.superclass.show.apply(this, arguments);
		this.setTimeMenuVisibility();
		this.ARMType = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0].userMedStaffFact || null;

		if ( !this.ARMType.toString().inlist([ 'smpvr', 'smpreg' ]) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), lang['kuda_dostavlen']);

		switch ( this.ARMType.toString() ) {
			case 'smpreg':
				this.GridPanel.getAction('action_add').show();
			break;

			case 'smpvr':
				this.GridPanel.getAction('action_add').hide();
			break;
		}
		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}
		
		this.startTask();
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
			lang['ne_mogut_byit_peredanyi_v_nmp'],
			lang['ojidanie_v_nmp'],
			lang['prinyatyi_v_nmp'],
			lang['otklonenyi_nmp'],
			lang['obslujenyi_nmp'],
			lang['zakryityie'],
			lang['otkaz']
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
	
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment, parent_obj, CmpReturnToSmpReason_id) {
		var true_this = (!parent_obj)? this : parent_obj;
		var record = true_this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		true_this.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id'),
				CmpCallCardStatusType_id: StatusType_id,
				CmpReturnToSmpReason_id: CmpReturnToSmpReason_id || null,
				CmpCallCardStatus_Comment: StatusComment || null,
				CmpCallCard_IsOpen: IsOpen
			},
			callback: function(o, s, r) {
				true_this.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						record.set('CmpGroup_id', obj.CmpGroup_id);
						if( obj.CmpGroup_id != 5 ) {
							record.set('PPDResult', '');
						}
						record.commit();
						with(true_this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						true_this.addEmptyRecord();
						true_this.autoEvent = false;
						true_this.doSearch();
					}
				}
			}.createDelegate(true_this)
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
				if (i != 1) { //Скрываем группу "Невозможно передать" пока в ней что-нибудь не появится
					gs.push(+i);
				}
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
	selectLpuTransmit: function() {		
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;		
		getWnd('swSelectLpuWithMedServiceWindow').show({
			MedServiceType_id: 18,
			MedServiceType_Name: lang['clujba_neotlojnoy_pomoschi'],
			callback: function(data) {
					this.setLpuTransmit(record, data);
			}.createDelegate(this)
		});
	},
	
	deleteLpuTransmit: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		this.setLpuTransmit(record, {
			Lpu_id: 0,
			Lpu_Nick: ''
		});
	},
	
	setLpuTransmit: function(selrecord, lpu_data) { 
		if( selrecord.get('CmpGroup_id').inlist([1,4]) ) {
			var parent_object = this;
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
						//selrecord.set('CmpGroup_id', lpu_data.Lpu_id > 0 ? 1 : 2);
						selrecord.commit();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						if( parent_object ) parent_object.setStatusCmpCallCard(2, 1, null,parent_object);
					}
				}
			}.createDelegate(this)
		});
	},	
	// Выбор ЛПУ со службой НМП для просмотре оперативной обстановки
	selectLpuOperEnv: function() {
		getWnd('swSelectLpuWithMedServiceWindow').show({
			MedServiceType_id: 18,
			MedServiceType_Name: lang['clujba_neotlojnoy_pomoschi'],
			callback: function(data){
				this.openLpuOperEnv( data.Lpu_id );
			}.createDelegate(this)
		});
	},
	
	// Просмотр оперативной обстановки выбранного ЛПУ
	openLpuOperEnv: function( Lpu_id ){
		getWnd('swLpuOperEnvWindow').show({
			Lpu_id: Lpu_id
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
	
	rejectCmpCallCard: function() {
		var parent_obj = this
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		getWnd('swSelectReturnToSmpReason').show({
			callback: function(data) {
				this.deleteLpuTransmit();
				this.setStatusCmpCallCard(this, 1, data.comment, parent_obj, data.CmpReturnToSmpReason_id);
			}.createDelegate(this)
		});
	},
	
	refuseCmpCallCard: function() {
		Ext.Msg.prompt(lang['prichina_otkaza'], lang['vvedite_prichinu'], function(btn, txt) {
			if( btn == 'ok' ) {
				if( txt == '' ) {
					return sw.swMsg.alert(lang['oshibka'], lang['vyi_doljnyi_vvesti_prichinu_otkaza'], this.rejectCmpCallCard, this);
				}
				this.setStatusCmpCallCard(null, 5, txt);
			}
		}, this, true);
	},
	
	unrefuseCmpCallCard: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		this.getLoadMask(lang['snyatie_otkaza']).show();
		Ext.Ajax.request({
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id')
			},
			url: '/?c=CmpCallCard&m=unrefuseCmpCallCard',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {
					this.GridPanel.ViewActions.action_refresh.execute();
				}
			}.createDelegate(this)
		});
	},
	schedulePrint: function(action) {
        var params = {},
            _this = this,
            rec = _this.GridPanel.getSelectionModel().getSelected();

        if (!rec) {
            sw.swMsg.alert(lang['oshibka'], lang['ne_vyibrana_zapis']);
            return false;
        }

        params.notPrintEmptyRows = true;

        if (action && action == 'row') {
            params.rowId = rec.id;
        }

        Ext.ux.GridPrinter.print(_this.GridPanel, params);
    },
	initComponent: function() {
		var form = this;
		log(Ext.globalOptions);
		//id 10011393 -  ПЕРМЬ ГССМП #11426
		var timeMultiplier = (Ext.globalOptions.globals.lpu_id == 10011393)? 3 : 1;
		this.task = {run: this.doSearch.createDelegate(this), interval: timeMultiplier*60*1000};
		
		this.onKeyDown = function (inp, e) {
			if (e.getKey() == Ext.EventObject.ENTER) {
				e.stopEvent();
				this.autoEvent = false;
				this.doSearch();
				this.GridPanel.setParam('start', 0);
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
								form.GridPanel.setParam('start', 0);
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
					}, {
						layout: 'form',
						items:
							[{
								style: "padding-left: 10px",
								xtype: 'button',
								text: lang['schitat_s_kartyi'],
								iconCls: 'idcard16',
								handler: function()
								{
									form.readFromCard();
								}
							}]
					}]
				}]
			}
		});

		var gridFields = [
			{dataIndex: 'CmpCallCard_id', header: 'ID', key: true, hidden: true, hideable: false},
			{dataIndex: 'PPD_WaitingTime', hidden: true, hideable: false},
			{dataIndex: 'Person_id', hidden: true, hideable: false},
			{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
			{dataIndex: 'Server_id', hidden: true, hideable: false},
			{dataIndex: 'Person_Surname', hidden: true, hideable: false},
			{dataIndex: 'Person_Firname', hidden: true, hideable: false},
			{dataIndex: 'Person_Secname', hidden: true, hideable: false},
			{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
			{dataIndex: 'PersonQuarantine_IsOn', type: 'string', hidden: true},
			{dataIndex: 'CmpCallCard_prmDate', type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), header: lang['data_vremya'], width: 110},
			{dataIndex: 'CmpCallCard_Numv', header: lang['№_vyizova_za_den'], width: 100},
			{dataIndex: 'CmpCallCard_Ngod', header: lang['№_vyizova_za_god'], width: 100},
			{dataIndex: 'Person_FIO', header: lang['patsient'], width: 250},
			{dataIndex: 'Person_Birthday', header: lang['data_rojdeniya'], width: 100},
			{dataIndex: 'CmpCallType_Name', header: lang['tip_vyizova'], width: 200},
			{dataIndex: 'CmpReason_Name', header: lang['povod'], width: 200},
			{dataIndex: 'CmpLpu_Name', hidden: true, hideable: false},//header: 'ЛПУ', width: 100 },
			{dataIndex: 'CmpDiag_Name', header: lang['diagnoz_smp'], width: 100},
			{dataIndex: 'StacDiag_Name', header: lang['diagnoz_statsionara'], width: 130},
			{dataIndex: 'SendLpu_Nick', header: lang['lpu_peredachi'], width: 100},
			{dataIndex: 'PPDUser_Name', header: lang['prinyal'], width: 200},
			{dataIndex: 'ServedBy', header: lang['obslujeno'], width: 150},
			{dataIndex: 'PPDDiag', header: lang['diagnoz_nmp'], width: 200, render: function(v, p, r) {
				//var res = '';
				//ext:qtip=
				return '<p title="'+v+'">'+v+'</p>';
			}},
			{dataIndex: 'PPDResult', header:lang['rezultat_obrabotki_v_nmp'], width: 170},
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
			url: '/?c=CmpCallCard&m=loadSMPWorkPlace'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			firstShow: true,
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			title: lang['jurnal_rabochego_mesta'],
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
                {name:'action_print', text:BTN_GRIDPRINT, tooltip: BTN_GRIDPRINT, iconCls : 'x-btn-text', icon: 'img/icons/print16.png', menu: [
                    new Ext.Action({name:'print_rec', text:lang['pechat'], handler: function() {this.schedulePrint('row')}.createDelegate(this)}),
                    new Ext.Action({name:'print_all', text:lang['pechat_spiska'], handler: function() {this.schedulePrint()}.createDelegate(this)})
                ]},
				{name: 'action_back', text: lang['vernut_iz_nmp'], handler: this.rejectCmpCallCard.createDelegate(this)},
				{name: 'action_refuse', text: lang['otkaz'], tooltip: lang['otkaz'], handler: this.refuseCmpCallCard.createDelegate(this)},
				//{name: 'action_unrefuse', text: 'Снять отказ', tooltip: 'Снять отказ', handler: this.unrefuseCmpCallCard.createDelegate(this)},
				{name: 'action_selectlpu', text: lang['vyibrat_lpu'], tooltip: lang['vyibrat_lpu_dlya_peredachi'], handler: this.selectLpuTransmit.createDelegate(this)},
				{name: 'action_operenv', text: lang['operativnaya_obstanovka'], tooltip: lang['prosmotr_operativnoy_obstanovki_po_vyizovam_v_lpu_so_slujboy_nmp'], handler: this.selectLpuOperEnv.createDelegate(this)},
				{name: 'action_open', hidden: true, text: lang['otkryit_test'], handler: this.setStatusCmpCallCard.createDelegate(this, [2, null])},
				{name: 'action_close', hidden: true, text: lang['zakryit_test'], handler: this.setStatusCmpCallCard.createDelegate(this, [1, null])}
			],
			loadMask: {msg: lang['zagruzka']},
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			view: new Ext.grid.GroupingView({
				//groupTextTpl: '{[values.gvalue + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
				groupTextTpl: '{[values.gvalue-1 + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({["записей "+Ext.getCmp("'+this.id+'").getRowCount(values.gvalue)]})',
				enableGroupingMenu:false,
				getRowClass: function (row, index) {
					var cls = '';
					if (row.get('PersonQuarantine_IsOn') == 'true') {
						cls = cls + 'x-grid-rowbackred ';
					}
					return cls;
				}
			}),
			getAction: function(action) {
				return this.ViewActions[action] || null;
			},
			setParam: function(p, v) {
				this.getStore().baseParams[p] = v;
			},
			store: gridStore
		});
		
		var keys_enable = true;
		
		this.GridPanel.getStore().on('load', function(store, rs) {
			if(store.getCount()) {
				this.getSelectionModel().selectFirstRow();
				form.focusSelectedRow();
			}
			var d = !this.getSelectionModel().hasSelection();
			this.getAction('action_edit').setDisabled(d);
			this.getAction('action_view').setDisabled(d);
			this.getAction('action_delete').setDisabled(d);
			this.getAction('action_back').setDisabled(d);			
			this.getAction('action_refuse').setDisabled(d);
			this.getAction('action_selectlpu').setDisabled(d);
			keys_enable = false;
			if( Ext.get(this.getView().getGroupId(7)) != null && this.firstShow ) {
				this.getView().toggleGroup(this.getView().getGroupId(7), false);
				this.firstShow = false;
			}
			
			form.addEmptyRecord();
			form.refreshTaskTime();
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			this.getAction('action_back').setDisabled(!rec.get('CmpGroup_id').inlist([1,2,3,4]) || rec.get('CmpCallCard_id') == null );
			this.getAction('action_refuse').setDisabled( !rec.get('CmpGroup_id').inlist([1,2,3,4,5]) || rec.get('CmpCallCard_id') == null );
			//this.getAction('action_unrefuse').setDisabled( rec.get('CmpGroup_id') != 8 || rec.get('CmpCallCard_id') == null );
			this.getAction('action_edit').setDisabled( !rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null );
			this.getAction('action_delete').setDisabled( !rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null );
			
			form.setTimeMenu(rec.get('PPD_WaitingTime'));
			keys_enable = (!rec.get('CmpGroup_id').inlist([1,2]) || rec.get('CmpCallCard_id') == null)? false : true;
			
			this.getAction('action_selectlpu').setDisabled( !rec.get('CmpGroup_id').inlist([1,2,4]) || rec.get('CmpCallCard_id') == null );
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			var noedit = this.getAction('action_edit').isDisabled();
			this.getAction( noedit ? 'action_view' : 'action_edit' ).execute();
		});
		
		sw.Promed.swWorkPlaceSMPWindow.superclass.initComponent.apply(this, arguments);
	}
});
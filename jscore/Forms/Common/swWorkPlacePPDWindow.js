/**
* АРМ оператора НМП и/или врача НМП
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		
* @copyright    Copyright (c) 2012 Swan Ltd.
* @version      июнь.2012
*/
sw.Promed.swWorkPlacePPDWindow = Ext.extend(sw.Promed.swWorkPlaceWindow, {
	useUecReader: true,
	gridPanelAutoLoad: false,
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
				}
			},
			icon: Ext.MessageBox.QUESTION,
			msg: lang['udalit_kartu_vyizova'],
			title: lang['vopros']
		});
	},
	checkCovidPovod: function() {
		var me = this,
			row,
			els = me.getEl().select('.covidReason').elements;

		els.forEach(function (el) {
			row = el.closest('table');
			row.classList.add('covidReason');
		})
	},
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), {msg: lang['podojdite']});
		}

		return this.loadMask;
	},
	id: 'swWorkPlacePPDWindow',
	openCmpCallCardEditWindow: function(action) {
		if ( !action || !action.toString().inlist([ 'add', 'edit', 'view']) ) {
			return false;
		}

		if ( this.ARMType.toString() == 'smpvr' && action == 'add' ) {
			return false;
		}

		var wnd = 'swCmpCallCardNewShortEditWindow'; // 'swCmpCallCardEditWindow'

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
		};

		if ( action == 'add' ) {
			formParams.CmpCallCard_id = 0;

			params.onHide = function() {
				grid.getView().focusRow(0);
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
		formParams.ARMType = this.ARMType.toString();
		params.formParams = formParams;

		getWnd(wnd).show(params);
	},
	show: function() {
		sw.Promed.swWorkPlacePPDWindow.superclass.show.apply(this, arguments);

		this.ARMType = null;
		this.userMedStaffFact = null;
		
		if ( !arguments[0] || !arguments[0].ARMType ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], this.hide.createDelegate(this, []) );
			return false;
		}

		this.ARMType = arguments[0].ARMType;
		this.userMedStaffFact = arguments[0];

		if ( !this.ARMType.toString().inlist([ 'slneotl' ]) ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyiy_tip_arm'], this.hide.createDelegate(this, []) );
			return false;
		}

		var doings = new sw.Promed.Doings();

		//loadComboOnce(this.FilterPanel.getForm().findField('CmpLpu_id'), lang['kuda_dostavlen']);

		doings.start('loadMedServiceCombo');
		var MedServiceCombo = this.FilterPanel.getForm().findField('MedService_id');
		MedServiceCombo.setValue(this.userMedStaffFact.MedService_id);
		MedServiceCombo.getStore().baseParams = {
			MedServiceType_SysNick: 'slneotl',
			Lpu_id: getGlobalOptions().lpu_id,
			filterByCurrentMedPersonal: 2
		};
		loadComboOnce(MedServiceCombo, 'Служба НМП', function() {
			MedServiceCombo.setValue(MedServiceCombo.getValue());
			doings.finish('loadMedServiceCombo');
		});

		
		with(this.LeftPanel.actions) {
			action_RLS.setHidden(true);
			action_Mes.setHidden(true);
			action_Report.setHidden(true);
		}

		this.FilterPanel.fieldSet.expand();
		this.FilterPanel.fieldSet.collapse();

		//this.startTimer();
		this.startTask();						

		doings.doLater('doSearch', this.doSearch.createDelegate(this));
	},
	
	// <!-- КОСТЫЛЬ
	autoEvent: true,
	/*
	startTimer: function() {	
		var topTitle = this.GridPanel;
		setInterval(function(){			
			date = new Date(), 
			d = date.getDay(),
			mo = date.getMonth(),
			y = date.getFullYear(),
			h = date.getHours(), 
			m = date.getMinutes(), 
			s = date.getSeconds(), 
			d = (d < 10) ? '0' + d : d, 
			mo = (mo < 10) ? '0' + mo : mo, 
			h = (h < 10) ? '0' + h : h, 
			m = (m < 10) ? '0' + m : m, 
			s = (s < 10) ? '0' + s : s,			
			topTitle.setTitle('АРМ диспетчера ПДД. ' + window.MedPersonal_FIO + ', Сегодня ' + d + '.' + mo + '.' + y + 'г. ' + h + ':' + m + ':' + s);			
		 }, 1000); 
	},	
	*/
	
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
	
	buttonPanelActions: {
		action_messages: {
			iconCls: 'messages48',
			tooltip: lang['sistema_soobscheniy'],
			handler: function() {
				getWnd('swMessagesViewWindow').show();
			}
		},
		action_evnPLSearch: {
			iconCls: 'search32',
			tooltip: lang['poisk_talona_ambulatornogo_patsienta'],
			handler: function() {
				getWnd('swEvnPLSearchWindow').show();
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
	
	getGroupName: function(id) {
		var groups = [
			langs('Поступившие из СМП'),
			langs('Принятые из СМП'),
			langs('Обслуженные из СМП'),
			langs('Поступившие из НМП'),
			langs('Принятые из НМП'),
			langs('Обслуженные из НМП'),
			langs('Отказ'),
			langs('Отклоненные'),
			langs('Закрытые')
		];
		if( id ) {
			return groups[id-1];
		} else {
			return groups;
		}
	},
	
	setStatusCmpCallCard: function(IsOpen, StatusType_id, StatusComment, isreload, CmpCallCard_id, CmpMoveFromNmpReason_id) {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if ( typeof CmpCallCard_id == 'undefined' || CmpCallCard_id === null) {

			if ( !record ) {
				return false;
			}
			var CmpCallCard_id = record.get('CmpCallCard_id');
		}
		
		if( !CmpCallCard_id ) {
			return false;
		}
		
		var keyMedService_id = null;
		if(!record.get('MedService_id')){
			keyMedService_id = this.userMedStaffFact.MedService_id;
		}
			
		this.getLoadMask().show();
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setStatusCmpCallCard',
			params: {
				CmpCallCard_id: CmpCallCard_id,
				CmpCallCardStatusType_id: StatusType_id,
				CmpMoveFromNmpReason_id: CmpMoveFromNmpReason_id || null,
				CmpCallCardStatus_Comment: StatusComment || null,
				CmpCallCard_IsOpen: IsOpen,
				armtype: this.ARMType.toString(),
				MedService_id: keyMedService_id
			},
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {
					var obj = Ext.util.JSON.decode(r.responseText);
					if( obj.success ) {
						this.doSearch();
					}
				}
			}.createDelegate(this)
		});
	},
	setStatusServed: function(){
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		alert( selrecord.get('CmpGroup_id') );return;

	},
	setLpuTransmit: function(selrecord, lpu_data) {
		if( selrecord.get('CmpGroup_id') == 5 ) {
			var cb = this.setStatusCmpCallCard;
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
						this.GridPanel.getAction('action_refresh').execute();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						if( cb ) cb.call(this, null, 0);
					}
				}
			}.createDelegate(this)
		});
	},
	setResult: function(selrecord, res_data) {
		//if( selrecord.get('CmpGroup_id') == 5 ) {
		//	var cb = this.setStatusCmpCallCard;
		//}				
		this.getLoadMask(lang['sohranenie']).show();
		Ext.Ajax.request({
			params: {
				CmpPPDResult_id: res_data.CmpPPDResult_id,
				CmpCallCard_id: selrecord.get('CmpCallCard_id')
			},
			url: '/?c=CmpCallCard&m=setResult',
			callback: function(o, s, r) {
				this.getLoadMask().hide();
				if(s) {								
					var resp = Ext.util.JSON.decode(r.responseText);
					if( resp.success ) {
					//	selrecord.set('SendLpu_Nick', res_data.Lpu_Nick);
					//	selrecord.set('CmpGroup_id', res_data.Lpu_id > 0 ? 1 : 2);
					//	selrecord.commit();
						this.GridPanel.getAction('action_refresh').execute();
						with(this.GridPanel.getStore()) {
							var ss = getSortState();
							sort(ss.field, ss.direction);
						}
						this.addEmptyRecord();
						//if( cb ) cb.call(this, null, 0);
					}
				}
			}.createDelegate(this)
		});
	},
	createEvnPL: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		log(record);
		
		var CmpCallCard_id = record.get('CmpCallCard_id');
		
		if( record.get('Person_id') != null && record.get('Person_IsUnknown') != 2 ) {
			getWnd('swEvnPLEditWindow').show({
				action: 'add',
				CmpCallCard_id: CmpCallCard_id,
				Person_id: record.get('Person_id'),
				Server_id: record.get('Server_id'),
				PersonEvn_id: record.get('PersonEvn_id'),
				ServiceType_SysNick: 'neotl',
				callback: record.get('CmpGroup_id') != 3 || record.get('ServeDT') == null ? this.setStatusCmpCallCard.createDelegate(this, [null, 4, null, true, CmpCallCard_id]) : Ext.emptyFn
			});
		} else {
			getWnd('swPersonSearchWindow').show({
				onSelect: function(data) {
					getWnd('swPersonSearchWindow').hide();
					getWnd('swEvnPLEditWindow').show({
						action: 'add',
						CmpCallCard_id: CmpCallCard_id,
						Person_id: data.Person_id,
						Server_id: data.Server_id,
						ServiceType_SysNick: 'neotl',
						PersonEvn_id: data.PersonEvn_id,
						callback: function(data) {
							this.setPerson({Person_id: data.Person_id, CmpCallCard_id: CmpCallCard_id}, this.setStatusCmpCallCard.createDelegate(this, [null, 4, null, true, CmpCallCard_id]));
						}.createDelegate(this, [data])
					});
				}.createDelegate(this)
			})
		}
	},
	
	setPerson: function(data, cb) {
		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=setPerson',
			params: {
				Person_id: data.Person_id,
				CmpCallCard_id: data.CmpCallCard_id
			},
			callback: cb || Ext.emptyFn
		});
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
	servedCmpCallCard: function() {
		var record = this.GridPanel.getSelectionModel().getSelected();
		if(!record) return false;
		
		getWnd('swSelectResultWindow').show({
			MedServiceType_id: 18,
			MedServiceType_Name: lang['clujba_neotlojnoy_pomoschi'],			
			callback: function(data) {
				this.setResult(record, data);
				this.setStatusCmpCallCard(null, 4, null, true);
			}.createDelegate(this)
		});
	},
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
	rejectCmpCallCard: function() {
		var me = this,
			record = this.GridPanel.getSelectionModel().getSelected();

		if(!record) return false;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=getAdressByCardId',
			params: {
				CmpCallCard_id: record.get('CmpCallCard_id')
			},
			callback: function (opts, success, response) {
				if (success) {
					var data = JSON.parse(response.responseText)[0];

					Ext.Ajax.request({
						url: '/?c=TerritoryService&m=getLpuBuildingIdByAddress',
						params: {
							KLStreet_id: data.KLStreet_id,
							house: data.CmpCallCard_Dom,
							building: data.CmpCallCard_Korp,
							city: data.KLCity_id,
							town: data.KLTown_id
						},
						success: function(response){
							var resp = JSON.parse(response.responseText)[0];

							getWnd('swSelectMoveFromNmpReason').show({
								toSmp: true,
								LpuBuilding_id: resp? resp.LpuBuilding_id: null,
								Lpu_id: resp? resp.Lpu_id: null,
								callback: function(data) {
									me.sendCallToCmp(record.get('CmpCallCard_id'), data);
									me.setStatusCmpCallCard(null, 3, data.comment, true, null, data.CmpMoveFromNmpReason_id );
								}
							});
						}
					});
				}
			}
		});
	},
	
	refuseCmpCallCard: function() {
		Ext.Msg.prompt(lang['prichina_otkaza'], lang['vvedite_prichinu'], function(btn, txt) {
			if( btn == 'ok' ) {
				if( txt == '' ) {
					return sw.swMsg.alert(lang['oshibka'], lang['vyi_doljnyi_vvesti_prichinu_otkaza'], this.refuseCmpCallCard, this);
				}
				this.setStatusCmpCallCard(null, 5, txt, true);
			}
		}, this, true);
	},
	

	initComponent: function() {
		var form = this;
		
		this.task = {run: this.doSearch.createDelegate(this), interval: 60*1000*3};
		
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
					items: [/*{
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
					}, */{
						layout: 'form',
						labelWidth: 120,
						items: [{
							allowBlank: true,
							enableKeyEvents: true,
							fieldLabel: 'Служба НМП',
							hiddenName: 'MedService_id',							
							listeners: {
								'keydown': form.onKeyDown
							},
							listWidth: 400,
							width: 200,
							tpl: new Ext.XTemplate(
								'<tpl for="."><div class="x-combo-list-item">',
								'{values.MedService_Nick}',
								//'{[values.MedService_id > 0 ? values.Lpu_id_Nick + " / " + values.MedService_Nick : "&nbsp;"]}',
								'</div></tpl>'
							),
							xtype: 'swmedservicecombo'
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
			{dataIndex: 'EvnPL_id', hidden: true, hideable: false},
			{dataIndex: 'Person_id', hidden: true, hideable: false},
			{dataIndex: 'Person_IsUnknown', hidden: true, hideable: false},
			{dataIndex: 'PersonEvn_id', hidden: true, hideable: false},
			{dataIndex: 'Server_id', hidden: true, hideable: false},
			{dataIndex: 'Person_Surname', hidden: true, hideable: false},
			{dataIndex: 'Person_Firname', hidden: true, hideable: false},
			{dataIndex: 'Person_Secname', hidden: true, hideable: false},
			{dataIndex: 'pmUser_insID', hidden: true, hideable: false},
			{dataIndex: 'MedService_id', hidden: true, hideable: false},
			{dataIndex: 'CmpReason_Code', hidden: true, hideable: false},
			{dataIndex: 'CmpCallCard_prmDate', header: langs('Дата/время'), type: 'date',
				renderer: function(v, p, r) {
					return (!Ext.isEmpty(getGlobalOptions().smp_call_time_format) && getGlobalOptions().smp_call_time_format == 2) ?
						Ext.util.Format.date(v, 'd.m.Y H:i') : Ext.util.Format.date(v, 'd.m.Y H:i:s');
				},
				width: 110},
			{dataIndex: 'ServeDT', header: langs('Обслужено'),type: 'date', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i'), width: 120},
			{dataIndex: 'CmpCallCard_Numv', header: langs('№ вызова за день'), width: 100},
			{dataIndex: 'CmpCallCard_Ngod', header: langs('№ вызова (за год)'), width: 100},
			{dataIndex: 'LpuRegion_Name', header: langs('Участок'), width: 40},
			{dataIndex: 'Adress_Name', header: langs('Адрес вызова'), width: 300},
			{dataIndex: 'CmpCallType_Name', header: langs('Тип вызова'), width: 200},
			{dataIndex: 'Person_FIO', header: langs('Пациент'), width: 250},
			{dataIndex: 'Person_Birthday', header: langs('Дата рождения'), width: 100},
			{dataIndex: 'CmpReason_Name', header: langs('Повод'), width: 200,
				renderer: function (value, p, rec) {
					if (rec.data.CmpReason_Code == 'НГ1') {
						value = '<div class="covidReason">' + value + '</div>'
					}

					return value;
				}
			},
			//{ dataIndex: 'CmpLpu_Name', header: 'ЛПУ', width: 100 },
			//{ dataIndex: 'CmpDiag_Name', header: 'Диагноз СМП', width: 100 },
			//{ dataIndex: 'StacDiag_Name', header: 'Диагноз стационара', width: 130 },
			{dataIndex: 'PPDUser_Name', header: lang['prinyal'], width: 200},
			{dataIndex: 'MedStaffFact_FIO', header: lang['vrach'], width: 200},
			{dataIndex: 'PPDResult', header: lang['rezultat_obrabotki_v_nmp'], width: 330, render: function(v, p, r) {
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
				field: 'CmpCallCard_prmDate',
				direction: 'DESC'
			},
			groupField: 'CmpGroup_id',
			reader: new Ext.data.JsonReader({
				totalProperty: 'totalCount',
				root: 'data'
			}, storeFields),
			url: '/?c=CmpCallCard&m=loadPPDWorkPlace'
		});
		
		this.GridPanel = new Ext.grid.GridPanel({
			stripeRows: true,
			//autoExpandColumn: 'autoexpand',
			title: lang['jurnal_rabochego_mesta'],
			bodyCssClass:'yourclass',
			id: form.id + '_Grid',
			hideTaskTime: true,
			paging: true,
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
				{name: 'action_add', iconCls: 'add16', text: lang['dobavit'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['add'])},
				{name: 'action_edit', iconCls: 'edit16', text: lang['izmenit'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['edit']), hidden: true},
				{name: 'action_delete', iconCls: 'delete16', text: lang['udalit'], handler: this.deleteCmpCallCard.createDelegate(this), hidden: true},
				{name: 'action_view', iconCls: 'view16', text: lang['prosmotr'], handler: this.openCmpCallCardEditWindow.createDelegate(this, ['view'])},
				{name: 'action_refresh', iconCls: 'refresh16', text: lang['obnovit'], handler: function(btn) {this.autoEvent = false;this.doSearch();}.createDelegate(this)},
				{name: 'action_print', iconCls: 'print16', text: lang['pechat_spiska'], 
					handler: 
						function() { 
							var params = {};
							params.notPrintEmptyRows = true;
							Ext.ux.GridPrinter.print(this.GridPanel, params);
						}.createDelegate(this) 
				},
				{name: 'action_to', text: lang['prinyat'], handler: this.setStatusCmpCallCard.createDelegate(this, [null, 2])},
				{name: 'action_back', text: lang['peredat_v_smp'], handler: this.rejectCmpCallCard.createDelegate(this)},
				{name: 'action_refuse', text: lang['otkaz'], handler: this.refuseCmpCallCard.createDelegate(this)},
				//{name: 'action_served', text: 'Обслужено', handler: this.setStatusCmpCallCard.createDelegate(this, [null, 4, null, true])},
				{name: 'action_served', text: lang['obslujeno'], handler: this.servedCmpCallCard.createDelegate(this)},
				{name: 'action_create_evnpl', text: lang['sozdat_sluchay_apl'], handler: this.createEvnPL.createDelegate(this)},
				//{name: 'action_tosmp', text: 'Передать в СМП', handler: this.setStatusCmpCallCard.createDelegate(this, [null, 1])},
				{name: 'action_selectlpu', text: lang['perenaznachit_lpu'], tooltip: lang['perenaznachit_lpu'], handler: this.selectLpuTransmit.createDelegate(this)}
			],
			loadMask: {msg: lang['zagruzka']},
			region: 'center',
			colModel: new Ext.grid.ColumnModel({
				columns: gridFields
			}),
			view: new Ext.grid.GroupingView({
				//groupTextTpl: '{[values.gvalue + ". " + Ext.getCmp("'+this.id+'").getGroupName(values.gvalue)]} ({[values.rs.length]} {[values.rs.length.inlist([2,3,4]) ? "записи" : "записей"]})'
                //enableGrouping:false,
                enableGroupingMenu:false,
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
			var d = store.getCount() > 0;
			this.getAction('action_to').setDisabled(!d);
			this.getAction('action_create_evnpl').setDisabled(!d);
			this.getAction('action_edit').setDisabled(!d);
			this.getAction('action_view').setDisabled(!d);
			this.getAction('action_delete').setDisabled(!d);
			this.getAction('action_back').setDisabled(!d);
			this.getAction('action_refuse').setDisabled(!d);
			//this.getAction('action_tosmp').setDisabled(!d);
			//this.getAction('action_served').setDisabled(!d);
			this.getAction('action_selectlpu').setDisabled(!d);
			//this.getAction('action_tosmp').setDisabled(!d);
			
			if( d ) {
				this.getSelectionModel().selectFirstRow();
			}
			form.addEmptyRecord();
			form.refreshTaskTime();
			form.checkCovidPovod();
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.getSelectionModel().on('rowselect', function(sm, rowIndex, rec) {
			this.getAction('action_to').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([1,4]) );
			this.getAction('action_back').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([1,4]) );
			this.getAction('action_refuse').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([1,2,4,5]) );
			//this.getAction('action_tosmp').setDisabled( rec.get('CmpCallCard_id') == null || rec.get('CmpGroup_id') != 4 );
			this.getAction('action_served').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([2,5]) );
			this.getAction('action_create_evnpl').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([2,3,5,6]) || rec.get('EvnPL_id') != null );
			this.getAction('action_selectlpu').setDisabled( rec.get('CmpCallCard_id') == null || !rec.get('CmpGroup_id').inlist([1,4]) );
		}.createDelegate(this.GridPanel));
		
		this.GridPanel.on('rowdblclick', function() {
			this.getAction('action_view').execute();
		});
		
		sw.Promed.swWorkPlacePPDWindow.superclass.initComponent.apply(this, arguments);
	},

	sendCallToCmp: function (card_id, params) {
		if (!card_id) return false;

		Ext.Ajax.request({
			url: '/?c=CmpCallCard&m=sendCallToSmp',
			params: {
				CmpCallCard_id: card_id,
				Lpu_id: params.Lpu_id,
				LpuBuilding_id: params.LpuBuilding_id
			}
		})
	}
});
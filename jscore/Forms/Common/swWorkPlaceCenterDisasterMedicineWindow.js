/**
* АРМ Центр Медицины Катастроф
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @autor		Dmitry Vlasenko
* @copyright    Copyright (c) 2014 Swan Ltd.
* @version      12.03.2014
*/
sw.Promed.swWorkPlaceCenterDisasterMedicineWindow = Ext.extend(sw.Promed.BaseForm, {
	title: 'АРМ Центра медицины кататстроф',
	modal: false,
	maximized: true,
	closable: true,
	id: 'swWorkPlaceCenterDisasterMedicineWindow',
	screenMode: 'HalfScreenMap',
	Lpu_ids: [],
	SmpTiming: {
		minTimeSMP: 0,
		maxTimeSMP: 0,
		minTimeNMP: 0,
		maxTimeNMP: 0,
		minResponseTimeNMP: 0
	},
	intervalUpdate: false,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event) {
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Закрыть',
			tabIndex  : -1,
			tooltip   : 'Закрыть',
			iconCls   : 'cancel16',
			handler   : function() {
				this.ownerCt.hide();
			}
		}
	],

	listeners: {
		hide: function(win) {
			if(win.intervalUpdate) {
				clearInterval(win.intervalUpdate);
				delete win.intervalUpdate;
			}
			win.headerFieldsSetVisible(false);
		}
	},

	headerFieldsSetVisible: function(bool) {
		var lpuBuldingLabel = Ext.getCmp('CmkLpuBuilding_label'),
			lpuBuildingCombo = Ext.getCmp('CmkLpuBuilding_combo');

		if(lpuBuldingLabel) {
			lpuBuildingCombo.setVisible(bool);
			lpuBuldingLabel.setVisible(bool);
		}
	},

	show: function(){
		sw.Promed.swWorkPlaceCenterDisasterMedicineWindow.superclass.show.apply(this, arguments);

		var win = this;

		win.clearIntervals(win);


		Ext.Ajax.request({
			callback: function (options, success, response) {
				if (!success) {
					return;
				}

				var res = Ext.util.JSON.decode(response.responseText),
				select_mo_win = Ext.getCmp('select_mo_win'),
				SelectMoToControl = Ext.getCmp('SelectMoToControl');

				win.Lpu_ids = [];
				Ext.each( res, function(rec) {
					win.Lpu_ids.push( rec.Lpu_id );
				});

				if(res.length == 1) {
					Ext.Ajax.request({
						url: '/?c=Options&m=saveLpuWorkAccess',
						params: {
							'lpuWorkAccess': [res[0].Lpu_id]
						}
					});

				}else if(res.length > 1){

					if(SelectMoToControl){
						SelectMoToControl.setVisible(true);
					}
					if(select_mo_win){
						select_mo_win.setVisible(true);
					}

					var callback = function() {
						Ext.getCmp('CmkLpuBuilding_combo').getStore().reload();
					};
					getWnd('swSelectMOToControlWindow').show({ callback: callback });
				}
			},
			url: '/?c=LpuStructure&m=getLpuListWithSmp'
		});


		if(!win.intervalUpdate){
			//проверка на подключенный ноджс
			//если нет то вручную апдейт при условии что окно активно
			win.intervalUpdate = setInterval(function(){
				var activeWin = Ext.WindowMgr.getActive();
				if (activeWin && win.id==activeWin.id){
					if (win.socket){
						if (!win.socket.connected){
							win.reloadStores();
						}
						else{
							win.setNodeListeners();
						}
					}
					else{
						win.reloadStores();
					}
				}
			}.bind(this),60000);
		}

		if ( arguments[0] ) {
			if ( arguments[0].userMedStaffFact && arguments[0].userMedStaffFact.ARMType ) {
				this.ARMType = arguments[0].userMedStaffFact.ARMType;
				this.userMedStaffFact = arguments[0].userMedStaffFact;
			}
			else {
				if ( arguments[0].MedService_id ) {
					this.MedService_id = arguments[0].MedService_id;
					this.userMedStaffFact = arguments[0];
				}
				else {
					if ( arguments[0].ARMType ) { // Это АРМ без привязки к врачу - АРМ администратора или кадровика 
						this.userMedStaffFact = arguments[0];
					} else {
						this.hide();
						sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Не указан тип АРМа.');
						return false;
					}
				}
			}
		}
		else {
			this.hide();
			sw.swMsg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}

		Ext.getCmp(win.id + '_OutfitsFilters').hide();
		if (getRegionNick() == 'ufa') {
			win.headerFieldsSetVisible(true);

			Ext.Ajax.request({
				url: '/?c=Options&m=getLpuBuildingForTimingCmk',
				callback: function(options, success, response) {
					if(!success) return;

					var result = Ext.util.JSON.decode(response.responseText);

					if(!result) return;

					var lpuBuildingCombo = Ext.getCmp('CmkLpuBuilding_combo'),
						rec = lpuBuildingCombo.getStore().getById( lpuBuildingCombo.getValue() );

					if(rec) {
						lpuBuildingCombo.setValue( result.LpuBuilding_id );
						lpuBuildingCombo.fireEvent('change', lpuBuildingCombo, result.LpuBuilding_id );
					}
				}
			})
		}

		sw.Promed.MedStaffFactByUser.setMenuTitle(this, this.userMedStaffFact);
	},
	
	setNodeListeners: function(){
		var win = this;
		//установка листенерсов для нода если он есть
		//проверка на установленные если нод был запущен
		
		//листенер на action CmpCallCard
		if(!win.socket.hasListeners('changeCmpCallCard')){
			win.socket.on('changeCmpCallCard', function (card, action) {
				console.log('nodeon changeCmpCallCard')
				win.reloadStores();
			})
		};		
		
		//листенер на action Изменение статуса бригады
		if(!win.socket.hasListeners('changeEmergencyTeamStatus')){
			win.socket.on('changeEmergencyTeamStatus', function (team, action) {
				console.log('nodeon changeEmergencyTeamStatus')
				win.reloadStores();
			})
		};		
		
		//листенер на action Выход бригады на смену
		if(!win.socket.hasListeners('setEmergencyTeamDutyTime')){
			win.socket.on('setEmergencyTeamDutyTime', function (teams) {
				console.log('nodeon setEmergencyTeamDutyTime', teams)
				win.reloadStores();
			})
		};
		
		//листенер на GeoserviceTransportList
		if(!win.socket.hasListeners('changeGeoserviceTransportList')){
			//запускаем интервал на ноде (получение трекеров)
			win.socket.emit('setUpdateGeoserviceTransportList');
			//слушаем нод (получение трекеров)
			win.socket.on('changeGeoserviceTransportList', function (data,p) {
				var data = Ext.util.JSON.decode(data,true);
				if (!data) {log({ERROR: data});	return false; }
				console.log('nodeon changeGeoserviceTransportList')
				win.carMarkersStore.loadData(data);
			} );
		}
		
		win.socket.on('disconnect', function(){
			win.socket.removeListener('changeGeoserviceTransportList');
		});
		
		/*
		//установка листенерсов для нода если он есть
		//проверка на установленные если нод был запущен
		
		if(!win.socket.hasListeners('changeEmergencyTeamsARMCenterDisaster')){
			//запускаем интервал на ноде (получение бригад)
			win.socket.emit('setUpdateEmergencyTeamsARMCenterDisaster');
			//слушаем нод (получение бригад)
			win.socket.on('changeEmergencyTeamsARMCenterDisaster', function (data,p) {
				var data = Ext.util.JSON.decode(data,true);
				if (!data) {log({ERROR: data});	return false; }
				//log('Node_LoadEmergencyTeams', data[0], p);
				win.emergencyTeamsGrid.getStore().loadData(data);
			} );
		}
		
		if(!win.socket.hasListeners('changeLpuBuildingsARMCenterDisaster')){
			//запускаем интервал на ноде (получение подстанций)
			win.socket.emit('setUpdateLpuBuildingsARMCenterDisaster');
			//слушаем нод (получение бригад)
			win.socket.on('changeLpuBuildingsARMCenterDisaster', function (data,p) {
				var data = Ext.util.JSON.decode(data,true);
				if (!data) {log({ERROR: data});	return false; }
				//log('Node_LoadLpuCounts', data[0], p);
				win.buildingsGrid.getStore().loadData(data);
			} );
		}
		
		if(!win.socket.hasListeners('changeCmpCallCardsARMCenterDisaster')){
			//запускаем интервал на ноде (получение подстанций)
			win.socket.emit('setUpdateCallsARMCenterDisaster');
			//слушаем нод (получение бригад)
			win.socket.on('changeCmpCallCardsARMCenterDisaster', function (data,p) {
				var data = Ext.util.JSON.decode(data,true);
				if (!data) {log({ERROR: data});	return false; }
				//log('Node_LoadCmpCallCards', data[0], p);
				win.emergencyCallsGrid.getStore().loadData(data);
			} );
		}
			
		if(!win.socket.hasListeners('changeGeoserviceTransportList')){
			//запускаем интервал на ноде (получение подстанций)
			win.socket.emit('setUpdateGeoserviceTransportList');
			//слушаем нод (получение бригад)
			win.socket.on('changeGeoserviceTransportList', function (data,p) {
				var data = Ext.util.JSON.decode(data,true);
				if (!data) {log({ERROR: data});	return false; }
				//log('Node_LoadeGeoserviceTransportList', data[0], p);
				win.carMarkersStore.loadData(data);
			} );
		}
		*/
	},

	setIntervals: function(win){
		var topToolBar = win.getTopToolbar(),
			begDateField = topToolBar.find('name', 'CmpCallCard_begDate')[0],
			begTimeField = topToolBar.find('name', 'CmpCallCard_begTime')[0],
			endDateField = topToolBar.find('name', 'CmpCallCard_endDate')[0],
			endTimeField = topToolBar.find('name', 'CmpCallCard_endTime')[0],
			dateFactStartField = topToolBar.find('name', 'dateFactStart')[0],
			timeFactStartField = topToolBar.find('name', 'timeFactStart')[0],
			dateFactFinishField = topToolBar.find('name', 'dateFactFinish')[0],
			timeFactFinishField = topToolBar.find('name', 'timeFactFinish')[0],
			lpuField = topToolBar.find('name', 'Lpu_ids')[0];

		var begDate = begDateField.value,
			begTime = begTimeField.getValue(),
			endDate = endDateField.value,
			endTime = endTimeField.getValue(),
			lpu_ids = lpuField.getValue(),
			dateFactStart = dateFactStartField.getValue(),
			timeFactStart = timeFactStartField.getValue(),
			dateFactFinish = dateFactFinishField.getValue(),
			timeFactFinish = timeFactFinishField.getValue();

		if(dateFactStart) {
			dateFactStart = dateFactStart.format('d.m.Y');
		}

		if(dateFactFinish) {
			dateFactFinish = dateFactFinish.format('d.m.Y');
		}


		win.emergencyCallsGrid.getStore().baseParams = {
			begDate: begDate,
			begTime: begTime,
			endDate: endDate,
			endTime: endTime,
			Lpu_ids: lpu_ids
		};

		win.buildingsGrid.getStore().baseParams = {
			Lpu_ids: lpu_ids
		};

		win.emergencyTeamsGrid.getStore().baseParams = {
			Lpu_ids: lpu_ids
		};

		win.OutfitsGrid.getStore().baseParams = {
			//showCurrentTeamsByFact: true,
			//dateStart: new Date().format('d.m.Y'),
			//dateFinish: new Date().format('d.m.Y'),
			dateStart: begDate,
			dateFinish: endDate,
			dateFactStart: dateFactStart,
			timeFactStart: timeFactStart,
			dateFactFinish: dateFactFinish,
			timeFactFinish: timeFactFinish,
			Lpu_ids: lpu_ids
		};

		win.nmpCallsGrid.getStore().baseParams = {
			isNmp: 1,
			begDate: begDate,
			begTime: begTime,
			endDate: endDate,
			endTime: endTime,
			Lpu_ids: lpu_ids
		}

		win.reloadStores();
	},

	clearIntervals: function(win){
		var topToolBar = win.getTopToolbar(),
			begDateField = topToolBar.find('name', 'CmpCallCard_begDate')[0],
			begTimeField = topToolBar.find('name', 'CmpCallCard_begTime')[0],
			endDateField = topToolBar.find('name', 'CmpCallCard_endDate')[0],
			endTimeField = topToolBar.find('name', 'CmpCallCard_endTime')[0],
			dateFactStartField = topToolBar.find('name', 'dateFactStart')[0],
			timeFactStartField = topToolBar.find('name', 'timeFactStart')[0],
			dateFactFinishField = topToolBar.find('name', 'dateFactFinish')[0],
			timeFactFinishField = topToolBar.find('name', 'timeFactFinish')[0],
			lpuField = topToolBar.find('name', 'Lpu_ids')[0],
			dateTime = new Date();

		begTimeField.setValue(dateTime.format('H:i'));
		endTimeField.setValue(dateTime.format('H:i'));

		dateFactStartField.setValue();
		dateFactFinishField.setValue();
		timeFactStartField.setValue();
		timeFactFinishField.setValue();
		lpuField.setValue();

		endDateField.setValue(dateTime);
		dateTime.addDays(-1)
		begDateField.setValue(dateTime);

		if(getRegionNick() == 'ufa') {
			this.OutfitsGrid.getStore().baseParams = {};
			this.nmpCallsGrid.getStore().baseParams = { isNmp: 1 };
		}
	},

	//функция формирования маркеров вызовов
	prepareCallsMarkersToMap: function(recs){
		var win = this;
		
		//удаление старых маркеров
		win.mapPanel.callsAcceptedMarkersCollection.removeAll();
		win.mapPanel.callsNoAcceptedMarkersCollection.removeAll();

		recs.forEach(function(r){

			if(r.json) r = r.json;

			//нет координат - нет проблем, пропускаем, ждем следующего
			if( !(r.get('CmpCallCard_CallLng') && r.get('CmpCallCard_CallLtd'))) return;
			
			var type, coords, title, descr,
				showNoAcceptedCalls = Ext.getCmp(win.id + '_NoAcceptedCallsCheckbox').getValue(),
				showAcceptedCalls = Ext.getCmp(win.id + '_AcceptedCallsCheckbox').getValue();
			
			coords = [r.get('CmpCallCard_CallLtd'),r.get('CmpCallCard_CallLng')];
			type = r.get('EmergencyTeam_id')?'emergencyCallAccepted':'emergencyCallNoAccepted';
			title = r.get('Adress_Name');


			if( (showAcceptedCalls && type == 'emergencyCallAccepted')
			 || (showNoAcceptedCalls && type == 'emergencyCallNoAccepted')) {
				win.mapPanel.addMarker(type, coords, title, descr);
			}
		});
	},

	initComponent: function () {
		var win = this,
			isUfa = getRegionNick() == 'ufa';
		
		win.separatorPos = 0;

		win.searchBtn = new Ext.Button({
			xtype: 'button',
			style: 'padding: 0 0 3px 20px;',
			text: langs('Найти'),
			handler: function(){
				win.setIntervals(win);
			}
		});

		win.resetBtn = new Ext.Button({
			xtype: 'button',
			style: 'padding: 0 0 3px 10px;',
			text: langs('Сброс'),
			handler: function(){
				win.clearIntervals(win);
				win.reloadStores();
			}
		});

		var emptyArr = [];

		win.mergedStore = new Ext.data.JsonStore({
			data: emptyArr,
			id: 'mergedStore',
			fields: [
				{ type: 'int', name: 'CmpCallCard_isExtra' },
				{ type: 'int', name: 'is112' },
				{ type: 'int', name: 'Lpu_id' },
				{ type: 'int', name: 'Lpu_hid' },
				{ type: 'int', name: 'Diag_uid' },
				{ type: 'int', name: 'EmergencyTeam_id' },
				{ type: 'int', name: 'LpuBuilding_id' },
				{ type: 'int', name: 'EmergencyTeamStatus_id' },
				{ type: 'int', name: 'EmergencyTeamStatus_Code' },
				{ type: 'int', name: 'Person_Age' },
				{ type: 'int', name: 'CountCmpCallCards' },
				{ type: 'int', name: 'EmergencyTeam_HeadShiftCount' },
				{ type: 'int', name: 'EmergencyTeam_AssistantCount' },
				{ type: 'int', name: 'CmpCallCard_id' },
				{ type: 'string', name: 'EmergencyTeam_Name' },
				{ type: 'string', name: 'LpuBuildingName' },
				{ type: 'string', name: 'EmergencyTeamSpec_Code' },
				{ type: 'string', name: 'EmergencyTeamStatus_Name' },
				{ type: 'string', name: 'EmergencyTeamStatus_Color' },
				{ type: 'string', name: 'CmpReason_Name' },
				{ type: 'string', name: 'CmpCallCard_CallLng' },
				{ type: 'string', name: 'CmpCallCard_CallLtd' },
				{ type: 'string', name: 'Person_FIO' },
				{ type: 'string', name: 'Adress_Name' },
				{ type: 'string', name: 'personAgeText' },
				{ type: 'string', name: 'CmpCallCardStatusType_Code' },
				{ type: 'string', name: 'Lpu_Nick' },
				{ type: 'string', name: 'LpuHid_Nick' },
				{ type: 'string', name: 'Diag_Name' },
				{ type: 'string', name: 'Diag_Code' },
				{ type: 'string', name: 'Duplicate_Count' },
				{ type: 'string', name: 'CmpCallCard_Numv' },
				{ type: 'string', name: 'CmpCallCard_Ngod' },
				{ type: 'string', name: 'CmpCallType_Name' },
				{ type: 'date', name: 'CmpCallCard_prmDT' },
				{ type: 'date', name: 'CmpCallCardStatus_insDT'},
				{ type: 'date', name: 'statusTime' },
				{ name: 'callsCheck', defaultValue: false}
			],
			listeners: {
				load: function () {
					if(this.data.items.length > 0) win.prepareCallsMarkersToMap(this.data.items);
				}
			}
		});

		win.tbar = new Ext.Panel({
			style: 'padding-top: 5px;',
			border: false,
			bodyStyle: 'background-color: #dfe8f6;',
			layout: 'column',
			items: [{
					xtype: 'label',
					text: langs('МО'),
					width: 50,
					hidden: getRegionNick() != 'ufa',
					style: 'text-align: right; margin: 2px 8px;'
				}, new Ext.ux.Andrie.Select({
					history: true,
					allowBlank: true,
					hidden: getRegionNick() != 'ufa',
					multiSelect: true,
					mode: 'local',
					emptyText: langs('Все'),
					fieldLabel: langs('МО'),
					displayField: 'Lpu_Nick',
					valueField: 'Lpu_id',
					xtype:'swlpucombo',
					name: 'Lpu_ids',
					width: 300,
					store: new Ext.db.AdapterStore({
						
						dbFile: 'Promed.db',
						tableName: 'LpuSearch',
						key: 'Lpu_id',
						sortInfo: {field: 'Lpu_Nick'},
						autoLoad: getRegionNick() == 'ufa',
						fields: [
							{name: 'Lpu_id', mapping: 'Lpu_id'},
							{name: 'Lpu_IsOblast', mapping: 'Lpu_IsOblast'},
							{name: 'Lpu_Name', mapping: 'Lpu_Name'},
							{name: 'Lpu_Nick', mapping: 'Lpu_Nick', type: 'string'},
							{name: 'Lpu_Ouz', mapping: 'Lpu_Ouz'},
							{name: 'Lpu_RegNomC', mapping: 'Lpu_RegNomC'},
							{name: 'Lpu_RegNomC2', mapping: 'Lpu_RegNomC2'},
							{name: 'Lpu_RegNomN2', mapping: 'Lpu_RegNomN2'},
							{name: 'Lpu_DloBegDate', mapping: 'Lpu_DloBegDate'},
							{name: 'Lpu_DloEndDate', mapping: 'Lpu_DloEndDate'},
							{name: 'Lpu_BegDate', mapping: 'Lpu_BegDate'},
							{name: 'Lpu_EndDate', mapping: 'Lpu_EndDate', type: 'string'},
							{name: 'Lpu_IsAccess', mapping: 'Lpu_IsAccess'}
						],
					}),
					tpl: new Ext.XTemplate(
						'<tpl for="."><div class="x-combo-list-item">',
						'{[(values.Lpu_EndDate != "") ? values.Lpu_Nick + " (закрыта " + values.Lpu_EndDate + ")" : values.Lpu_Nick ]}&nbsp;',
						'</div></tpl>'
					),
					listeners: {
						expand: function(combo) {
							this.store.filterBy( function(rec) {
								if(!rec.get('Lpu_id')) return;
								return rec.get('Lpu_id').inlist(win.Lpu_ids);
							} );
						}
					}
				}), {
					xtype: 'label',
					text: langs('Дата с'),
					width: 50,
					style: 'text-align: right; margin: 2px 8px;'
				}, {
					format: 'd.m.Y',
					name: 'CmpCallCard_begDate',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					width: 90,
					xtype: 'swdatefield'
				}, {
					xtype: 'label',
					text: langs('Время c'),
					width: 40,
					style: 'text-align: right; margin: 2px 8px;'
				}, {
					name: 'CmpCallCard_begTime',
					plugins: [new Ext.ux.InputTextMask('99:99', true)],
					validateOnBlur: false,
					width: 60,
					xtype: 'swtimefield'
				}, {
					xtype: 'label',
					text: langs('Дата по'),
					width: 80,
					style: 'text-align: right; margin: 2px 8px;'
				}, {
					format: 'd.m.Y',
					name: 'CmpCallCard_endDate',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					width: 90,
					xtype: 'swdatefield'
				}, {
					xtype: 'label',
					text: langs('Время по'),
					width: 50,
					style: 'text-align: right; margin: 2px 8px;'
				}, {
					fieldLabel: langs('Время по'),
					name: 'CmpCallCard_endTime',
					plugins: [new Ext.ux.InputTextMask('99:99', true)],
					validateOnBlur: false,
					width: 60,
					xtype: 'swtimefield'
				}, 
				{
					width: 500,
					id: this.id + '_OutfitsFilters',
					bodyStyle: 'background-color: #dfe8f6;',
					border: false,
					layout: 'column',
					items:[{
						xtype: 'label',
						text: langs('Факт. нач.'),
						width: 80,
						style: 'text-align: right; margin: 2px 8px;'
					}, {
						format: 'd.m.Y',
						name: 'dateFactStart',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 90,
						xtype: 'swdatefield'
					}, {
						name: 'timeFactStart',
						plugins: [new Ext.ux.InputTextMask('99:99', true)],
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					}, {
						xtype: 'label',
						text: langs('Факт. оконч.'),
						width: 80,
						style: 'text-align: right; margin: 2px 8px;'
					}, {
						format: 'd.m.Y',
						name: 'dateFactFinish',
						plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
						width: 90,
						xtype: 'swdatefield'
					}, {
						name: 'timeFactFinish',
						plugins: [new Ext.ux.InputTextMask('99:99', true)],
						validateOnBlur: false,
						width: 60,
						xtype: 'swtimefield'
					} ]
				},
				win.searchBtn,
				win.resetBtn
			]});

		//плагин для чекбоксов
		Ext.grid.CheckColumn = function(config){
			Ext.apply(this, config);
			if(!this.id){
				this.id = Ext.id();
			}
			this.renderer = this.renderer.createDelegate(this);
		};
	
		Ext.grid.CheckColumn.prototype ={
			init : function(grid){
				this.grid = grid;
				this.grid.on('render', function(){
					var view = this.grid.getView();
					view.mainBody.on('mousedown', this.onMouseDown, this);
					this.grid.on('headerclick', this.hClick, this);
				}, this);
			},

			onMouseDown : function(e, t){
				if(t.className && t.className.indexOf('x-grid3-cc-'+this.id) != -1){
					e.stopEvent();
					var index = this.grid.getView().findRowIndex(t);
					var record = this.grid.store.getAt(index);
					record.set(this.dataIndex, !record.data[this.dataIndex]);
				}				
			},
			
			hClick: function( grid, columnIndex, e){
				if(e.target.classList.contains('x-grid3-check-col')) {
					//вкл					
					e.target.classList.remove('x-grid3-check-col');
					e.target.classList.add('x-grid3-check-col-on');
					this.grid.store.each(function(rec){rec.set(this.dataIndex, true);}, this);
					return;
				}
				if(e.target.classList.contains('x-grid3-check-col-on')) {
					//выкл
					e.target.classList.remove('x-grid3-check-col-on');
					e.target.classList.add('x-grid3-check-col');
					this.grid.store.each(function(rec){rec.set(this.dataIndex, false);}, this);
				}
			},

			renderer : function(v, p, record){
				p.css += ' x-grid3-check-col-td'; 
				return '<div class="x-grid3-check-col'+(v?'-on':'')+' x-grid3-cc-'+this.id+'">&#160;</div>';
			}
		};
		//конец плагину для чекбоксов
		
		var emergencyTeamsCheck = new Ext.grid.CheckColumn({  
			header: '<div class="x-grid3-check-col x-grid3-cc-'+this.id+'">&#160;</div>', 
			dataIndex: 'emergencyTeamsCheck', 
			width: 5, 
			menuDisabled: true 
		});
		
		//грид бригад
		//@todo сделать адрес после карты
		win.emergencyTeamsGrid = new Ext.grid.GridPanel({
			layout: 'column',
			title: 'Бригады',			
			plugins: emergencyTeamsCheck,
			selectionModel: 'multiselect',
			tbar: [
				{
					xtype: 'button',
					iconCls: 'expand-all-btn',
					name: 'expandAll',
					text: 'Свернуть все',
					tooltip: 'Свернуть все',
					handler: function(btn,e){						
						if(btn.btnEl.hasClass('opened')){
							btn.setText('Свернуть все');
							win.emergencyTeamsGrid.getView().expandAllGroups();
						}
						else{
							btn.setText('Развернуть все');
							win.emergencyTeamsGrid.getView().collapseAllGroups();
						};
						btn.btnEl.toggleClass('opened');
					}
				},
				{
					xtype: 'label',
					text: 'Показать',
					style: 'margin: 0 5px 0 10px;'
				},
				{
					id: win.id + '_EmergencyTeamStatusFilter',
					xtype: 'swbaselocalcombo',
					displayField:'nameOfGroupStatuses',
					editable: false,
					valueField: 'codesOfStatuses',
					fieldLabel: 'Показать',
					allowBlank: false,
					value: '*',
					store: new Ext.data.Store({
						reader: new Ext.data.JsonReader({}, [
							{ name: 'codesOfStatuses', type: 'string' },
							{ name: 'nameOfGroupStatuses', type: 'string' }
						]),
						data: [{
							codesOfStatuses:'*',
							nameOfGroupStatuses: 'Все статусы'
						},{
							codesOfStatuses: isUfa ? '4,5,13,47' : '13,21,36',
							nameOfGroupStatuses: 'Свободные'
						},{
							codesOfStatuses: isUfa ? '36,1,2,3,41,17,48' : '',
							nameOfGroupStatuses: 'Занятые'
						},{
							codesOfStatuses: isUfa ? '37,38,8,9,10,11,43,44,45,50,40' : '8,9,23',
							nameOfGroupStatuses: 'Не доступные'
						}
						]
					}),
					listeners: {
						select: function( combo, record, index )
						{
							win.setFiltersToEmergencyTeamsGrid();
						}
					}
				},
				{
					xtype: 'container',
					autoEl: {},
					style: 'margin: 0 5px 0 10px;',
					items:[
					{
						xtype: 'checkbox',
						checked: true,
						//fieldLabel: '',
						//labelSeparator: '',
						boxLabel: 'Группировать по подстанциям',
						name: 'groupByStation',
						listeners: {
							check: function(cmp, checked){
								if(checked) win.emergencyTeamsGrid.store.groupBy('LpuBuilding_id', true)
								else win.emergencyTeamsGrid.store.clearGrouping( )
							}
						}
					}]					
				}
			],
			store: new Ext.data.GroupingStore({
				reader: new Ext.data.JsonReader({}, [
					{name: 'EmergencyTeam_id', type: 'int'},
					{name: 'LpuBuilding_id', type: 'int'},
					{name: 'EmergencyTeamBuildingName', type: 'string'},
					{name: 'EmergencyTeamNum', type: 'string'},
					{name: 'EmergencyTeamSpec_Code', type: 'string'},
					{name: 'EmergencyTeam_Num', type: 'string'},
					{name: 'EmergencyTeamStatus_Name', type: 'string'},
					{name: 'EmergencyTeamStatus_id', type: 'int'},
					{name: 'EmergencyTeamStatus_Code', type: 'int'},
					{name: 'EmergencyTeamStatus_Color', type: 'string'},
					{name: 'CmpReason_Name', type: 'string'},
					{name: 'Person_Age', type: 'int'},
					{name: 'CountCmpCallCards', type: 'int'},
					{name: 'EmergencyTeam_HeadShiftCount', type: 'int'},
					{name: 'EmergencyTeam_AssistantCount', type: 'int'},
					{name: 'CmpCallCard_id', type: 'int'},
					{name: 'GeoserviceTransport_id', type: 'int'},
					{name: 'Address_Name',type: 'string'},
					{name: 'Person_FIO', type: 'string'},
					{name:'emergencyTeamsCheck', defaultValue: false},
					{name: 'statusTime', type: 'date'},
					{name: 'personAgeText', type: 'string'},
					{name: 'CmpCallCard_isExtra', type: 'int'},
					{name: 'CCCLpu_Nick', type: 'string'},
					{name: 'Lpu_Nick', type: 'string'}
				]),				
				sortInfo:{field: 'EmergencyTeam_id', direction: "ASC"},
				groupField:'LpuBuilding_id',
				url: '/?c=EmergencyTeam4E&m=loadEmergencyTeamsARMCenterDisaster',
				autoLoad: true,
				listeners: {
					load: function() {
						win.setFiltersToEmergencyTeamsGrid();
						win.carMarkersStore.prepareData();
					}
				}
			}),

			columns: [
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeam_id', id:'EmergencyTeam_id' },
				{ hidden: true, hideable: false, dataIndex: 'LpuBuilding_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeamStatus_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeamBuildingName' },
				emergencyTeamsCheck,
				{ width: 40, sortable: true, header: "Номер", dataIndex: 'EmergencyTeamNum' },
				{ width:  8, sortable: true, header: "Профиль",  dataIndex: 'EmergencyTeamSpec_Code' },
				{ width: 20, sortable: true, header: "Статус",  dataIndex: 'EmergencyTeamStatus_Name' },
				{ width: 10, sortable: true, header: 'Время', dataIndex: 'statusTime', renderer: Ext.util.Format.dateRenderer('H:i')},
				{ width: 20, sortable: true, header: "Повод", dataIndex: 'CmpReason_Name' },
				{ width: 20, sortable: true, header: "Вид вызова", dataIndex: 'CmpCallCard_isExtra', renderer: function(rowNum,cell,rec) {
					switch(rec.get('CmpCallCard_isExtra')) {
						case 1: return 'Экстренный';
						case 2: return 'Неотложный';
						default: return '';
					}
				}},
				{ width: 20, sortable: true, header: "Адрес",  dataIndex: 'Address_Name' },
				{ width: 10, sortable: true, header: "Возраст",  dataIndex: 'personAgeText' },
				{ width: 20, sortable: true, header: "ФИО", dataIndex: 'Person_FIO' },
				{ width: 20, sortable: true, header: 'МО госпитализации', dataIndex: 'CCCLpu_Nick'}
			],

			view: new Ext.grid.GroupingView({
				getRowClass: function(record) {
					var statusCls = '';
					switch(true){
						case record.get('EmergencyTeamStatus_Code').inlist([13,21,36]): {statusCls = 'teamStatusFree'; break;}
						case record.get('EmergencyTeamStatus_Code').inlist([8,9,23]): {statusCls = 'teamStatusUnaccepted'; break;}
					}
					return statusCls; 
				},
				forceFit:true,
				interceptMouse : function(e){
					//оверрайдим событие на распахивание группы в гриде
					var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
					if(hd){
						e.stopEvent();
						if(e.target.classList.contains('showBuildingOnMap')){
							var buildId = e.target.classList[2].replace('itemBuild-', '')
							win.showBuildingOnMap(buildId);
						}
						else{
							this.toggleGroup(hd.parentNode);
						}						
					}
				},
				startGroup: new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd cmk-group-header" style="{style}"><div class="x-grid-group-title">',
						'{[this.countTotal(values.rs)]}',
					'</div></div><div id="{groupId}-bd" class="x-grid-group-body">',
					{
						countTotal: function(records){
						//формирование заголовка у группы в гриде
							var teamStatusFree = 0,
								teamStatusDuty = 0,
								teamStatusUnaccepted = 0,
								resultGroupHeaderString,
								callsAccepted = 0,
								callsNoAccepted = 0,
								countHeadShifts = 0,
								countAssistants = 0;
								
							for (i=0;i<records.length;i++){
								switch(records[i].get('EmergencyTeamStatus_Color')){
									case 'green': {teamStatusFree++; break;}
									case 'gray': {teamStatusUnaccepted++; break;}
									case 'black': {teamStatusDuty++; break;}
									default: {teamStatusDuty++; break;}
								}
								//назначенные
								if(records[i].get('CmpCallCard_id'))callsAccepted++;
								if(records[i].get('EmergencyTeam_HeadShiftCount'))countHeadShifts++;
								if(records[i].get('EmergencyTeam_AssistantCount'))countAssistants++;
							};

							//callsNoAccepted = records[0].get('CountCmpCallCards') - callsAccepted;
							win.emergencyCallsGrid.store.each( function(rec) {
								if(records[0].get('LpuBuilding_id') == rec.get('LpuBuilding_id') && !rec.get('EmergencyTeam_id'))
									callsNoAccepted ++;
							});
							
							resultGroupHeaderString =
								'<div style="float: left; background: none;">' +
									'Подстанция ' + records[0].get('EmergencyTeamBuildingName') +
									'<span title="Показать на карте" style="font-weight:normal; margin: 0px 2px 0 8px;" class="cmkimg showBuildingOnMap itemBuild-' + records[0].get('LpuBuilding_id') + '"></span>' +
								'</div>' +
								'<div style="float: right; background: none;">' +
									'<span title="Бригад свободных" style="color:#0d9100; margin: 0px 4px;"><span class="cmkimg teamStatusFree"></span>' + teamStatusFree + '</span>' +
									'<span title="Бригад на вызове" style="color:#195ffe; margin: 0px 4px;"><span class="cmkimg teamStatusDuty"></span>' + teamStatusDuty + '</span>' +
									'<span title="Бригад недоступных" style="color:#858585; margin: 0px 4px;"><span class="cmkimg teamStatusUnaccepted"></span>' + teamStatusUnaccepted + '</span>' +
									'<span title="Вызовов назначенных" style="color:#ff6c00; margin: 0px 4px;"><span class="cmkimg callsAccepted"></span>' + callsAccepted + '</span>' +
									'<span title="Вызовов неназначенных" style="color:#ff0000; margin: 0px 4px;"><span class="cmkimg callsNoAccepted"></span>' + callsNoAccepted + '</span>' +
									'<span title="Врачей / фельдшеров" style="color:#15428b; margin: 0px 4px;"><span class="cmkimg countDocs"></span>' + countHeadShifts + '/' + countAssistants + '</span>' +
								'</div><div style="clear:both;"></div>';
							
							return resultGroupHeaderString;
						}
					}
				)
			})
		});

		win.setFiltersToEmergencyTeamsGrid = function() {
			var status_id = Ext.getCmp(win.id + '_EmergencyTeamStatusFilter').getValue();

			win.emergencyTeamsGrid.store.filterBy( function(rec){
				var EmergencyTeamStatus_Code = rec.get('EmergencyTeamStatus_Code');
				return status_id=='*' || EmergencyTeamStatus_Code.inlist(status_id.split(','));
			});
		}
		
		var buildingsTeamsCheck = new Ext.grid.CheckColumn({  
			header: '<div class="x-grid3-check-col x-grid3-cc-'+this.id+'">&#160;</div>', 
			dataIndex: 'lpuDuildingCheck', 
			width: 5, 
			menuDisabled: true 
		});

		var emergencyTeamWindow = new Ext.Window({
			title: "Информация о бригаде",
			autoWidth: true,
			autoHeight: true,
			resizable: false,
			shadow: false,
			closeAction: 'hide',
			modal: true,
			buttons: [ {
				text      : 'Закрыть',
				iconCls   : 'cancel16',
				handler   : function() {
					emergencyTeamWindow.hide();
				}
			}],
			init: function(EmergencyTeam_id) {
				if(!EmergencyTeam_id) return;
				emergencyTeamWindow.show();

				var form = emergencyTeamWindow.items.items[0].getForm(),
					mask = new Ext.LoadMask(form.getEl(), {msg: 'Загрузка'});
				mask.show();
				form.reset();

				Ext.Ajax.request({
					url: '/?c=EmergencyTeam4E&m=getEmergencyTeam',
					params: { EmergencyTeam_id: EmergencyTeam_id },
					callback: function(options, success, response) {
						mask.hide();
						if(success) {
							var obj = Ext.util.JSON.decode(response.responseText);
							if(!obj[0]) return;
							form.setValues(obj[0]);
						} else {
							sw.swMsg.alert('Сообщение', 'Ошибка получения данных');
						}
					}
				});

			},
			items: [ new sw.Promed.FormPanel({
				layout: 'form',
				autoHeight: true,
				border: false,
				labelWidth: 200,
				defaults: {
					xtype: 'textfield',
					width: 200,
				},
				items: [{
					fieldLabel: 'Номер бригады',
					name: 'EmergencyTeam_Num',
				}, {
					fieldLabel: 'Профиль бригады',
					name: 'EmergencyTeamSpec_Code'
				}, {
					fieldLabel: 'Статус бригады',
					name: 'EmergencyTeamStatus_Name'
				}, {
					fieldLabel: 'Номер вызова за день',
					name: 'CmpCallCard_Numv'
				},{
					fieldLabel: 'Номер вызова за год',
					name: 'CmpCallCard_Ngod'
				}, {
					fieldLabel: 'Старший бригады',
					name: 'EmergencyTeam_HeadShift'
				}, {
					fieldLabel: 'Помощник 1',
					name: 'EmergencyTeam_HeadShift2'
				}, {
					fieldLabel: 'Помощник 2',
					name: 'EmergencyTeam_Assistant1'
				}, {
					fieldLabel: 'Водитель',
					name: 'EmergencyTeam_Driver',
				}, {
					fieldLabel: 'МО Госпитализации',
					name: 'LpuHid_Nick'
				}, {
					fieldLabel: 'Время доезда бригады',
					name: ''
				}, {
					fieldLabel: 'Информация о количестве вызовов',
					name: 'countCateredCmpCallCards'
				}]
			})]
		});

		var teamsGridMenu = new Ext.menu.Menu();

		teamsGridMenu.add({ text: 'Информация о бригаде', handler: function() {
			var row = win.emergencyTeamsGrid.getSelectionModel().getSelected();
			if(row && row.get('EmergencyTeam_id')) {
				emergencyTeamWindow.init( row.get('EmergencyTeam_id') );
			}
		} });

		win.emergencyTeamsGrid.addListener('rowcontextmenu', function(grid, rowIndex, e) {
			e.stopEvent();
			grid.getSelectionModel().selectRow(rowIndex);
			var coords = e.getXY();
			teamsGridMenu.showAt([coords[0], coords[1]]);
		}, this);

		//грид подстанций
		//@todo сделать адрес после карты
		win.buildingsGrid = new Ext.grid.GridPanel({
			title: 'Подстанции',
			plugins: buildingsTeamsCheck,
			selectionModel: 'multiselect',
			viewConfig:{
				forceFit: true
			},
			tbar: [
				/*{ xtype: 'tbfill' },
				{	
					xtype: 'splitbutton',
					icon: 'img/icons/actions16.png',
					iconCls: 'x-btn-text',
					name: 'actions',
					text: 'Действия',
					tooltip: 'Действия',
					menu: new Ext.menu.Menu({
						items: [
							{text: 'Item 1', handler: function(){}},
							{text: 'Item 2', handler: function(){}}
						]
					})
				}*/
			],
			store: new Ext.data.GroupingStore({
				reader: new Ext.data.JsonReader({}, [
					{ name: 'LpuBuilding_Name', type: 'string' },
					{ name: 'LpuBuilding_id', type: 'int' },
					
					{ name: 'TeamsStatusFree_Count', type: 'int' },
					{ name: 'TeamsStatusUnaccepted_Count', type: 'int' },					
					{ name: 'TeamsStatusDuty_Count', type: 'int' },
					{ name: 'Team_HeadShiftCount', type: 'int' },
					{ name: 'Team_AssistantCount', type: 'int' },
					{ name: 'CallsAccepted', type: 'int' },
					{ name: 'CallsNoAccepted', type: 'int' },
					{ name:'lpuDuildingCheck', defaultValue: false}
				]),				
				sortInfo:{field: 'LpuBuilding_id', direction: "ASC"},
				groupField:'LpuBuilding_id',
				url: '/?c=EmergencyTeam4E&m=getCountsTeamsCallsAndDocsARMCenterDisaster',
				autoLoad: getRegionNick() != 'ufa'
			}),
			columns: [
				{ hidden: true, hideable: false, dataIndex: 'LpuBuilding_id', id:'LpuBuilding_id' },
				buildingsTeamsCheck,
				{ width: 40, sortable: true, header: "Подстанция", dataIndex: 'LpuBuilding_Name',
					renderer:function(rowNum,cell,rec){
						var innerCell = rec.get('LpuBuilding_Name')+'<span title="Показать на карте" style="font-weight:normal; margin: 0px 2px 0 8px;" class="cmkimg showBuildingOnMap itemBuild-' + rec.get('LpuBuilding_id') + '"></span>';
						return innerCell;
					}
				},
				{ width: 20, sortable: true, header: "Бр. свободные", dataIndex: 'TeamsStatusFree_Count' },
				{ width: 20, sortable: true, header: "Бр. недоступные", dataIndex: 'TeamsStatusUnaccepted_Count' },
				{ width: 20, sortable: true, header: "Бр. на вызове", dataIndex: 'TeamsStatusDuty_Count', hidden: true},				
				{ width: 20, sortable: true, header: "Выз. назнач.", dataIndex: 'CallsAccepted' },
				{ width: 20, sortable: true, header: "Выз. неназнач.", dataIndex: 'CallsNoAccepted', 
					renderer:function(rowNum,cell,rec){
						return '<span style="color:#ff0000;">'+rec.get('CallsNoAccepted')+'</span>'
					}
				},
				{ width: 20, sortable: true, header: "Врачи", dataIndex: 'Team_HeadShiftCount' },
				{ width: 20, sortable: true, header: "Фельдшеры", dataIndex: 'Team_AssistantCount' },
				
			],
			listeners: {
				cellclick: function( grid, rowIndex, columnIndex, e){
					if(e.target.classList.contains('showBuildingOnMap')){
						var buildId = e.target.classList[2].replace('itemBuild-', '')
						win.showBuildingOnMap(buildId);
					}
					else{
						this.toggleGroup(hd.parentNode);
					}					
				}
			}
		});

		win.OutfitsGrid = new Ext.grid.GridPanel({
			title: 'Наряды',
			id: win.id + '_OutfitsGrid',
			dataUrl: '/?c=EmergencyTeam4E&m=loadEmergencyTeamShiftList',
			firstShow: true,
			stripeRows: true,
			autoExpandColumn: 'autoexpand',
			paging: true,
			root: 'data',
			listeners: {
				activate: function() {
					Ext.getCmp(win.id + '_OutfitsFilters').show();
				},
				deactivate: function() {
					Ext.getCmp(win.id + '_OutfitsFilters').hide();
				}
			},
			tbar: [{
					xtype: 'label',
					text: 'Показать',
					style: 'margin: 0 5px 0 10px;'
				}, {
					id: win.id + '_OutfitTypeFilter',
					xtype: 'swbaselocalcombo',
					valueField: 'code',
					displayField: 'name',
					editable: false,
					value: 0,
					store: new Ext.data.SimpleStore ({
						fields: ['code', 'name' ],
						data : [
							[ 0, "Все наряды" ],
							[ 1, "Текущие наряды" ]
						]
					}),
					listeners: {
						select: function(combo, record, idx) {
							win.setFiltersToOutfitsGrid();
						}
					}
				}, {
					xtype: 'label',
					text: 'Профиль бригады',
					style: 'margin: 0 15px 0 5px;'
				}, {
					id: win.id + '_EmergencyTeamSpecFilter',
					xtype: 'swcommonsprcombo',
					comboSubject: 'EmergencyTeamSpec',
					autoLoad: true,
					listeners: {
						expand: function(combo) {
							combo.store.filterBy( function (rec) {
								return rec.get('EmergencyTeamSpec_Code').inlist(['РБ','ВВБ','ФВБ',"К",'Н','П','СА','']);
							})
						},
						select: function(combo, record, idx) {
							win.setFiltersToOutfitsGrid();
						}
					}
				}, {
					xtype: 'label',
					text: 'Номер бригады',
					style: 'margin: 0 15px 0 5px;',
				}, {
					id: win.id + '_ETNumFilter',
					xtype: 'textfield',
					enableKeyEvents: true,
					listeners: {
						keyup: function(textfield) {
							win.setFiltersToOutfitsGrid();
						}
					}
				}
			],
			actions: [ { name: 'action_refresh', iconCls: 'refresh16', text: 'Обновить', handler: function(btn) { } } ],
			store: new Ext.data.GroupingStore({
				autoLoad: true,
				reader: new Ext.data.JsonReader({},[
					{ type: 'int', name: 'EmergencyTeamSpec_id' },
					{ type: 'int', name: 'EmergencyTeam_id' },
					{ type: 'int', name: 'Lpu_id' },
					{ type: 'int', name: 'LpuBuilding_id' },
					{ type: 'string', name: 'EmergencyTeam_Num' },
					{ type: 'string', name: 'EmergencyTeam_CarBrand' },
					{ type: 'string', name: 'EmergencyTeam_GpsNum' },
					{ type: 'string', name: 'EmergencyTeamDuty_ChangeComm' },
					{ type: 'string', name: 'CMPTabletPC_id' },
					{ type: 'string', name: 'Lpu_Nick' },
					{ type: 'string', name: 'LpuBuilding_Name' },
					{ type: 'string', name: 'MedProduct_Name' },
					{ type: 'string', name: 'GeoserviceTransport_name' },
					{ type: 'date', name: 'EmergencyTeamDuty_DTStart' },
					{ type: 'date', name: 'EmergencyTeamDuty_DTFinish' },
					{ type: 'date', name: 'EmergencyTeamDuty_factToWorkDT' },
					{ type: 'date', name: 'EmergencyTeamDuty_factEndWorkDT' }

				]),
				sortInfo:{field: 'LpuBuilding_id', direction: "ASC"},
				groupField:'LpuBuilding_id',
				url: '/?c=EmergencyTeam4E&m=loadOutfitsARMCenterDisaster',
				listeners: {
					load: function() {
						win.setFiltersToOutfitsGrid();
					}
				}
			}),
			columns: [
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeam_id',  key: true },
				{ hidden: true, hideable: false, dataIndex: 'Lpu_id' },
				{ hidden: true, hideable: false, dataIndex: 'LpuBuilding_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeamSpec_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeam_HeadShift' },
				{ hidden: true, sortable: true, dataIndex: 'Lpu_Nick', header: 'МО' },
				{ hidden: true, sortable: true, dataIndex: 'LpuBuilding_Name',  header: 'Подразделение', id: 'autoexpand' },
				{ sortable: true, dataIndex: 'EmergencyTeam_Num', header: 'Номер бригады' },
				{ sortable: true, dataIndex: 'MedProduct_Name',   header: 'Автомобиль' },
				{ sortable: true, dataIndex: 'GeoserviceTransport_name',    header: 'GPS/Глонасс' },
				{ sortable: true, dataIndex: 'EmergencyTeamDuty_DTStart',   header: 'Дата начала', width: 60, renderer: Ext.util.Format.dateRenderer('d-m-Y H:i') },
				{ sortable: true, dataIndex: 'EmergencyTeamDuty_DTFinish',  header: 'Дата окончания', width: 60, renderer: Ext.util.Format.dateRenderer('d-m-Y H:i') },
				{ sortable: true, dataIndex: 'EmergencyTeamDuty_factToWorkDT',  header: 'Факт. начало',width: 60, renderer: Ext.util.Format.dateRenderer('d-m-Y H:i') },
				{ sortable: true, dataIndex: 'EmergencyTeamDuty_factEndWorkDT', header: 'Факт. окончание',width: 60, renderer: Ext.util.Format.dateRenderer('d-m-Y H:i') },
				{ sortable: true, dataIndex: 'CMPTabletPC_id', header: 'Планшет', width: 20,
					renderer: function(rowNum,cell,rec) {
						if(rec) {
							return Ext.isEmpty(rec.get('CMPTabletPC_id')) ? 'Нет': 'Да' 
						}
						return '';
					}
				}
			],
			view: new Ext.grid.GroupingView({
				forceFit: true,
				startGroup: new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd cmk-group-header" style="{style}"><div class="x-grid-group-title">',
						'{[this.countTotal(values.rs)]}',
					'</div></div><div id="{groupId}-bd" class="x-grid-group-body">',
					{
						countTotal: function(records){
						//формирование заголовка у группы в гриде
							var teamStatusFree = 0,
								teamStatusDuty = 0,
								teamStatusUnaccepted = 0,
								resultGroupHeaderString,
								callsAccepted = 0,
								callsNoAccepted = 0,
								countHeadShifts = 0,
								countAssistants = 0;
								
							for (i=0;i<records.length;i++){
								switch(records[i].get('EmergencyTeamStatus_Color')){
									case 'green': {teamStatusFree++; break;}
									case 'gray': {teamStatusUnaccepted++; break;}
									case 'black': {teamStatusDuty++; break;}
									default: {teamStatusDuty++; break;}
								}
								//назначенные
								if(records[i].get('CmpCallCard_id'))callsAccepted++;
								if(records[i].get('EmergencyTeam_HeadShiftCount'))countHeadShifts++;
								if(records[i].get('EmergencyTeam_AssistantCount'))countAssistants++;
							};
							callsNoAccepted = records[0].get('CountCmpCallCards') - callsAccepted;

							resultGroupHeaderString =
								'<div style="float: left; background: none;">' +
									records[0].get('Lpu_Nick') + 
									' (' + records[0].get('LpuBuilding_Name') + ')' +
									'<span title="Показать на карте" style="font-weight:normal; margin: 0px 2px 0 8px;" class="cmkimg showBuildingOnMap itemBuild-' + records[0].get('LpuBuilding_id') + '"></span>' +
								'</div><div style="clear:both;"></div>';
							
							return resultGroupHeaderString;
						}
					}
				),
				getRowClass: function(record) {
					return record.get('EmergencyTeam_id')? 'callNoAccepted':'callAccepted'
				},
				interceptMouse : function(e){
					//оверрайдим событие на распахивание группы в гриде
					var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
					if(hd){
						e.stopEvent();
						if(e.target.classList.contains('showBuildingOnMap')){
							var buildId = e.target.classList[2].replace('itemBuild-', '')
							win.showBuildingOnMap(buildId);
						}
						else{
							this.toggleGroup(hd.parentNode);
						}
					}
				}
			})
		})

		var outfitsGridMenu = new Ext.menu.Menu();

		outfitsGridMenu.add({ text: 'Информация о бригаде', handler: function() {
			var row = win.OutfitsGrid.getSelectionModel().getSelected();
			if(row && row.get('EmergencyTeam_id')) {
				emergencyTeamWindow.init( row.get('EmergencyTeam_id') );
			}
		} });

		win.OutfitsGrid.addListener('rowcontextmenu', function(grid, rowIndex, e) {
			e.stopEvent();
			grid.getSelectionModel().selectRow(rowIndex);
			var coords = e.getXY();
			outfitsGridMenu.showAt([coords[0], coords[1]]);
		}, this);

		win.setFiltersToOutfitsGrid = function() {
			if(getRegionNick() == 'ufa') {
				var outfitType = Ext.getCmp(win.id + '_OutfitTypeFilter').getValue(),
					ETSpec_id = Ext.getCmp(win.id + '_EmergencyTeamSpecFilter').getValue(),
					ETNum = Ext.getCmp(win.id + '_ETNumFilter').getValue();
			}

			win.OutfitsGrid.store.filterBy(function(rec) {
				var factEnd = rec.get('EmergencyTeamDuty_factEndWorkDT'),
					EmergencyTeamSpec_id = rec.get('EmergencyTeamSpec_id'),
					EmergencyTeam_Num = rec.get('EmergencyTeam_Num');
				return (!outfitType || !factEnd)
					&& (!ETSpec_id || ETSpec_id == EmergencyTeamSpec_id)
					&& (!ETNum || EmergencyTeam_Num.includes(ETNum))
			})
		};
		
		//грид вызовов
		var emergencyCallsCheck = new Ext.grid.CheckColumn({  
			header: '<div class="x-grid3-check-col x-grid3-cc-'+this.id+'">&#160;</div>', 
			dataIndex: 'callsCheck', 
			width: 5, 
			menuDisabled: true 
		});

		//грид вызовов
		//@todo сделать адрес после карты
		win.emergencyCallsGrid = new Ext.grid.GridPanel({
			title: 'Вызовы СМП',
			plugins: emergencyCallsCheck,
			selectionModel: 'multiselect',
			tbar: [
				{
					xtype: 'button',
					iconCls: 'expand-all-btn',
					name: 'expandAll',
					text: 'Свернуть все',
					tooltip: 'Свернуть все',
					handler: function(btn,e){						
						if(btn.btnEl.hasClass('opened')){
							btn.setText('Свернуть все');
							win.emergencyCallsGrid.getView().expandAllGroups();
						}
						else{
							btn.setText('Развернуть все');
							win.emergencyCallsGrid.getView().collapseAllGroups();
						};
						btn.btnEl.toggleClass('opened');
					}
				},
				{
					xtype: 'label',
					text: 'Показать',
					style: 'margin: 0 5px 0 10px;'
				},
				{
					id: win.id + '_IsSetTeamFilter',
					xtype: 'swbaselocalcombo',
					displayField:'name',
					editable: false,
					valueField: 'code',
					value: 0,
					store: new Ext.data.Store({
						reader: new Ext.data.JsonReader({}, [
							{ name: 'code', type: 'int' },
							{ name: 'name', type: 'string' }
						]),
						data: [{
							code: 0,
							name: 'Все вызовы'
						},{
							code: 1,
							name: 'Назначенные'
						},{
							code: 2,
							name: 'Неназначенные'
						}]
					}),
					listeners: {
						select: function( combo, record, index ) {
							win.setFiltersToEmergencyCallsGrid();
						}
					}
				},
				{
					xtype: 'label',
					text: 'МО госпитализации',
					style: 'margin: 0 5px 0 10px;'
				},
				{
					xtype: 'swlpuopenedcombo',
					id: win.id + '_LpuHidFilter',
					autoLoad: true,
					listeners: {
						change: function(combo, newValue) {
							win.setFiltersToEmergencyCallsGrid();
						}
					}
				},
				{
					xtype: 'label',
					text: 'Диагноз c',
					style: 'margin: 0 5px 0 10px;'
				},
				{
					id: win.id + '_DiagFromFilter',
					listWidth: 620,
					width: 100,
					valueField: 'Diag_Code',
					xtype: 'swdiagcombo',
					listeners: {
						change: function() {
							win.setFiltersToEmergencyCallsGrid();
						}
					}
				},
				{
					xtype: 'label',
					text: 'по',
					style: 'margin: 0 5px 0 10px;'
				},
				{
					id: win.id + '_DiagToFilter',
					listWidth: 620,
					width: 100,
					valueField: 'Diag_Code',
					xtype: 'swdiagcombo',
					listeners: {
						change: function() {
							win.setFiltersToEmergencyCallsGrid();
						}
					}
				},
				{
					xtype: 'container',
					autoEl: {},
					style: 'margin: 0 5px 0 10px;',
					items:[
					{
						xtype: 'checkbox',
						checked: true,
						boxLabel: 'Группировать по подстанциям',
						name: 'groupByStation',
						listeners: {
							check: function(cmp, checked){
								if(checked) win.emergencyCallsGrid.store.groupBy('LpuBuilding_id', true)
								else win.emergencyCallsGrid.store.clearGrouping( )
							}
						}
					}]					
				}
			],
			store: new Ext.data.GroupingStore({
				reader: new Ext.data.JsonReader({}, [
					{ type: 'int', name: 'CmpCallCard_isExtra' },
					{ type: 'int', name: 'is112' },
					{ type: 'int', name: 'Lpu_id' },
					{ type: 'int', name: 'Lpu_hid' },
					{ type: 'int', name: 'Diag_uid' },
					{ type: 'int', name: 'EmergencyTeam_id' },
					{ type: 'int', name: 'LpuBuilding_id' },
					{ type: 'int', name: 'EmergencyTeamStatus_id' },
					{ type: 'int', name: 'EmergencyTeamStatus_Code' },
					{ type: 'int', name: 'Person_Age' },
					{ type: 'int', name: 'CountCmpCallCards' },
					{ type: 'int', name: 'EmergencyTeam_HeadShiftCount' },
					{ type: 'int', name: 'EmergencyTeam_AssistantCount' },
					{ type: 'int', name: 'CmpCallCard_id' },
					{ type: 'string', name: 'EmergencyTeam_Name' },
					{ type: 'string', name: 'LpuBuildingName' },
					{ type: 'string', name: 'EmergencyTeamSpec_Code' },
					{ type: 'string', name: 'EmergencyTeamStatus_Name' },
					{ type: 'string', name: 'EmergencyTeamStatus_Color' },
					{ type: 'string', name: 'CmpReason_Name' },
					{ type: 'string', name: 'CmpCallCard_CallLng' },
					{ type: 'string', name: 'CmpCallCard_CallLtd' },
					{ type: 'string', name: 'Person_FIO' },
					{ type: 'string', name: 'Adress_Name' },
					{ type: 'string', name: 'personAgeText' },
					{ type: 'string', name: 'CmpCallCardStatusType_Code' },
					{ type: 'string', name: 'Lpu_Nick' },
					{ type: 'string', name: 'LpuHid_Nick' },
					{ type: 'string', name: 'Diag_Name' },
					{ type: 'string', name: 'Diag_Code' },
					{ type: 'string', name: 'Duplicate_Count' },
					{ type: 'string', name: 'CmpCallCard_Numv' },
					{ type: 'string', name: 'CmpCallCard_Ngod' },
					{ type: 'string', name: 'CmpCallType_Name' },
					{ type: 'date', name: 'CmpCallCard_prmDT' },
					{ type: 'date', name: 'CmpCallCardStatus_insDT'},
					{ type: 'date', name: 'statusTime' },
					{ name: 'callsCheck', defaultValue: false}
				]),				
				sortInfo:{field: 'EmergencyTeam_id', direction: "ASC"},
				groupField:'LpuBuilding_id',
				url: '/?c=EmergencyTeam4E&m=loadCmpCallCardsARMCenterDisaster',
				autoLoad: true,
				listeners: {
					load: function(st){
						win.emergencyCallsGrid.setTitle('Вызовы СМП ('+st.getCount()+')');
						win.setFiltersToEmergencyCallsGrid();
						win.emergencyTeamsGrid.view.refresh();
					}
				}
			}),

			columns: [
				{ hidden: true, hideable: false, dataIndex: 'CmpCallCard_id', id:'CmpCallCard_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeam_id' },
				{ hidden: true, hideable: false, dataIndex: 'LpuBuilding_id' },
				{ hidden: true, hideable: false, dataIndex: 'LpuBuildingName' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeamStatus_id' },
				{ hidden: true, hideable: false, dataIndex: 'CmpCallCardStatus_insDT' },
				{ hidden: true, hideable: false, dataIndex: 'Lpu_id' },
				{ hidden: true, hideable: false, dataIndex: 'Lpu_hid' },
				{ hidden: true, hideable: false, dataIndex: 'Diag_uid' },
				emergencyCallsCheck,
				{ width: 24, sortable: true, header: "Поступил", dataIndex: 'CmpCallCard_prmDT', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{ width: 20, sortable: true, header: "Повод", dataIndex: 'CmpReason_Name' },
				{ width: 20, sortable: true, header: "Адрес", dataIndex: 'Adress_Name' },
				{ width: 10, sortable: true, header: "Возраст", dataIndex: 'personAgeText' },
				{ width: 20, sortable: true, header: "ФИО", dataIndex: 'Person_FIO' },
				{ width: 20, sortable: true, header: "Бригада", dataIndex: 'EmergencyTeam_Name' },
				{ width:  8, sortable: true, header: "Профиль", dataIndex: 'EmergencyTeamSpec_Code' },
				{ width: 20, sortable: true, header: "Статус", dataIndex: 'EmergencyTeamStatus_Name' },
				{ width: 10, sortable: true, header: "Время", dataIndex: 'statusTime', renderer: Ext.util.Format.dateRenderer('H:i') },
				{ width: 20, sortable: true, header: "МО госпитализации", dataIndex: 'LpuHid_Nick' },
				{ width: 20, sortable: true, header: 'Тип обращения', dataIndex: 'CmpCallType_Name'},
				{ width: 20, sortable: true, header: "Вид вызова", dataIndex: 'CmpCallCard_isExtra', renderer: function(rowNum,cell,rec) {
					switch(rec.get('CmpCallCard_isExtra')) {
						case 1: return 'Экстренный';
						case 2: return 'Неотложный';
						default: return '';
					}
				}},
				{ width: 20, sortable: true, header: 'Диагноз', dataIndex: 'Diag_Name' },
				{ width: 10, sortable: true, header: 'Дублирующие обращения', dataIndex: 'Duplicate_Count'}
			],

			view: new Ext.grid.GroupingView({
				forceFit:true,
				getRowClass: function(record) {
					if( getRegionNick() == 'ufa' ) {

						var statusDT = record.get('CmpCallCardStatus_insDT'),
							isSmp = record.get('CmpCallCard_isExtra') == 1, //СМП ? НМП
							status = record.get('CmpCallCardStatusType_Code'),
							isSetTeam = !Ext.isEmpty( record.get('EmergencyTeam_id') ),
							nowDT = new Date(),
							minTimeSMP = win.SmpTiming.minTimeSMP,
							maxTimeSMP = win.SmpTiming.maxTimeSMP,
							minTimeNMP = win.SmpTiming.minTimeNMP,
							maxTimeNMP = win.SmpTiming.maxTimeNMP,
							minResponseTimeNMP = win.SmpTiming.minResponseTimeNMP;

						//только для статусов: передано, принято, решение старшего врача
						if( !isValidDate(statusDT) || !status.inlist(['1','2','10']) ) return;

						//минут прошло со смены статуса
						var minutes = ( nowDT.getTime() - statusDT.getTime() ) / ( 1000 * 60 );
						
						switch(true) {
							//решение старшего врача
							case status == '10':
							//передано или из 112 не закрытая карта
							case status == '1' && minTimeSMP < minutes && isSmp:
							case status == '1' && minResponseTimeNMP < minutes && !isSmp:
							case status == '2' && maxTimeSMP < minutes && isSmp:
							case status == '2' && maxTimeNMP < minutes && !isSmp:
								return 'danger'

							//dspjd 
							case !isSetTeam:
								return 'attemption';
						}
					} else {
						return record.get('EmergencyTeam_id')? 'callNoAccepted':'callAccepted';
					}
				},
				interceptMouse : function(e){
					//оверрайдим событие на распахивание группы в гриде
					var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
					if(hd){
						e.stopEvent();
						if(e.target.classList.contains('showBuildingOnMap')){
							var buildId = e.target.classList[2].replace('itemBuild-', '')
							win.showBuildingOnMap(buildId);
						}
						else{
							this.toggleGroup(hd.parentNode);
						}
					}
				},
				startGroup: new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd cmk-group-header" style="{style}"><div class="x-grid-group-title">',
						'{[this.countTotal(values.rs)]}',
					'</div></div><div id="{groupId}-bd" class="x-grid-group-body">',
					{
						countTotal: function(records){
						//формирование заголовка у группы в гриде
							var teamStatusFree = 0,
								teamStatusDuty = 0,
								teamStatusUnaccepted = 0,
								resultGroupHeaderString,
								callsAccepted = 0,
								callsNoAccepted = 0,
								countHeadShifts = 0,
								countAssistants = 0;
								
							for (i=0;i<records.length;i++){
								switch(records[i].get('EmergencyTeamStatus_Color')){
									case 'green': {teamStatusFree++; break;}
									case 'gray': {teamStatusUnaccepted++; break;}
									case 'black': {teamStatusDuty++; break;}
									default: {teamStatusDuty++; break;}
								}
								//назначенные
								if(records[i].get('CmpCallCard_id'))callsAccepted++;
								if(records[i].get('EmergencyTeam_HeadShiftCount'))countHeadShifts++;
								if(records[i].get('EmergencyTeam_AssistantCount'))countAssistants++;
							};
							callsNoAccepted = records[0].get('CountCmpCallCards') - callsAccepted;

							resultGroupHeaderString =
								'<div style="float: left; background: none;">' +
									records[0].get('Lpu_Nick') +' (' + records[0].get('LpuBuildingName') + ')' +
									'<span title="Показать на карте" style="font-weight:normal; margin: 0px 2px 0 8px;" class="cmkimg showBuildingOnMap itemBuild-' + records[0].get('LpuBuilding_id') + '"></span>' +
								'</div><div style="clear:both;"></div>';
							
							return resultGroupHeaderString;
						}
					}
				)
			})
		});

		win.setFiltersToEmergencyCallsGrid = function() {
			var val = Ext.getCmp(win.id + '_IsSetTeamFilter').getValue(),
				Lpu_id = Ext.getCmp(win.id + '_LpuHidFilter').getValue(),
				codeFrom = Ext.getCmp(win.id + '_DiagFromFilter').getValue(),
				codeTo = Ext.getCmp(win.id + '_DiagToFilter').getValue();

			if( codeFrom && !codeTo || !codeFrom && codeTo) {
				if( !codeFrom )
					codeFrom = 'A';

				if( !codeTo )
					codeTo = 'ZZ';
			}

			win.emergencyCallsGrid.store.filterBy( function(rec){
				var Lpu_hid = rec.get('Lpu_hid'),
					code = rec.get('Diag_Code');
				return (!val || val == 1 && rec.get('EmergencyTeam_id') || val == 2 && !(rec.get('EmergencyTeam_id')) )
					&& (!Lpu_id || Lpu_hid == Lpu_id)
					&& (!codeFrom && !codeTo || ( codeFrom <= code ) && ( code <= codeTo ) )
			});
		};

		var cmpCallCardHistoryStore = new Ext.data.JsonStore({
			fields: [
				{ type: 'int', name: 'CmpCallCard_id' },
				{ type: 'int', name: 'event_id'},
				{ type: 'string', name: 'CmpCallCardEventType_Name'},
				{ type: 'string', name: 'pmUser_FIO'},
				{ type: 'string', name: 'EventValue' },
				{ type: 'date', name: 'EventDT' }
			],
			url: '/?c=CmpCallCard4E&m=loadCmpCallCardEventHistory',
			sortInfo: { field: 'EventDT', direction: "ASC" }
		});

		var cmpCallCardHistoryWindow = new Ext.Window({
			title: "История вызова",
			closeAction: 'hide',
			resizable: false,
			listeners: {
				hide: function() {
					cmpCallCardHistoryStore.removeAll();
				}
			},
			init: function(CmpCallCard_id) {
				if(!CmpCallCard_id) return;
				cmpCallCardHistoryWindow.show();
				cmpCallCardHistoryStore.load({ params: { CmpCallCard_id: CmpCallCard_id }});
			},
			items: [new Ext.grid.GridPanel({
				height: 300,
				loadMask: { msg: 'Загрузка...' },
				columns: [
					{ hidden: true, hideable: false, dataIndex: 'CmpCallCard_id' },
					{ width: 150, sortable: true, header: 'Дата и время', dataIndex: 'EventDT', renderer: Ext.util.Format.dateRenderer('d.m.Y H:i:s') },
					{ width: 300, sortable: true, header: 'Событие', name: 'CmpCallCardEventType_Name' },
					{ width: 300, sortable: true, header: 'ФИО', name: 'pmUser_FIO' },
					{ width: 200, sortable: true, header: 'Значение события', name: 'EventValue' }
				],
				store: cmpCallCardHistoryStore
			})]
		});

		var nmpCallsCheck = new Ext.grid.CheckColumn({  
			header: '<div class="x-grid3-check-col x-grid3-cc-'+this.id+'">&#160;</div>',
			dataIndex: 'callsCheck',
			width: 5,
			menuDisabled: true
		});

		win.nmpCallsGrid = new Ext.grid.GridPanel({
			title: 'Вызовы НМП',
			plugins: nmpCallsCheck,
			selectionModel: 'multiselect',
			tbar: [{
				xtype: 'button',
				iconCls: 'expand-all-btn',
				name: 'expandAll',
				text: 'Свернуть все',
				handler: function(btn,e){						
					if(btn.btnEl.hasClass('opened')){
						btn.setText('Свернуть все');
						win.nmpCallsGrid.getView().expandAllGroups();
					}
					else{
						btn.setText('Развернуть все');
						win.nmpCallsGrid.getView().collapseAllGroups();
					};
					btn.btnEl.toggleClass('opened');
				}
				}, {
					xtype: 'label',
					html: '&nbsp МО НМП &nbsp'
				},//todo
				{
					id: win.id + '_NmpLpuFilter',
					xtype: 'swlpuopenedcombo',
					autoLoad: true,
					listeners: {
						change: function(combo, newValue) {
							win.setFiltersToNmpCallsGrid();
						}
					}
				}, {
					xtype: 'label',
					text: 'Статус',
					style: 'margin: 0 5px 0 10px;'
				}, {
					id: win.id + '_CmpCallCardStatusFilter',
					editable: false,
					xtype: 'swbaselocalcombo',
					displayField:'name',
					valueField: 'code',
					value: 0,
					store: new Ext.data.Store({
						reader: new Ext.data.JsonReader({}, [
							{ name: 'code', type: 'int' },
							{ name: 'name', type: 'string' }
						]),
						data: [{
							code: 0,
							name: 'Все вызовы'
						}, {
							code: 1,
							name: 'Передано'
						}, {
							code: 2,
							name: 'Принято'
						}, {
							code: 10,
							name: 'Решение старшего врача'
						}]
					}),
					listeners: {
						select: function( combo, record, index ) {
							win.setFiltersToNmpCallsGrid();
						}
					}
				}, {
					xtype: 'checkbox',
					style: 'margin: 0 10px 0 5px;',
					checked: true,
					listeners: {
						check: function(cmp, checked){
							if(checked) win.nmpCallsGrid.store.groupBy('NmpLpu_id', true)
							else win.nmpCallsGrid.store.clearGrouping( )
						}
					}
				}, {
					xtype: 'label',
					html: '&nbsp;Группировать по МО',
				}
			],
			store: new Ext.data.GroupingStore({
				baseParams: {
					isNmp: 1
				},
				reader: new Ext.data.JsonReader({}, [
					{ type: 'int', name: 'CmpCallCard_id' },
					{ type: 'int', name: 'Lpu_id' },
					{ type: 'int', name: 'NmpLpu_id' },
					{ type: 'int', name: 'Person_Age' },
					{ type: 'int', name: 'CountCmpCallCards' },
					{ type: 'int', name: 'MedService_id' },
					{ type: 'string', name: 'LpuBuildingName' },
					{ type: 'string', name: 'CmpReason_Name' },
					{ type: 'string', name: 'CmpCallCard_CallLng' },
					{ type: 'string', name: 'CmpCallCard_CallLtd' },
					{ type: 'string', name: 'CmpCallCardStatusType_Code' },
					{ type: 'string', name: 'CmpCallCardStatusType_Name' },
					{ type: 'string', name: 'Person_FIO' },
					{ type: 'string', name: 'Adress_Name' },
					{ type: 'string', name: 'Lpu_Nick' },
					{ type: 'string', name: 'NmpLpu_Nick' },
					{ type: 'string', name: 'MedService_Nick' },
					{ type: 'string', name: 'personAgeText' },
					{ type: 'string', name: 'PPDUser_Name' },
					{ type: 'date', name: 'CmpCallCard_prmDT' },
					{ type: 'date', name: 'CallAcceptanceDT' },
					{ type: 'date', name: 'statusTime' },
					{ name:'callsCheck', defaultValue: false}
				]),
				sortInfo:{ field: 'CmpCallCard_prmDT', direction: "DESC" },
				groupField: 'Lpu_id',
				url: '/?c=EmergencyTeam4E&m=loadCmpCallCardsARMCenterDisaster',
				autoLoad: true,
				listeners: {
					load: function(store){
						win.nmpCallsGrid.setTitle('Вызовы НМП ('+store.getCount()+')');
						win.setFiltersToNmpCallsGrid();
					}
				}
			}),

			columns: [
				{ hidden: true, hideable: false, dataIndex: 'CmpCallCard_id', id:'CmpCallCard_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeam_id' },
				{ hidden: true, hideable: false, dataIndex: 'Lpu_id' },
				{ hidden: true, hideable: false, dataIndex: 'NmpLpu_id' },
				{ hidden: true, hideable: false, dataIndex: 'MedService_id' },
				{ hidden: true, hideable: false, dataIndex: 'EmergencyTeamStatus_id' },
				nmpCallsCheck,
				{ width: 24, sortable: true, header: "Дата/время передачи вызова", dataIndex: 'CmpCallCard_prmDT', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{ width: 24, sortable: true, header: "Дата/время принятия вызова", dataIndex: 'CallAcceptanceDT', renderer: Ext.util.Format.dateRenderer('Y-m-d H:i:s')},
				{ width: 20, sortable: true, header: "Повод", dataIndex: 'CmpReason_Name' },
				{ width: 20, sortable: true, header: "Адрес", dataIndex: 'Adress_Name' },
				{ width: 10, sortable: true, header: "Возраст", dataIndex: 'personAgeText' },
				{ width: 20, sortable: true, header: "ФИО", dataIndex: 'Person_FIO' },
				{ width: 20, sortable: true, header: "Статус", dataIndex: 'CmpCallCardStatusType_Name', 
					filter: new Ext.form.TriggerField({
						name:'CmpCallCardStatusType_Name',
						enableKeyEvents: true,
						cls: 'inputClearTextfieldsButton',
						triggerConfig: {
							tag: 'span',
							cls: 'x-field-combo-btns',
							cn: [
								{tag: 'div', cls: 'x-form-trigger clearTextfieldsButton'},
							]
						},
						onTriggerClick: function (e) {
							this.setValue();
							win.filterRowReq._search();
						}
					})
				},
				{ width: 10, sortable: true, header: "Время", dataIndex: 'statusTime', renderer: Ext.util.Format.dateRenderer('H:i') },
				{ width: 20, sortable: true, header: "Сотрудник", dataIndex: 'PPDUser_Name' },
				{ width: 20, sortable: true, header: "МО НМП", dataIndex: 'NmpLpu_Nick'},
				{ width: 20, sortable: true, header: 'Служба', dataIndex: 'MedService_Nick' }
			],

			view: new Ext.grid.GroupingView({
				forceFit:true,
				getRowClass: function(record) {
					var prmDT = record.get('CmpCallCard_prmDT'),
						nowDT = new Date(),
						status = record.get('CmpCallCardStatusType_Code'),
						minTimeNmp = win.SmpTiming.minTimeNMP,
						maxTimeNMP = win.SmpTiming.maxTimeNMP;

					// передано, передано из 112, принято, решение старшего врача
					if( !isValidDate(prmDT) || !status.inlist(['1', '12', '2','10']) ) return;

					var minutes = ( nowDT.getTime() - prmDT.getTime() ) / ( 1000 * 60 );
					switch(true) {
						case status == '10':
						case status.inlist(['1','12']) && minutes > minTimeNmp:
						case status == '2' && minutes > maxTimeNMP:
							return 'danger';
					}
				},
				interceptMouse : function(e){
					//оверрайдим событие на распахивание группы в гриде
					var hd = e.getTarget('.x-grid-group-hd', this.mainBody);
					if(hd){
						e.stopEvent();
						if(e.target.classList.contains('showBuildingOnMap')){
							var buildId = e.target.classList[2].replace('itemBuild-', '')
							win.showBuildingOnMap(buildId);
						}
						else{
							this.toggleGroup(hd.parentNode);
						}
					}
				},
				startGroup: new Ext.XTemplate(
					'<div id="{groupId}" class="x-grid-group {cls}">',
					'<div id="{groupId}-hd" class="x-grid-group-hd cmk-group-header" style="{style}"><div class="x-grid-group-title">',
						'{[this.countTotal(values.rs)]}',
					'</div></div><div id="{groupId}-bd" class="x-grid-group-body">',
					{
						countTotal: function(records){
						//формирование заголовка у группы в гриде
							var teamStatusFree = 0,
								teamStatusDuty = 0,
								teamStatusUnaccepted = 0,
								resultGroupHeaderString,
								callsAccepted = 0,
								callsNoAccepted = 0,
								countHeadShifts = 0,
								countAssistants = 0;
								
							for (i=0;i<records.length;i++){
								switch(records[i].get('EmergencyTeamStatus_Color')){
									case 'green': {teamStatusFree++; break;}
									case 'gray': {teamStatusUnaccepted++; break;}
									case 'black': {teamStatusDuty++; break;}
									default: {teamStatusDuty++; break;}
								}
								//назначенные
								if(records[i].get('CmpCallCard_id'))callsAccepted++;
								if(records[i].get('EmergencyTeam_HeadShiftCount'))countHeadShifts++;
								if(records[i].get('EmergencyTeam_AssistantCount'))countAssistants++;
							};
							callsNoAccepted = records[0].get('CountCmpCallCards') - callsAccepted;

							resultGroupHeaderString =
								'<div style="float: left; background: none;">' +
									records[0].get('Lpu_Nick') +
									'<span title="Показать на карте" style="font-weight:normal; margin: 0px 2px 0 8px;" class="cmkimg showBuildingOnMap itemBuild-' + records[0].get('NmpLpu_id') + '"></span>' +
								'</div><div style="clear:both;"></div>';
							
							return resultGroupHeaderString;
						}
					}
				)
			})
		});

		win.setFiltersToNmpCallsGrid = function() {
			var CCCStatus_Code = Ext.getCmp(win.id + '_CmpCallCardStatusFilter').getValue(),
				Lpu_id = Ext.getCmp(win.id + '_NmpLpuFilter').getValue();

			win.nmpCallsGrid.store.filterBy( function(rec){
				var CCST_C = rec.get('CmpCallCardStatusType_Code'),
					NmpLpu_id = rec.get('NmpLpu_id');
				return (CCST_C == CCCStatus_Code || !CCCStatus_Code)
					&& (!Lpu_id || NmpLpu_id == Lpu_id);
			});
		};

		var nmpGridMenu = new Ext.menu.Menu(),
			smpGridMenu = new Ext.menu.Menu();

		nmpGridMenu.add({ text: 'История вызовов', handler: function() {
			var row = win.nmpCallsGrid.getSelectionModel().getSelected();
			if(row && row.get('CmpCallCard_id')) {
				cmpCallCardHistoryWindow.init( row.get('CmpCallCard_id') );
			}
		} });

		win.nmpCallsGrid.addListener('rowcontextmenu', function(grid, rowIndex, e) {
			e.stopEvent();
			grid.getSelectionModel().selectRow(rowIndex);
			var coords = e.getXY();
			nmpGridMenu.showAt([coords[0], coords[1]]);
		}, this);

		smpGridMenu.add({ text: 'История вызовов', handler: function() {
			var row = win.emergencyCallsGrid.getSelectionModel().getSelected();
			if(row && row.get('CmpCallCard_id')) {
				cmpCallCardHistoryWindow.init( row.get('CmpCallCard_id') );
			}
		} });

		win.emergencyCallsGrid.addListener('rowcontextmenu', function(grid, rowIndex, e) {
			e.stopEvent();
			grid.getSelectionModel().selectRow(rowIndex);
			var coords = e.getXY();
			smpGridMenu.showAt([coords[0], coords[1]]);
		}, this);

		win.CenterPanel = new Ext.Panel({
			region: 'center',
			layout: 'anchor',
			//border: false,
			defaults: {
				anchor: '100% 50%',
				activeItem: 0,
				//plain: true,
				border: false,
				deferredRender: false,
				xtype: 'tabpanel',
			},
			items: [ {
				items: [
					win.emergencyTeamsGrid
				]
			}, {
				items: [
					win.emergencyCallsGrid,
					win.nmpCallsGrid
				]
			} ]
		});

		var topTabPanel = win.CenterPanel.items.items[0];
		if(getRegionNick() != 'ufa') {
			topTabPanel.add(win.buildingsGrid);
		} else {
			topTabPanel.add(win.OutfitsGrid);
		}
		
		win.mapPanel = new Ext.Panel({
			//border: false,
			animCollapse: false,
			animFloat: false,
			floatable: true,
			collapseMode: 'mini',
			split: true,
			region: 'east',
			layout: 'fit',
			cls: 'mapTools',
			tbar: new Ext.Toolbar({
				items:[{ //кнопка для расширения карты
					xtype: 'button',
					text: '«',
					handler: function() {
					switch(win.screenMode) {
						//разворачиваем карту на весь экран
						case 'HalfScreenMap':
							win.screenMode = "FullScreenMap";
							win.mapPanel.setWidth( win.getSize().width );
							win.doLayout();
						break;

						case 'FullScreenMap':
						break;

						//разворачиваем на пол экрана
						case 'MapCollapsed':
							this.ownerCt.expand();
						break;
					}
					}
				}, {
					xtype: 'button',
					text: '»',
					handler: function() {
					switch(win.screenMode) {
						//скрываем карту
						case 'HalfScreenMap':
							win.mapPanel.collapse();
							win.mapPanel.collapsible = true; //setCollapsed
						break;

						//разворачиваем на пол экрана
						case 'FullScreenMap':
							win.screenMode = "HalfScreenMap";
							win.mapPanel.setWidth( win.getSize().width / 2 );
							win.doLayout();
						break;


						case 'MapCollapsed':
						break;
					}
					}
				}, '-', {
					xtype: 'label',
					html: '<div class="mapObj mapLpuStations"></div><span>Подстанции </span>'
				}, {
					xtype: 'checkbox',
					checked: true,
					name: 'mapDisplayLpuStationsField',
					listeners: {
						check: function(cmp, checked){
						}
					}
				}, {
					xtype: 'label',
					text: 'Бригады:',
					style: 'margin-left: 30px;'
				}, {
					xtype: 'label',
					html: '<div class="mapObj mapFreeEmergencyTeam"></div><span>Свободные </span>'
				},  {
					xtype: 'checkbox',
					cls: 'checkbox',
					checked: true,
					name: 'mapDisplayFreeEmergencyTeamField',
					listeners: {
						check: function(cmp, checked){
							if(checked){
								win.mapPanel.emergencyTeamFreeMarkersCollection.each(function(cmp){
									cmp.options.set('visible', true);
								});
							}
							else{
								win.mapPanel.emergencyTeamFreeMarkersCollection.each(function(cmp){
									cmp.options.set('visible', false);
								});
							}
						}
					}
				}, {
					xtype: 'label',
					html: '<div class="mapObj mapDutyEmergencyTeam"></div><span>На вызове </span>'
				}, {
					xtype: 'checkbox',
					checked: true,
					name: 'mapDisplayDutyEmergencyTeamField',
					listeners: {
						check: function(cmp, checked){
							if(checked){
								win.mapPanel.emergencyTeamDutyMarkersCollection.each(function(cmp){
									cmp.options.set('visible', true);
								});
							}
							else{
								win.mapPanel.emergencyTeamDutyMarkersCollection.each(function(cmp){
									cmp.options.set('visible', false);
								});
							}
						}
					}
				}, {
					xtype: 'label',
					html: '<div class="mapObj mapUnacceptedEmergencyTeam"></div><span>Недоступные </span>'
				}, {
					xtype: 'checkbox',
					checked: true,
					id: win.id + '_UnacceptedEmergencyTeamsCheckbox',
					listeners: {
						check: function(cmp, checked){
							if(checked){
								win.mapPanel.emergencyTeamNoAcceptedMarkersCollection.each(function(cmp){
									cmp.options.set('visible', true);
								});
							}
							else{
								win.mapPanel.emergencyTeamNoAcceptedMarkersCollection.each(function(cmp){
									cmp.options.set('visible', false);
								});
							}
						}
					}
				}, {
					xtype: 'label',
					text: 'Вызовы: ',
					style: 'margin-left: 30px;'
				}, {
					xtype: 'label',
					html: '<div class="mapObj mapAcceptedCalls"></div><span>Назначенные </span>'
				}, {
					xtype: 'checkbox',
					checked: true,
					id: win.id + '_AcceptedCallsCheckbox',
					listeners: {
						check: function(cmp, checked){
							if(checked) {
								win.prepareCallsMarkersToMap(win.mergedStore.data.items);
							} else {
								win.mapPanel.callsAcceptedMarkersCollection.removeAll();
							}
						}
					}
				}, {
					xtype: 'label',
					html: '<div class="mapObj mapNoAcceptedCalls"></div><span>Неназначенные </span>'
				}, {
					xtype: 'checkbox',
					checked: true,
					id: win.id + '_NoAcceptedCallsCheckbox',
					listeners: {
						check: function(cmp, checked){
							if(checked) {
								win.prepareCallsMarkersToMap(win.mergedStore.data.items);
							} else {
								win.mapPanel.callsNoAcceptedMarkersCollection.removeAll();
							}
						}
					}
				}]
			}),
			listeners: {
				collapse: function(panel) {
					win.screenMode = "MapCollapsed";
				},
				expand: function(panel) {
					win.screenMode = "HalfScreenMap";
				},
				beforerender: function(panel) {
					panel.setWidth( win.getSize().width / 2 );
					win.initMap();
				},
				afterlayout: function() {
					if(win.mapPanel.yanMap)
						win.mapPanel.yanMap.container.fitToViewport();
				}
			},
			items: [{
				xtype: 'container',
				autoEl: {},
				id: 'mapWrapper',
				layout: 'fit'
			}]
		});

		win.initMap = function(){
			if(typeof ymaps != 'undefined'){ win.showMap(); return; }
			
			//функция загрузка скрипта
			var loadScript = function(src, callback, appendTo) {
				var script = document.createElement('script');

				if (!appendTo) {
					appendTo = document.getElementsByTagName('head')[0];
				}

				if (script.readyState && !script.onload) {
					// IE, Opera
					script.onreadystatechange = function() {
						if (script.readyState == "loaded" || script.readyState == "complete") {
							script.onreadystatechange = null;
							callback();
						}
					}
				}
				else {
					// Rest
					script.onload = callback;
				}

				script.src = src;
				appendTo.appendChild(script);
			}

			var src = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU&apikey=' + getGlobalOptions().yandex_api_key;

			loadScript(src, function(){
				win.showMap();				
			})

		};


		win.showMap = function(){
			ymaps.ready(function () {
				
				var centerMap = [];
				
				//временно - захардкоденные координаты регионов
				switch(getGlobalOptions().region.nick){
					case 'krym' : {centerMap = [45.311411, 34.123286];	break;	} 
					case 'ufa' : {centerMap = [54.689761, 55.808338];	break;	} 
					case 'astra' : {centerMap = [46.350165, 47.967853];	break;	}
					default: {centerMap = [58.00310,56.24622];	break;	}
				}
				
				win.mapPanel.yanMap = new ymaps.Map("mapWrapper", {
					center: centerMap,
					zoom: 8,
					//controls: ['routePanelControl']
				});
				
				//коллекции маркеров
				win.mapPanel.callsAcceptedMarkersCollection = new ymaps.GeoObjectCollection({}, {});
				win.mapPanel.callsNoAcceptedMarkersCollection = new ymaps.GeoObjectCollection({}, {});
				win.mapPanel.emergencyTeamFreeMarkersCollection = new ymaps.GeoObjectCollection({}, {});
				win.mapPanel.emergencyTeamNoAcceptedMarkersCollection = new ymaps.GeoObjectCollection({}, {});
				win.mapPanel.emergencyTeamDutyMarkersCollection = new ymaps.GeoObjectCollection({}, {});
				//

				win.mapPanel.callsNoAcceptedMarkersCollection.events.add('click', function() {
					console.log('Не принятый вызов');
				}).add('mouseenter', function(e) {
					console.log(e.get('target'));
					e.get('target').options.set('preset', 'islands#greenIcon');
				}).add('mouseleave', function(e) {
					e.get('target').options.unset('preset');
				})


				var callsStore = win.emergencyCallsGrid.getStore();
				var nmpCallsStore = win.nmpCallsGrid.getStore();

				win.mergedStore.loadData(callsStore.data.items,true);
				win.mergedStore.loadData(nmpCallsStore.data.items,true);
				//win.prepareCallsMarkersToMap(win.mergedStore.data.items);

			})
		};
		
		
		win.mapPanel.addMarker = function(type, coords, title, descr){
			var markerImg, markerCollection;

			//type - это и маркер, это и группа коллекции маркеров
			switch (type){
				case 'emergencyCallAccepted': {
					markerImg = '/img/icons/mapMarkers/emergencyCallAccepted.png';
					markerCollection = win.mapPanel.callsAcceptedMarkersCollection;
					break;
				}
				case 'emergencyCallNoAccepted': {
					markerImg = '/img/icons/mapMarkers/emergencyCallNoAccepted.png';
					markerCollection = win.mapPanel.callsNoAcceptedMarkersCollection;
					break;
				}
				case 'emergencyTeamFree': {
					markerImg = '/img/icons/mapMarkers/emergencyTeamFree.png';
					markerCollection = win.mapPanel.emergencyTeamFreeMarkersCollection;
					break;
				}
				case 'emergencyTeamNoAccepted': {
					markerImg = '/img/icons/mapMarkers/emergencyTeamNoAccepted.png';
					markerCollection = win.mapPanel.emergencyTeamNoAcceptedMarkersCollection;
					break;
				}
				case 'emergencyTeamDuty': {
					markerImg = '/img/icons/mapMarkers/emergencyTeamDuty.png';
					markerCollection = win.mapPanel.emergencyTeamDutyMarkersCollection;
					break;
				}
			}
			
			var callAccepted = new ymaps.GeoObject({
				// Описываем геометрию типа "Точка".
				geometry: {
					type: "Point",
					coordinates: [parseFloat(coords[0]), parseFloat(coords[1])]
				},
				// Описываем данные геообъекта.
				properties: {
					hintContent: title,
					//balloonContentHeader: "Москва",
					//balloonContentBody: "Столица России"
				}
			},{
				iconLayout: 'default#image',
				iconImageHref: markerImg,
				iconImageSize: [16, 16],
				iconImageOffset: [-16, -8]
			});
			
			markerCollection.add(callAccepted);

			win.mapPanel.yanMap.geoObjects.add(markerCollection);
		};
		
		//стор блуждающих бригад
		win.carMarkersStore = new Ext.data.Store({
			reader: new Ext.data.JsonReader({}, [
				{ name: 'GeoserviceTransport_id', type: 'int' },
				{ name: 'GeoserviceTransport_name', type: 'string' },	
				{ name: 'lat', type: 'string' },
				{ name: 'lng', type: 'string' },
				{ name: 'direction', type: 'string' },
				{ name: 'groups', type: 'auto' }
			]),				
			url: '/?c=GeoserviceTransport&m=getGeoserviceTransportListWithCoords',
			autoLoad: true,
			listeners: {
				load: function(carSt){
					carSt.prepareData();
				}
			},
			prepareData: function(){
				var emTeamsStore = win.emergencyTeamsGrid.getStore(),
					collectTeamRecords = function(){
						var geoIds = emTeamsStore.collect('GeoserviceTransport_id');
						
						//удаление старых меток
						if (win.mapPanel.emergencyTeamFreeMarkersCollection) {
							win.mapPanel.emergencyTeamFreeMarkersCollection.removeAll();
						}
						if (win.mapPanel.emergencyTeamNoAcceptedMarkersCollection) {
							win.mapPanel.emergencyTeamNoAcceptedMarkersCollection.removeAll();
						}
						if (win.mapPanel.emergencyTeamDutyMarkersCollection) {
							win.mapPanel.emergencyTeamDutyMarkersCollection.removeAll();
						}
						
						win.carMarkersStore.each(function(rec){
							if(rec.get('GeoserviceTransport_id').inlist(geoIds)){
								var type, coords, title, descr,
									teamRec = emTeamsStore.query( 'GeoserviceTransport_id', rec.get('GeoserviceTransport_id') ).items[0],
									teamStatus = teamRec.get('EmergencyTeamStatus_Code');
								
								switch(true){
									case (teamStatus.inlist([13,21,36])): {type = 'emergencyTeamFree';break;}
									case (teamStatus.inlist([8,9,23])): {type = 'emergencyTeamNoAccepted';break;}
									case (!teamStatus.inlist([8,9,23,13,21,36])): {type = 'emergencyTeamDuty';break;}
								}
								coords = [rec.get('lat'),rec.get('lng')];
								title = 'МО: ' + teamRec.get('Lpu_Nick') + '<br>'
									  + 'Подстанция: ' + teamRec.get('EmergencyTeamBuildingName') + '<br>'
									  + 'Номер бригады:' + teamRec.get('EmergencyTeam_Num') + '<br>'
									  + 'Статус: ' + teamRec.get('EmergencyTeamStatus_Name') + '<br>'
									  + 'Время: ' + teamRec.get('statusTime').format('H:i') + '<br>'
									  + 'Профиль:' + teamRec.get('EmergencyTeamSpec_Code') + '<br>';

								win.mapPanel.addMarker(type, coords, title, descr);
							};
						});

					};
					
				if(emTeamsStore.getCount()){collectTeamRecords();}
				else{emTeamsStore.on('load', function(){collectTeamRecords();})}
			}
		
		});
		
		//отображение подстанции на карте
		win.showBuildingOnMap = function(buildId){
			console.log('@todo сделать отображение подстанции на карте Building_id - ', buildId );
		};
		
		win.reloadStores = function(){

			win.mergedStore.removeAll();
			win.emergencyCallsGrid.getStore().reload();
			win.emergencyTeamsGrid.getStore().reload();
			win.nmpCallsGrid.getStore().reload();

			if(getRegionNick() != 'ufa') {
				win.buildingsGrid.getStore().reload();
			} else {
				win.OutfitsGrid.getStore().reload();
			}

			win.carMarkersStore.reload();
		};

		win.emergencyCallsGrid.getStore().on('load', function(st,recs){
			win.mergedStore.loadData(recs,true);
		});
		win.nmpCallsGrid.getStore().on('load', function(st,recs){
			win.mergedStore.loadData(recs,true);
		});

		Ext.apply(win, {
			layout: 'border',
			cls: 'CenterDisasterMedicine',
			bodyStyle: 'background-color: white;',
			items: [ 
				win.mapPanel,
				win.CenterPanel
			]
		})
		
		sw.Promed.swWorkPlaceCenterDisasterMedicineWindow.superclass.initComponent.apply(this, arguments);
	}
});
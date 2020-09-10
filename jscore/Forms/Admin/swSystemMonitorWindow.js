/**
 * swSystemMonitorWindow - окно мониторинга системы
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			03.04.2014
 */

/*NO PARSE JSON*/

sw.Promed.swSystemMonitorWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemMonitorWindow',
	width: 800,
	height: 600,
	maximized: true,
	layout: 'border',
	title: lang['monitoring_sistemyi'],

	isFilter: false,
	isMonitorRunning: false,
	isQueryListRunning: false,
	inRowQueryExecCount: 0,
	intervalHandler: null,
	runTime: [],
	QueryLogCount: 0,

	listeners: {
		hide: function () {
			clearInterval(this.intervalHandler);
			this.isMonitorRunning = false;
			this.isQueryListRunning = false;
			this.findById('SMW_StartMonitoringButton').setText(lang['pusk']);
		}
	},

	downloadQueryLog: function() {
		Ext.Ajax.request({
			url: '\?c=SystemMonitor&m=exportSystemMonitorQueryLog',
			callback: function(options, success, response) {
				var result = Ext.util.JSON.decode(response.responseText);
				window.open(result.url, '_blank');
			}
		});
	},

	doSort: function() {
		var grid = this.QueryLogGrid.getGrid();
		var ss = grid.getStore().getSortState();
		if (ss) {
			grid.getStore().sort(ss.field, ss.direction);
		}
	},

	doFilter: function() {
		var base_form = this.FilterPanel.getForm();
		var grid = this.QueryLogGrid.getGrid();

		grid.getStore().clearFilter();
		var count = grid.getStore().getCount();

		var f = base_form.getValues();
		f.LogQuery_DateRange1 = base_form.findField('LogQuery_DateRange').getValue1();
		f.LogQuery_DateRange2 = base_form.findField('LogQuery_DateRange').getValue2();

		grid.getStore().filterBy(function(rec) {
			var flag = true;
			if (count > 100) {
				flag = (flag && rec.get('SystemMonitorQueryLog_Num') > count-100);
			}
			if (this.isFilter) {
				if (!Ext.isEmpty(f.SystemMonitorQuery_id)) {
					flag = (flag && rec.get('SystemMonitorQuery_id') == f.SystemMonitorQuery_id);
				}
				if (!Ext.isEmpty(f.LogQuery_RunTime1)) {
					flag = (flag && rec.get('SystemMonitorQueryLog_minRunTime') > f.LogQuery_RunTime1);
				}
				if (!Ext.isEmpty(f.LogQuery_RunTime2)) {
					flag = (flag && rec.get('SystemMonitorQueryLog_maxRunTime') < f.LogQuery_RunTime2);
				}
				if (!Ext.isEmpty(f.LogQuery_DateRange1) && !Ext.isEmpty(f.LogQuery_DateRange2)) {
					flag = (flag && rec.get('SystemMonitorQueryLog_Date') >= f.LogQuery_DateRange1);
					flag = (flag && rec.get('SystemMonitorQueryLog_Date') <= f.LogQuery_DateRange2);
				}
			}

			return flag;
		}.createDelegate(this));
	},

	cancelFilter: function() {
		var grid = this.QueryLogGrid.getGrid();

		grid.getStore().clearFilter();
		var index = 0;
		var count = grid.getStore().getCount();
		if (count > 100) {
			grid.getStore().filterBy(function(rec) {
				index = rec.get('SystemMonitorQueryLog_Num');
				return (index > count-100);
			});
		}
	},

	openQueryListWindow: function() {
		var params = new Object();

		params.callback = function() {
			var base_form = this.FilterPanel.getForm();
			var query_combo = base_form.findField('SystemMonitorQuery_id');

			this.SystemMonitorQueryStore.load({
				callback: function() {
					query_combo.getStore().loadData(getStoreRecords(this.SystemMonitorQueryStore, {exceptionFields: ['SystemMonitorQuery_Query']}));
				}.createDelegate(this)
			});
		}.createDelegate(this)

		getWnd('swSystemMonitorQueryListWindow').show(params);
	},

	openMarkerListWindow: function() {
		var params = new Object();

		params.MarkerListData = this.Markers.getMarkerListData();

		getWnd('swSystemMonitorMarkerListWindow').show(params);
	},

	/**
	 * Запускает/останавливает периодическое выполнение очереди запросов
	 */
	onStartButtonPress: function() {
		if (this.isMonitorRunning) {
			this.isMonitorRunning = false;
			this.isQueryListRunning = false;
			this.findById('SMW_StartMonitoringButton').setText(lang['pusk']);
			clearInterval(this.intervalHandler);
		} else {
			this.isMonitorRunning = true;
			this.findById('SMW_StartMonitoringButton').setText(lang['stop']);
			var interval = this.findById('SystemMonitor_Interval').getValue() * 1000 * 60;

			var a = 0;
			var func = function(){
				log(lang['zapusk_ocheredi']+(++a)+lang['y_raz']);
				if (!this.isQueryListRunning) {
					this.isQueryListRunning = true;
					this.runSystemMonitorQuery(0);
				}
			}.createDelegate(this);

			this.intervalHandler = setInterval(func, interval);
			func();
		}
	},

	/**
	 * Вызывает выполнение запроса из стора по индексу
	 */
	runSystemMonitorQuery: function(index) {
		if ( index >= this.SystemMonitorQueryStore.getCount() ) {
			this.isQueryListRunning = false;
			return;
		}
		var nextIndex = index;
		var record = this.SystemMonitorQueryStore.getAt(index);
		var url = this.Markers.processQuery(record.get('SystemMonitorQuery_Query'));

		var repeatCount = record.get('SystemMonitorQuery_RepeatCount');
		if (repeatCount == 0) {
			nextIndex++;
			this.runSystemMonitorQuery(nextIndex);
			return;
		}

		var date1, date2;

		date1 = new Date();
		Ext.Ajax.request({
			url: url,
			showErrors: false,
			callback: function(action, success, response) {
				var result = null;
				var respSuccess = false;
				if (success && response.responseText.length > 0) {
					try {
						result = Ext.util.JSON.decode(response.responseText);
						if (result && result.success != undefined) {
							respSuccess = result.success;
							if (result.Error_Msg) {
								log(result.Error_Msg);
							}
						} else {
							respSuccess = true;
						}
					} catch(e) {
						respSuccess = false;
					}
				}
				if (respSuccess) {
					for (var item in result) {
						if (/_id$/.test(item)) {
							log(item+': '+result[item]);
							this.Markers.LastResult_id = result[item];
							break;
						}
					}
				}

				date2 = new Date();
				this.runTime.push(date2.getElapsed(date1)/1000);

				var i = this.inRowQueryExecCount;
				log(
					lang['vyipolnen']+(i+1)+lang['iz']+repeatCount+lang['raz_vremya_curr']+this.runTime[i]
					+', min: '+Math.min.apply(null, this.runTime)+', max: '+Math.max.apply(null, this.runTime)
				);

				this.inRowQueryExecCount++;
				if (this.inRowQueryExecCount == repeatCount || !respSuccess /*|| !this.isMonitorRunning*/) {
					this.writeQueryLog(index, respSuccess);
					this.inRowQueryExecCount = 0;
					this.runTime = [];
					nextIndex++;
				}

				if (this.isMonitorRunning) {
					this.runSystemMonitorQuery(nextIndex);
				}
			}.createDelegate(this)
		});
	},

	writeQueryLog: function(index, query_success) {
		var recQuery = this.SystemMonitorQueryStore.getAt(index);
		var grid = this.QueryLogGrid.getGrid();
		var date = new Date();
		var avgRunTime = 0;

		this.runTime.forEach(function(val) { avgRunTime += val; }.createDelegate(this));
		avgRunTime = avgRunTime/this.runTime.length;
		this.QueryLogCount++;
		var data = {
			SystemMonitorQueryLog_id: null,
			SystemMonitorQueryLog_Num: this.QueryLogCount,
			SystemMonitorQuery_id: recQuery.get('SystemMonitorQuery_id'),
			SystemMonitorQuery_Name: recQuery.get('SystemMonitorQuery_Name'),
			SystemMonitorQueryLog_Date: date,
			SystemMonitorQueryLog_Time: date,
			SystemMonitorQueryLog_RunCount: this.inRowQueryExecCount,
			SystemMonitorQuery_TimeLimit: recQuery.get('SystemMonitorQuery_TimeLimit'),
			SystemMonitorQueryLog_minRunTime: Math.min.apply(null, this.runTime).toFixed(3),
			SystemMonitorQueryLog_maxRunTime: Math.max.apply(null, this.runTime).toFixed(3),
			SystemMonitorQueryLog_avgRunTime: avgRunTime.toFixed(3),
			SystemMonitorQueryLog_isError: query_success ? 1 : 2
		};

		//Копирует объект и подменяет дату и время на строковые
		var params = Ext.apply({}, {
			SystemMonitorQueryLog_Date: Ext.util.Format.date(date, 'd.m.Y'),
			SystemMonitorQueryLog_Time: Ext.util.Format.date(date, 'H:i')
		}, data);

		var record = new Ext.data.Record(data);
		grid.getStore().add([record]);
		this.doSort();
		this.doFilter();

		Ext.Ajax.request({
			url: '/?c=SystemMonitor&m=saveSystemMonitorQueryLog',
			params: params,
			callback: function(action, success, response) {
				var result = null;
				if (success && response.responseText.length > 0) {
					result = Ext.util.JSON.decode(response.responseText);
					if (result.SystemMonitorQueryLog_id) {
						record.id = result.SystemMonitorQueryLog_id;
						record.set('SystemMonitorQueryLog_id', result.SystemMonitorQueryLog_id);
					}
				}
			}.createDelegate(this)
		});
	},

	clearQueryLog: function() {
		sw.swMsg.show({
			buttons: Ext.Msg.YESNO,
			fn: function(buttonId, text, obj) {
				if ( buttonId == 'yes' ) {
					Ext.Ajax.request({
						failure: function() {

						},
						url: '/?c=SystemMonitor&m=clearSystemMonitorQueryLog',
						success: function(result_form, action) {
							this.QueryLogGrid.removeAll({addEmptyRecord: false});
							this.QueryLogCount = 0;
						}.createDelegate(this)
					});
				}
			}.createDelegate(this),
			icon: Ext.MessageBox.QUESTION,
			msg: lang['vyi_deystvitelno_hotite_ochistit_log'],
			title: lang['vopros']
		});
	},

	show: function() {
		sw.Promed.swSystemMonitorWindow.superclass.show.apply(this, arguments);

		var base_form = this.FilterPanel.getForm();
		var grid = this.QueryLogGrid.getGrid();

		this.isFilter = false;
		this.isMonitorRunning = false;
		this.isQueryListRunning = false;
		this.inRowQueryExecCount = 0;
		this.intervalHandler = null;
		this.runTime = [];

		this.findById('SMW_StartMonitoringButton').setText(lang['pusk']);

		if(sw.Promed.MedStaffFactByUser.current.ARMType === "smpadmin" ){
			this.findById('SMW_StartMonitoringButton').setDisabled(true);
			this.findById('SMW_OpenMarkerListWindowButton').setDisabled(true);
			this.findById('SMW_OpenQueryListWindowButton').setDisabled(true);
			this.findById('SMW_DownloadQueryLogButton').setDisabled(true);
			this.findById('SMW_ClearQueryLogButton').setDisabled(true);
		}else{
			this.findById('SMW_StartMonitoringButton').setDisabled(false);
			this.findById('SMW_OpenMarkerListWindowButton').setDisabled(false);
			this.findById('SMW_OpenQueryListWindowButton').setDisabled(false);
			this.findById('SMW_DownloadQueryLogButton').setDisabled(false);
			this.findById('SMW_ClearQueryLogButton').setDisabled(false);
		}

		this.findById('SystemMonitor_Interval').setValue(15);
		base_form.reset();
		this.QueryLogGrid.removeAll({addEmptyRecord: false});

		var query_combo = base_form.findField('SystemMonitorQuery_id');

		this.SystemMonitorQueryStore.load({
			callback: function() {
				query_combo.getStore().loadData(getStoreRecords(this.SystemMonitorQueryStore, {exceptionFields: ['SystemMonitorQuery_Query']}));

				grid.getStore().load({
					callback: function(store) {
						this.doFilter();
						this.QueryLogCount = grid.getStore().getTotalCount();
					}.createDelegate(this)
				});
			}.createDelegate(this)
		});
	},

	initComponent: function() {
		this.Markers = {
			Person_id: 3914363,
			LastResult_id: null,

			list: [
				{
					Marker: 'currDate',
					Descr: lang['tekuschaya_data'],
					getValue: function() {
						return Ext.util.Format.date(new Date(), 'd.m.Y');
					}.createDelegate(this)
				},
				{
					Marker: 'currDateTime',
					Descr: lang['tekuschaya_data_i_vremya'],
					getValue: function() {
						return Ext.util.Format.date(new Date(), 'd.m.Y H:i');
					}.createDelegate(this)
				},
				{
					Marker: 'Person_id',
					Descr: lang['identifikator_cheloveka'],
					getValue: function() {
						return this.Markers.Person_id;
					}.createDelegate(this)
				},
				{
					Marker: 'LastResult_id',
					Descr: lang['identifikator_prishedshiy_v_poslednem_otvete'],
					getValue: function() {
						return this.Markers.LastResult_id;
					}.createDelegate(this)
				}
			],

			getMarker: function(marker_name) {
				for (var i=0; i<this.list.length; i++) {
					if (this.list[i].Marker == marker_name) {
						return this.list[i];
					}
				}
				return null;
			},

			getMarkerListData: function() {
				var data = [];
				for (var i=0; i<this.list.length; i++) {
					data.push({
						id: i+1,
						Marker: this.list[i].Marker,
						Descr: this.list[i].Descr,
						Value: this.list[i].getValue()
					});
				}
				return data;
			},

			processQuery: function(query) {
				var Markers = this;
				var str = query;
				var regexp = /[{](\w+)[}]/g;

				for (var i=0; i<Markers.list.length; i++) {
					str = str.replace(regexp, function(s, marker_name){
						var value = Markers.getMarker(marker_name).getValue();
						return value ? value : '';
					});
				}
				return str;
			}
		};


		this.SystemMonitorQueryStore = new Ext.data.JsonStore({
			url: '/?c=SystemMonitor&m=loadSystemMonitorQueryList',
			root: 'data',
			fields: [
				{name: 'SystemMonitorQuery_id', type:'int'},
				{name: 'SystemMonitorQuery_Name', type: 'string'},
				{name: 'SystemMonitorQuery_Query', type:'string'},
				{name: 'SystemMonitorQuery_RepeatCount', type:'int'},
				{name: 'SystemMonitorQuery_TimeLimit', type:'float'}
			],
			key: 'SystemMonitorQuery_id'
		});

		this.FilterPanel = getBaseFiltersFrame({
			defaults: {
				frame: true,
				collapsed: true
			},
			ownerWindow: this,
			items: [{
				layout: 'column',
				items: [{
					layout: 'form',
					border: false,
					labelWidth: 60,
					items: [{
						fieldLabel: lang['period'],
						name: 'LogQuery_DateRange',
						xtype: 'daterangefield',
						width: 180
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 80,
					items: [{
						fieldLabel: lang['zapros'],
						mode: 'local',
						lastQuery: '',
						hiddenName: 'SystemMonitorQuery_id',
						valueField: 'SystemMonitorQuery_id',
						displayField: 'SystemMonitorQuery_Name',
						store: new Ext.data.JsonStore({
							url: '/?c=SystemMonitor&m=loadSystemMonitorQueryList',
							fields: [
								{name: 'SystemMonitorQuery_id', type:'int'},
								{name: 'SystemMonitorQuery_Name', type: 'string'},
								{name: 'SystemMonitorQuery_RepeatCount', type:'int'},
								{name: 'SystemMonitorQuery_TimeLimit', type:'float'}
							],
							key: 'SystemMonitorQuery_id',
							sortInfo:{
								field: 'SystemMonitorQuery_Name'
							}
						}),
						tpl: '<tpl for="."><div class="x-combo-list-item">{SystemMonitorQuery_Name}</div></tpl>',
						xtype: 'swbaseremotecombosingletrigger',
						width: 240
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 160,
					items: [{
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: lang['vremya_vyipolneniya_ot'],
						name: 'LogQuery_RunTime1',
						xtype: 'numberfield',
						width: 60
					}]
				}, {
					layout: 'form',
					border: false,
					labelWidth: 30,
					items: [{
						allowDecimals: true,
						allowNegative: false,
						fieldLabel: lang['do'],
						name: 'LogQuery_RunTime2',
						xtype: 'numberfield',
						width: 60
					}]
				}, {
					layout: 'form',
					border: false,
					items: [{
						id: 'SMW_DoFilterQueryLogButton',
						style: 'margin-left: 30px',
						xtype: 'button',
						text: lang['ustanovit'],
						handler: function() {
							this.isFilter = true;
							this.doFilter();
						}.createDelegate(this),
						minWidth: 100
					}]
				},  {
					layout: 'form',
					border: false,
					items: [{
						id: 'SMW_CancelFilterQueryLogButton',
						style: 'margin-left: 20px',
						xtype: 'button',
						text: lang['otmenit'],
						handler: function() {
							this.cancelFilter();
						}.createDelegate(this),
						minWidth: 100
					}]
				}]
			}],
			onExpand: function() {
				var height = this.FilterPanel.getSize().height + 75;
				this.TopPanel.setHeight(height);
				this.doLayout();
			}.createDelegate(this),
			onCollapse: function() {
				this.TopPanel.setHeight(100);
				this.doLayout();
			}.createDelegate(this)
		});

		this.ToolsPanel = new sw.Promed.Panel({
			height: 100,
			layout: 'border',
			region: 'center',
			//border: false,
			frame: true,
			items: [{
				layout: 'form',
				region: 'west',
				style: 'padding-top: 5px;',
				width: 500,
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						labelAlign: 'right',
						labelWidth: 140,
						items: [{
							id: 'SystemMonitor_Interval',
							allowNegative: true,
							allowDecimals: false,
							fieldLabel: lang['zapuskat_kajdyie'],
							name: 'SystemMonitor_Interval',
							xtype: 'numberfield',
							width: 45
						}]
					}, {
						layout: 'form',
						border: false,
						style: 'font-size: 12px; padding: 3px 3px 3px 0; margin-left: 5px',
						items: [{
							text: lang['min'],
							xtype: 'label'
						}]
					}]
				}, {
					layout: 'form',
					style: 'padding-left: 120px; padding-top: 5px;',
					items: [{
						id: 'SMW_StartMonitoringButton',
						xtype: 'button',
						text: lang['pusk'],
						handler: function() {
							this.onStartButtonPress();
						}.createDelegate(this),
						minWidth: 100
					}]
				}]
			},{
				style: 'padding-top: 5px;',
				region: 'center'
			}, {
				layout: 'form',
				region: 'east',
				style: 'padding-top: 5px;',
				width: 300,
				items: [{
					layout: 'column',
					border: false,
					items: [{
						layout: 'form',
						border: false,
						items: [{
							id: 'SMW_OpenQueryListWindowButton',
							xtype: 'button',
							text: lang['zaprosyi'],
							style: 'padding-bottom: 10px;',
							handler: function() {
								this.openQueryListWindow();
							}.createDelegate(this),
							minWidth: 100
						}, {
							id: 'SMW_OpenMarkerListWindowButton',
							xtype: 'button',
							text: lang['markeryi'],
							handler: function() {
								this.openMarkerListWindow();
							}.createDelegate(this),
							minWidth: 100
						}]
					}, {
						layout: 'form',
						border: false,
						style: 'padding-left: 80px;',
						items: [{
							id: 'SMW_DownloadQueryLogButton',
							xtype: 'button',
							text: lang['skachat_log'],
							style: 'padding-bottom: 10px;',
							handler: function() {
								this.downloadQueryLog();
							}.createDelegate(this),
							minWidth: 100
						}, {
							id: 'SMW_ClearQueryLogButton',
							xtype: 'button',
							text: lang['ochistit_log'],
							handler: function() {
								this.clearQueryLog();
							}.createDelegate(this),
							minWidth: 100
						}]
					}]
				}]
			}]
		});

		this.TopPanel = new sw.Promed.Panel({
			height: 120,
			layout: 'border',
			region: 'north',
			border: false,
			items: [
				this.FilterPanel,
				this.ToolsPanel
			]
		});

		this.QueryLogGrid = new sw.Promed.ViewFrame({
			id: 'SMW_QueryLogGrid',
			region: 'center',
			dataUrl: '/?c=SystemMonitor&m=loadSystemMonitorQueryLogGrid',
			paging: false,
			autoLoadData: false,
			root: 'data',
			toolbar: false,
			useEmptyRecord: false,
			stringfields:
				[
					{name: 'SystemMonitorQueryLog_id', type: 'int', header: 'ID', key: true},
					{name: 'SystemMonitorQuery_id', type: 'int', hidden: true},
					{name: 'SystemMonitorQuery_TimeLimit', type: 'float', hidden: true},
					{name: 'SystemMonitorQueryLog_isError', type: 'int', hidden: true},
					{name: 'SystemMonitorQueryLog_Num', type: 'int', header: lang['№'], width: 60},
					{name: 'SystemMonitorQueryLog_Date', type: 'date', dateFormat: 'd.m.Y', header: lang['data'], width: 120},
					{name: 'SystemMonitorQueryLog_Time', type: 'time', header: lang['vremya'], width: 100},
					{name: 'SystemMonitorQuery_Name', type: 'string', header: lang['zapros'], id: 'autoexpand'},
					{name: 'SystemMonitorQueryLog_RunCount', type: 'int', header: lang['kolichestvo_zapuskov'], width: 140},
					{name: 'SystemMonitorQueryLog_minRunTime', type: 'float', header: lang['min_sek'], width: 100},
					{name: 'SystemMonitorQueryLog_maxRunTime', type: 'float', header: lang['maks_sek'], width: 100},
					{name: 'SystemMonitorQueryLog_avgRunTime', type: 'float', header: lang['sred_sek'], width: 100}
				]
		});

		this.QueryLogGrid.getGrid().view = new Ext.grid.GridView({
			getRowClass: function (row, index) {
				var cls = '';
				var timeLimit = row.get('SystemMonitorQuery_TimeLimit');

				if ( row.get('SystemMonitorQueryLog_isError') == 2 ) {
					cls = cls + 'x-grid-rowbackred';
				} else if (!Ext.isEmpty(timeLimit) && row.get('SystemMonitorQueryLog_maxRunTime') > timeLimit ) {
					cls = cls + 'x-grid-rowred';
				} else {
					cls = 'x-grid-panel';
				}

				return cls;
			}.createDelegate(this)
		});

		Ext.apply(this, {
			items: [
				this.TopPanel,
				this.QueryLogGrid
			],
			buttons: [
			{
				text: '-'
			},
			HelpButton(this, 1),
			{
				handler: function () {
					this.hide();
				}.createDelegate(this),
				iconCls: 'close16',
				id: 'SMW_CancelButton',
				text: lang['zakryit']
			}]
		});

		sw.Promed.swSystemMonitorWindow.superclass.initComponent.apply(this, arguments);
	}
});
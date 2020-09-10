/**
 * swWorkPlacePolkaRegPrivateWindow - АРМ регистратора частной поликлиники
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    2020, brotherhood of swan developers
 */
Ext6.define('common.PolkaRegPrivateClinic.swWorkPlacePolkaRegPrivateWindow', {
	requires: [
		'common.PolkaRegPrivateClinic.model.EvnQueueRecRequest'
	],
	noCloseOnTaskBar: true,
	extend: 'base.BaseForm',
	alias: 'widget.swWorkPlacePolkaRegPrivateWindow',
	autoShow: false,
	maximized: true,
	width: 1000,
	refId: 'polkawp',
	findWindow: false,
	closable: false,
	frame: false,
	cls: 'arm-window-new PolkaWP',
	title: 'АРМ регистратора частной поликлиники',
	header: false,
	callback: Ext6.emptyFn,
	layout: 'border',
	constrain: true,
	refreshGrid: function () {
		var panel = this;
		panel.refreshInterval = setInterval(function(){
			panel.loadData({grid:panel.incomeRequestsGrid});
			panel.loadData({grid:panel.processedRequestsGrid})
		}.bind(this),15000);
	},
	onRecordSelect: function() {
		var me = this;
	},
	getGrid: function ()
	{
		return this.mainGrid;
	},
	getSelectedRecord: function() {
		if (this.mainGrid.getSelectionModel().hasSelection()) {
			var record = this.mainGrid.getSelectionModel().getSelection()[0];
			if (record && record.get('Person_id')) {
				return record;
			}
		}
		return false;
	},
	stepDay: function(day)
	{
		const me = this;
		const dateFilter = me.getFormCmp('#datefilter');

		let date1 = (dateFilter.getDateFrom() || Date.parseDate(me.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		let date2 = (dateFilter.getDateTo() || dateFilter.getDateFrom() || Date.parseDate(me.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();

		if (date1.toJSONString() === date2.toJSONString()) {
			dateFilter.setDates(date1);
		} else {
			dateFilter.setDates([date1, date2]);
		}
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function () {
		this.stepDay(1);
	},
	initDateFilter: function(params){

		const me = this;
		const dateFilter = me.getFormCmp('#datefilter');

		me.mask(LOAD_WAIT);
		Ext6.Ajax.request({
			url: C_LOAD_CURTIME,
			callback: function(opt, success, response) {
				me.unmask();
				if (success && response.responseText != '') {
					var result  = Ext6.JSON.decode(response.responseText);
					me.curDate = result.begDate;
					me.curTime = result.begTime;

					dateFilter.setDates(Date.parseDate(result.begDate, 'd.m.Y'));

					if (params.callback && typeof params.callback === 'function') {
						params.callback();
					}
				}
			}
		});
	},
	show: function() {

		this.callParent(arguments);
		const me = this;

		sw.Promed.MedStaffFactByUser.setMenuTitle(me, arguments[0]);
		me.setTitle('Журнал заявок');

		// грузим текущую дату
		me.initDateFilter({
			callback: function(){
				me.loadData({grid: me.incomeRequestsGrid});
				me.loadData({grid: me.processedRequestsGrid});
			}
		});

		me.refreshGrid();

		me.disableCmp([
			'#action_process-request',
			'#action_decline-request',
			'#action_edit-request',
		]);

	},
	disableCmp: function(selectors){
		const me = this;
		selectors.forEach(function(cmp){
			me.getFormCmp(cmp).disable();
		})
	},
	loadData: function(params){

		const me = this;
		const dateMenu = me.getFormCmp('#datefilter');

		let loadParams = {
			date: dateMenu.getDateFrom().format('d.m.Y')
		};

		if (params.grid) {
			params.grid.getStore().load({
				params: loadParams
			});
		}
	},
	setCounter: function (params) {
		if (!params) return false;

		if (params.titleBar && params.count !== undefined) {
			var label = Ext6.ComponentQuery.query('label', params.titleBar);
			if (label[0]) {
				label[0].setHtml(params.titleBar.storedHeaderTitle + '<span class=\'titleCount\'> '+params.count+'</span>');
			}
		}
	},
	addQTip: function(row, record) {
		var tooltip = '';
		tooltip += '<b>ФИО Пациента:</b> ' + record.get('Person_FullName');
		tooltip += '<br><b>Дата рождения:</b> ' + record.get('Person_BirthDay');
		tooltip += '<br><b>Телефон:</b> ' + record.get('Person_Phone');
		tooltip += '<br><b>E-mail:</b> ' + record.get('Person_Email');
		if(record.data.EvnDirection_Descr != "") {
			tooltip += '<br><b>Комментарий:</b> ' + record.get('EvnDirection_Descr');
		}
		if(record.data.EvnStatus_id == 12 || record.data.EvnStatus_id == 13) {
			tooltip += '<br><b>Отклонение приема:</b> ' + record.get('EvnStatusCause_Name');
		}
		row.tdAttr = 'data-qtip="' + tooltip + '"';
	},
	minutesConverter: function(minutes){

		let time = {
			days: {
				value: 0,
				title: ''
			},
			hours: {
				value: 0,
				title: ''
			},
			minutes: {
				value: 0,
				title: ''
			}
		};

		if (minutes > 60) {
			time.minutes.value = minutes % 60;
			time.minutes.title = this.strEnd(time.minutes.value, 'minute');

			time.hours.value = Math.floor(minutes / 60);

			if (time.hours.value > 24) {
				time.days.value = Math.floor(time.hours.value / 24);
				time.days.title = this.strEnd(time.days.value, 'day');

				time.hours.value = time.hours.value %24
			}

			time.hours.title = this.strEnd(time.hours.value, 'hour');

		} else {
			time.minutes.value = minutes;
			time.minutes.title = this.strEnd(time.minutes.value, 'minute');
		}

		return time;
	},
	getStringEnding: function(type, objectName){
		const metadata = {
			age: {
				singular: 'год',
				singular_genetive: 'года',
				plural: 'лет'
			},
			day: {
				singular: 'день',
				singular_genetive: 'дня',
				plural: 'дней'
			},
			hour: {
				singular: 'час',
				singular_genetive: 'часа',
				plural: 'часов'
			},
			minute: {
				singular: 'минута',
				singular_genetive: 'минуты',
				plural: 'минут'
			}
		};

		if (metadata[objectName] && metadata[objectName][type]) {
			return metadata[objectName][type];
		} else {
			return '';
		}
	},
	strEnd: function(inputValue, objectName){

		let digits = 1;
		inputValue = parseInt(inputValue);

		// 1245 => round(log(1235)+ 1) = 4
		if (inputValue > 0) {
			// считаем количество символов в числе
			digits = Math.round(Math.log10(inputValue) + 1);
		}

		//var ageValue = age;
		let value = inputValue;

		// вычисляем остаточное количество разрядов базового числа
		// если есть доп. разряды значит число большое
		// и его нужно уменьшить до минимального
		// 1245 => (round(1245/10^2))*10^2 = 1200
		if ((digits - 2) > 0) {
			//var ageBase = (Math.round(age/(10*10)))*(10*10);
			let base = (Math.round(inputValue/(10*10)))*(10*10);
			// 1245 - 1200 = 45;
			value = inputValue-base;
		}

		if (value > 20) {
			value = value%10;
		}

		let stringWithEnding = '';

		if (value === 1) {
			// единственное число
			stringWithEnding = this.getStringEnding('singular', objectName);
		} else if (value > 1 && value < 5) {
			// единственное число, родительный падеж
			stringWithEnding = this.getStringEnding('singular_genetive', objectName);
		} else {
			// множественное число
			stringWithEnding = this.getStringEnding('plural', objectName);
		}

		return stringWithEnding;
	},
	declineRequest: function(params){

		const me = this;
		let record;

		if (!params.record && params.grid) {
			record = params.grid.getSelectionModel().getSelectedRecord();
		} else {
			record = params.record;
		}

		if (!record) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Заявка не выбрана'));
		}

		if (record && record.get('EvnDirection_id')) {

			getWnd('swSelectEvnStatusCauseWindow').show({
				EvnClass_id: 27,
				formType: 'regprivate',
				winTitle: 'Выбор причины отмены заявки',
				btnAcceptText: 'Отклонить',
				callback: function(cause) {

					me.getLoadMask('Отклонение заявки').show();
					Ext6.Ajax.request({
						params:{
							EvnDirection_id: record.get('EvnDirection_id'),
							EvnStatusCause_id: cause.EvnStatusCause_id,
							EvnStatusHistory_Cause: cause.EvnStatusHistory_Cause
						},
						url: '/?c=RegPrivate&m=declineRequest',
						success: function (response, opts) {

							me.getLoadMask().hide();
							me.loadData({grid: me.processedRequestsGrid});

							var resp = Ext.decode(response.responseText);
							log('successResponse', response);
							if (resp.success) {
								Ext6.Msg.alert(langs('Успех'), langs('Заявка успешно отклонена'));
							} else {
								var err = resp.Error_Msg ?  ': '+resp.Error_Msg : '';
								Ext6.Msg.alert(langs('Ошибка'), langs('При отклонении заявки произошла ошибка' + err));
							}

						},
						failure: function (response, opts) {
							me.getLoadMask().hide();
							log('failureResponse', response);
							Ext6.Msg.alert(langs('Ошибка'), langs('При отклонении заявки произошла ошибка'));
						}
					});
				}
			});
		}
	},
	showRegPrivateRequestEditWindow: function(params){

		const me = this;
		const curdt = Date.parseDate(me.curDate, 'd.m.Y').format('d.m.Y');
		let record;

		if (!params.record && params.grid) {
			record = params.grid.getSelectionModel().getSelectedRecord();
		} else {
			record = params.record;
		}

		if (!record) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Заявка не выбрана'));
		}

		let action = params.action;

		if (action !== 'process' && (record.get('EvnStatus_id') === 13
			|| record.get('EvnStatus_id') === 12
			|| curdt !== record.get('TimetableGraf_begTime_date'))
		) {
			action = 'view';
		}

		if (action === 'process'
			&& record.get('EvnStatus_id') === 51
			&& record.get('EvnStatus_pmUser_insID')
			&& getGlobalOptions().pmuser_id != record.get('EvnStatus_pmUser_insID')
		) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Заявка уже в обработке'));
			return false;
		}

		getWnd('swPolkaRegPrivateRequestEditWindow').show({
			action: action,
			request: record,
			onSubmitSuccess: function(){
				me.loadData({grid: me.incomeRequestsGrid});
				me.loadData({grid: me.processedRequestsGrid});
			},
			onSetRequestStatus: function(params) {
				if (action === 'process') {
					me.loadData({grid: me.incomeRequestsGrid});

					if (params && params.EvnStatus_SysNick && params.EvnStatus_SysNick === 'Queued') {
						me.incomeRequestsGrid.getView().focusRow(0);
					}
				}
			}
		});
	},
	getFormCmp: function(selector, scope){
		var cmp = Ext6.ComponentQuery.query(selector, scope);
		if (cmp[0]) cmp = cmp[0];
		return cmp;
	},
	setPatientVisitApprove: function(params){

		const me = this;
		let record;

		if (!params.record && params.grid) {
			record = params.grid.getSelectionModel().getSelectedRecord();
		} else {
			record = params.record;
		}

		if (!record) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Заявка не выбрана'));
		}

		Ext6.Ajax.request({
			params:{
				EvnQueue_id: record.get('EvnQueue_id'),
				isApprove: params.isApprove
			},
			url: '/?c=RegPrivate&m=setVisitApproveStatus',
			success: function (response, opts) {

				me.getLoadMask().hide();
				me.loadData({grid: me.processedRequestsGrid});

				const resp = Ext.decode(response.responseText);
				log('successResponse', response);
				if (resp.success) {
					//Ext6.Msg.alert(langs('Успех'), langs('Статус доходимости успешно изменен'));
				} else {
					let err = resp.Error_Msg ?  ': '+resp.Error_Msg : '';
					Ext6.Msg.alert(langs('Ошибка'), langs('При изменении статуса доходимости заявки произошла ошибка' + err));
				}

			},
			failure: function (response, opts) {
				me.getLoadMask().hide();
				log('failureResponse', response);
				Ext6.Msg.alert(langs('Ошибка'), langs('При изменении статуса доходимости заявки произошла ошибка'));
			}
		});
	},
	initComponent: function() {

		var me = this;

		me.selectedLabel = Ext6.create('Ext6.form.Label', {
			xtype: 'label',
			cls: 'person-double-text-select',
			text: ''
		});

		me.incomeRequestsTitleBar = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #f6f6f6;',
			storedHeaderTitle: 'Новые',
			items: [
				{
					region: 'center',
					border: false,
					bodyStyle: 'background-color: #f6f6f6;',
					height: 40,
					bodyPadding: 10,
					items: [
						Ext6.create('Ext6.form.Label', {
							xtype: 'label',
							cls: 'no-wrap-ellipsis',
							style: 'font-size: 16px;',
							html: this.storedHeaderTitle
						})
					]
				},
				Ext6.create('Ext6.Toolbar', {
					region: 'east',
					height: 40,
					border: false,
					noWrap: true,
					right: 0,
					cls: 'grid-toolbar',
					items: [
						{
							text: langs('Обновить'),
							xtype: 'button',
							itemId: 'action_refresh',
							iconCls: 'action_refresh',
							handler: function() {
								me.loadData({grid:me.incomeRequestsGrid})
							}
						},
						{
							text: langs('Обработать'),
							itemId: 'action_process-request',
							xtype: 'button',
							cls: 'toolbar-padding',
							iconCls: 'private-clinic-process-request',
							handler: function() {
								me.showRegPrivateRequestEditWindow({
									action: 'process',
									grid: me.incomeRequestsGrid
								});
							}
						}
					]
				})],
			xtype: 'panel'
		});

		me.incomeRequestsGrid = Ext6.create('Ext6.grid.Panel', {
			flex: 600,
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			plugins: {},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					let cls = '';
					if (record.get('EvnStatus_id') === 51) {
						cls += 'x-grid-rowgray ';
					}
					return cls;
				}
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						var processBtn = me.getFormCmp('#action_process-request');

						if (record.get('EvnStatus_id') == 10) {
							processBtn.enable();
						} else {
							processBtn.disable();
						}
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.showRegPrivateRequestEditWindow({
						action: 'process',
						grid: me.incomeRequestsGrid
					});
				}
			},
			store: {
				fields: [
					{name: 'Person_id', type: 'int'},
					{name: 'EvnQueue_insDT', type: 'string'},
					{name: 'EvnStatus_id', type: 'int'},
					{name: 'EvnStatus_Name', type: 'string'},
					{name: 'RequestStatus_Name', type: 'string'},
					{name: 'time_diff', type: 'int'},
				],
				pageSize: 100,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=RegPrivate&m=loadIncomeRequests',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [],
				listeners: {
					load: function(grid, records) {
						me.setCounter({
							titleBar: me.incomeRequestsTitleBar,
							count: records.length
						});

						if (!records.length) {
							me.disableCmp(['#action_process-request']);
						}
					}
				}
			},
			columns: [
				{text: 'Дата заявки', dataIndex: 'EvnQueue_insDT', width: '49%'},
				{
					xtype:'actioncolumn',
					width: 40,
					renderer: function (value, metaData) {
						metaData.tdCls = 'regpol-tools-panel';
						return value;
					},
					items: [{
						getClass: function(v, metadata, r){

							let cls = 'regpol-tools disable-hover regpol-income-warning';
							if (r.get('time_diff') < 11) {
								cls += ' hidden';
							} else {
								cls += ' visibleAlways';
							}

							return cls;
						},
						handler: function(view, rI, cI, item, e, r) {
							return false;
						},
						getTip: function(v, metadata, r){

							log('time_diff', r.get('time_diff'));

							let text = 'С момента получения заявки прошло';
							if (r.get('time_diff') > 10) {
								const minutes = parseInt(r.get('time_diff'));
								const time = me.minutesConverter(minutes);

								text += ' '
									+ (time.days.value ? time.days.value + ' ' + time.days.title + ' ' : '')
									+ (time.hours.value ? time.hours.value + ' ' + time.hours.title + ' ' : '')
									+ (time.minutes.value ? time.minutes.value + ' ' + time.minutes.title : '')
								;
							}

							return text;
						}
					}]
				},
				{text: 'Статус заявки', dataIndex: 'RequestStatus_Name', width: '40%'}
			]
		});

		me.processedRequestsTitleBar = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #f6f6f6;',
			storedHeaderTitle: 'Обработанные',
			items: [
				{
					region: 'center',
					border: false,
					bodyStyle: 'background-color: #f6f6f6;',
					height: 40,
					bodyPadding: 10,
					items: [
						Ext6.create('Ext6.form.Label', {
							xtype: 'label',
							cls: 'no-wrap-ellipsis',
							style: 'font-size: 16px;',
							html: this.storedHeaderTitle
						})
					]
				},
				Ext6.create('Ext6.Toolbar', {
					region: 'east',
					height: 40,
					border: false,
					noWrap: true,
					right: 0,
					cls: 'grid-toolbar',
					items: [
						{
							text: langs('Обновить'),
							xtype: 'button',
							itemId: 'action_refresh',
							iconCls: 'action_refresh',
							handler: function() {
								me.loadData({grid:me.processedRequestsGrid})
							}
						},
						{
							text: langs('Редактировать'),
							hidden: true,
							itemId: 'action_edit-request',
							xtype: 'button',
							cls: 'toolbar-padding',
							iconCls: 'private-clinic-edit-request',
							handler: function() {
								me.showRegPrivateRequestEditWindow({
									action: 'edit',
									grid: me.processedRequestsGrid
								});
							}
						},
						{
							text: langs('Отклонить'),
							itemId: 'action_decline-request',
							xtype: 'button',
							cls: 'toolbar-padding',
							hidden: true,
							iconCls: 'private-clinic-decline-request',
							handler: function() {
								me.declineRequest({
									grid: me.processedRequestsGrid
								});
							}
						},
						{
							xtype: 'button',
							cls: 'bgTrans',
							border: false,
							iconCls: 'arrow-previous16-2017',
							handler: function()
							{
								me.prevDay();
								me.loadData({grid:me.processedRequestsGrid})
							}
						},
						Ext6.create('Ext6.date.RangeField', {
							hideLabel: true,
							autoWidth: true,
							itemId: 'datefilter',
							margin: 0,
							width: 210
						})
						, {
							xtype: 'button',
							cls: 'bgTrans',
							border: false,
							iconCls: 'arrow-next16-2017',
							handler: function()
							{
								me.nextDay();
								me.loadData({grid:me.processedRequestsGrid})
							}
						}
					]
				})],
			xtype: 'panel'
		});

		me.rowToolsConfig = {
			xtype: 'toolbar',
			items:[
				{
					iconCls: 'lpupassport16-2017',
					handler: function() {
						getWnd('swLpuPassportEditWindow').show({
							action: 'edit',
							Lpu_id: getGlobalOptions().lpu_id
						});
					},
					text: 'Паспорт МО'
				}
			]
		};

		me.processedRequestsGrid = Ext6.create('Ext6.grid.Panel', {
			flex: 600,
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false,
				})
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						const declineBtn = me.getFormCmp('#action_decline-request');
						const editBtn = me.getFormCmp('#action_edit-request');

						const curdt = Date.parseDate(me.curDate, 'd.m.Y').format('d.m.Y');

						if (record.get('EvnStatus_id') === 13
							|| record.get('EvnStatus_id') === 12
							|| curdt !== record.get('TimetableGraf_begTime_date')
						) {
							declineBtn.disable();
						} else {
							declineBtn.enable();
						}

						if (editBtn.isDisabled()) {
							editBtn.enable();
						}
					}
				}
			},
			viewConfig: {
				getRowClass: function(record, rowIndex, rowParams, store) {
					var cls = '';
					return cls;
				}
			},
			listeners: {
				itemdblclick: function() {
					me.showRegPrivateRequestEditWindow({
						action: 'edit',
						grid: me.processedRequestsGrid
					});
				},
			},
			store: {
				model: 'common.PolkaRegPrivateClinic.model.EvnQueueRecRequest',
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=RegPrivate&m=loadProcessedRequests',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [],
				listeners: {
					load: function(store, records) {
						me.setCounter({
							titleBar: me.processedRequestsTitleBar,
							count: records.length
						});

						if (!records.length) {
							me.disableCmp([
								'#action_edit-request',
								'#action_decline-request'
							])
						}
					}
				}
			},
			columns: [
				{text: 'Дата приема111', dataIndex: 'TimetableGraf_begTime', width: '20%', renderer: function(val,p, rec){
						me.addQTip(p, rec);
						return val;
					}},
				{text: 'Статус заявки', dataIndex: 'RequestStatus_Name', width: '15%', renderer: function(val, p, rec){

						var renderedValue = val;

						if (rec.get('EvnStatus_id')) {
							if (rec.get('EvnStatus_id') == 17 || rec.get('EvnStatus_id') == 15) {
								renderedValue = '<span class=\'regpol-private-clinic-icon regpol-processed-accepted\'></span>' + '<span class="grid-row-spacer"></span>' + renderedValue;
							}

							if (rec.get('EvnStatus_id') == 12) {
								renderedValue = '<span class=\'regpol-private-clinic-icon regpol-processed-canceled-client\'></span>'+ '<span class="grid-row-spacer"></span>' + renderedValue;
							}

							if (rec.get('EvnStatus_id') == 13 && rec.get('EvnStatusCause_id') != 18) {
								renderedValue = '<span class=\'regpol-private-clinic-icon regpol-processed-canceled-clinic\'></span>'+ '<span class="grid-row-spacer"></span>' + renderedValue;
							}
						}

						me.addQTip(p, rec);
						return renderedValue;
					}},
				{
					text: '',  width: '25%', dataIndex: 'Person_FullName_Short', flex: 1,
					filter: {
						type: 'string',
						xtype: 'textfield',
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								handler: function () {
									// ?
								}
							}
						},
						anchor: '-30',
						emptyText: 'ФИО пациента',
						enableInstantSearch: true
					},
					renderer: function(val, p, rec){

						var renderedValue = rec.get('Person_FullName_Short');
						if (rec.get('Person_Age'))  {
							var ageTitle = me.strEnd(rec.get('Person_Age'), 'age');
							renderedValue += ' <span style="color: #888888">' + rec.get('Person_Age') + ' ' +ageTitle + '</span>';
						}

						me.addQTip(p, rec);
						return renderedValue;
					}
				},
				{text: '', dataIndex: 'MedPersonal_Name_Short', width: '25%',
					filter: {
						type: 'string',
						xtype: 'textfield',
						triggers: {
							search: {
								cls: 'x6-form-search-trigger',
								handler: function () {
									// ?
								}
							}
						},
						anchor: '-30',
						emptyText: 'ФИО врача',
						enableInstantSearch: true
					},
					renderer: function(val, p, rec){

						var renderedValue = rec.get('MedPersonal_Name_Short');
						if (rec.get('ProfileSpec_Name')) renderedValue += ' <span style="color: #888888; text-transform:capitalize">' + rec.get('ProfileSpec_Name')+'</span>';

						me.addQTip(p, rec);
						return renderedValue;
					}
				},
				{
					xtype:'actioncolumn',
					width: '14%',
					renderer: function (value, metaData, record, rowIndex, colIndex, store) {
						metaData.tdCls = 'regpol-tools-panel';
						return value;
					},
					items: [{
						getClass: function(v, metadata, r){

							let cls = 'regpol-tools regpol-tools-patient_approve';
							if (r.get('EvnStatus_id') === 13 || r.get('EvnStatus_id') === 12) {
								cls += ' hidden';
							}

							if (r.get('EvnStatus_id') === 15) {
								cls += ' visibleAlways';
							}

							if (r.get('EvnStatus_id') === 17) {
								cls += ' visibleOnHover';
							}

							return cls;
						},
						handler: function(view, rI, cI, item, e, r) {
							me.setPatientVisitApprove(
								{
									record: r,
									isApprove: 1
								}
							);
						},
						getTip: function(){
							return 'Пациент пришел';
						}
					},{
						getClass: function(v, metadata, r){

							let cls = 'regpol-tools regpol-tools-patient_unapprove';

							if (r.get('QueueFailCause_id') === 12 && r.get('EvnStatus_id') === 17) {
								cls += ' visibleAlways';
							} else if (r.get('EvnStatus_id') === 15 || r.get('EvnStatus_id') === 17) {
								cls += ' visibleOnHover';
							}

							if (r.get('EvnStatus_id') === 13 || r.get('EvnStatus_id') === 12) {
								cls += ' hidden';
							}

							return cls;
						},
						handler: function(view, rI, cI, item, e, r) {
							me.setPatientVisitApprove(
								{
									record: r,
									isApprove: 0
								}
							);
						},
						getTip: function() {
							return 'Пациент не пришел'
						}
					},
					{
						getClass: function(v, metadata, r){
							let cls = 'regpol-tools regpol-tools-edit visibleOnHover';
							return cls;
						},
						handler: function(view, rI, cI, item, e, r) {
							me.showRegPrivateRequestEditWindow({
								action: 'edit',
								grid: me.processedRequestsGrid,
								record: r
							});
						},
						getTip: function() {
							return 'Редактировать'
						}
					},
					{
						getClass: function(v, metadata, r){

							const curdt = Date.parseDate(me.curDate, 'd.m.Y').format('d.m.Y');
							let cls = 'regpol-tools regpol-tools-decline visibleOnHover';

							if (r.get('EvnStatus_id') === 13
								|| r.get('EvnStatus_id') === 12
								|| curdt !== r.get('TimetableGraf_begTime_date')
							) {
								cls += ' disabled';
							}

							if(r.get('TimeHasPassed') >=1) {
								cls += ' disabled';
							}

							return cls;
						},
						handler: function(view, rI, cI, item, e, r) {

							const curdt = Date.parseDate(me.curDate, 'd.m.Y').format('d.m.Y');

							if (r.get('EvnStatus_id') === 13
								|| r.get('EvnStatus_id') === 12
								|| curdt !== r.get('TimetableGraf_begTime_date')
							) {
								return false;
							}

							me.declineRequest({
								grid: me.processedRequestsGrid,
								record: r
							});
						},
						getTip: function(){
							return 'Отклонить'
						}
					}
					]
				}
			]
		});

		me.leftMenu = new Ext6.menu.Menu({
			xtype: 'menu',
			floating: false,
			dock: 'left',
			cls: 'leftPanelWP',
			border: false,
			padding: 0,
			defaults: {
				margin: 0
			},
			mouseLeaveDelay: 100,
			collapsedWidth: 30,
			collapseMenu: function() {
				if (!me.leftMenu.activeChild || me.leftMenu.activeChild.hidden) {
					clearInterval(me.leftMenu.collapseInterval); // сбрасывем
					me.leftMenu.getEl().setWidth(me.leftMenu.collapsedWidth); // сужаем
					me.leftMenu.body.setWidth(me.leftMenu.collapsedWidth - 1); // сужаем
					me.leftMenu.deactivateActiveItem();
				}
			},
			listeners: {
				mouseover: function() {
					clearInterval(me.leftMenu.collapseInterval); // сбрасывем
					me.leftMenu.getEl().setWidth(me.leftMenu.items.items[0].getWidth());
					me.leftMenu.body.setWidth(me.leftMenu.items.items[0].getWidth() - 1);
				},
				afterrender : function(scope) {
					me.leftMenu.setWidth(me.leftMenu.collapsedWidth); // сразу сужаем
					me.leftMenu.setZIndex(10); // fix zIndex чтобы панель не уезжала под грид

					this.el.on('mouseout', function() {
						// сужаем, если нет подменю
						clearInterval(me.leftMenu.collapseInterval); // сбрасывем
						me.leftMenu.collapseInterval = setInterval(me.leftMenu.collapseMenu, 100);
					});
				}
			},
			items: [{
				iconCls: 'lpupassport16-2017',
				handler: function() {
					getWnd('swLpuPassportEditWindow').show({
						action: 'edit',
						Lpu_id: getGlobalOptions().lpu_id
					});
				},
				text: 'Паспорт МО'
			},{
				iconCls: 'structure16-2017',
				handler: function() {
					getWnd('swLpuStructureViewForm').show();
				},
				text: 'Структура'
			},{
				iconCls: 'timetable16-2017',
				handler: function() {
					getWnd('swTimetableScheduleViewWindow').show({
						userMedStaffFact: me.userMedStaffFact,
						ARMType: me.ARMType
					});
				},
				text: 'Расписание'
			},{
				iconCls: 'spr16-2017',
				menu: [{
					text: langs('Справочник услуг'),
					handler: function() {
						getWnd('swUslugaTreeWindow').show({action: 'view'});
					}
				},{
					text: langs('Справочник МКБ-10'),
					handler: function() {
						if ( !getWnd('swMkb10SearchWindow').isVisible() )
							getWnd('swMkb10SearchWindow').show();
					}
				}, {
					name: 'action_DrugNomenSpr',
					text: langs('Номенклатурный справочник'),
					handler: function()
					{
						getWnd('swDrugNomenSprWindow').show();
					}
				}, {
					name: 'action_PriceJNVLP',
					hidden: getRegionNick().inlist(['by']),
					text: langs('Цены на ЖНВЛП'),
					handler: function() {
						getWnd('swJNVLPPriceViewWindow').show();
					}
				}, {
					name: 'action_DrugMarkup',
					hidden: getRegionNick().inlist(['by']),
					text: langs('Предельные надбавки на ЖНВЛП'),
					handler: function() {
						getWnd('swDrugMarkupViewWindow').show({readOnly: true});
					}
				}, {
					text: langs('Справочник фальсификатов и забракованных серий ЛС'),
					handler: function()
					{
						getWnd('swPrepBlockViewWindow').show();
					}
				}, {
					text: 'Справочники системы учета медикаментов',
					handler: function()
					{
						getWnd('swDrugDocumentSprWindow').show();
					}
				}],
				text: 'Справочники'
			},
				{
					iconCls: 'structure16-2017',
					handler: function() {
						return false;
					},
					text: 'Журнал уведомлений'
				},
				{
					iconCls: 'structure16-2017',
					handler: function() {
						return false;
					},
					text: 'Управление рассылками'
				}
			]
		});

		this.personLabel = new Ext6.form.Label({
			style: 'padding-left: 20px; color: #FFFFFF;',
			html: ''
		});

		me.mainPanel = new Ext6.Panel({
			animCollapse: false,
			floatable: false,
			collapsible: false,
			flex: 100,
			region: 'center',
			layout: 'border',
			activeItem: 0,
			border: false,
			items: [
				me.filterPanel, {
					dockedItems: [me.leftMenu],
					border: false,
					region: 'center',
					layout: 'border',
					items: [
						new Ext6.Panel({
							region: 'center',
							layout: 'border',
							items:[
								me.incomeRequestsTitleBar,
								me.incomeRequestsGrid
							]
						}),
						new Ext6.Panel({
							region: 'east',
							width: '70%',
							split: true,
							layout: 'border',
							items:[
								me.processedRequestsTitleBar,
								me.processedRequestsGrid
							]
						})
					]
				}
			]
		});

		Ext6.apply(me, {
			items: [
				me.mainPanel
			]
		});

		this.callParent(arguments);
	}
});
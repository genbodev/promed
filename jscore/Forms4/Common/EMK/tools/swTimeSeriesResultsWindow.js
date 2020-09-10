/**
 * swSpecificationDetailWnd - Окно детализации назначений
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @author       gtp_fox
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.EMK.tools.swTimeSeriesResultsWindow', {
	requires: [
		'common.EMK.PersonInfoPanel',
		'common.EMK.models.EvnPrescribePanelModel'
	],
	extend: 'base.BaseForm',
	maximized: true,
	//itemId: 'common',
	callback: Ext6.emptyFn,
	historyGroupMode: null,
	//объект с параметрами рабочего места, с которыми была открыта форма АРМа
	userMedStaffFact: null,
	addedCharts: [],
	/* свойства */
	alias: 'widget.swTimeSeriesResultsWindow',
	autoShow: false,
	closable: true,
	cls: 'arm-window-new evnPrescribePanel',
	constrain: true,
	autoHeight: true,
	findWindow: false,
	header: false,
	modal: false,
	layout: 'border',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	width: 1000,
	title: 'Динамика результатов тестов',
	storeChartConfig: {
		//type: 'gdp',
		fields: [{
			name: 'Evn_setDT',
			type: 'date',
			dateFormat: 'Y-m-d'
		}, {
			name: 'UslugaTest_ResultValue',
			type: 'float'
		}, {
			name: 'UslugaTest_ResultLower',
			type: 'float'
		}, {
			name: 'UslugaTest_ResultUpper',
			type: 'float'
		}, {
			name: 'textValue',
			type: 'boolean'
		}],
		autoLoad: false,
		proxy: {
			/*extraParams: {
				Person_id: 5526859,
				UslugaComplex_id: 223929
			},*/
			type: 'ajax',
			actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
			url: '/?c=EvnUslugaPar&m=loadEvnUslugaParResults',
			reader: {
				type: 'json',
				rootProperty: 'data'
			}
		}
	},
	chartDefaultConfig: {
		border: false,
		hideHeader: true,
		minHeight: 200,
		maxHeight: 400,
		plugins: {
			ptype: 'chartitemevents',
			moveEvents: true
		},
		interactions: [{
			type: 'panzoom',
			zoomOnPan: true
		}],
		width: '100%',
		//insetPadding: 40,
		collapsible: true,
		innerPadding: {
			left: 20,
			right: 20,
			//top: 20
		},
		minWidth: 450,
		height: 360,
		animate: true,
		axes: [{
			name: 'y',
			hidden: true,
			type: 'numeric',
			fields: ['UslugaTest_ResultValue', 'UslugaTest_ResultLower', 'UslugaTest_ResultUpper'],
			position: 'left',
			/*renderer: function(v, layoutContext) {
				return '$' + layoutContext.renderer(v);
			}*/
		}, {
			name: 'x',
			type: 'time',
			fields: 'Evn_setDT',
			grid: {
				lineDash: [2, 5]
			},
			position: 'top',
			dateFormat: 'd.m',
			style: {
				axisLine: false
			},
			segmenter: {
				type: 'time',
				step: {
					unit: 'd',
					step: 2
				}
			}
		}],
		series: [
			{
				type: 'area',
				xField: 'Evn_setDT',
				yField: ['UslugaTest_ResultUpper'],
				style: {
					opacity: 0.2
				},
				highlightCfg: {
					opacity: 1,
					scaling: 1.5
				},
				colors: ['#2196f3']
			}, {
				type: 'area',
				xField: 'Evn_setDT',
				yField: ['UslugaTest_ResultLower'],
				style: {
					opacity: 1
				},
				highlightCfg: {
					opacity: 1,
					scaling: 1.5
				},
				colors: ['white']
			}, {
				type: 'line',
				curve: {
					type: 'cardinal',
					tension: 0.02
				},
				xField: 'Evn_setDT',
				yField: 'UslugaTest_ResultValue',
				style: {
					lineWidth: 2
				},
				marker: {
					radius: 2
				},
				highlight: {
					fillStyle: '#2196f3',
					radius: 4,
					lineWidth: 2,
					strokeStyle: '#fff'
				},
				colors: ['#2196f3'],
				tooltip: {
					trackMouse: true,
					style: 'background: #fff',
					showDelay: 0,
					dismissDelay: 0,
					hideDelay: 0,
					renderer: function (tooltip, rec, item) {
						var max = rec.get('UslugaTest_ResultUpper'),
							min = rec.get('UslugaTest_ResultLower'),
							val = rec.get('UslugaTest_ResultValueText');
						if (min && max && (val < min || val > max))
							tooltip.addCls('over-limit');
						else tooltip.removeCls('over-limit');
						tooltip.setHtml(val);
					}
				}
			}
		]
	},
	/* методы */
	show: function(data) {

		this.callParent(arguments);
		var win = this;
			//cntr = win.getController();

		if (!arguments || !arguments[0]) {
			this.hide();
			Ext6.Msg.alert('Ошибка открытия формы', 'Ошибка открытия формы "'+this.title+'".<br/>Отсутствуют необходимые параметры.');
			return false;
		}
		win.action = (typeof data.action == 'string' ? data.action : 'add');
		win.callback = (typeof data.callback == 'function' ? data.callback : Ext6.emptyFn);
		//log(arguments);

		this.Person_id = arguments[0].Person_id;
		this.PersonEvn_id = arguments[0].PersonEvn_id;
		this.Server_id = arguments[0].Server_id;
		this.userMedStaffFact = arguments[0].userMedStaffFact;
		this.openEvn = arguments[0].openEvn || null;
		this.evnPrescrCntr = arguments[0].evnPrescrCntr || null;



		this.PersonInfoPanel.load({
			noToolbar: true,
			Person_id: this.Person_id,
			Server_id: this.Server_id,
			userMedStaffFact: this.userMedStaffFact,
			PersonEvn_id: this.PersonEvn_id,
			callback: function () {
				// эх
			}
		});

		var conf = {
			userMedStaffFact: arguments[0].userMedStaffFact,
			Person_id: arguments[0].Person_id,
			PersonEvn_id: arguments[0].PersonEvn_id,
			Server_id: arguments[0].Server_id,
			EvnVizitPL_id: arguments[0].EvnVizitPL_id,
			EvnVizitPL_setDate: arguments[0].EvnVizitPL_setDate,
			LpuSection_id: arguments[0].LpuSection_id,
			MedPersonal_id: arguments[0].MedPersonal_id,
			Diag_id: data.Diag_id,
			callback: function() {
				//cntr.loadGrids();
			}
		};
		win.center();
		win.setTitle('Динамика результатов тестов');
		win.clearCharts();
		var dateTo =  Date.parseDate(getGlobalOptions().date, 'd.m.Y');
		var dateFrom = dateTo.add(Date.MONTH, -1);
		win.dateMenu.setDates([dateFrom, dateTo]);
		win.checkForComplexUslugaList(arguments[0].UslugaComplex_id,true);
	},
	loadDefaultChart: function(UslugaComplex_id){
		var me = this,
			params = {
			Person_id: me.Person_id,
			UslugaComplex_id: UslugaComplex_id
		};
		this.chartStore.load({params: params});
	},
	clearCharts: function(){
		var arrCharts = this.addedCharts;
		arrCharts.forEach(function(chart){
			chart.destroy();
		});
	},
	setChartLimits: function (records, chart) {
		var me = this;
		if(records.length>0){
			if(!chart)
				chart = me.chartDefault;
			var rec = records[0],
				title = rec.get('UslugaComplex_Code')+' '+rec.get('UslugaComplex_Name');
			if (rec) {
				if (rec.get('UslugaTest_ResultLower') && rec.get('UslugaTest_ResultUpper'))
					title += '<span style="color: #ccc; font-size: 12px;"> норма ' + rec.get('UslugaTest_ResultLower') + ' - ' + rec.get('UslugaTest_ResultUpper') + '</span>';
				chart.setTitle(title);
				var series = chart.getSeries();

				var limitU = series[0],  // Верхняя граница допустимых значений
					limitL = series[1], // Нижняя граница допустимых значений
					line = series[2]; // line - линия значения тестов на графике

				if (rec.get('textValue'))
					line.setCurve({type: 'step-after'});
				else
					line.setCurve({
						type: 'cardinal',
						tension: 0.02
					});
				// Флаг снятия границ допустимых значений
				var withoutLimits = !rec.get('UslugaTest_ResultLower') || !rec.get('UslugaTest_ResultUpper') || rec.get('textValue');
				limitU.setHidden(withoutLimits);
				limitL.setHidden(withoutLimits)
			}
			var maxminFn = function(records, param, isMax){
				var val = false;
				if(isMax)
					val = Math.max.apply(Math, records.map(function(r) { return r.get(param); }));
				else val = Math.min.apply(Math, records.map(function(r) { return r.get(param); }));
				return val;
			};
			//Получаем максимальные значения по осям
			var minV = maxminFn(records,'UslugaTest_ResultValue'),
				maxV = maxminFn(records,'UslugaTest_ResultValue',true),
				minL = maxminFn(records,'UslugaTest_ResultLower'),
				maxU = maxminFn(records,'UslugaTest_ResultUpper',true),
				numAxis = chart.getAxis(0),
				dayAxis = chart.getAxis(1),
				dateTo = new Date(maxminFn(records,'Evn_setDT',true)).add(Date.HOUR, +1),
				dateFrom = new Date(maxminFn(records,'Evn_setDT')).add(Date.HOUR, -1);
			//Добавляем промежуток для корректного отображения на краях
			if(maxU > maxV)
				maxV = maxU+1;
			else maxV +=1;
			if(minL < minV)
				minV = minL-1;
			else minV -=1;
			//Меняем по вычисленным значениям
			numAxis.setMaximum(maxV);
			numAxis.setMinimum(minV);
			dayAxis.setToDate(me.dateMenu.getDateTo());
			dayAxis.setFromDate(me.dateMenu.getDateFrom());
			chart.redraw();
		}
	},
	setDateToFrom: function(){
		var me = this,
			arrCharts = this.ChartsPanel.query('cartesian');
		arrCharts.forEach(function(chart){
			var dayAxis = chart.getAxis(1);
			dayAxis.setToDate(me.dateMenu.getDateTo());
			dayAxis.setFromDate(me.dateMenu.getDateFrom());
			chart.redraw();
		});

	},
	getChartByIndex: function (index) {
		if(this.addedCharts.length < index) return false;
		return this.addedCharts[index];
	},
	addChart: function(UslugaComplex_id,UslugaComplex_Name){
		var me = this;
		var index = this.addedCharts.length;
		if(Ext6.isEmpty(UslugaComplex_Name))
			UslugaComplex_Name = '';
		var store = Ext6.create('Ext6.data.Store', me.storeChartConfig);
		store.storeIndex = index;
		store.proxy.extraParams.Person_id = me.Person_id;
		store.proxy.extraParams.UslugaComplex_id = UslugaComplex_id;
		store.addListener('load', function (store, records, successful, operation, eOpts) {
			if(records && records.length>0)
				me.setChartLimits(records,me.getChartByIndex(store.storeIndex));
			else{
				store.destroy();
				var chartEmpty = me.getChartByIndex(store.storeIndex);
				if(chartEmpty) chartEmpty.destroy();
			}
		});

		me.chartDefaultConfig.store = store;
		me.chartDefaultConfig.chartIndex = this.index;
		var chart = Ext6.create('Ext6.chart.CartesianChart', me.chartDefaultConfig);

		this.addedCharts.push(chart);
		this.ChartsPanel.add(chart);
		store.load();
	},
	checkForComplexUslugaList: function(UslugaComplex_id, show){
		var me = this;
		me.mask('Проверка наличия исследования');
		var params = {
			UslugaComplex_id: UslugaComplex_id,
			Person_id: me.Person_id
		};
		Ext6.Ajax.request({
			url: '/?c=EvnUslugaPar&m=checkForComplexUslugaList',
			params: params,
			callback: function (opt, success, response) {
				me.unmask();
				if (success && response && response.responseText) {
					var data = Ext6.JSON.decode(response.responseText);
					if (data.Error_Msg) {
						sw.swMsg.alert(langs('Ошибка'), langs(data.Error_Msg));
					}
					else{
						me.suspendLayouts();
						if(data && data.length > 1 ){ // Если в исследовании множество тестов
							data.forEach(function(test,i){
								if(test.UslugaComplex_id){
									if(i>0)
										me.addChart(test.UslugaComplex_id,(test.UslugaComplex_Code?test.UslugaComplex_Code:'')+' '+test.UslugaComplex_Name);
									else
										me.loadDefaultChart(test.UslugaComplex_id);
								}
							});
						}
						else{
							if(data[0] && data[0].UslugaComplex_id){
								// Если в исследовании один тест
								if(show) // Если это первичная загрузка формы, обновляем график
									me.loadDefaultChart(data[0].UslugaComplex_id);
								else{ // Если мы добавляем параметр, добавляем дополнительный график
									me.addChart(data[0].UslugaComplex_id,data[0].UslugaComplex_Code+' '+data[0].UslugaComplex_Name);
								}
							}
							else
								sw.swMsg.alert(langs('Ошибка'), langs('Выбранная услуга пациенту не оказывалась'));
						}
						me.resumeLayouts(true);
					}
				}
				else
					sw.swMsg.alert(langs('Ошибка'), langs('Ошибка при сохранении данных формы'));
			}
		});
	},
	stepDay: function(day)
	{
		var win = this;
		var date1 = (win.dateMenu.getDateFrom() || Date.parseDate(win.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		var date2 = (win.dateMenu.getDateTo() || win.dateMenu.getDateFrom() || Date.parseDate(win.curDate, 'd.m.Y')).add(Date.DAY, day).clearTime();
		if( date1.toJSONString() === date2.toJSONString() ) {
			win.dateMenu.setDates(date1);
		} else {
			win.dateMenu.setDates([date1, date2]);
		}
	},
	prevDay: function() {
		this.stepDay(-1);
	},
	nextDay: function ()
	{
		this.stepDay(1);
	},
	/* конструктор */
	initComponent: function() {
		var me = this;


		this.PersonInfoPanel = Ext6.create('common.EMK.PersonInfoPanel', {
			region: 'north',
			buttonPanel: false,
			border: false,
			userMedStaffFact: this.userMedStaffFact,
			ownerWin: this
		});

		this.chartStore = Ext6.create('Ext6.data.Store', me.storeChartConfig);
		me.chartStore.addListener('load', function (store, records, successful, operation, eOpts) {
			me.chartDefault.setVisible(records && records.length>0);
			if(records && records.length>0)
				me.setChartLimits(records);
		});

		me.chartDefaultConfig.store = this.chartStore;
		this.chartDefault = Ext6.create('Ext6.chart.CartesianChart', me.chartDefaultConfig);
		this.dateMenu = Ext6.create('Ext6.date.RangeField', {
			hideLabel: true,
			autoWidth: true,
			itemId: 'datefilter',
			margin: 0,
			width: 210
		});
		me.dateMenu.addListener('set', function () {
			me.setDateToFrom()
		});
		this.ChartsPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			autoHeight: true,
			bodyBorder: false,
			userCls: 'time-series-results',
			scrollable: 'y',
			layout: {
				//type: 'accordion',
				titleCollapse: false,
				animate: true,
				multi: true,
				activeOnTop: false
			},
			listeners: {
				'resize': function() {
					this.updateLayout();
				}
			},
			defaults: {border:false},
			items: [
				this.chartDefault
			]
		});
		this.addChartBtn = Ext6.create('Ext6.button.Button',{
			iconCls: 'add-chart',
			text: 'Добавить показания',
			handler: function(){
				getWnd('swSelectTimeSeriesUslugaWindow').show({
					callback: function(UslugaComplex_id) {
						me.checkForComplexUslugaList(UslugaComplex_id);
					},
					Person_id: me.Person_id
				});
			}
		});
		this.setCollapsedBtn = Ext6.create('Ext6.button.Button',{
			scale: 'small',
			text: 'Свернуть все',
			userCls: 'button-without-frame coll-exp-all button-expand-all button-expanded-all',
			margin: '0 0 0 2',
			padding: 5,
			pressed: true,
			enableToggle: true,
			toggleHandler: function (button, pressed, eOpts) {
				me.mask('Применение настройки');
				this.toggleCls('button-expanded-all');
				this.setText(pressed?'Свернуть все':'Развернуть все');
				var arrCharts = me.ChartsPanel.query('cartesian');
				arrCharts.forEach(function(chart){
					chart.setCollapsed(!pressed)
				});
				me.unmask();

			}
		});
		this.CardPanel = Ext6.create('Ext6.panel.Panel', {
			region: 'center',
			userCls: 'timeSer-card-panel',
			layout: 'card',
			activeItem: 0,
			defaults: {border:false},
			tbar: {
				xtype: 'toolbar',
				cls: 'toptoolbar',
				items: [{
					text: 'Период:',
					padding: "10px 0px 10px 10px",
					margin: 0,
					xtype: 'label'
				}, {
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-previous16-2017',
					handler: function()
					{
						me.prevDay();
						me.setDateToFrom()
					}
				}, this.dateMenu, {
					xtype: 'button',
					cls: 'bgTrans',
					border: false,
					margin: 3,
					iconCls: 'arrow-next16-2017',
					handler: function()
					{
						me.nextDay();
						me.setDateToFrom()
					}
				},
					this.addChartBtn,
					this.setCollapsedBtn,
					'->',
					{
					xtype: 'segmentedbutton',
					userCls: 'segmentedButtonGroup',//для применение стилей ставить этот класс
					items: [{
						text: 'Таблица',
						refId: 'table',
						handler: function() {
							me.CardPanel.getLayout().setActiveItem(1);
						}
					}, {
						text: 'Графики',
						refId: 'charts',
						pressed: true,
						handler: function() {
							me.CardPanel.getLayout().setActiveItem(0);
						}
					}]
				}]
			},
			items: [
				this.ChartsPanel,
				{
					layout: 'center',
					anchor: 'center',
					width: '75%',
					height: '95%',
					bodyPadding: '0',
					items: [
						{
							border: false,
							html: '<div class="specific-action-container" style="width: 270px;"><p class="action-container-text">Функционал в разработке</p></div>'
						}
					]
				}
			]
		});
		Ext6.apply(me, {
			items: [
				me.PersonInfoPanel,
				me.CardPanel
			]
		});

		this.callParent(arguments);
	}
});

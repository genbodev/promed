/**
* ЛИС: форма "Контроль качества"
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package    All
* @access     public
* @autor      Salavat Magafurov
* @copyright  Copyright (c) 2019 EMSIS.
* @version    01.07.2019
*/

sw.Promed.swAnalyzerQualityControlWindow = Ext.extend(sw.Promed.BaseForm, {
	objectName: 'swAnalyzerQualityControlWindow',
	title: langs('Контроль качества'),
	modal: true,
	maximized: true,
	layout: 'fit',
	stageQualityControl: 3,
	stageInstallationSeries: 2,
	stageConvergence: 1,
	show: function() {
		sw.Promed.swAnalyzerQualityControlWindow.superclass.show.apply(this, arguments);
		var win = this,
			data = arguments[0],
			MedService_id = data ? data.MedService_id : null;

		win.formPanel.getForm().findField('MedService_id').setValue(MedService_id);
		win.RuleLabGrid.savedata = { 'MedService_id': MedService_id };
		win.RuleLabGrid.setParam('MedService_id', MedService_id);

		win.fullAccess = data && data.ARMType && data.ARMType.inlist(['lpuadmin', 'reglab']);

		win.RuleLpuGrid.setReadOnly(!win.fullAccess);

		if(MedService_id) {
			win.RuleLabGrid.loadData();
		}

		//контрольные материалы
		win.MaterialGrid.removeAll();
		win.MaterialValueGrid.removeAll();

		//контрольные серии
		win.filterMaterialValue.getStore().removeAll();
		win.ControlSeriesGrid.removeAll();
		win.ControlSeriesValueGrid.removeAll();
		win.ControlSeriesValueGrid.setParam('isCull', 0);
		win.CullJournalGrid.removeAll();

		win.initFilters(win.fullAccess ? null : MedService_id);
		win.RuleLpuGrid.loadData();
		win.MaterialGrid.loadData();
		win.ControlSeriesValueGrid.addActions( win.getActionDisable(win, 'Исключить'), 1 );
		win.MaterialGrid.addActions( win.getActionDisable(win.MaterialGrid), 2 );
		win.MaterialValueGrid.addActions( win.getActionDisable(win.MaterialValueGrid), 2 );
		win.ControlSeriesGrid.addActions( win.getActionDisable(win.ControlSeriesGrid), 2);
	},

	getActionDisable: function(object, name) {
		name = !name ? langs('Удалить') : langs(name);
		var win = this,
			handler = object == win ? win.actionDisableSeriesValueGrid : win.actionDisable;
		return {
			name: 'action_disable',
			text: name,
			iconCls: 'delete16',
			handler: handler.createDelegate(object),
			disabled: true
		}
	},

	initFilters: function(MedService_id) {

		var win = this,
			filters = Array();

		filters.push(
			win.filterLabOnFirstTab,
			win.filterLabOnSecondTab,
			win.filterLabOnThirdTab,
			win.filterLabOnFourthTab
		);
		

		filters.forEach(function(field) {
			field.setValue(MedService_id);
			field.fireEvent('change', field, MedService_id);
			field.setDisabled(!win.fullAccess);
			var store = field.getStore();
			store.load({ params: { MedServiceType_SysNick: 'lab' }});
			if(!store.hasListener('load')) {
				store.on('load', function(){
					field.setValue(field.getValue());
				});
			}
		});
	},

	getChartGuides: function(chartX, chartS) {
		var guides = [
			{
				name: '-3S',
				value: chartX - 3 * chartS,
				color: "#AA0000"
			}, {
				name: '-2S',
				value: chartX - 2 * chartS,
				color: "#AAAA00"
			}, {
				name: '-1S',
				value: chartX - chartS,
				color: "#00AA00"
			}, {
				name: 'X',
				value: chartX,
				color: "#000000"
			}, {
				name: '+1S',
				value: chartX + chartS,
				color: "#00AA00"
			}, {
				name: '+2S',
				value: chartX + 2 * chartS,
				color: "#AAAA00"
			}, {
				name: '+3S',
				value: chartX + 3 * chartS,
				color: "#AA0000"
			}
		];
		return guides;
	},

	getSeriesChartData: function(viewframe, chartX, chartS, chartSize) {
		var win = this,
			data = [],
			isCullJournal = viewframe == win.CullJournalGrid,
			chartMaxX = chartX + (chartSize - 0.1) * chartS,
			chartMinX = chartX - (chartSize - 0.1) * chartS;

		viewframe.getGrid().getStore().each(function(rec) {
			var date = Date.parseDate(rec.get('QcControlSeriesValue_setDT'), 'Y-m-d H:i:s'),
				dateString = Ext.util.Format.date(rec.get('QcControlSeriesValue_setDT'), 'd.m.Y H:i:s'),
				value = rec.get('UslugaTest_ResultValue'),
				ruleName = rec.get('QcRule_Name'),
				desc = "<div style='margin:5px; font-size:16px;'><span style='font-size:10px;'>"+dateString+"</span><br>"+value,
				num = rec.get('num'),
				isDisabledRec = rec.get('QcControlSeriesValue_isDisabled') == 2;

			//в журнале выбраковок отображаются исключенные результаты и результаты не прошедшие правила, нам нужны только исключенные
			if(isCullJournal && !isDisabledRec) {
				return;
			}

			if(ruleName) {
				desc += '<br>' + ruleName
			}

			if(value > chartMaxX) {
				value = chartMaxX;
			} else if (value < chartMinX) {
				value = chartMinX;
			}

			if(isCullJournal) {
				value = value > chartX ? chartMaxX : chartMinX;
			}

			desc += "</div>";
			data.push({
				value: value,
				date: date,
				description: desc,
				num: num,
				bullet: 'round',
				color: ruleName ? '#FF0000' : '#0000FF',
				size: ruleName ? 15 : 10
			});
		});

		return data;
	},
	// Рисует контрольную карту по значениям контрольной серии
	makeSeriesChart: function(){

		var win = this,
			form = win.seriesForm.getForm(),
			chartX = form.findField('QcControlSeries_Xcp').getValue(),
			seriesS = form.findField('QcControlSeries_S').getValue(),
			chartS = seriesS ? seriesS : 1,
			guides = [],
			chartSize = 4,
			chartMaxX = chartX + chartSize * chartS,
			chartMinX = chartX - chartSize * chartS;

		if(win.seriesChart) {
			win.seriesChart.cleanChart();
		}

		if(!chartX) return;

		var dataProvider = win.getSeriesChartData(win.ControlSeriesValueGrid, chartX, chartS, chartSize),
			cullData = win.getSeriesChartData(win.CullJournalGrid, chartX, chartS, chartSize);

		for(var i = dataProvider.length + 1; i <= 30; ++i) {
			dataProvider.push({
				num: i
			});
		}

		cullData.forEach(function (cullRec) {
			for(var i = 0; i < dataProvider.length; ++i) {
				var rec = dataProvider[i];
				if(cullRec.date <= rec.date) {
					rec.cullValue = cullRec.value;
					if(typeof(rec.cullDescription) !== 'string') {
						rec.cullDescription = '';
					}
					rec.cullDescription += '<br>' + cullRec.description;
					return; 
				}
				
			}
		});

		win.getChartGuides(chartX, chartS).forEach (function(label){
			guides.push({
				value: label.value,
				label: label.name,
				lineAlpha: 0.5,
				position: 'left',
				lineThickness: 1,
				lineColor: label.color
			})
		})

		//CHART
		win.seriesChart = new AmCharts.AmSerialChart();
		var chart = win.seriesChart;
		chart.dataProvider = dataProvider;
		chart.categoryField = 'num';
		chart.marginRight = 40;
		chart.marginLeft = 40;
		chart.categoryAxis.gridAlpha = 0;


		//categoryAxis
		var categoryAxis = new AmCharts.CategoryAxis();
		categoryAxis.gridAlpha = 0;
		categoryAxis.dashLength =  0;

		//VALUE AXIS
		var valueAxis = new AmCharts.ValueAxis();
		valueAxis.minimum = chartMinX;
		valueAxis.maximum = chartMaxX;
		valueAxis.includeGuidesInMinMax = true;
		valueAxis.ignoreAxisWidth = true;
		valueAxis.axisAlpha = 0;
		valueAxis.gridAlpha = 0;
		valueAxis.guides = guides;
		valueAxis.labelsEnabled = false;

		//GRAPH
		var graphs = new AmCharts.AmGraph();
		graphs.valueField = 'value';
		graphs.balloonText = '[[description]]';
		graphs.bulletField = 'bullet';
		graphs.lineThickness = 1.3;
		graphs.bulletSizeField = 'size';
		graphs.colorField = 'color';
		graphs.lineColor = '#2222FF';

		var graphsPoint = new AmCharts.AmGraph();
		graphsPoint.valueField = 'cullValue';
		graphsPoint.balloonText = '[[cullDescription]]';
		graphsPoint.bulletField = 'bullet';
		graphsPoint.lineThickness = 1.3;
		//graphsPoint.color = '#000000';
		graphsPoint.lineColor = '#000000';
		graphsPoint.lineAlpha = 0;

		chart.addValueAxis(valueAxis);
		chart.addGraph(graphs);
		chart.addGraph(graphsPoint);
		chart.write(win.id+'SeriesChart');
	},

	// Рисует радиальный график во вкладке сводная информация
	makeMethodicsChart: function() {
		var win = this,
			store = win.SvodGrid.getGrid().getStore();

		if(win.methodicsChart) {
			win.methodicsChart.cleanChart();
		}

		if(!store.getCount()) return;

		var axisLength = 800,
			data = [],
			guides = [],
			chartSize = 4,
			chartS = axisLength/(chartSize * 2),
			chartX = axisLength/2;

		win.getChartGuides(chartX, chartS).forEach (function(label){
			guides.push({
				value: label.value,
				label: label.name,
				lineAlpha: 0.5,
				position: 'right',
				lineThickness: 1,
				lineColor: label.color,
				//inside: true
			})
		});

		store.each(function(rec) {
			var Xcp = rec.get('QcControlSeries_Xcp'),
				S = rec.get('QcControlSeries_S'),
				value = rec.get('UslugaTest_ResultValue'),
				name = rec.get('AnalyzerTest_Name'),
				ruleName = rec.get('QcRule_Name'),
				date = Ext.util.Format.date(rec.get('QcControlSeriesValue_setDT'), 'd.m.Y H:i:s'),
				seriesMaxValue = Xcp + S * chartSize,
				seriesMinValue = Xcp - S * chartSize,
				seriesRange = seriesMaxValue - seriesMinValue,
				a = value - seriesMinValue,
				k = axisLength / seriesRange,
				newValue = k * a,
				desc = date + '<br>' + value; //полученное значение размещается от 0 до 800

				if(newValue < 0) {
					newValue = 0;
				} else if(newValue > axisLength) {
					newValue = axisLength;
				}

				if(ruleName) {
					desc += '<br>' + ruleName;
				}

				data.push({
					newValue: newValue,
					oldValue: value,
					name: name,
					description: desc,
					bullet: 'round',
					color: ruleName ? '#FF0000' : '#0000FF',
					size: ruleName ? 15 : 10
				})
		});

		if(data.length < 3)
			return;

		//CHART
		win.methodicsChart = new AmCharts.AmRadarChart();
		var chart = win.methodicsChart;
		chart.dataProvider = data;
		chart.categoryField = 'name';

		//VALUE AXIS
		var valueAxis = new AmCharts.ValueAxis();
		valueAxis.minimum = 0;
		valueAxis.maximum = axisLength;
		valueAxis.axisTitleOffset = 30;
		valueAxis.includeGuidesInMinMax = true;
		valueAxis.ignoreAxisWidth = true;
		valueAxis.axisAlpha = 0.1;
		valueAxis.gridAlpha = 0;
		valueAxis.fillAlpha = 0;
		valueAxis.guides = guides;
		valueAxis.labelsEnabled = false;

		//GRAPH
		var graphs = new AmCharts.AmGraph();
		graphs.valueField = 'newValue';
		graphs.balloonText = '[[description]]';
		graphs.bulletField = 'bullet';
		graphs.lineThickness = 1;
		//graphs.bulletSizeField = 'size';
		graphs.colorField = 'color';
		graphs.lineColor = '#2222FF';

		chart.addValueAxis(valueAxis);
		chart.addGraph(graphs);
		chart.write(win.id+'MethodicsChart');
	},

	initComponent: function() {
		var win = this;

		//Правила КК - общие
		win.RuleLpuGrid = new sw.Promed.ViewFrame({
			object: 'QcRuleLpu',
			dataUrl: '/?c=QcRuleLpu&m=loadGrid',
			region: 'center',
			selectionModel: 'cell',
			autoLoadData: false,
			contextmenu: false,
			saveAtOnce: false,
			saveAllParams: true,
			border: false,
			showCountInTop: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_save', url: '/?c=QcRuleLpu&m=doSaveGrid' }
			],
			stringfields: [
				{ type: 'int', name: 'QcRule_id' },
				{ type: 'int', name: 'QcRuleLpu_id', hidden: true, isparams: true },
				{ type: 'checkcolumnedit', header: langs('Включить'), name: 'isOn', isparams: true, width: 70 },
				{ type: 'string', header: langs('Правило'), name: 'QcRule_Name', width: 150 },
				{ type: 'checkcolumnedit', header: langs('Запрещает исследования'), name: 'QcRuleLpu_isStrong', isparams: true, width: 150 }
			],
			onLoadData: function(loaded) {
				var form = win.formPanel.getForm(),
					isLpuRule = form.findField('MedService_IsGeneralQcRule').getValue();
				if(isLpuRule) {
					win.initRuleLabGrid(true);
				}
			}
		});

		//Правила КК - По лабораториям
		win.RuleLabGrid = new sw.Promed.ViewFrame({
			object: 'QcRuleLab',
			dataUrl: '/?c=QcRuleLab&m=loadGrid',
			region: 'center',
			autoLoadData: false,
			contextmenu: false,
			saveAtOnce: false,
			saveAllParams: true,
			saveBaseParams: true,
			showCountInTop: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_save', url: '/?c=QcRuleLab&m=doSaveGrid' }
			],
			stringfields: [
				{ type: 'int', name: 'QcRule_id', key: true },
				{ type: 'int', name: 'QcRuleLab_id', hidden: true, isparams: true },
				{ type: 'checkcolumnedit', header: langs('Включить'), name: 'isOn', isparams: true, width: 70 },
				{ type: 'string', header: langs('Правило'), name: 'QcRule_Name', width: 150 },
				{ type: 'checkcolumnedit', header: langs('Запрещает исследования'), name: 'QcRuleLab_isStrong', isparams: true, width: 150 }
			],
			onLoadData: function(loaded) {
				var form = win.formPanel.getForm(),
					isLpuRule = form.findField('MedService_IsGeneralQcRule').getValue();
				if(isLpuRule) {
					win.initRuleLabGrid(true);
				}
			}
		});

		//Контрольные материалы
		win.MaterialGrid = new sw.Promed.ViewFrame({
			object: 'QcControlMaterial',
			dataUrl: '/?c=QcControlMaterial&m=loadGrid',
			//selectionModel: 'multiselect',
			region: 'center',
			focusOnFirstLoad: false,
			useEmptyRecord: false,
			autoLoadData: false,
			toolbar: true,
			border: false,
			scheme: 'lis',
			linkedTables: 'QcControlMaterialValue',
			editformclassname: 'swQcControlMaterialWindow',
			disableMsg: langs('Удалить контрольный материал?'),
			actions: [
				{ name: 'action_add' },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ type: 'int', name: 'QcControlMaterial_id', key: true },
				{ type: 'int', name: 'QcControlMaterialType_id', isparams: true, hidden: true },
				{ type: 'string', header: langs('Наименование'), name: 'QcControlMaterial_Name', isparams: true, width: 200 },
				{ type: 'string', header: langs('Вид'), name: 'QcControlMaterialType_Name',  width: 150 },
				{ type: 'checkbox', header: langs('Аттестован'), name: 'QcControlMaterial_IsAttested', isparams: true, width: 70 },
				{ type: 'string', header: langs('Лот'), name: 'QcControlMaterial_LotNum', isparams: true, width: 90},
				{ type: 'string', header: langs('Каталожный номер'), name: 'QcControlMaterial_CatalogNum', isparams: true, width: 90 },
				{ type: 'date', header: langs('Срок годности'), name: 'QcControlMaterial_ExpDate', isparams: true, width: 100 },
				{ type: 'date', header: langs('Дата добавления'), name: 'QcControlMaterial_begDT', isparams: true, width: 100 },
				{ type: 'date', header: langs('Дата удаления'), name: 'QcControlMaterial_endDT', isparams: true, width: 100 }
			],
			afterDeleteRecord: function(object, id, answer) {
				win.MaterialValueGrid.loadData();
			},
			setDisabledMaterialValueActions: function(disabled) {
				if(disabled === undefined) {
					var rec = win.MaterialGrid.getGrid().getSelectionModel().getSelected();
					disabled = !rec ? true : !Ext.isEmpty(rec.get('QcControlMaterial_endDT'));
				}
				win.MaterialValueGrid.ViewActions.action_edit.setDisabled( disabled );
				win.MaterialValueGrid.ViewActions.action_add.setDisabled( disabled );
			},
			onRowSelect: function(sm, rowIdx, rec) {
				var id = rec.get('QcControlMaterial_id'),
					viewframe = this;
				win.MaterialValueGrid.setParam('QcControlMaterial_id',id);
				win.MaterialValueGrid.params = {
					QcControlMaterial_id: rec.get('QcControlMaterial_id'),
					QcControlMaterial_Name: rec.get('QcControlMaterial_Name'),
					QcControlMaterial_IsAttested: rec.get('QcControlMaterial_IsAttested'),
					MedService_id: win.filterLabOnSecondTab.getValue(),
					callback: function() {
						win.MaterialValueGrid.loadData();
					}
				};
				if(win.filterLabOnSecondTab.getValue() && id) {
					win.MaterialValueGrid.loadData();
				}
				var isDisabled = Boolean(rec.get('QcControlMaterial_endDT')) || !rec.get('QcControlMaterial_id');
				viewframe.ViewActions.action_disable.setDisabled(isDisabled);
				viewframe.ViewActions.action_edit.setDisabled(isDisabled);
				var disable = !Ext.isEmpty(rec.get('QcControlMaterial_endDT'));
				viewframe.setDisabledMaterialValueActions(disable);
			},
			onBeforeLoadData: function () {
				var viewframe = this,
					materialTypeId = win.materialTypeFilter.getValue(),
					name = win.materialNameFilter.getValue();
				viewframe.setParam('QcControlMaterialType_id', materialTypeId);
				viewframe.setParam('QcControlMaterial_Name', name);
			},
			onLoadData: function(loaded) {
				var viewframe = this;
				if(!loaded) {
					win.MaterialValueGrid.removeAll();
					viewframe.ViewActions.action_disable.setDisabled(true);
				}
			}
		});

		win.MaterialGrid.ViewGridPanel.view = new Ext.grid.GridView({
			enableRowBody: true,
			getRowClass : function (row, index) {
				var cls = '';
				if(row.get('QcControlMaterial_endDT')){
					cls = cls+'x-grid-rowgray ';
				}
				return cls;
			},
			listeners: {
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		//Методики (Контрольные материалы)
		win.MaterialValueGrid = new sw.Promed.ViewFrame({
			object: 'QcControlMaterialValue',
			dataUrl: '/?c=QcControlMaterialValue&m=loadGrid',
			//selectionModel: 'multiselect',
			region: 'center',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: true,
			border: false,
			scheme: 'lis',
			editformclassname: 'swQcControlMaterialValueWindow',
			linkedTables: 'QcControlSeries',
			disableMsg: langs('Удалить методику?'),
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit' },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ type: 'int', name: 'QcControlMaterialValue_id', key: true },
				{ type: 'int', name: 'MedService_id', isparams: true, hidden: true },
				{ type: 'int', name: 'QcControlMaterial_id', isparams: true, hidden: true },
				{ type: 'int', name: 'UslugaComplex_id', isparams: true, hidden: true },
				{ type: 'int', name: 'Analyzer_id', isparams: true, hidden: true },
				{ type: 'int', name: 'AnalyzerTest_id', isparams: true, hidden: true },
				{ type: 'string', header: langs('Тест'), name: 'UslugaComplex_Name', isparams: true, hidden: true },
				{ type: 'string', header: langs('Методика КМ'), name: 'AnalyzerTest_Name', isparams: true, width: 150 },
				{ type: 'string', header: langs('Методика лаборатории'), name: 'labMethod', isparams: true, width: 150 },
				{ type: 'string', header: langs('Код теста'), name: 'UslugaComplex_Code', isparams: true, width: 150 },
				{ type: 'string', header: langs('Среднее Xср'), name: 'QcControlMaterialValue_X', isparams: true },
				{ type: 'string', header: langs('Отклонение Scp'), name: 'QcControlMaterialValue_S', isparams: true },
				{ type: 'string', header: langs('Коэффициент вариации CV10'), name: 'QcControlMaterialValue_CV10', isparams: true },
				{ type: 'string', header: langs('Смещение B10'), name: 'QcControlMaterialValue_B10', isparams: true },
				{ type: 'string', header: langs('Коэффициент вариации CV20'), name: 'QcControlMaterialValue_CV20', isparams: true },
				{ type: 'string', header: langs('Смещение B20'), name: 'QcControlMaterialValue_B20', isparams: true },
				{ type: 'string', header: langs('Анализатор'), name: 'Analyzer_Name', width: 150 },
				{ type: 'date', header: langs('Дата добавления'), name: 'QcControlMaterialValue_begDT', isparams: true, width: 100 },
				{ type: 'date', header: langs('Дата удаления'), name: 'QcControlMaterialValue_endDT', isparams: true, width: 100 }
			],
			checkBeforeLoadData: function(store, options) {
				return this.getParam('QcControlMaterial_id') && this.getParam('MedService_id');
			},
			onRowSelect: function(sm, rowIdx, rec) {
				var viewframe = this,
					isDisabled = Boolean(rec.get('QcControlMaterialValue_endDT')) || !rec.get('QcControlMaterialValue_id');

				var materialRec = win.MaterialGrid.getGrid().getSelectionModel().getSelected();

				if(materialRec) {
					isDisabled |= Boolean(materialRec.get('QcControlMaterial_endDT'));;
				} else {
					isDisabled = true;
				}

				viewframe.ViewActions.action_disable.setDisabled(isDisabled);
				viewframe.ViewActions.action_edit.setDisabled(isDisabled);
			},
			onLoadData: function(loaded) {
				if(!loaded) {
					this.ViewActions.action_disable.setDisabled(true);
				}
			}
		});

		win.MaterialValueGrid.ViewGridPanel.view = new Ext.grid.GridView({
			enableRowBody: true,
			getRowClass : function (row, index) {
				var cls = '';
				if(row.get('QcControlMaterialValue_endDT')){
					cls = cls+'x-grid-rowgray ';
				}
				return cls;
			},
			listeners: {
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		//Контрольные серии
		win.ControlSeriesGrid = new sw.Promed.ViewFrame({
			region: 'center',
			object: 'QcControlSeries',
			dataUrl: '/?c=QcControlSeries&m=loadGrid',
			focusOnFirstLoad: false,
			useEmptyRecord: false,
			autoLoadData: false,
			toolbar: true,
			split: true,
			scheme: 'lis',
			editformclassname: 'swQcControlSeriesWindow',
			linkedTables: 'QcControlSeriesValue',
			disableMsg: langs('Удалить контрольную серию?'),
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', hidden: true }
			],
			stringfields: [
				{ type: 'int', name: 'QcControlSeries_id', key: true, isparams: true },
				{ type: 'int', name: 'QcControlSeries_pid', hidden: true, isparams: true },
				{ type: 'int', name: 'QcControlMaterialValue_id', hidden: true, isparams: true},
				{ type: 'int', name: 'QcControlStage_id', hidden: true, isparams: true },
				{ type: 'int', name: 'QcControlSeries_IsControlPassed', hidden: true, isparams: true },
				{ type: 'string', header: langs('Наименование'), name: 'QcControlSeries_Name',  width: 250, id: 'autoexpand' },
				{ type: 'string', header: langs('Материал'), name: 'QcControlMaterial_Name',  width: 250 },
				{ type: 'string', header: langs('Стадия контроля'), name: 'QcControlStage_Name', width: 100, isparams: true},
				{ type: 'float', name: 'QcControlSeries_Xcp', hidden: true },
				{ type: 'float', name: 'QcControlSeries_S', hidden: true },
				{ type: 'float', name: 'QcControlSeries_CV', hidden: true },
				{ type: 'float', name: 'QcControlSeries_B', hidden: true },
				{ type: 'date', header: langs('Дата добавления'), name: 'QcControlSeries_begDT' },
				{ type: 'date', header: langs('Дата удаления'), name: 'QcControlSeries_endDT' }
			],
			onRowSelect: function(sm, rowIdx, rec) {
				if(!rec) return;
				var viewframe = this,
					id = rec.get('QcControlSeries_id'),
					pid = rec.get('QcControlSeries_pid'),
					QcControlStage_id = rec.get('QcControlStage_id');
				win.ControlSeriesValueGrid.removeAll();
				win.ControlSeriesValueGrid.setParam('QcControlSeries_id', id);
				win.ControlSeriesValueGrid.setParam('QcControlSeries_pid', pid);
				win.ControlSeriesValueGrid.setParam('QcControlStage_id', QcControlStage_id);
				win.ControlSeriesValueGrid.loadData();
				win.CullJournalGrid.removeAll();
				win.CullJournalGrid.setParam('QcControlSeries_id',id);
				win.CullJournalGrid.setParam('isCull', 1);
				win.CullJournalGrid.loadData();
				win.seriesForm.getForm().setValues(rec.data);
				win.filterSeries.getStore().baseParams.QcControlSeries_pid = rec.get('QcControlSeries_pid');
				win.filterSeries.getStore().load();
				var isDisabled = Boolean(rec.get('QcControlSeries_endDT'));
				viewframe.ViewActions.action_disable.setDisabled(isDisabled);
			},
			checkBeforeLoadData: function(store, options) {
				return !Ext.isEmpty( this.getParam('QcControlMaterialValue_id') );
			},
			onBeforeLoadData: function() {
				win.ControlSeriesValueGrid.removeAll();
				win.CullJournalGrid.removeAll();
				win.makeSeriesChart();
			},
			onLoadData: function(loaded) {
				if(!loaded) {
					win.ControlSeriesGrid.removeAll();
					this.ViewActions.action_disable.setDisabled(true);
				}
			}
		});

		win.ControlSeriesGrid.ViewGridPanel.view = new Ext.grid.GridView({
			enableRowBody: true,
			getRowClass : function (row, index) {
				var cls = '';
				if(row.get('QcControlSeries_endDT')){
					cls = cls+'x-grid-rowgray ';
				}
				return cls;
			},
			listeners: {
				rowupdated: function(view, first, record) {
					view.getRowClass(record);
				}
			}
		});

		win.filterSeries = new sw.Promed.SwBaseLocalCombo({
			valueField: 'QcControlSeries_id',
			displayField: 'period',
			listWidth: 300,
			width: 170,
			allowBlank: false,
			editable: false,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
					'<div {[this.getStyle(values.QcControlSeries_endDT)]}>',
						'<div>{period}</div>',
						'<div style="font-size: 10px;">{QcControlStage_Name}</div>',
					'</div>',
				'</div></tpl>',
				{
					getStyle: function(values) {
						var cls = 'style="margin:2px;';
						if(values!="")
							cls +='color:#808080;';
						return cls + '"';
					}
				}
			),
			store: new Ext.data.JsonStore({
				autoLoad: false,
				baseParams: {
					'QcControlSeries_pid': null
				},
				url: '/?c=QcControlSeries&m=loadCombo',
				key: 'QcControlSeries_id',
				fields: [
					{ type: 'int', name: 'QcControlSeries_id' },
					{ type: 'int', name: 'QcControlSeries_pid' },
					{ type: 'int', name: 'QcControlStage_id' },
					{ type: 'int', name: 'QcControlSeries_IsControlPassed' },
					{ type: 'date', name: 'QcControlSeries_begDT'},
					{ type: 'date', name: 'QcControlSeries_endDT'},
					{ type: 'date', name: 'begDT'},
					{ type: 'date', name: 'endDT'},
					{ type: 'float', name: 'QcControlSeries_Xcp' },
					{ type: 'float', name: 'QcControlSeries_S' },
					{ type: 'float', name: 'QcControlSeries_CV' },
					{ type: 'float', name: 'QcControlSeries_B' },
					{ type: 'string', name: 'QcControlStage_Name' },
					{ type: 'string', name: 'period',
						convert: function (val, row) {
							var begDate = Ext.util.Format.date(row.begDT,'d.m.Y'),
								endDate = Ext.util.Format.date(row.endDT,'d.m.Y');
							if(begDate)
								return 'c ' + begDate + ( !endDate ? '' : ' по ' + endDate );
							else
								return 'Новая';
						}
					}
				],
				listeners: {
					load: function () {
						var rec = win.ControlSeriesGrid.getGrid().getSelectionModel().getSelected();
						if(!rec) return;
						var value = rec.get('QcControlSeries_id');
						win.ControlSeriesValueGrid.setParam('QcControlSeries_id', value);
						win.ControlSeriesValueGrid.setParam('QcControlSeries_pid', rec.get('QcControlSeries_pid'));
						win.ControlSeriesValueGrid.setParam('QcControlStage_id', rec.get('QcControlStage_id'));
						win.filterSeries.setValue(value);
						win.setDisabledSeriesValueGridActions();
						win.makeSeriesChart();
						win.toogleDisabledStageBtn();
					}
				}
			}),
			listeners: {
				change: function(combo, newValue) {
					var rec = combo.getStore().getById(newValue);
					if(!rec) return;
					win.ControlSeriesValueGrid.removeAll();
					win.ControlSeriesValueGrid.setParam('QcControlSeries_id', newValue);
					win.ControlSeriesValueGrid.setParam('QcControlSeries_pid', rec.get('QcControlSeries_pid'));
					win.ControlSeriesValueGrid.setParam('QcControlStage_id', rec.get('QcControlStage_id'));
					win.ControlSeriesValueGrid.loadData();
					win.CullJournalGrid.removeAll();
					win.CullJournalGrid.setParam('QcControlSeries_id',newValue);
					win.CullJournalGrid.setParam('isCull', 1);
					win.CullJournalGrid.loadData();
					win.seriesForm.getForm().setValues(rec.data);
					win.setDisabledSeriesValueGridActions();
				}
			}
		});

		//Журнал измерений
		win.ControlSeriesValueGrid = new sw.Promed.ViewFrame({
			//title: langs('Значения контрольной серии'),
			object: 'QcControlSeriesValue',
			dataUrl: '/?c=QcControlSeriesValue&m=loadGrid',
			//selectionModel: 'multiselect',
			autoLoadData: false,
			useEmptyRecord: false,
			saveAllParams: true,
			saveAtOnce: false,
			toolbar: true,
			editing: true,
			region: 'center',
			border: false,
			isScrollToTopOnLoad: false,
			focusOnFirstLoad: true,
			pruneModifiedRecords: true,
			saveBaseParams: true,
			noSelectFirstRowOnFocus: true,
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', 
					menuConfig: {
						print: {
							name: 'print', text: langs('Печать отчетной формы'),
							handler: win.printControlSeriesReport.createDelegate(win)
						}
					}
				},
				{ name: 'action_save', url: '/?c=QcControlSeriesValue&m=doSaveGrid' }
			],
			stringfields: [
				{ type: 'int', name: 'num', header: langs('№п/п'), align:'right', hidden: false, width: 50, key: true },
				{ type: 'int', name: 'QcControlSeriesValue_id', hidden: true, isparams: true },
				{ type: 'int', name: 'QcRule_id', hidden: true },
				{ type: 'int', name: 'QcControlSeriesValue_isDisabled', hidden: true, isparams: true },
				{ type: 'int', name: 'UslugaTest_id', hidden: true, isparams: true },
				{ type: 'string', name: 'QcControlSeriesValue_setDT', hidden: true },
				{ type: 'date', header: langs('Дата'), name: 'setDate', sortable: false, isparams: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), width: 75,
					editor: new sw.Promed.SwDateField({
						format: 'd.m.Y'
					})
				},
				{ header: langs('Время'), name: 'setTime', sortable: false, isparams: true, renderer: Ext.util.Format.dateRenderer('H:i'), width: 60,
					editor: new sw.Promed.TimeField({
						plugins: [ new Ext.ux.InputTextMask('99:99', false) ]
					})
				},
				{ type: 'float', header: langs('Результат'), name: 'UslugaTest_ResultValue', isparams: true, sortable: false, width: 100, id: 'UslugaTest_ResultValue',
					editor: new Ext.form.NumberField({
						decimalPrecision: 10
					})
				},
				{
					type: 'int', header: langs('Контроль пройден'), name: 'QcControlSeriesValue_IsControlPassed',
					renderer: function(value) {
						if(!value) return '';
						return value == 1 ? 'Нет' : 'Да';
					}
				},
				{ type: 'string', header: langs('Правило'), name: 'QcRule_Name', width: 50, sortable: false, width: 70 },
				{ type: 'string', header: langs('Примечание'), name: 'QcControlSeriesValue_Comment',sortable: false, id: 'autoexpand' }
			],
			function_action_add: function() {
				var viewframe = this,
					grid = viewframe.getGrid(),
					store = grid.getStore(),
					count = store.getCount(),
					params = win.getSeriesParams(),
					QcControlStage_id = params.QcControlStage_id,
					QcControlSeries_id = params.QcControlSeries_id,
					QcControlSeries_pid = params.QcControlSeries_pid,
					today = new Date(),
					lastRec = store.getAt(count - 1),
					modifRecs = store.getModifiedRecords(),
					StoreRecord = Ext.data.Record.create(viewframe.jsonData['store']),
					record = new StoreRecord({
						num: count + 1,
						QcControlSeries_id: QcControlSeries_id,
						QcControlSeries_pid: QcControlSeries_pid,
						QcControlStage_id: QcControlStage_id,
						QcControlSeriesValue_id: null,
						setDate: null,
						setTime: null,
						UslugaTest_ResultValue: null
					});

				if( lastRec ) {
					var lastValue = lastRec.get('UslugaTest_ResultValue'),
						lastDate = lastRec.get('setDate'),
						lastTime = lastRec.get('setTime'),
						needSave = modifRecs.length && QcControlStage_id == win.stageQualityControl;
					if(lastValue == null || lastValue === "" || !lastDate || !lastTime || needSave) {
						sw.swMsg.alert(langs('Сообщение'),langs('Сохраните ранее введенный результат'));
						return;
					}
				}

				var requiredCount = win.getResultCountForNextStage(count);

				if(count >= requiredCount) {
					sw.swMsg.alert(langs('Сообщение'),langs('Необходимо сохранить результаты и провести расчет'));
					return;
				}

				store.add(record);
				var addedRec = store.getAt(count);
				if(addedRec) {
					addedRec.beginEdit();
					addedRec.set('setDate', today);
					addedRec.set('setTime', today.format('H:i'));
					addedRec.set('UslugaTest_ResultValue', '');
					addedRec.endEdit();
				}
				grid.getView().focusRow(count);
				grid.getSelectionModel().selectLastRow();
				grid.getView().scroller.scroll('bottom',10000);
				var valueColumnIdx = grid.getColumnModel().getIndexById('UslugaTest_ResultValue');
				grid.startEditing(count, valueColumnIdx);
			},
			onRowSelect: function() {
				win.setDisabledSeriesValueGridActions();
			},
			onBeforeEdit: function(o) {
				var rec = o.record,
				id = rec.get('QcControlSeriesValue_id');
				o.cancel = id ? true : false;
			},
			onLoadData: function(loaded) {
				var viewframe = this,
					grid = viewframe.getGrid();
				win.toogleDisabledStageBtn();
				win.makeSeriesChart();
				grid.getSelectionModel().selectLastRow();
				grid.getView().scroller.scroll('bottom',10000);
			},
			onRecordSave: function () {
				this.loadData();
			},
			getLastRec: function() {
				var viewframe = this,
					store = viewframe.getGrid().getStore(),
					count = store.getCount(),
					rec = false;
				if(count) {
					rec = store.getAt(count-1);
				}
				return rec;
			},
			checkBeforeLoadData: function () {
				return !Ext.isEmpty( this.getParam('QcControlSeries_id') );
			}
		});

		//Журнал выбраковок
		win.CullJournalGrid = new sw.Promed.ViewFrame({
			//title: langs('Журнал выбраковок'),
			dataUrl: '/?c=QcControlSeriesValue&m=loadGrid',
			region: 'center',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: true,
			border: false,
			actions: [
				{ name: 'action_add', hidden: true },
				{ name: 'action_edit', hidden: true },
				{ name: 'action_view', hidden: true },
				{ name: 'action_delete', hidden: true },
				{ name: 'action_print', 
					menuConfig: {
						print: {
							name: 'print', text: langs('Печать отчетной формы'),
							handler: win.printCullJournalReport.createDelegate(win)
						}
					}
				}
			],
			stringfields: [
				{ type: 'int', name: 'QcControlSeriesValue_id', key: true },
				{ type: 'int', name: 'QcRule_id', hidden: true },
				{ type: 'int', name: 'QcControlStage_id', hidden: true },
				{ type: 'int', name: 'QcControlSeriesValue_isDisabled', hidden: true },
				{ type: 'string', name: 'QcControlSeriesValue_setDT', hidden: true },
				{ type: 'date', header: langs('Дата'), name: 'setDate', sortable: false, isparams: true, renderer: Ext.util.Format.dateRenderer('d.m.Y'), width: 75,
					editor: new sw.Promed.SwDateField({
						format: 'd.m.Y'
					})
				},
				{ header: langs('Время'), name: 'setTime', sortable: false, isparams: true, renderer: Ext.util.Format.dateRenderer('H:i'), width: 60,
					editor: new sw.Promed.TimeField({
						plugins: [ new Ext.ux.InputTextMask('99:99', false) ]
					})
				},
				{ type: 'string', header: langs('Результат'), name: 'UslugaTest_ResultValue', width: 100 },
				{ type: 'int', header: langs('Контроль пройден'), name: 'QcControlSeriesValue_IsControlPassed',
					renderer: function(value) {
						if(!value) return '';
						return value == 1 ? 'Нет' : 'Да';
					}
				},
				{ type: 'string', header: langs('Правило'), name: 'QcRule_Name', width: 70 },
				{ type: 'string', header: langs('Примечание'), name: 'QcControlSeriesValue_Comment', id: 'autoexpand' }
			],
			checkBeforeLoadData: function(store, options) {
				return !Ext.isEmpty( this.getParam('QcControlSeries_id') );
			}
		});

		//Журнал выбраковок
		win.SvodGrid = new sw.Promed.ViewFrame({
			title: langs('Нарушение правил КК'),
			dataUrl: '/?c=QcControlMaterialValue&m=loadSvodGrid',
			region: 'center',
			layout: 'fit',
			region: 'west',
			autoLoadData: false,
			useEmptyRecord: false,
			toolbar: true,
			border: true,
			width: 450,
			actions: [
				{ name:'action_add', hidden: true },
				{ name:'action_edit', hidden: true },
				{ name:'action_view', hidden: true },
				{ name:'action_delete', hidden: true },
				{ name:'action_print', hidden: true }
			],
			stringfields: [
				{ type: 'int', name: 'QcControlSeriesValue_id', key: true },
				{ type: 'int', name: 'QcRule_id', hidden: true },
				{ type: 'int', name: 'QcControlMaterialValue_id', hidden: true },
				{ type: 'float', name: 'QcControlSeries_Xcp', hidden: true },
				{ type: 'float', name: 'QcControlSeries_S', hidden: true },
				{ type: 'string', header: langs('Методика'), name: 'AnalyzerTest_Name', width: 250 },
				{ type: 'float', header: langs('Результат'), name: 'UslugaTest_ResultValue', width: 75 },
				{ type: 'string', header: langs('Правило'), name: 'QcRule_Name', width: 75 },
				{ type: 'string', name: 'QcControlSeriesValue_setDT', hidden: true }
			],
			onLoadData: function(loaded) {
				win.makeMethodicsChart();
			},
			checkBeforeLoadData: function(store, options) {
				return !Ext.isEmpty( this.getParam('Analyzer_id') );
			}
		});

		win.filterLabOnFirstTab = new sw.Promed.SwMedServiceGlobalCombo({
			fieldLabel: langs('Лаборатория'),
			hiddenName: 'MedService_id',
			listWidth: 300,
			anchor: '100%',
			listeners: {
				change: function(combo, newValue) {
					var form = win.formPanel.getForm();
					if(newValue) {
						win.formPanel.loadForm({ MedService_id: newValue });
						win.RuleLabGrid.setParam('MedService_id', newValue);
						//win.RuleLabGrid.saveParams = { MedService_id: newValue };
						win.RuleLabGrid.setReadOnly(false);
						win.RuleLabGrid.loadData();
					}
					else {
						form.reset();
						win.RuleLabGrid.removeAll();
					}
				}
			}
		});

		win.filterLabOnSecondTab = new sw.Promed.SwMedServiceGlobalCombo({
			fieldLabel: langs('Лаборатория'),
			listWidth: 300,
			anchor: '100%',
			listeners: {
				change: function(combo, newValue) {
					win.filterAnalyzerOnSecondTab.clearValue();
					win.filterAnalyzerOnSecondTab.getStore().removeAll();
					win.MaterialValueGrid.removeAll();
					win.MaterialValueGrid.setParam('MedService_id', newValue);
					win.MaterialValueGrid.setParam('MedService_id', newValue, false);
					if(newValue) {
						win.filterAnalyzerOnSecondTab.getStore().load({ params: { MedService_id: newValue } });
						if(win.MaterialValueGrid.params && win.MaterialValueGrid.params.QcControlMaterial_id) {
							win.MaterialValueGrid.loadData();
						}
					}
				}
			}
		});

		win.filterAnalyzerOnSecondTab = new sw.Promed.SwAnalyzerCombo({
			fieldLabel: langs('Анализатор'),
			allowBlank: true,
			editable: true,
			anchor: '100%',
			separateStore: true,
			listeners: {
				change: function(combo, newValue) {
					delete win.MaterialValueGrid.params.Analyzer_id;
					win.MaterialValueGrid.setParam('Analyzer_id', newValue);
					if(newValue) {
						win.MaterialValueGrid.setParam('Analyzer_id', newValue, false);
					}
					win.MaterialValueGrid.removeAll();
					win.MaterialValueGrid.loadData();
				}
			}
		});

		win.filterLabOnThirdTab = new sw.Promed.SwMedServiceGlobalCombo({
			fieldLabel: langs('Лаборатория'),
			listWidth: 300,
			anchor: '100%',
			listeners: {
				change: function(combo, newValue) {
					win.filterAnalyzerOnThirdTab.clearValue();
					win.filterAnalyzerOnThirdTab.getStore().removeAll();
					win.filterAnalyzerOnThirdTab.fireEvent('select', win.filterAnalyzerOnThirdTab);
					win.ControlSeriesGrid.removeAll();
					win.ControlSeriesValueGrid.removeAll();
					win.CullJournalGrid.removeAll();
					if(newValue) {
						win.filterAnalyzerOnThirdTab.getStore().load({ params: {MedService_id: newValue}});
					}
				}
			}
		});

		win.filterAnalyzerOnThirdTab = new sw.Promed.SwAnalyzerCombo({
			fieldLabel: langs('Анализатор'),
			allowBlank: true,
			editable: true,
			anchor: '100%',
			separateStore: true,
			listeners: {
				select: function(combo, rec) {
					var mValueField = win.filterMaterialValue;
					mValueField.clearValue();
					mValueField.getStore().removeAll();
					mValueField.fireEvent('select', mValueField);
					if(!rec) return;
					var id = rec.get('Analyzer_id');
					if(id) {
						mValueField.getStore().baseParams.Analyzer_id = id;
						mValueField.getStore().load();
					}
				}
			}
		});

		win.filterLabOnFourthTab = new sw.Promed.SwMedServiceGlobalCombo({
			fieldLabel: langs('Лаборатория'),
			listWidth: 300,
			anchor: '100%',
			listeners: {
				change: function(combo, newValue) {
					win.filterAnalyzerOnFourthTab.clearValue();
					win.filterAnalyzerOnFourthTab.getStore().removeAll();
					win.filterAnalyzerOnFourthTab.fireEvent('select', win.filterAnalyzerOnFourthTab);
					win.ControlSeriesGrid.removeAll();
					win.ControlSeriesValueGrid.removeAll();
					win.CullJournalGrid.removeAll();
					if(newValue) {
						win.filterAnalyzerOnFourthTab.getStore().load({ params: {MedService_id: newValue}});
					}
				}
			}
		});

		win.filterAnalyzerOnFourthTab = new sw.Promed.SwAnalyzerCombo({
			fieldLabel: langs('Анализатор'),
			allowBlank: true,
			editable: true,
			anchor: '100%',
			separateStore: true,
			listeners: {
				change: function(combo, newValue) {
					if(!newValue) return;
					win.SvodGrid.setParam('Analyzer_id', newValue);
					win.SvodGrid.loadData();
				}
			}
		});

		win.filterMaterialValue = new sw.Promed.SwBaseLocalCombo({
			valueField: 'QcControlMaterialValue_id',
			displayField: 'AnalyzerTest_Name',
			listWidth: 500,
			tpl: new Ext.XTemplate(
				'<tpl for="."><div class="x-combo-list-item">',
					'<div {[this.getStyle(values.QcControlMaterialValue_endDT)]}>',
						'<div>{AnalyzerTest_Name}&nbsp</div>',
						'<div style="font-size: 10px;">{[!Ext.isEmpty(values.QcControlMaterialValue_endDT) ? "Дата удаления: " + Ext.util.Format.date(values.QcControlMaterialValue_endDT,"d.m.Y"):""]}</div>',
					'</div>',
				'</div></tpl>',
				{
					getStyle: function(values) {
						var cls = 'style="margin:2px;';
						if(values!="")
							cls +='color:#808080;';
						return cls + '"';
					}
				}
			),
			store: new Ext.data.JsonStore({
				autoLoad: false,
				baseParams: {
					'Analyzer_id': null,
					'MedService_id': null
				},
				url: '/?c=QcControlMaterialValue&m=loadCombo',
				key: 'QcControlMaterialValue_id',
				fields: [
					{ name: 'QcControlMaterialValue_id', type: 'int' },
					{ name: 'QcControlMaterialValue_X', type: 'float' },
					{ name: 'QcControlMaterialValue_S', type: 'float' },
					{ name: 'QcControlMaterialValue_CV10', type: 'float' },
					{ name: 'QcControlMaterialValue_CV20', type: 'float' },
					{ name: 'QcControlMaterialValue_B10', type: 'float' },
					{ name: 'QcControlMaterialValue_B20', type: 'float' },
					{ name: 'QcControlMaterial_id', type: 'int' },
					{ name: 'QcControlMaterial_Name', type: 'string' },
					{ name: 'AnalyzerTest_id', type: 'int' },
					{ name: 'AnalyzerTest_Name', type: 'string' },
					{ name: 'Analyzer_id', type: 'int' },
					{ name: 'QcControlMaterial_IsAttested', type: 'int' },
					{ name: 'QcControlMaterial_IsAttestedName',
						convert: function (val, row) {
							return row.QcControlMaterial_IsAttested == 2 ? 'Да' : 'Нет';
						}
					},
					{ name: 'QcControlMaterialType_Name', type: 'string' },
					{ name: 'UslugaComplex_id', type: 'int' },
					{ name: 'QcControlMaterialValue_begDT', type: 'date' },
					{ name: 'QcControlMaterialValue_endDT', type: 'date' }
				]
			}),
			listeners: {
				select: function(combo, rec, idx) {
					win.ControlSeriesGrid.removeAll();
					win.ControlSeriesValueGrid.removeAll();
					win.CullJournalGrid.removeAll();
					win.seriesForm.getForm().reset();
					win.seriesForm.getForm().setValues(combo.getSelectedRecordData());
					var disableAddAction = !rec || !rec.get('QcControlMaterialValue_id') || rec.get('QcControlMaterialValue_endDT');
					win.ControlSeriesGrid.ViewActions.action_add.setDisabled(disableAddAction);
					if(!rec) return;
					var id = rec.get('QcControlMaterialValue_id');
					win.ControlSeriesGrid.removeAll();
					win.ControlSeriesGrid.params = {
						Analyzer_id: rec.get('Analyzer_id'),
						QcControlMaterial_id: rec.get('QcControlMaterial_id'),
						QcControlMaterialValue_id: rec.get('QcControlMaterialValue_id'),
						QcControlMaterialValue_X: rec.get('QcControlMaterialValue_X'),
						QcControlMaterialValue_S: rec.get('QcControlMaterialValue_S'),
						AnalyzerTest_Name: rec.get('AnalyzerTest_Name'),
						callback: function() {
							win.ControlSeriesGrid.loadData();
						}
					}
					if(id) {
						win.ControlSeriesGrid.setParam('QcControlMaterialValue_id', id);
						win.ControlSeriesGrid.loadData();
					}
				}
			}
		});

		win.materialTypeFilter = new sw.Promed.SwCommonSprCombo ({
		//	xtype: 'swcommonsprcombo',
			comboSubject: 'QcControlMaterialType',
			autoLoad: true
		});

		win.materialNameFilter = new Ext.form.TextField({
			width: 100
		});

		win.formPanel = new sw.Promed.FormPanel({
			layout: 'column',
			saveUrl: '/?c=AnalyzerQualityControl&m=doSave',
			url: '/?c=AnalyzerQualityControl&m=loadEditForm',
			defaults: {
				bodyStyle: 'background:#DFE8F6',
				columnWidth: 0.3,
				layout: 'form'
			},
			items: [
				{
					border: false,
					items: win.filterLabOnFirstTab
				},
				{
					labelWidth: 200,
					border: false,
					items: [{
						xtype: 'checkbox',
						fieldLabel: langs('Использовать общие правила КК'),
						name: 'MedService_IsGeneralQcRule',
						listeners: {
							change: function(combo, newValue) {
								win.initRuleLabGrid(newValue);
								win.saveForm();
							}
						}
					}]
				}
			],
			reader: new Ext.data.JsonReader({
					success: function() {
						
					}
				},
				[
					'MedService_id',
					'MedService_IsGeneralQcRule'
				]
			),
			beforeSave: function(params) {
				params.MedService_id = win.filterLabOnFirstTab.getValue();
			},
			onLoad(loaded) {
				var form = win.formPanel.getForm(),
					useLpuRule = form.findField('MedService_IsGeneralQcRule').getValue();
				if(useLpuRule) {
					win.initRuleLabGrid(useLpuRule);
				}
			}
		});

		win.seriesForm = new sw.Promed.FormPanel({
			bodyStyle:'background:#DFE8F6;padding: 5px;width:100%;',
			title: langs('Измерения контрольной серии'),
			saveUrl: '/?c=QcControlSeries&m=doSave',
			collapsible: true,
			//border: true,
			layout: 'column',
			region: 'east',
			//split: true,
			labelAlign: 'right',
			labelWidth: 130,
			items: [
				{
					layout: 'form',
					bodyStyle:'background:#DFE8F6;',
					border: false,
					items: [
						{
							xtype: 'hidden',
							name: 'QcControlStage_id'
						},
						{
							xtype: 'hidden',
							name: 'QcControlSeries_id'
						},
						{
							xtype: 'hidden',
							name: 'QcControlSeries_pid'
						},
						{
							xtype: 'hidden',
							name: 'Analyzer_id'
						},
						{
							xtype: 'hidden',
							name: 'QcControlMaterialValue_id'
						},
						{
							xtype: 'hidden',
							name: 'QcControlMaterial_IsAttested'
						},
						{
							xtype: 'hidden',
							name: 'QcControlSeries_IsControlPassed'
						},
						{
							xtype: 'hidden',
							name: 'NextStage_id'
						},
						{
							xtype: 'textfield',
							fieldLabel: langs('Имя контрльной серии'),
							name: 'QcControlSeries_Name',
							width: 405,
							disabled: true
						},
						{
							xtype: 'textfield',
							fieldLabel: langs('Стадия контроля'),
							name: 'QcControlStage_Name',
							width: 405,
							disabled: true
						},
						{
							
							bodyStyle:'background:#DFE8F6;',
							layout: 'column',
							border: false,
							defaults: {
								labelAlign: 'right',
								height: 90,
								style: 'padding: 5px; margin: 10px 0 0 10px;',
								border: true
							},
							items: [
								{
									xtype: 'fieldset',
									title: langs('Материал'),
									labelWidth: 115,
									columnWidth: 0.6,
									defaults : {
										width: 150
									},
									items: [
										{
											xtype: 'textfield',
											fieldLabel: langs('Наименование'),
											name: 'QcControlMaterial_Name',
											allowBlank: false,
											disabled: true
										},
										{
											xtype: 'textfield',
											fieldLabel: langs('Вид материала'),
											name: 'QcControlMaterialType_Name',
											disabled: true
										},
										{
											xtype: 'textfield',
											fieldLabel: langs('Аттестован'),
											name: 'QcControlMaterial_IsAttestedName',
											disabled: true
										}
									]
								},
								{
									xtype: 'fieldset',
									layout: 'column',
									title: langs('Установочные значения'),
									labelWidth: 40,
									columnWidth: 0.4,
									items: [
										{
											layout: 'form',
											columnWidth: 0.5,
											bodyStyle:'background:#DFE8F6;',
											border: false,
											defaults: {
												width: 70
											},
											items: [
												{
													name: 'QcControlMaterialValue_X',
													xtype: 'numberfield',
													fieldLabel: langs('Xcp'),
													disabled: true,
													anchor: '100%'
												},
												{
													name: 'QcControlMaterialValue_CV10',
													xtype: 'numberfield',
													fieldLabel: langs('CV10'),
													disabled: true,
													anchor: '100%'
												},
												{
													name: 'QcControlMaterialValue_CV20',
													xtype: 'numberfield',
													fieldLabel: langs('CV20'),
													disabled: true,
													anchor: '100%'
												}
											]
										},
										{
											layout: 'form',
											columnWidth: 0.5,
											bodyStyle:'background:#DFE8F6;',
											border: false,
											defaults: {
												width: 70
											},
											items: [
												{
													name: 'QcControlMaterialValue_S',
													xtype: 'numberfield',
													fieldLabel: langs('S'),
													disabled: true,
													anchor: '100%'
												},
												{
													name: 'QcControlMaterialValue_B10',
													xtype: 'numberfield',
													fieldLabel: langs('B10'),
													disabled: true,
													anchor: '100%'
												},
												{
													name: 'QcControlMaterialValue_B20',
													xtype: 'numberfield',
													fieldLabel: langs('B20'),
													disabled: true,
													anchor: '100%'
												}
											]
										}
									]
								}
							]
						}
					]
				},
				{
					title: langs('Расчетные'),
					style: 'margin-left: 10px; padding: 5px;',
					xtype: 'fieldset',
					layout: 'column',
					autoHeight: true,
					autoWidth: false,
					labelWidth: 40,
					items: [
						{
							layout: 'form',
							bodyStyle:'background:#DFE8F6;',
							border: false,
							defaults: {
								width: 70
							},
							items: [
								{
									name: 'QcControlSeries_Xcp',
									xtype: 'numberfield',
									fieldLabel: 'Xcp',
									decimalPrecision: 10,
									readOnly: true
								},
								{
									name: 'QcControlSeries_S',
									xtype: 'numberfield',
									fieldLabel: 'S',
									decimalPrecision: 10,
									readOnly: true
								},
								{
									name: 'QcControlSeries_CV',
									xtype: 'numberfield',
									fieldLabel: 'CV',
									decimalPrecision: 10,
									readOnly: true
								},
								{
									name: 'QcControlSeries_B',
									xtype: 'numberfield',
									fieldLabel: 'B',
									decimalPrecision: 10,
									readOnly: true
								}
							]
						}
					]
				}
			],
			afterSave: function() {
				win.seriesForm.getForm().findField('NextStage_id').setValue(null);
				win.ControlSeriesGrid.loadData();
			}
		});

		win.btnCalcStage = new Ext.Button({
			text: langs('Провести расчет для стадии'),
			disabled: true,
			handler: win.calculateStage.createDelegate(win)
		});

		win.btnNextStage = new Ext.Button({
			text: langs('Перейти на следующую стадию'),
			disabled: true,
			handler: win.goToNextStage.createDelegate(win)
		});

		//Главная форма с вкладками
		win.TabPanel = new Ext.TabPanel({
			deferredRender: false,
			activeTab: 0,
			border: false,
			items: [
				//правила КК
				new Ext.TabPanel({
					deferredRender: false,
					title: langs('Правила КК'),
					activeTab: 0,
					items: [
						{
							title: langs('Общие'),
							layout: 'border',
							border: false,
							items: win.RuleLpuGrid
						},
						{
							title: langs('По лабораториям'),
							layout: 'border',
							tbar: win.formPanel,
							items: win.RuleLabGrid
						}
					]
				}),
				//Контрольные материалы
				{
					title: langs('Контрольные материалы'),
					layout: 'anchor',
					tbar: [
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Тип материала:')
						},
						win.materialTypeFilter,
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Наименование:')
						},
						win.materialNameFilter,
						new Ext.Button({
							text: langs('Найти'),
							handler: function() {
								win.MaterialGrid.loadData();
							}
						}),
						new Ext.Button({
							text: langs('Сброс'),
							handler: function() {
								win.materialTypeFilter.clearValue();
								win.materialNameFilter.setValue(null);
								
							}
						})
					],
					items: [
						//панель с гридом
						{
							layout: 'border',
							anchor: '100% 50%',
							title: langs('Контрольные материалы'),
							border: false,
							items: [
								win.MaterialGrid
							]
						},
						//панель фильтров
						{
							layout: 'border',
							anchor: '100% 50%',
							title: langs('Методики'),
							border: false,
							items: [
								{
									layout: 'column',
									region: 'north',
									border: false,
									bodyStyle:'width:100%;background:#DFE8F6;padding:1px;padding-top:4px;',
									defaults: {
										layout: 'form',
										labelAlign: 'right',
										labelWidth: 100,
										border: false,
										bodyStyle: 'background:#DFE8F6',
										style: 'margin-left: 10px;'
									},
									items: [
										{
											columnWidth: 0.3,
											items: win.filterLabOnSecondTab
										},
										{
											columnWidth: 0.3,
											items: win.filterAnalyzerOnSecondTab
										},
										{
											style: 'margin-left: 10px;',
											items: new Ext.Button({
												text: langs('Найти'),
												handler: function() {
													win.MaterialValueGrid.loadData();
												}
											})
										},
										{
											items: new Ext.Button({
												text: langs('Сброс'),
												handler: function() {
													if(win.fullAccess) {
														win.filterLabOnSecondTab.clearValue();
														win.filterLabOnSecondTab.fireEvent('change', win.filterAnalyzerOnSecondTab, null);
													}
													win.filterAnalyzerOnSecondTab.clearValue();
													win.filterAnalyzerOnSecondTab.fireEvent('change',win.filterAnalyzerOnSecondTab, null);
												}
											})
										}
									]
								},
								win.MaterialValueGrid
							]
						}
					]
				},
				//Контрольные серии
				{
					title: langs('Контрольные серии'),
					layout: 'border',
					tbar: [
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Лаборатория:')
						},
						win.filterLabOnThirdTab,
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Анализатор:')
						},
						win.filterAnalyzerOnThirdTab,
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Методика:')
						},
						win.filterMaterialValue,
						new Ext.Button({
							text: langs('Найти'),
							handler: function() {
								win.filterMaterialValue.getStore().load();
							}
						}),
						new Ext.Button({
							text: langs('Сброс'),
							handler: function() {
								if(win.fullAccess) {
									win.filterLabOnThirdTab.clearValue();
									win.filterLabOnThirdTab.fireEvent('change', win.filterAnalyzerOnThirdTab, null);
								} else {
									win.filterAnalyzerOnThirdTab.clearValue();
									win.filterAnalyzerOnThirdTab.fireEvent('select', win.filterAnalyzerOnThirdTab);
								}
							}
						})
					],
					items: [
						new Ext.Panel({
							height: 200,
							layout: 'border',
							region: 'north',
							title: langs('Контрольные серии'),
							collapsible: true,
							animate: false,
							border: false,
							items: [
								win.ControlSeriesGrid,
								win.seriesForm
							]
						}),
						new Ext.TabPanel({
							split: true,
							height: 200,
							region: 'center',
							deferredRender: false,
							tbar: [
								win.btnCalcStage,
								win.btnNextStage,
								{
									xtype: 'label',
									text: 'Период:'
								},
								new Ext.Button({
									text: '<',
									handler: function() {
										win.periodBtnClick('<');
									}
								}),
								win.filterSeries,
								new Ext.Button({
									text: '>',
									style: 'margin-left: 187px;', //комбик без размера?
									handler: function() {
										win.periodBtnClick('>');
									}
								})
							],
							activeTab: 0,
							items: [
								{
									layout: 'border',
									title: langs('Журнал измерений'),
									items: win.ControlSeriesValueGrid
								},
								{
									title: langs('Контрольная карта'),
									layout: 'border',
									border: false,
									items: [
										{
											region: 'center',
											border: false,
											html: "<div id='"+win.id+"SeriesChart' style='height: 100%;'></div>"
										}
									],
									listeners: {
										activate: function() {
											win.makeSeriesChart();
										}
									}
								},
								{
									layout: 'border',
									title: langs('Журнал выбраковок'),
									items: win.CullJournalGrid
								}
							]
						})
					]
				},
				//Сводная информация
				{
					title: langs('Сводная информация'),
					layout: 'border',
					tbar: [
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Лаборатория:')
						},
						win.filterLabOnFourthTab,
						{
							xtype: 'label',
							style: 'margin-left: 10px;',
							text: langs('Анализатор:')
						},
						win.filterAnalyzerOnFourthTab,
						new Ext.Button({
							text: langs('Найти'),
							handler: function() {
								win.SvodGrid.loadData();
							}
						}),
						new Ext.Button({
							text: langs('Сброс'),
							handler: function() {
								if(win.fullAccess) {
									win.filterLabOnFourthTab.clearValue();
									win.filterLabOnFourthTab.fireEvent('change', win.filterAnalyzerOnFourthTab, null);
								} else {
									win.filterAnalyzerOnFourthTab.clearValue();
									win.filterAnalyzerOnFourthTab.fireEvent('select', win.filterAnalyzerOnFourthTab);
									win.SvodGrid.removeAll();
									win.makeMethodicsChart();
								}
							}
						})
					],
					items: [
						win.SvodGrid,
						{
							title: langs('Графический компонент'),
							region: 'center',
							html: "<div id='"+win.id+"MethodicsChart' style='height: 100%;'></div>"
						}
					]
				}
			],
			listeners: {
				resize: function() {
					win.TabPanel.doLayout();
				}
			}
		});

		//кнопки формы
		win.buttons = [
			'-',
			{
				text     : BTN_FRMHELP,
				tabIndex : -1,
				//tooltip  : BTN_FRMHELP_TIP,
				iconCls  : 'help16',
				handler  : function() {
					ShowHelp(win.title);
				}
			},
			{
				text   : BTN_FRMCLOSE,
				tabIndex : -1,
				//tooltip: BTN_FRMCLOSE_TIP,
				iconCls: 'cancel16',
				handler: function() {
					win.hide();
				}
			}
		];

		Ext.apply(win, { items: win.TabPanel });
		sw.Promed.swAnalyzerQualityControlWindow.superclass.initComponent.apply(this, arguments);
	},

	goToNextStage: function() {
		var win = this,
			series = win.getSeriesParams(),
			stage = series.QcControlStage_id;

		if(stage.inlist([ win.stageConvergence, win.stageInstallationSeries])) {
			stage++;
		}

		win.seriesForm.getForm().findField('NextStage_id').setValue(stage);
		win.seriesForm.saveForm();
	},

	initRuleLabGrid: function(useLpuRule) {
		var win = this;
		if(useLpuRule) {
			win.RuleLabGrid.setReadOnly(true);
			win.RuleLpuGrid.getGrid().getStore().each( function (lpuRuleRec) {
				var labRuleStore = win.RuleLabGrid.getGrid().getStore(),
					labRuleRec = labRuleStore.getById(lpuRuleRec.get('QcRule_id'));
				labRuleRec.set('isOn', lpuRuleRec.get('isOn'));
				labRuleRec.set('QcRuleLab_isStrong', lpuRuleRec.get('QcRuleLpu_isStrong'));
				labRuleRec.commit();
			});
		} else {
			win.RuleLabGrid.setReadOnly(false);
			win.RuleLabGrid.loadData();
		}

	},

	//журнал измерений: кнопки '<' '>'
	periodBtnClick: function(btn) {
		var win = this,
			store = win.filterSeries.getStore(),
			pos = store.indexOfId(win.filterSeries.getValue());
		if(btn == '<') {
			pos++; //движение по списку вниз
		} else {
			pos--; //вверх
		}

		var rec = store.getAt(pos);
		if(!rec || !rec.get('QcControlSeries_id')) return;
		var value = rec.get('QcControlSeries_id');
		win.filterSeries.setValue(value);
		win.filterSeries.fireEvent('change',win.filterSeries, value);
	},

	//расчет для стадии
	calculateStage: function () {
		var win = this,
			formParams = win.getSeriesParams(),
			form = win.seriesForm.getForm(),
			params = {
				QcControlSeries_pid: formParams.QcControlSeries_pid,
				QcControlSeries_id: formParams.QcControlSeries_id,
				QcControlStage_id: formParams.QcControlStage_id
			};

		if(!win.validateBeforeCalculate()) return;

		Ext.Ajax.request({
			url: '/?c=QcControlSeries&m=calculateForStage',
			params: params,
			callback: function(options, success, response) {
				var resp_obj = response.responseText ? Ext.util.JSON.decode(response.responseText) : false;
				if(!resp_obj || resp_obj.Error_Code || resp_obj.Error_Msg || !success || !resp_obj.success) {
					if(isDebug() && resp_obj.Error_Msg) {
						sw.swMsg.alert( 'Ошибка', resp_obj.Error_Msg );
					} else {
						sw.swMsg.alert( 'Ошибка', 'При сохранении произошла ошибка' );
					}
					return;
				}
				if(resp_obj.success) {
					delete resp_obj.success;
					win.afterCalculate(resp_obj);
					win.toogleDisabledStageBtn();
				}
			}
		})
	},

	//логика после расчета для стадии
	afterCalculate: function(resp) {
		var win = this,
			series = win.getSeriesParams();
		var title = langs('Сообщение');
		var msg = resp.msg;
		var passed = !resp.msg;
		var stage = series.QcControlStage_id;
		var form = win.seriesForm.getForm();

		msg = !msg ? langs('Оценка пройдена') : langs('Оценка не пройдена');

		msg += '. ';

		if(passed) {
			msg += langs('Показатель CV = ') + resp.QcControlSeries_CV;
			if(!Ext.isEmpty(resp.QcControlSeries_B)) {
				msg +=  ', ' + langs('показатель B = ') + resp.QcControlSeries_B;
			}
			msg += '. ';
		}

		var showMessage = function(message, isConfirm, btnNo) {
			if(isConfirm) {
				sw.swMsg.confirm(
					title,
					message,
					function(btn) {
						if ( btn == 'yes' ) {
							form.findField('NextStage_id').setValue(stage);
							form.setValues(resp);
							win.seriesForm.saveForm();
						}
						if ( btnNo && btn === 'no') {
							form.findField('NextStage_id').setValue(stage);
							win.seriesForm.saveForm();
						}
					}
				);
			} else {
				sw.swMsg.alert( title, message );
			}
		}

		switch(true) {

			case stage == win.stageQualityControl:
				//todo: Что делать если контроль не пройден?
				msg += langs('Построить новую контрольную карту?');
				showMessage(msg, true, true);
				break;

			case stage == win.stageConvergence:
				if(passed) {
					form.setValues(resp);
					win.seriesForm.saveForm();
					showMessage(msg);
				} else {
					msg += langs('Начать серию заново?');
					showMessage(msg, true);
				}
				break;

			case stage == win.stageInstallationSeries:
				//todo: Что делать если контроль не пройден?
				form.setValues(resp);
				win.seriesForm.saveForm();
				showMessage(msg);
				break;

		}
	},

	//поиск пустых значений и не сохраненных результатов
	validateBeforeCalculate: function() {
		var records = this.ControlSeriesValueGrid.getGrid().getStore().data.items;
		if(!records.length) {
			sw.swMsg.alert(langs('Сообщение'),langs('Список пуст'));
			return false;
		}

		for(var i = 0; i < records.length; i++) {
			rec = records[i];
			var value = rec.get('UslugaTest_ResultValue'),
				date = rec.get('setDate'),
				time = rec.get('setTime');

			if(!value && value != 0 || !date || !time || rec.dirty) {
				sw.swMsg.alert(langs('Сообщение'),langs('Сохраните результаты'));
				return false;
			}
		}
		return true;
	},

	getSeriesParams: function() {
		return this.filterSeries.getSelectedRecordData()
	},

	getMaterialValueParams: function() {
		return this.filterMaterialValue.getSelectedRecordData();
	},

	//грид журнал измерений экшн удалить
	actionDisableSeriesValueGrid: function () {
		var win = this,
			viewframe = win.ControlSeriesValueGrid,
			store = viewframe.getGrid().getStore(),
			record = viewframe.getGrid().getSelectionModel().getSelected();

		if(!record) return;

		var id = record.get('QcControlSeriesValue_id');

		if(!id) {
			store.remove(record);
			return;
		}

		var params = {
			QcControlSeriesValue_id: record.get('QcControlSeriesValue_id'),
			callback: function () {
				win.ControlSeriesGrid.loadData();
			}
		};

		getWnd('swQcControlSeriesValueDisWindow').show(params)
	},

	//грид материалы,методики,серии экшн удалить
	actionDisable: function () {
		var viewframe = this,
			grid = viewframe.ViewGridPanel;

		if(!grid) return;

		var record = grid.getSelectionModel().getSelected();

		if(!record) return;

		var objectName = viewframe.object,
			keyField = objectName + '_id',
			keyValue = record.get(keyField),
			params = new Object(),
			requestParam = new Object(),
			loadMask = new Ext.LoadMask(viewframe.getEl(), {msg:langs('Удаление...')});

		params[keyField] = keyValue;

		requestParam.url = "/?c=" + objectName + "&m=doDisable";
		requestParam.params = params;
		requestParam.failure = function(response, options) {
			loadMask.hide();
			Ext.Msg.alert(langs('Ошибка'), langs('При удалении произошла ошибка!'));
		};
		requestParam.success = function(response, action) {
			loadMask.hide();
			if (response.responseText) {
				var answer = Ext.util.JSON.decode(response.responseText);
				if (!answer.success) {
					if ( !answer.Error_Msg ) {
						Ext.Msg.alert(langs('Ошибка'), answer.Error_Msg);
					}
				} else {
					grid.getStore().reload();
					if (viewframe.afterDeleteRecord) {
						viewframe.afterDeleteRecord({object:viewframe.object, id:id, answer:answer});
					}
				}
			} else {
				Ext.Msg.alert(langs('Ошибка'), langs('Ошибка при удалении! Отсутствует ответ сервера.'));
			}
		};

		sw.swMsg.show({
			icon: Ext.MessageBox.QUESTION,
			msg: viewframe.disableMsg,
			title: langs('Подтверждение'),
			buttons: {
				ok: true, cancel: true
			},
			fn: function(buttonId, text, obj) {
				if ('ok' == buttonId) {
					loadMask.show();
					Ext.Ajax.request(requestParam);
				}
			}
		});
	},

	getResultCountForCalc: function() {
		var win = this,
			series = win.getSeriesParams();

		switch( series['QcControlStage_id'] ) {
			case win.stageConvergence:
				return 10;
			case win.stageInstallationSeries:
				return 10;
			case win.stageQualityControl:
				return 30;
		}
	},

	getResultCountForNextStage: function(valuesCount) {
		var win = this,
			seriesForm = win.seriesForm.getForm(),
			series = win.getSeriesParams();

		switch( series['QcControlStage_id'] ) {
			case win.stageConvergence:
				return 10;
			case win.stageInstallationSeries:
				var count = valuesCount < 10 ? 10 : 20,
					cv = parseFloat(seriesForm.findField('QcControlSeries_CV').getValue()),
					b = parseFloat(seriesForm.findField('QcControlSeries_B').getValue()),
					isAttested = seriesForm.findField('QcControlMaterial_IsAttested') == 2,
					CV = parseFloat(seriesForm.findField('QcControlMaterialValue_CV'+count).getValue()),
					B = parseFloat(seriesForm.findField('QcControlMaterialValue_B'+count).getValue());

				if(!isAttested ) {
					return cv < CV && count == 20 ? 20 : 10;
				}
				else {
					return cv < CV && b < B && count == 20 ? 20 : 10;
				}
			case win.stageQualityControl:
				return 30;
		}
	},

	setDisabledSeriesValueGridActions: function() {
		var win = this,
			combo = win.filterSeries,
			value = combo.getValue(),
			seriesValueGrid = win.ControlSeriesValueGrid,
			seriesValueRec = seriesValueGrid.getGrid().getSelectionModel().getSelected(),
			comboRec = combo.getStore().getById(value),
			isDisabledActionAdd = true,
			isDisabledActionDel = true,
			isQcStage = false;

		if(comboRec) {
			var isEndedSeries = !Ext.isEmpty(comboRec.get('QcControlSeries_endDT'));
			isQcStage = comboRec.get('QcControlStage_id') == win.stageQualityControl;
			isDisabledActionAdd = isEndedSeries;
			isDisabledActionDel = isEndedSeries;
		}

		if(isQcStage && seriesValueRec && !isDisabledActionDel) {
			var id = seriesValueRec.get('QcControlSeriesValue_id'),
				lastRec = seriesValueGrid.getLastRec();
			if(id && lastRec) {
				var lastId = lastRec.get('QcControlSeriesValue_id');
				isDisabledActionDel = id != lastId;
			}
		}

		seriesValueGrid.ViewActions.action_add.setDisabled(isDisabledActionAdd);
		seriesValueGrid.ViewActions.action_disable.setDisabled(isDisabledActionDel);
	},

	toogleDisabledStageBtn: function() {
		var win = this,
			form = win.seriesForm.getForm(),
			seriesRec = win.filterSeries.getSelectedRecordData(),
			isControlPassed = form.findField('QcControlSeries_IsControlPassed').getValue() == 2,
			count = win.ControlSeriesValueGrid.getCount();

		win.btnNextStage.setDisabled(true);
		win.btnCalcStage.setDisabled(true);

		//если серия закончилась, то кнопки недоступны
		if( seriesRec['QcControlSeries_endDT'] ) { 
			return;
		}


		//если контроль пройден и есть нужное количество результатов, то кнопка перехода доступна
		if(isControlPassed && count == win.getResultCountForNextStage(count)) {
			win.btnNextStage.setDisabled(false);
			return;
		}

		
		var btnEnableCount = win.getResultCountForCalc();
		//если количество результатов больше положенного, то кнопка расчета доступна
		if(count >= btnEnableCount) {
			win.btnCalcStage.setDisabled(false);
		}
	},

	getRptFileName: function(name) {
		if(Ext.globalOptions.lis.use_postgresql_lis) {
			name += '_pg';
		}
		return name + '.rptdesign';
	},

	printControlSeriesReport: function() {
		var win = this,
			form = win.seriesForm.getForm(),
			QcControlStage_id = form.findField('QcControlStage_id').getValue(),
			QcControlSeries_id = form.findField('QcControlSeries_id').getValue(),
			QcControlSeries_Xcp = form.findField('QcControlSeries_Xcp').getValue(),
			params = {
				Report_Format: 'pdf',
				Report_Params: '&QcControlSeries_id='+QcControlSeries_id
			};

		if(!QcControlSeries_Xcp) {
			sw.swMsg.alert(langs('Сообщение'), langs('Необходимо выполнить расчет'));
			return;
		}
		switch(parseInt(QcControlStage_id)) {
			case 1:
				params.Report_FileName = win.getRptFileName('QcRepeatableStage');
				break;
			case 2:
				params.Report_FileName = win.getRptFileName('QcInstallationSeriesStage');
				break;
			case 3:
				params.Report_FileName = win.getRptFileName('QcQualityControlStage');
				break;
		}
		printBirt(params);
	},

	printCullJournalReport: function() {
		var win = this,
			series = win.getSeriesParams(),
			params = {
				Report_Format: 'pdf',
				Report_FileName: win.getRptFileName('QcCullJournal'),
				Report_Params: '&QcControlSeries_id='+series.QcControlSeries_id
			};
		printBirt(params);
	}
});
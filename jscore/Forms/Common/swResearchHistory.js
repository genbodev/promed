/**
 * swResearchHistory - История исследований.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 * @package      Assistant
 * @access       public
 * @copyright    Copyright (c) 2009-2014 Swan Ltd.
 * @author       
 * @version      
 */
sw.Promed.swResearchHistory = Ext.extend(sw.Promed.BaseForm, {
	width: 900,
	height: 500,
	modal: true,
	plain: true,
	autoScroll: true,
	draggable: true,
	formParams: null,
	resizable: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	title: 'История исследований',
	id: 'swResearchHistory',
	listeners: {
		hide: function(wnd) {
			wnd.accordion.removeAll();
		}
	},

	show: function () {
		sw.Promed.swResearchHistory.superclass.show.apply(this, arguments);
		var wnd = this;
		if (!arguments[0] || !arguments[0].Codes) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function () { wnd.hide(); }
			});
		}
		this.focus(); this.center();

		wnd.personStore.baseParams = {
			EvnLabSample_id: arguments[0].EvnLabSample_id
		};
		wnd.testStore.baseParams = {
			EvnLabSample_id: arguments[0].EvnLabSample_id,
			Codes: arguments[0].Codes
		};

		wnd.personStore.load();
		wnd.testStore.load();
		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		loadMask.show();
	},

	initComponent: function () {
		var wnd = this;

		this.accordion = new Ext.Panel({
			id: 'q-accordion',
			layout:'fit',
			activeOnTop: false,
			animate: false,
			autoHeight: true,
			autoScroll: true,
			header: false,
			layoutConfig: {
				titleCollapse: false,
				animate: false,
				activeOnTop: false
			}
		});

		this.personStore = new Ext.data.Store({
			url: '?c=EvnLabSample&m=getPersonBySample',
			autoLoad: false,
			reader: new Ext.data.JsonReader({}, [
				{ name: 'Sex_id', type: 'int' },
				{ name: 'Person_Fio', type: 'string' },
				{ name: 'Person_Age', type: 'int' }
			]),
			listeners: {
				load: function(store, records) {
					var person = records[0].data;
					wnd.setTitle([
						'История исследований',
						'-',
						person.Person_Fio,
						[
							'(Пол: ',
							person.Sex_id === 1 ? 'М' : 'Ж',
							', Возраст: ',
							person.Person_Age,
							')'
						].join('')
					].join(' '));
				}
			}
		});

		this.testStore = new Ext.data.Store({
			url: '/?c=EvnLabSample&m=loadResearchHistory',
			autoLoad: true,
			reader: new Ext.data.JsonReader({}, [
				{ name: 'UslugaComplex_id', type: 'int' },
				{ name: 'UslugaTest_ResultValue', type: 'string' },
				{ name: 'UslugaTest_ResultUnit', type: 'string' },
				{ name: 'UslugaTest_RefValues', type: 'string' },
				{ name: 'UslugaTest_CheckDT', type: 'string' },
				{ name: 'UslugaTest_Comment', type: 'string' },
				{ name: 'Lpu_Nick', type: 'string' },
				{ name: 'UslugaComplex_Name', type: 'string' },
				{ name: 'UslugaTestHistory_Count', type: 'int' },
				{ name: 'UslugaTest_id', type: 'int' }
			]),
			listeners: {
				load: function(store, records) {
					if (store.getTotalCount() === 0) {
						wnd.hide();
						return;
					}
					wnd.fillMainPanel(records);
				}
			}
		});

		this.dateMenu = new Ext.form.DateRangeFieldAdvanced({
			width: 160,
			showApply: false,
			id:'dateMenu',
			fieldLabel: langs('Период'),
			plugins: [
				new Ext.ux.InputTextMask('99.99.9999 - 99.99.9999', false)
			]
		});

		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text: langs('Обновить'),
					handler: function() { wnd.reloadGrid(); }
				}, {
					text: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: function () { wnd.hide(); }
				}
			],
			items: [
				this.dateMenu,
				this.accordion
			]
		});
		sw.Promed.swResearchHistory.superclass.initComponent.apply(this, arguments);
	},

	openLabResearchResultHistory: function(ut_id) {
		getWnd('swLabResearchResultHistoryWindow').show({
			UslugaTest_id: ut_id
		});
	},

	getMinMaxDate: function(dateList) {
		var minDate = "", maxDate = ""
		for (var i = 0; i < dateList.length; i++) {
			var match = dateList[i].match(/(\d+)/g);
			var currentDate = Date.parse([
				match[1],
				match[0],
				match[2]
			].join(' '));
			if (i === 0) {
				minDate = currentDate;
				maxDate = currentDate;
			}

			if (minDate > currentDate) minDate = currentDate;
			if (maxDate < currentDate) maxDate = currentDate;
		}
		return {
			min: minDate,
			max: maxDate
		};
	},

	fillMainPanel: function(records) {
		var wnd = this;
		wnd.accordion.removeAll();
		var currentTest = "",
			testList = {},
			dateList = [];
		for (var i = 0; i < records.length; i++) {
			var test = records[i].data;
			if (currentTest !== test.UslugaComplex_id) {
				currentTest = test.UslugaComplex_id;
				testList[currentTest] = [];
			}
			testList[currentTest].push({
				UslugaTest_ResultValue: test.UslugaTest_ResultValue,
				UslugaTest_ResultUnit: test.UslugaTest_ResultUnit,
				UslugaTest_RefValues: test.UslugaTest_RefValues,
				UslugaTest_CheckDT: test.UslugaTest_CheckDT,
				Lpu_Nick: test.Lpu_Nick,
				UslugaTest_Comment: test.UslugaTest_Comment,
				UslugaComplex_Name: test.UslugaComplex_Name,
				UslugaTestHistory_Count: test.UslugaTestHistory_Count,
				UslugaTest_id: test.UslugaTest_id
			});
			dateList.push(test.UslugaTest_CheckDT);
		}

		var date = wnd.getMinMaxDate(dateList);
		wnd.dateMenu.setValue([
			Ext.util.Format.date(date.min, 'd.m.Y'),
			'-',
			Ext.util.Format.date(date.max, 'd.m.Y')
		].join(' '));

		for (var key in testList) {
			var store = new Ext.data.Store({
				reader: new Ext.data.JsonReader({
						id: 'id'
					}, [
						{ mapping: 'UslugaTest_ResultValue', name: 'UslugaTest_ResultValue', type: 'string' },
						{ mapping: 'UslugaTest_ResultUnit', name: 'UslugaTest_ResultUnit', type: 'string' },
						{ mapping: 'UslugaTest_RefValues', name: 'UslugaTest_RefValues', type: 'string' },
						{ mapping: 'UslugaTest_CheckDT', name: 'UslugaTest_CheckDT', type: 'string' },
						{ mapping: 'Lpu_Nick', name: 'Lpu_Nick', type: 'string' },
						{ mapping: 'UslugaTest_Comment', name: 'UslugaTest_Comment', type: 'string' },
						{ mapping: 'UslugaTestHistory_Count', name: 'UslugaTestHistory_Count', type: 'int' },
						{ mapping: 'UslugaTest_id', name: 'UslugaTest_id', type: 'int' }
					]
				)
			});
			store.loadData(testList[key]);

			var panel = new Ext.Panel({
				id: 'q-pnl-' + key,
				title: testList[key][0].UslugaComplex_Name,
				autoHeight: true,
				collapsible: true, 
				layout: 'fit',
				listeners: {
					'render': function(panel) {
						if (panel.header) {
							panel.header.on({
								'click': {
									fn: this.toggleCollapse,
									scope: panel
								}
							});
						}
					}
				}
			});
			wnd.accordion.add(panel); wnd.doLayout();

			var grid = new Ext.grid.GridPanel({
				store: store,
				autoHeight: true,
				columns: [{
						header: "№",
						dataIndex: 'serial',
						renderer: function(value, metaData, record, rowIndex) {
							return rowIndex + 1;
						}
					},
					{ header: "Результат", dataIndex: 'UslugaTest_ResultValue' },
					{ header: "Ед. измерения", dataIndex: 'UslugaTest_ResultUnit' },
					{ header: "Реф. значения", dataIndex: 'UslugaTest_RefValues' },
					{ header: "Дата одобрения", dataIndex: 'UslugaTest_CheckDT' },
					{ header: "Комментарий", dataIndex: 'UslugaTest_Comment' },
					{ header: "МО проведения", dataIndex: 'Lpu_Nick' },
					{
						header: "История изменений",
						dataIndex: 'chanheHistory',
						renderer: function(value, metaData, record, rowIndex) {
							if (record.get('UslugaTestHistory_Count') == 0) return;
							return '<a href="#">Изменений: ' + record.get('UslugaTestHistory_Count') + '</a>';
						}
					}
				],
				listeners: {
					cellclick: function(grid, rowIndex, colIndex) {
						if (colIndex !== 7) return;
						var record = grid.getStore().getAt(rowIndex);
						wnd.openLabResearchResultHistory(record.get('UslugaTest_id'));
					}
				}
			});
			panel.add(grid); panel.doLayout();
		}

		wnd.getLoadMask().hide();
	},

	reloadGrid: function() {
		var wnd = this;
		var minDate = Ext.util.Format.date(wnd.dateMenu.getValue1(), 'Y-m-d');
		var maxDate = Ext.util.Format.date(wnd.dateMenu.getValue2(), 'Y-m-d');

		if (minDate == "" || maxDate == "") {
			return;
		}

		wnd.testStore.baseParams.MinDate = minDate + ' 00:00:00';
		wnd.testStore.baseParams.MaxDate = maxDate + ' 23:59:59';

		var loadMask = new Ext.LoadMask(wnd.getEl(), { msg: "Подождите..." });
		loadMask.show();
		wnd.testStore.load();
	}
});

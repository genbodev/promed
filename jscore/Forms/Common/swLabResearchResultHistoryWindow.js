/**
 * swLabResearchResultHistoryWindow - Изменение результатов лабораторного исследования.
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
sw.Promed.swLabResearchResultHistoryWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 600,
	height: 300,
	modal: true,
	plain: true,
	autoScroll: true,
	draggable: true,
	formParams: null,
	resizable: false,
	buttonAlign: 'left',
	closeAction: 'hide',
	title: 'Изменение результатов лабораторного исследования',
	id: 'swLabResearchResultHistoryWindow',
	listeners: {
		hide: function(wnd) {
		}
	},

	show: function () {
		sw.Promed.swLabResearchResultHistoryWindow.superclass.show.apply(this, arguments);
		var wnd = this;
		if (!arguments[0] || !arguments[0].UslugaTest_id) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function () { wnd.hide(); }
			});
		}
		this.focus(); this.center();

		this.HistoryStore.baseParams = {
			UslugaTest_id: arguments[0].UslugaTest_id
		};
		this.HistoryStore.load();
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите..." });
		loadMask.show();
	},

	initComponent: function () {
		var wnd = this;

		this.HistoryStore = new Ext.data.Store({
			id: 'HistoryStore',
			autoLoad: false,
			url: '/?c=EvnLabSample&m=loadLabResearchResultHistory',
			reader: new Ext.data.JsonReader({
				id: 'id'
			}, [
				{ mapping: 'UslugaTestHistory_id', name: 'UslugaTestHistory_id', type: 'int' },
				{ mapping: 'UslugaTest_id', name: 'UslugaTest_id', type: 'int' },
				{ mapping: 'UslugaComplex_Name', name: 'UslugaComplex_Name', type: 'string' },
				{ mapping: 'UslugaTestHistory_Result', name: 'UslugaTestHistory_Result', type: 'string' },
				{ mapping: 'UslugaTestHistory_Comment', name: 'UslugaTestHistory_Comment', type: 'string' },
				{ mapping: 'UslugaTestHistory_CheckDT', name: 'UslugaTestHistory_CheckDT', type: 'string' },
				{ mapping: 'Person_Fio', name: 'Person_Fio', type: 'string' }
			]),
			listeners: {
				load: function(store) {
					wnd.setTitle('Изменение результатов лабораторного исследования - ' + store.getAt(0).get('UslugaComplex_Name'));
					wnd.getLoadMask().hide();
				}
			}
		});

		this.HistoryGrid = new Ext.grid.GridPanel({
			store: this.HistoryStore,
			autoWidth: true,
			autoHeight: true,
			columns: [{
					header: "№",
					width: 30,
					dataIndex: 'serial',
					renderer: function(value, metaData, record, rowIndex) {
						return rowIndex + 1;
					}
				},
				{ header: "Дата одобрения", dataIndex: 'UslugaTestHistory_CheckDT', width: 120 },
				{ header: "Результат", dataIndex: 'UslugaTestHistory_Result' },
				{ header: "Комментарий", dataIndex: 'UslugaTestHistory_Comment' },
				{ header: "ФИО", dataIndex: 'Person_Fio' }
			]
		});

		Ext.apply(this, {
			buttons: [{
					text: '-'
				}, {
					text: langs('Закрыть'),
					iconCls: 'cancel16',
					handler: function () { wnd.hide(); }
				}
			],
			items: [
				this.HistoryGrid
			]
		});
		sw.Promed.swLabResearchResultHistoryWindow.superclass.initComponent.apply(this, arguments);
	}
});

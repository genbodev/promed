/**
 * swRegistryHistoryViewWindow - История изменений по реестрам
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common.Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('common.Admin.swRegistryHistoryViewWindow', {
	/* свойства */
	alias: 'widget.swRegistryHistoryViewWindow',
    autoShow: false,
	closable: true,
	cls: 'arm-window-new',
	constrain: true,
	extend: 'base.BaseForm',
	findWindow: false,
    header: true,
	layout: 'border',
	maximized: true,
	refId: 'registryhistoryviewsw',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	resizable: false,
	title: 'Работа с реестрами',
	width: 1000,

	/* методы */
	doReset: function () {
		var base_form = this.FilterPanel.getForm();
		base_form.reset();
	},
	doSearch: function () {
		var
			base_form = this.FilterPanel.getForm(),
			params = base_form.getValues();

		params.limit = this.MasterGridStore.pageSize;
		params.start = 0;

		this.MasterGrid.getStore().removeAll();
		this.DetailGrid.getStore().removeAll();
		Ext6.apply(this.MasterGrid.getStore().proxy.extraParams, params);
		this.MasterGrid.getStore().load();
	},
	onDetailDblClick: function() {
		var win = this;

		if ( win.DetailGrid.getSelectionModel().hasSelection()) {
			var record = win.DetailGrid.getSelectionModel().getSelection()[0];

			if ( !Ext6.isEmpty(record.get('RegistryHistory_id')) ) {
				getWnd('swTableRecordDataWindow').show({
					recordId: record.get('RegistryHistory_id'),
					schema: 'dbo',
					table: 'RegistryHistory'
				});
			}
		}
	},
	onDetailRecordSelect: function() {

	},
	onLoadDetailGrid: function() {

	},
	onLoadMasterGrid: function() {

	},
	onMasterDblClick: function() {
		var win = this;

		if ( win.MasterGrid.getSelectionModel().hasSelection()) {
			var record = win.MasterGrid.getSelectionModel().getSelection()[0];

			if ( !Ext6.isEmpty(record.get('RegistryCache_id')) ) {
				getWnd('swTableRecordDataWindow').show({
					recordId: record.get('RegistryCache_id'),
					schema: 'dbo',
					table: 'RegistryCache'
				});
			}
		}
	},
	onMasterRecordSelect: function() {
		var win = this;
		
		if ( !win.MasterGrid.getSelectionModel().hasSelection()) {
			return false;
		}

		var record = win.MasterGrid.getSelectionModel().getSelection()[0];

		if ( typeof record != 'object' || Ext6.isEmpty(record.get('Registry_id')) ) {
			return false;
		}

		this.DetailGrid.getStore().removeAll();
		Ext6.apply(this.DetailGrid.getStore().proxy.extraParams, { Registry_id: record.get('Registry_id') });
		this.DetailGrid.getStore().load();
	},
	show: function() {
		this.callParent(arguments);
		this.doReset();
	},

	/* конструктор */
    initComponent: function() {
        var win = this;

		win.FilterPanel = new Ext6.form.FormPanel({
			autoHeight: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 5px;',
			defaults: {
				labelAlign: 'right',
				labelWidth: 100,
				listeners: {
					specialkey: function(field, e, eOpts) {
						if (e.getKey() == e.ENTER) {
							setTimeout(function() { // таймаут, т.к. specialkey срабатывает быстрее чем селектится значение в комбике по forceSelection.. надо думать как реализовать по нормальному
								win.doSearch();
							}, 200);
						}
					}
				}
			},
			region: 'north',
			items: [{
				listConfig: {
					minWidth: 800,
					resizable: true
				},
				name: 'Lpu_id',
				width: 800,
				xtype: 'swLpuCombo'
			}, {
				border: false,
				defaults: {
					labelAlign: 'right',
					listeners: {
						specialkey: function(field, e, eOpts) {
							if (e.getKey() == e.ENTER) {
								setTimeout(function() {
									win.doSearch();
								}, 200);
							}
						}
					}
				},
				layout: 'column',
				items: [{
					fieldLabel: 'Период с',
					format: 'd.m.Y',
					labelWidth: 100,
					name: 'begDT',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 240,
					xtype: 'datefield'
				}, {
					fieldLabel: 'по',
					format: 'd.m.Y',
					labelWidth: 30,
					name: 'endDT',
					plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false) ],
					width: 170,
					xtype: 'datefield'
				}]
			}, {
				border: false,
				layout: 'column',
				items: [{
					style: 'margin-left: 105px;',
					text: 'Найти',
					xtype: 'button',
					iconCls: 'search16',
					handler: function() {
						win.doSearch();
					}
				}, {
					text: 'Сброс',
					xtype: 'button',
					style: 'margin-left: 10px;',
					iconCls: 'reset16',
					handler: function() {
						win.doReset();
					}
				}]
			}]
		});

		win.MasterGridStore = Ext6.create('Ext.data.Store', {
			fields: [
				{ name: 'RegistryCache_id', type: 'int' },
				{ name: 'Registry_deleted', type: 'string' },
				{ name: 'Registry_id', type: 'int' },
				{ name: 'RegistryType_Name', type: 'string' },
				{ name: 'RegistryStatus_Name', type: 'string' },
				{ name: 'RegistryCondition_Name', type: 'string' },
				{ name: 'Registry_Num', type: 'string' },
				{ name: 'Registry_accDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Registry_begDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Registry_endDate', type: 'date', dateFormat: 'd.m.Y' },
				{ name: 'Registry_Count', type: 'int' },
				{ name: 'Registry_Sum', type: 'float' },
				{ name: 'Registry_RecordPaidCount', type: 'int' },
				{ name: 'Registry_SumPaid', type: 'float' },
				{ name: 'Registry_SumPaidPercent', type: 'float' },
				{ name: 'RegistryCache_GenCount', type: 'int' },
				{ name: 'RegistryCache_ExpCount', type: 'int' },
				{ name: 'RegistryCache_ResponseCount', type: 'int' },
				{ name: 'Registry_updDT', type: 'date', dateFormat: 'Y-m-d H:i:s' }
			],
			pageSize: 100,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Registry&m=loadRegistryCacheList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: [
				'RegistryCache_id'
			],
			listeners: {
				load: function() {
					win.onLoadMasterGrid();
				}
			}
		});

		win.MasterGrid = new Ext6.grid.Panel({
			columns: [
				{text: '', width: 30, dataIndex: 'Registry_deleted', renderer: function(val, metaData, record) {
					var s = '';
					if (val == 1) {
						s += "<img src='/img/icons/delete16.png' data-qtip='Реестр удален' />";
					}
					return s;
				}},
				{text: 'Registry_id', width: 80, dataIndex: 'Registry_id'},
				{text: 'МО', flex: 1, minWidth: 200, dataIndex: 'Lpu_Name'},
				{text: 'Тип', width: 150, dataIndex: 'RegistryType_Name'},
				{text: 'Номер/дата счета', width: 150, dataIndex: 'Registry_accDateNum'},
				{text: 'Последнее событие', width: 190, dataIndex: 'RegistryCondition_Name'},
				{text: 'Дата/время последнего события', width: 200, dataIndex: 'RegistryHistory_insDT', formatter: 'date("d.m.Y H:i:s")'},
				{text: 'Кол-во случаев', width: 100, dataIndex: 'Registry_Count'},
				{text: 'Сумма', width: 100, dataIndex: 'Registry_Sum'},
				{text: 'Кол-во принятых случаев', width: 100, dataIndex: 'Registry_RecordPaidCount'},
				{text: 'Сумма принятых случаев', width: 120, dataIndex: 'Registry_SumPaid'},
				{text: '% принятых (по сумме)', width: 100, dataIndex: 'Registry_SumPaidPercent'},
				{text: 'Ф', width: 40, dataIndex: 'RegistryCache_GenCount'},
				{text: 'Э', width: 40, dataIndex: 'RegistryCache_ExpCount'},
				{text: 'И', width: 40, dataIndex: 'RegistryCache_ResponseCount'},
				{text: 'Дата/время изменения реестра', width: 200, dataIndex: 'Registry_updDT', formatter: 'date("d.m.Y H:i:s")'}
			],
			dockedItems: [{
				displayInfo: true,
				dock: 'bottom',
				store: win.MasterGridStore,
				xtype: 'pagingtoolbar'
			}],
			features: [ ],
			height: 350,
			keyMap: {
				'F3': function(e) {
					e.stopEvent();
					win.onMasterDblClick();
				}
			},
			keyMapEnabled: true,
			listeners: {
				itemdblclick: function() {
					win.onMasterDblClick();
				}
			},
			region: 'north',
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onMasterRecordSelect();
					}
				}
			},
			store: win.MasterGridStore,
			title: 'Реестры'
		});

		win.DetailGridStore = Ext6.create('Ext.data.Store', {
			fields: [
				{ name: 'RegistryHistory_id', type: 'int' },
				{ name: 'Registry_id', type: 'int' },
				{ name: 'RegistryCondition_Name', type: 'string' },
				{ name: 'Registry_Count', type: 'int' },
				{ name: 'Registry_ErrorCount', type: 'int' },
				{ name: 'Registry_Sum', type: 'float' },
				{ name: 'Registry_ErrorSum', type: 'float' },
				{ name: 'RegistryHistory_updDT', type: 'date', dateFormat: 'Y-m-d H:i:s' }
			],
			pageSize: 100,
			proxy: {
				type: 'ajax',
				actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
				url: '/?c=Registry&m=loadRegistryHistoryList',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'totalCount'
				}
			},
			sorters: {
				property: 'RegistryHistory_updDT',
				direction: 'DESC'
			},
			listeners: {
				load: function() {
					win.onLoadDetailGrid();
				}
			}
		});

		win.DetailGrid = new Ext6.grid.Panel({
			columns: [
				{text: 'ID', width: 100, dataIndex: 'RegistryHistory_id'},
				{text: 'Состояние', flex: 1, minWidth: 150, dataIndex: 'RegistryCondition_Name'},
				{text: 'Кол-во случаев', width: 100, dataIndex: 'Registry_Count'},
				{text: 'Сумма', width: 100, dataIndex: 'Registry_Sum'},
				{text: 'Кол-во ошибок', width: 100, dataIndex: 'Registry_ErrorCount'},
				{text: 'Сумма по ошибкам', width: 100, dataIndex: 'Registry_ErrorSum'},
				{text: 'Дата/время события', width: 200, dataIndex: 'RegistryHistory_insDT', formatter: 'date("d.m.Y H:i:s")'}
			],
			dockedItems: [{
				displayInfo: true,
				dock: 'bottom',
				store: win.DetailGridStore,
				xtype: 'pagingtoolbar'
			}],
			features: [ ],
			keyMap: {
				'F3': function(e) {
					e.stopEvent();
					win.onDetailDblClick();
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onDetailDblClick();
				}
			},
			region: 'center',
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onDetailRecordSelect();
					}
				}
			},
			store: win.DetailGridStore,
			title: 'История'
		});

        Ext6.apply(win, {
			items: [
				win.FilterPanel,
				win.MasterGrid,
				win.DetailGrid
			],
			buttons: [
				'->',
				sw4.getHelpButton(win, -1),
				{
					handler:function () {
						win.hide();
					},
					text: BTN_FRMCLOSE
				}
			]
		});

		this.callParent(arguments);
    }
});
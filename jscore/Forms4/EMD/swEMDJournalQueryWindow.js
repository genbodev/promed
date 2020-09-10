/**
 * swEMDJournalQueryWindow - Форма поиска ЭМД и версий ЭМД в РЭМД
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      EMD
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDJournalQueryWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDJournalQueryWindow',
	autoShow: false,
	maximized: true,
	cls: 'arm-window-new',
	title: 'Журнал запросов РЭМД ЕГИСЗ',
	constrain: true,
	header: true,
	layout: 'border',
	onDblClick: function() {
		var win = this;
	},
	onRecordSelect: function() {
		var win = this;

		win.EMDQueryErrorGrid.getStore().removeAll();
		win.EMDJournalQueryGrid.down('#action_view').disable();

		if (this.EMDJournalQueryGrid.getSelectionModel().hasSelection()) {
			var record = this.EMDJournalQueryGrid.getSelectionModel().getSelection()[0];

			if (record.get('EMDJournalQuery_id')) {
				win.EMDJournalQueryGrid.down('#action_view').enable();

				win.EMDQueryErrorGrid.getStore().load({
					params: {
						EMDJournalQuery_id: record.get('EMDJournalQuery_id')
					}
				});
			}
		}
	},
	hideLpuFilterRegion: function(){
		//Проверка региона для задачи https://redmine.swan-it.ru/issues/191829
		var LpuFilterRegion = ["astra","ufa","buryatiya","vologda","kareliya","perm","krym","penza","pskov","stavropol","khak"];
		return !inlist(getRegionNick(),LpuFilterRegion);
	},
	getGrid: function ()
	{
		return this.EMDJournalQueryGrid;
	},
	getSelectedRecord: function() {
		if (this.EMDJournalQueryGrid.getSelectionModel().hasSelection()) {
			var record = this.EMDJournalQueryGrid.getSelectionModel().getSelection()[0];
			return record;
		}
		return false;
	},
	show: function(data) {
		this.callParent(arguments);
		var win = this;

		win.doReset(data);
		win.initFilter();
	},
	doSearch: function(options) {
		if (typeof options != 'object') {
			options = new Object();
		}

		var win = this;
		var base_form = this.filterPanel.getForm();
		var extraParams = base_form.getValues();

		win.EMDJournalQueryGrid.getStore().proxy.extraParams = extraParams;

		win.EMDJournalQueryGrid.getStore().load({
			callback: function () {
				if (options.callback && typeof options.callback == 'function') {
					options.callback();
				}
			}
		});
	},
	doReset: function (data) {
		var base_form = this.filterPanel.getForm();
		base_form.reset();
		this.initFilter();
		this.EMDJournalQueryGrid.getStore().removeAll();
		this.EMDQueryErrorGrid.getStore().removeAll();
		this.onRecordSelect();
		base_form.findField('EMDJournalQuery_OutDT').focus(true, 100);

		var emdRegistryNum = (data && data.EMDRegistry_Num);

		if (emdRegistryNum)
			base_form.findField('EMDRegistry_Num').setValue(emdRegistryNum);

		this.doSearch();
	},
	initFilter: function() {
        var win = this;
        var LpuCombo = win.filterPanel.getForm().findField('EMDLpu_id');
        LpuCombo.getStore().load({
            callback: function(){
                var lpu_id = getGlobalOptions().lpu_id;
                LpuCombo.select(lpu_id);
                LpuCombo.fireEvent('select', LpuCombo, LpuCombo.getSelection());
            }
        });
	},
	initComponent: function() {
		var win = this;

		win.EMDJournalQueryGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'center',
			border: false,
			tbar: {
				xtype: 'toolbar',
				defaults: {
					margin: '0 4 0 0',
					padding: '4 10'
				},
				height: 40,
				cls: 'grid-toolbar',
				overflowHandler: 'menu',
				items: [{
					margin: '0 0 0 6',
					text: 'Обновить',
					itemId: 'action_refresh',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						win.doSearch();
					}
				}, {
					text: 'Просмотреть запрос',
					itemId: 'action_view',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_view',
					handler: function() {
						getWnd("swEMDJournalQueryDetalWindow").show({
							EMDJournalQuery_id:win.getSelectedRecord().get("EMDJournalQuery_id")
						});
					}
				}, {
					text: 'Печать',
					itemId: 'action_print',
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_print',
					menu: new Ext6.menu.Menu({
						userCls: 'menuWithoutIcons',
						items: [{
							text: 'Печать списка',
							handler: function() {
								Ext6.ux.GridPrinter.print(win.EMDJournalQueryGrid);
							}
						}]
					})
				}]
			},			
			bbar: {
				xtype: 'pagingtoolbar',
				displayInfo: true
			},
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						win.onRecordSelect();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					win.onDblClick();
				}
			},
			store: {
				fields: [
					{name: 'EMDJournalQuery_id', type: 'int'},
					{name: 'EMDRegistry_id'},
					{name: 'EMDRegistry_Num'},
					{name: 'EMDVersion_FilePath'},
					{name: 'EMDDocumentTypeLocal_Name'},
					{name: 'EMDQueryType_Name'},
					{name: 'EMDJournalQuery_OutDT'},
					{name: 'EMDQueryStatus_Name'},
					//{name: 'EMDJournalQuery_OutParam'},
					//{name: 'EMDJournalQuery_InParam'},
					{name: 'EMDJournalQuery_InDT'},
					{name: 'Lpu_Name'}
				],
				pageSize: 100,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDJournalQuery',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [
					'login'
				],
				listeners: {
					load: function() {
						win.onRecordSelect();
					}
				}
			},
			columns: [
				{text: 'ID запроса', tdCls: 'padLeft', width: 150, dataIndex: 'EMDJournalQuery_id'},
				{text: 'UUID ЭМД', width: 150, dataIndex: 'EMDRegistry_id'},
				{
					text: 'ЭМД', 
					width: 150, 
					dataIndex: 'EMDVersion_FilePath',
					renderer: function(value){
						return '<a href='+value+' target="_blank">'+value+'</a>';
					}
				},
				{text: 'Рег №', width: 150, dataIndex: 'EMDRegistry_Num'},
				{text: 'Документ', width: 150, dataIndex: 'EMDDocumentTypeLocal_Name'},
				{text: 'Тип запроса', width: 150, dataIndex: 'EMDQueryType_Name'},
				{text: 'Дата и время запроса', width: 150, dataIndex: 'EMDJournalQuery_OutDT_RU'},
				{text: 'Статус запроса', width: 150, dataIndex: 'EMDQueryStatus_Name'},
				//{text: 'Данные запроса', width: 150, dataIndex: 'EMDJournalQuery_OutParam'},
				//{text: 'Данные ответа', width: 150, dataIndex: 'EMDJournalQuery_InParam'},
				{text: 'Дата и время ответа', width: 150, dataIndex: 'EMDJournalQuery_InDT'},
				//{text: 'Предупреждения по документу', width: 150, dataIndex: ''}, // todo
				{text: 'МО', width: 250, dataIndex: 'Lpu_Name', hidden: win.hideLpuFilterRegion() }
			]
		});

		win.EMDQueryErrorGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			xtype: 'grid',
			region: 'south',
			height: 300,
			border: false,
			title: 'Ошибки и предупреждения',
			selModel: {
				mode: 'SINGLE'
			},
			store: {
				fields: [
					{name: 'EMDQueryError_id', type: 'int'},
					{name: 'EMDQueryError_Code'},
					{name: 'EMDQueryError_Message'}
				],
				pageSize: 100,
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDQueryError',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'login'
				]
			},
			columns: [
				{text: 'Тип ошибки', tdCls: 'padLeft', width: 150, dataIndex: ''}, // todo
				{text: 'Код ошибки', width: 150, dataIndex: 'EMDQueryError_Code'},
				{text: 'Описание ошибки', flex: 1, minWidth: 150, dataIndex: 'EMDQueryError_Message'}
			]
		});

		win.filterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			layout: 'anchor',
			border: false,
			bodyStyle: 'padding: 20px 30px 0px 0px;',
			cls: 'person-search-input-panel',
			region: 'north',
			items: [{
				border: false,
				layout: 'column',
				padding: '0 0 0 28',
				items: [{
					border: false,
					layout: 'anchor',
					defaults: {
						anchor: '100%',
						labelWidth: 95,
						width: 300,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [Ext6.create('Ext6.date.RangeField', {
						fieldLabel: 'Дата запроса',
						name: 'EMDJournalQuery_OutDT',
						//plugins: [ new Ext6.ux.InputTextMask('99.99.9999', false)], 
						value:Ext6.util.Format.date(new Date(), 'd.m.Y')
					}),{
						xtype: 'commonSprCombo',
						comboSubject: 'EMDQueryType',
						fieldLabel: 'Тип запроса',
						name: 'EMDQueryType_id'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 27',
					defaults: {
						anchor: '100%',
						labelWidth: 100,
						width: 305,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'swEMDDocumentTypeLocalRemote',
						name: 'EMDDocumentTypeLocal_id',
					}, {
						xtype: 'textfield',
						fieldLabel: 'Номер ЭМД',
						name: 'EMDRegistry_Num'
					}]
				}, {
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%',
						labelWidth: 50,
						width: 250,
						listeners: {
							specialkey: function(field, e, eOpts) {
								if (e.getKey() == e.ENTER) {
									win.doSearch();
								}
							}
						}
					},
					items: [{
						xtype: 'swEMDQueryStatus',
						name: 'EMDQueryStatus_id',
						value:-1
					},{
						xtype: 'swLpuCombo',
						fieldLabel: 'МО',
						hidden: win.hideLpuFilterRegion(),
						name: 'EMDLpu_id',
						reference: 'EMDLpu_id',
						allowBlank:true,
						allowBlank: isUserGroup('SuperAdmin'),
						disabled: !isUserGroup('SuperAdmin'),
						anchor: '-5',
						plugins: [ new Ext6.ux.Translit(true, false) ]							
					}]
				},{
					border: false,
					layout: 'anchor',
					margin: '0 0 0 26',
					defaults: {
						anchor: '100%'
					},
					items: [{
						border: false,
						style: 'margin-top: 3px;',
						items: [{
							width: 100,
							cls: 'button-secondary',
							text: 'Очистить',
							xtype: 'button',
							cls: 'button-secondary',
							handler: function() {
								win.doReset();
							}
						}]
					}, {
						border: false,
						style: 'margin-top: 8px;',
						items: [{
							width: 100,
							cls: 'button-primary',
							text: 'Найти',
							xtype: 'button',
							handler: function() {
								win.doSearch();
							}
						}]
					}]
				}]
			}]
		});

		win.mainPanel = new Ext6.Panel({
			region: 'center',
			layout: 'border',
			border: false,
			items: [
				win.filterPanel,
				win.EMDJournalQueryGrid,
				win.EMDQueryErrorGrid
			]
		});

		Ext6.apply(win, {
			referenceHolder: true, // чтобы ЛУКап заработал по референсу
			items: [
				win.mainPanel
			]
		});

		this.callParent(arguments);
	}
});

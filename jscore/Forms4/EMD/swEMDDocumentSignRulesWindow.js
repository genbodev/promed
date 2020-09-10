/**
 * swEMDDocumentSignRulesWindow - Форма правил для листа согласования
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Common
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 */
Ext6.define('emd.swEMDDocumentSignRulesWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swEMDDocumentSignRulesWindow',
	autoShow: false,
	maximized: true,
	cls: 'arm-window-new emd-search',
	title: 'Правила для листа согласования при подписании документов',
	constrain: true,
	header: getGlobalOptions().client == 'ext2',
	show: function(data) {
		this.callParent(arguments);
		this.ArmType = 'lpuadmin';
		if (data.ArmType) {
			this.ArmType = data.ArmType;
		}
		var filter = this.FilterPanel.getForm();
		
		if (getRegionNick() == 'msk') {
			setLpuSectionGlobalStoreFilter({
				isStac: true
			}, sw4.swLpuSectionGlobalStore);
		} else {
			setLpuSectionGlobalStoreFilter({
				arrayLpuSectionProfile: ['37', '38', '39', '40', '0070']
			}, sw4.swLpuSectionGlobalStore);
		}

		if (this.ArmType == 'superadmin') {
			filter.findField('LpuSection_id').hideContainer();
			filter.findField('Lpu_id').showContainer();
		} else {
			filter.findField('LpuSection_id').showContainer();
			filter.findField('LpuSection_id').getStore().loadData(sw4.getStoreRecords(sw4.swLpuSectionGlobalStore));
			filter.findField('Lpu_id').hideContainer();
		}
		this.doReset();
	},
	doSearch: function() {
		var grid = this.RegistryGrid;

		var filter = this.FilterPanel.getForm();
		var params = filter.getValues();

		params.start = 0;
		params.limit = 100;

		grid.getStore().removeAll();
		grid.getStore().load({params: params});
	},
	doReset: function() {
		var filter = this.FilterPanel.getForm();
		filter.reset();
		this.RegistryGrid.getStore().removeAll();
	},
	doSave: function() {
		var me = this;
		var filter = this.FilterPanel.getForm();
		me.mask(LOAD_WAIT_SAVE);

		var LpuIds = filter.findField('Lpu_id').getValue();
		var LpuSectionIds = filter.findField('LpuSection_id').getValue();

		Ext6.Ajax.request({
			url: '/?c=EMD&m=saveEMDDocumentSignRules',
			params: {
				Lpu_id: !Ext6.isEmpty(LpuIds) ? LpuIds.toString() : null,
				LpuSection_id: !Ext6.isEmpty(LpuSectionIds) ? LpuSectionIds.toString() : null,
				records: Ext6.util.JSON.encode(sw4.getStoreRecords(me.RegistryGrid.getStore(), {
					exceptionFields: [
						'EMDDocumentTypeLocal_Name',
						'EMDDocumentSign_HeadSignWarn',
						'EMDDocumentSign_MainSignWarn'
					]
				}))
			},
			success: function(response) {
				me.unmask();

				var response_obj = Ext6.decode(response.responseText);
				if (response_obj.success) {
					me.hide();
				}
			}
		});
	},
	initComponent: function() {
		var me = this;

		me.FilterPanel = Ext6.create('Ext6.form.FormPanel', {
			autoScroll: true,
			region: 'north',
			border: false,
			bodyStyle: 'padding: 0 20px 20px 20px;',
			fieldDefaults: {
				labelAlign: 'top',
				msgTarget: 'side'
			},
			defaults: {
				border: false,
				xtype: 'panel',
				width: 250,
				layout: 'anchor'
			},
			layout: 'hbox',
			items: [{
				width: 600,
				items: [{
					xtype: 'swTagLpu',
					name: 'Lpu_id',
					fieldLabel: 'Выбор МО',
					anchor: '-5',
					width: 250,
					growMax: 87,
					growMin: 87,
					stacked: true,
					triggerOnClick: false
				}, {
					xtype: 'swTagLpuSection',
					name: 'LpuSection_id',
					fieldLabel: 'Выбор структурного подразделения',
					anchor: '-5',
					width: 250,
					growMax: 87,
					growMin: 87,
					stacked: true,
					triggerOnClick: false
				}]
			}, {
				width: 300,
				items: [{
					border: false,
					cls: 'panel-80',
					layout: 'column',
					style: 'margin-top: 35px;',
					items: [{
						cls: 'button-primary',
						text: 'Найти',
						//iconCls: 'person-search-btn-icon action_find_white',
						xtype: 'button',
						handler: function() {
							me.doSearch();
						}
					}, {
						cls: 'button-secondary',
						text: 'Очистить',
						xtype: 'button',
						//iconCls: 'person-clear-btn-icon action_clear',
						style: 'margin-left: 10px;',
						handler: function() {
							me.doReset();
						}
					}]
				}]
			}]
		});

		me.RegistryTitleBar = Ext6.create('Ext6.Panel', {
			region: 'north',
			style: {
				'box-shadow': '0px 1px 6px 2px #ccc',
				zIndex: 2
			},
			layout: 'border',
			border: false,
			height: 40,
			bodyStyle: 'background-color: #EEEEEE;',
			items: [{
				region: 'center',
				border: false,
				bodyStyle: 'background-color: #EEEEEE;',
				height: 40,
				bodyPadding: 10,
				items: [
					Ext6.create('Ext6.form.Label', {
						xtype: 'label',
						cls: 'no-wrap-ellipsis',
						style: 'font-size: 16px; padding: 3px 10px;',
						html: 'Правила согласования'
					})
				]
			}, Ext6.create('Ext6.Toolbar', {
				region: 'east',
				height: 40,
				border: false,
				noWrap: true,
				right: 0,
				style: 'background: rgb(238, 238, 238) !important;',
				defaults: {
					style: {
						'color': 'transparent'
					},
					userCls: 'button-without-frame'
				},
				cls: 'grid-toolbar',
				items: [{
					xtype: 'button',
					cls: 'toolbar-padding',
					iconCls: 'action_refresh',
					handler: function() {
						me.doSearch();
					}
				}]
			})], xtype: 'panel'
		});

		me.RegistryGrid = Ext6.create('Ext6.grid.Panel', {
			cls: 'grid-common',
			region: 'center',
			xtype: 'grid',
			selModel: {
				type: 'cellmodel'
			},
			listeners: {},
			plugins: {
				cellediting: {
					clicksToEdit: 1
				}
			},
			store: {
				fields: [
					{name: 'EMDDocumentTypeLocal_id', type: 'int'},
					{name: 'EMDDocumentTypeLocal_Name', type: 'string'},
					{name: 'EMDDocumentSign_HeadSignWarn', type: 'int'},
					{name: 'EMDDocumentSign_MainSignWarn', type: 'int'},
					{name: 'EMDDocumentSign_HeadSign', type: 'boolean'},
					{name: 'EMDDocumentSign_MainSign', type: 'boolean'}
				],
				proxy: {
					type: 'ajax',
					actionMethods: {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=EMD&m=loadEMDDocumentSignGrid',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [{
					property: 'EMDDocumentTypeLocal_Name',
					direction: 'ASC'
				}]
			},
			columns: [
				{text: 'Тип документа', width: 400, dataIndex: 'EMDDocumentTypeLocal_Name'},
				{text: 'Количество подписей', width: 200, renderer: function(val, metaData, row) {
					var cnt = 1;
					if (row.get('EMDDocumentSign_HeadSign')) {
						cnt++;
					}
					if (row.get('EMDDocumentSign_MainSign')) {
						cnt++;
					}
					return cnt;
				}},
				{
					text: 'Подпись заведующего',
					width: 200,
					xtype: 'checkcolumn',
					dataIndex: 'EMDDocumentSign_HeadSign',
					renderer: function(value, meta, rec) {
						if (rec.get('EMDDocumentSign_HeadSignWarn')) {
							meta.style = "background-color: #FF5555;";
						}
						return this.defaultRenderer(value, meta);
					}
				},
				{
					text: 'Подпись главного врача',
					width: 200,
					xtype: 'checkcolumn',
					dataIndex: 'EMDDocumentSign_MainSign',
					renderer: function(value, meta, rec) {
						if (rec.get('EMDDocumentSign_MainSignWarn')) {
							meta.style = "background-color: #FF5555;";
						}
						return this.defaultRenderer(value, meta);
					}
				}
			]
		});

		Ext6.apply(me, {
			layout: 'border',
			items: [
				me.FilterPanel,
				new Ext6.Panel({
					region: 'center',
					layout: 'border',
					items: [
						new Ext6.Panel({
							region: 'center',
							layout: 'border',
							items: [
								me.RegistryTitleBar,
								me.RegistryGrid
							]
						})
					]
				})
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					cls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					text: 'Сохранить',
					margin: '0 19 0 0',
					cls: 'buttonAccept',
					handler: function() {
						me.doSave()
					}
				}
			]
		});

		me.callParent(arguments);
	}
});

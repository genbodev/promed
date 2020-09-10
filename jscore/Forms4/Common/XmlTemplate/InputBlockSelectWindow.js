Ext6.define('common.XmlTemplate.InputBlockSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateInputBlockSelectWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new template-input-block',
	title: 'Область ввода данных',
	width: 540,
	height: 400,
	modal: true,

	apply: function() {
		var me = this;
		var data = [];

		me.SelectedInputBlockGridPanel.getStore().each(function(record) {
			data.push({
				XmlDataSection_id: record.get('XmlDataSection_id'),
				XmlDataSection_Code: record.get('XmlDataSection_Code'),
				XmlDataSection_Name: record.get('XmlDataSection_Name'),
				XmlDataSection_SysNick: record.get('XmlDataSection_SysNick')
			});
		});

		me.hide();
		me.callback(data);
	},

	refreshToolsButtons: function() {
		var me = this;
		var grid = me.InputBlockGridPanel;
		var selectedGrid = me.SelectedInputBlockGridPanel;

		var inputBlocks = grid.getSelection();
		var selectedInputBlocks = selectedGrid.getSelection();

		me.SectionTools.select.setDisabled(inputBlocks.length == 0);
		me.SectionTools.unselect.setDisabled(selectedInputBlocks.length == 0);
		me.SectionTools.moveUp.setDisabled(selectedInputBlocks.length == 0 || selectedGrid.getStore().first() == selectedInputBlocks[0]);
		me.SectionTools.moveDown.setDisabled(selectedInputBlocks.length == 0 || selectedGrid.getStore().last() == selectedInputBlocks[0]);

		me.down('#'+me.getId()+'-apply-btn').setDisabled(selectedInputBlocks.length == 0);
	},

	select: function() {
		var me = this;
		var grid = me.InputBlockGridPanel;
		var selectedGrid = me.SelectedInputBlockGridPanel;

		var records = grid.getSelection();

		if (records.length == 0) {
			return;
		}

		var index = grid.getStore().indexOf(records[0]);

		var prepareForAdd = function(records) {
			return records.map(function(record) {
				return Ext6.apply(record.getData(), {
					sort: selectedGrid.getStore().getCount()
				});
			});
		};

		selectedGrid.getStore().add(prepareForAdd(records));
		grid.getStore().remove(records);

		grid.getSelectionModel().select(grid.getStore().getCount()>index?index:index-1);

		var index1 = selectedGrid.getStore().getCount()-1;
		selectedGrid.getSelectionModel().select(index1);
		selectedGrid.getView().focusRow(index1);

		me.refreshToolsButtons();
	},

	unselect: function() {
		var me = this;
		var grid = me.InputBlockGridPanel;
		var selectedGrid = me.SelectedInputBlockGridPanel;

		var records = selectedGrid.getSelection();

		if (records.length == 0) {
			return;
		}

		var index = selectedGrid.getStore().indexOf(records[0]);

		grid.getStore().add(records);
		selectedGrid.getStore().remove(records);

		selectedGrid.getSelectionModel().select(selectedGrid.getStore().getCount()>index?index:index-1);

		var index1 = grid.getStore().findBy(function(item) {
			return item.get('XmlDataSection_id') == records[0].get('XmlDataSection_id');
		});
		grid.getSelectionModel().select(index1);
		grid.getView().focusRow(index1);

		me.refreshToolsButtons();
	},

	moveUp: function() {
		var me = this;
		var selectedGrid = me.SelectedInputBlockGridPanel;

		var records = selectedGrid.getSelection();

		if (records.length == 0) {
			return;
		}

		var sort = records[0].get('sort');

		selectedGrid.getStore().each(function(record){
			if (record.get('sort') == sort-1) {
				record.set('sort', sort);
			} else if (record.get('sort') == sort) {
				record.set('sort', sort-1);
			}
		});
		records[0].commit();

		var index = selectedGrid.getStore().indexOf(records[0]);
		selectedGrid.getView().focusRow(index);

		me.refreshToolsButtons();
	},

	moveDown: function() {
		var me = this;
		var selectedGrid = me.SelectedInputBlockGridPanel;

		var records = selectedGrid.getSelection();

		if (records.length == 0) {
			return;
		}

		var sort = records[0].get('sort');

		selectedGrid.getStore().each(function(record){
			if (record.get('sort') == sort) {
				record.set('sort', sort+1);
			} else if (record.get('sort') == sort+1) {
				record.set('sort', sort);
			}
		});
		records[0].commit();

		var index = selectedGrid.getStore().indexOf(records[0]);
		selectedGrid.getView().focusRow(index);

		me.refreshToolsButtons();
	},

	show: function() {
		var me = this;

		me.callParent(arguments);

		me.appliedInputBlockList = [];
		me.callback = Ext6.emptyFn;

		if (arguments[0] && arguments[0].appliedInputBlockList) {
			me.appliedInputBlockList = arguments[0].appliedInputBlockList;
		}
		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		me.InputBlockGridPanel.getStore().removeAll();
		me.SelectedInputBlockGridPanel.getStore().removeAll();
		me.refreshToolsButtons();

		me.InputBlockGridPanel.getStore().load({
			callback: function() {
				me.InputBlockGridPanel.getStore().each(function(record) {
					if (record.get('XmlDataSection_SysNick').inlist(me.appliedInputBlockList)) {
						record.set('disabled', true);
					}
				});

				var index = me.InputBlockGridPanel.getStore().findBy(function(record) {
					return !Ext6.isEmpty(record.get('XmlDataSection_id')) && !record.get('disabled');
				});
				me.InputBlockGridPanel.getSelectionModel().select(index);
				me.refreshToolsButtons();
			}
		});
	},

	initComponent: function() {
		var me = this;

		var disabledRenderer = function(value, meta, record) {
			if (record.get('disabled')) {
				meta.style = 'color: #9b9b9b';
			}
			return value;
		}

		me.InputBlockGridPanel = Ext6.create('Ext6.grid.Panel', {
			userCls:'select-input-area',
			border: false,
			store: {
				fields: [
					{name: 'XmlDataSection_id', type: 'int'},
					{name: 'XmlDataSection_Code', type: 'int'},
					{name: 'XmlDataSection_Name', type: 'string'},
					{name: 'XmlDataSection_SysNick', type: 'string'},
					{name: 'disabled', type: 'boolean', defaultValue: false}
				],
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=XmlTemplate6E&m=loadXmlDataSectionList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				sorters: [
					'XmlDataSection_Code'
				]
			},
			columns: [
				{dataIndex: 'XmlDataSection_Name', flex: 1, renderer: disabledRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					beforeselect: function(model, record, index) {
						return !record.get('disabled');
					},
					select: function(model, record, index) {
						me.refreshToolsButtons();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.select();
				}
			}
		});

		me.SelectedInputBlockGridPanel = Ext6.create('Ext6.grid.Panel', {
			userCls:'select-input-area',
			border: false,
			store: {
				fields: [
					{name: 'XmlDataSection_id', type: 'int'},
					{name: 'XmlDataSection_Code', type: 'int'},
					{name: 'XmlDataSection_Name', type: 'string'},
					{name: 'XmlDataSection_SysNick', type: 'string'},
					{name: 'sort', type: 'int'}
				],
				sorters: [
					'sort'
				]
			},
			columns: [
				{dataIndex: 'XmlDataSection_Name', flex: 1}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function(model, record, index) {
						me.refreshToolsButtons();
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.unselect();
				}
			}
		});

		me.SectionTools = {
			select: Ext6.create('Ext6.button.Button', {
				iconCls: 'icon-tg',
				tooltip: 'Добавить область',
				handler: function(){me.select()}
			}),
			unselect: Ext6.create('Ext6.button.Button', {
				iconCls: 'icon-tl',
				tooltip: 'Удалить область',
				handler: function(){me.unselect()}
			}),
			moveUp: Ext6.create('Ext6.button.Button', {
				iconCls: 'icon-arrow-up',
				tooltip: 'Переместить вверх',
				handler: function(){me.moveUp()}
			}),
			moveDown: Ext6.create('Ext6.button.Button', {
				iconCls: 'icon-arrow-down',
				tooltip: 'Переместить вниз',
				handler: function(){me.moveDown()}
			})
		};

		me.SelectionToolsPanel = Ext6.create('Ext6.panel.Panel', {
			border: false,
			userCls: 'template-input-block-action-button',
			defaults: {
				cls: 'sw-tool button-without-frame',
				padding: '0',
				margin: '8 0',
				border: false,
				disabled: true
			},
			layout: {
				type: 'vbox',
				align: 'middle',
				pack: 'center'
			},
			items: [
				me.SectionTools.select,
				me.SectionTools.unselect,
				me.SectionTools.moveUp,
				me.SectionTools.moveDown
			]
		});

		Ext6.apply(me, {
			layout: 'hbox',
			defaults: {height: '100%'},
			items: [
				{
					layout: 'fit',
					style: 'margin: 15px;',
					cls: 'sw-panel-gray',
					border: true,
					flex: 1,
					title: 'Выберите области',
					items: me.InputBlockGridPanel
				},
				me.SelectionToolsPanel,
				{
					layout: 'fit',
					style: 'margin: 15px;',
					cls: 'sw-panel-gray',
					border: true,
					flex: 1,
					title: 'Добавление области',
					items: me.SelectedInputBlockGridPanel
				}
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					cls:'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-apply-btn',
					cls: 'buttonAccept ',
					text: 'Применить',
					margin: '0 19 0 0',
					disabled: true,
					handler: function() {
						me.apply();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});
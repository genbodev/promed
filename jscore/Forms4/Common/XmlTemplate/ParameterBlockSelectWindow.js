Ext6.define('common.XmlTemplate.ParameterBlockSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateParameterBlockSelectWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new PolkaWP template-parameter-block-select-window',
	title: 'Параметр',
	width: 950,
	height: 480,
	modal: true,

	apply: function() {
		var me = this;

		var data = me.grid.getSelection().map(function(item) {
			return item.data;
		});

		me.hide();
		me.callback(data);
	},

	load: function() {
		var me = this;
		me.grid.store.removeAll();
		me.grid.store.load({
			callback: function() {
				var sm = me.grid.getSelectionModel();
				sm.fireEvent('selectionchange', sm, sm.selected.items);
			}
		});
	},

	filter: function() {
		var me = this;
		var store = me.grid.getStore();
		var filters = [];

		if (me.onlyMyCheckbox.getValue()) {
			var Author_id = getGlobalOptions().pmuser_id;
			filters.push(function(item) {
				return item.get('Author_id') == Author_id;
			});
		}

		if (me.aliasFilterField.getValue()) {
			var alias = me.aliasFilterField.getValue();
			filters.push(function(item) {
				return item.get('ParameterValue_Alias').toLowerCase().substr(0, alias.length) == alias.toLowerCase();
			});
		}

		store.clearFilter();
		store.filterBy(function(item) {
			return filters.every(function(filter) {
				return filter(item);
			})
		});
	},

	openEditWindow: function(action, record) {
		var me = this;
		if (!action || !action.inlist(['add','edit'])) {
			return;
		}

		var params = {};
		params.callback = function(id) {
			me.grid.store.reload({
				callback: function() {
					var index = me.grid.store.findBy(function(item) {
						return item.get('ParameterValue_id', id);
					});
					if (index >= 0) {
						me.grid.selModel.select(index)
					}
				}
			});
		};

		if (action == 'edit') {
			record = record || me.grid.selection;
			params.ParameterValue_id = record.get('ParameterValue_id');
			if (!params.ParameterValue_id) return;
		}

		getWnd('swXmlTemplateParameterBlockEditWindow').show(params);
	},

	deleteParamater: function(record) {
		var me = this;
		record = record || me.grid.selection;

		var params = {
			ParameterValue_id: record.get('ParameterValue_id')
		};

		checkDeleteRecord({
			callback: function () {
				me.mask('Удаление');
				Ext6.Ajax.request({
					url: '/?c=XmlTemplate6E&m=deleteParameterValue',
					params: params,
					callback: function(opt, success, response) {
						me.unmask();
						me.grid.store.reload();
					}
				})
			}
		});
	},

	show: function() {
		var me = this;

		me.callParent(arguments);

		me.callback = Ext6.emptyFn;

		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		me.aliasFilterField.setValue('');
		me.aliasFilterField.refreshTrigger();

		me.load();
	},

	initComponent: function() {
		var me = this;

		var listTypeRenderer = function(value, meta, record) {
			var result = '';

			switch(value) {
				case 'combobox':
					result = '<div class="sw-grid-cell-combobox-icon"></div>';
					break;
				case 'checkboxgroup':
					result = '<div class="sw-grid-cell-checkbox-icon"></div>';
					break;
				case 'radiogroup':
					result = '<div class="sw-grid-cell-radio-icon"></div>';
					break;
			}

			if (!Ext6.isEmpty(result)) {
				meta.tdAttr = 'data-qtip="'+record.get('ParameterValueListType_Name')+'"';
			}
			return result;
		};

		var grayTextRenderer = function(value, meta) {
			meta.style = 'color: #9b9b9b;';
			meta.tdAttr = 'data-qtip="'+value+'"';
			return value;
		};

		me.toolbarButtons = {
			add: Ext6.create('Ext6.button.Button', {
				cls: 'toolbar-padding',
				iconCls: 'action_add',
				padding: '4 10',
				text: 'Новый параметр',
				margin: '0 4 0 0',
				handler: function() {
					me.openEditWindow('add');
				}
			}),
			edit: Ext6.create('Ext6.button.Button', {
				cls: 'toolbar-padding',
				iconCls: 'action_edit',
				text: 'Редактировать',
				padding: '4 10',
				margin: '0 4 0 0',
				handler: function() {
					me.openEditWindow('edit');
				}
			}),
			remove: Ext6.create('Ext6.button.Button', {
				cls: 'toolbar-padding',
				iconCls: 'action_delete',
				padding: '4 10',
				text: 'Удалить',
				handler: function() {
					me.deleteParamater();
				}
			})
		};

		me.onlyMyCheckbox = Ext6.create('Ext6.form.Checkbox', {
			boxLabel: 'Только мои',
			userCls: 'only-my-search',
			height: 28,
			listeners: {
				change: function(checkbox, newValue, oldValue) {
					me.filter();
				}
			}
		});

		var delayFilter = function(delay) {
			if (me.delayFilterId) {
				clearTimeout(me.delayFilterId);
			}
			me.delayFilterId = setTimeout(function() {
				me.filter();
				me.delayFilterId = null;
			}, delay);
		};

		var aliasFilterField = {
			id: me.getId()+'-alias-filter',
			cls: 'sw-text-filter-trigger-field',
			type: 'string',
			width: 230,
			xtype: 'textfield',
			emptyText: 'Наименование параметра',
			enableKeyEvents: true,
			refreshTrigger: function(value) {
				var isEmpty = Ext6.isEmpty(value || me.aliasFilterField.getValue());
				me.aliasFilterField.triggers.clear.setVisible(!isEmpty);
				me.aliasFilterField.triggers.search.setVisible(isEmpty);
			},
			triggers: {
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {
						me.load();
					}
				},
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function() {
						me.aliasFilterField.setValue('');
						me.aliasFilterField.refreshTrigger();
						me.filter();
					}
				}
			},
			listeners: {
				keyup: function(field, e) {
					if (e.getKey() == e.ENTER) {
						me.load();
					} else {
						me.aliasFilterField.refreshTrigger(e.target.value);
						delayFilter(300);
					}
				},
				render: function() {
					me.aliasFilterField = Ext6.getCmp(me.getId()+'-alias-filter');

					var fieldBodyEl = me.aliasFilterField.el.down('.x6-form-item-body');
					var fieldContainerEl = me.aliasFilterField.el.up('.x-gridheaderfilters-filter-container');
					var column = me.aliasFilterField.column;

					var sorterIconStr = '<span class="column-header-field-sort-icon"/>';
					column.sortIconEl = Ext6.get(fieldBodyEl.insertHtml('afterEnd', sorterIconStr));
					column.sortIconEl.setLeft(fieldBodyEl.getWidth() + 25);

					var onClick = function(e) {
						if (!fieldBodyEl.contains(e.target)) {
							column.sort();
							e.stopEvent();
						}
					};

					fieldContainerEl.on('click', onClick);
				}
			}
		};

		var aliasTpl = new Ext6.Template([
			'<span style="color: #f44336;">{subalias1}</span><span>{subalias2}</span> ',
			'<span style="color: #9b9b9b;">({valuesCount})</span>'
		]);

		var aliasRenderer = function(value, meta, record) {
			if (Ext6.isEmpty(value)) {
				return value;
			}

			var alias = value;
			var subalias1 = '';
			var subalias2 = alias;
			var query = me.aliasFilterField.getValue();

			if (query.length > 0) {
				var l = query.length;
				var s1 = alias.slice(0, l);
				var s2 = alias.slice(l);

				if (s1.toLowerCase() == query.toLowerCase()) {
					subalias1 = s1;
					subalias2 = s2;
				}
			}

			var values = Ext6.JSON.decode(record.get('ParameterValueList'));
			var qtip = values.map(function(item){
				return item.ParameterValue_Name.replace(/"/g, '&quot;');
			}).join('<br/>');
			meta.tdAttr = 'data-qtip="'+qtip+'"';

			return aliasTpl.apply({
				subalias1: subalias1, subalias2: subalias2, valuesCount: values.length
			});
		};

		me.grid = Ext6.create('Ext6.grid.Panel', {
			xtype: 'grid',
			border: false,
			cls: 'select-parameter-grid grid-common template-parameter-block-select-grid',
			tbar: {
				cls: 'grid-toolbar',
				defaults: {
					margin: 0
				},
				items: [
					me.toolbarButtons.add,
					me.toolbarButtons.edit,
					me.toolbarButtons.remove,
					'->',
					me.onlyMyCheckbox,
					' '
				]
			},
			requires: [
				'Ext6.ux.GridHeaderFilters'
			],
			plugins: [
				Ext6.create('Ext6.grid.filters.Filters', {
					showMenu: false
				}),
				Ext6.create('Ext6.ux.GridHeaderFilters', {
					enableTooltip: false,
					reloadOnChange: false
				})
			],
			store: {
				fields: [
					{name: 'ParameterValue_id', type: 'int'},
					{name: 'ParameterValue_pid', type: 'int', allowNull: true},
					{name: 'ParameterValue_Name', type: 'string'},
					{name: 'ParameterValue_Alias', type: 'string'},
					{name: 'ParameterValue_SysNick', type: 'string'},
					{name: 'ParameterValue_Marker', type: 'string'},
					{name: 'ParameterValueListType_id', type: 'int'},
					{name: 'ParameterValueListType_Code', type: 'int'},
					{name: 'ParameterValueListType_Name', type: 'string'},
					{name: 'ParameterValueListType_SysNick', type: 'string'},
					{name: 'Author_id', type: 'int'},
					{name: 'Author_Fin', type: 'string'},
					{name: 'ParameterValueList', type: 'string'}
				],
				sorters: [
					'ParameterValue_Alias'
				],
				proxy: {
					type: 'ajax',
					url: '/?c=XmlTemplate6E&m=loadParameterValueList',
					reader: {
						type: 'json',
						rootProperty: 'data'
					}
				},
				listeners: {
					load: function() {
						me.filter();
					}
				}
			},
			columns: [
				{dataIndex: 'ParameterValue_Alias', flex: 1, minWidth: 280, text: '', filter: aliasFilterField, renderer: aliasRenderer},
				{dataIndex: 'ParameterValue_Name', flex: 1, minWidth: 190, text: 'Наименование для печати', renderer: grayTextRenderer},
				{dataIndex: 'ParameterValueListType_SysNick', text: 'Тип списка', renderer: listTypeRenderer},
				{dataIndex: 'Author_Fin', flex: 1, text: 'Автор', renderer: grayTextRenderer}
			],
			selModel: {
				mode: 'MULTI',
				listeners: {
					selectionchange: function(model, selected) {
						me.toolbarButtons.edit.setDisabled(selected.length != 1);
						me.toolbarButtons.remove.setDisabled(selected.length == 0);
						Ext6.getCmp(me.getId()+'-apply-btn').setDisabled(selected.length == 0);
					}
				}
			},
			listeners: {
				itemdblclick: function(grid, record) {
					me.apply();
				}
			}
		});

		Ext6.apply(me, {
			layout: 'fit',
			items: [
				me.grid
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-apply-btn',
					cls: 'buttonAccept',
					text: 'Выбрать',
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
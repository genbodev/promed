Ext6.define('common.XmlTemplate.DropdownSelectPanel', {
	extend: 'base.DropdownPanel',
	width: 704,
	params: {},
	onSelect: Ext6.emptyFn,

	select: function(record) {
		var me = this;

		if (!record) {
			var selection = me.grid.getSelection();
			if (selection.length > 0) {
				record = selection[0];
			}
		}
		if (record) {
			me.onSelect(record.data);
			me.hide();
		}
	},

	load: function(force) {
		var me = this;

		var params = Ext6.apply({}, me.params, {
			query: me.QueryField.getValue(),
			mode: me.ModeToggler.getValue() || 'own'
		});
		/*if (!Ext6.isEmpty(params.query) && Ext6.isEmpty(params.mode)) {
			params.mode = 'last10Templates';
		}*/

		var store = me.grid.getStore();

		if (force || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			me.grid.getStore().load({params: params});
		}
	},

	setParams: function(params) {
		var me = this;
		me.QueryField.setValue(params.query || '');
		//me.ModeToggler.setValue(params.mode || '');

		me.params = {
			EvnXml_id: params.EvnXml_id,
			Evn_id: params.Evn_id,
			XmlType_id: params.XmlType_id,
			EvnClass_id: params.EvnClass_id,
			LpuSection_id: params.LpuSection_id,
			MedPersonal_id: params.MedPersonal_id,
			MedStaffFact_id: params.MedStaffFact_id,
			MedService_id: params.MedService_id
		};

		me.QueryField.refreshTrigger();
		me.load(true);
	},

	openTemplateSearchWindow: function(XmlTemplate_id) {
		var me = this;
		var params = Ext6.apply({}, me.params);

		params.XmlTemplate_id = XmlTemplate_id;
		params.onSelect = me.onSelect;

		params.allowedEvnClassList = this.allowedEvnClassList;
		params.allowedXmlTypeEvnClassLink = this.allowedXmlTypeEvnClassLink;

		getWnd('swXmlTemplateEditorWindow').show(params);
	},

	toggleFavorite: function(template) {
		var me = this;

		if (!template || !template.get('XmlTemplate_id')) {
			return;
		}

		var infoMsg = sw4.showInfoMsg({
			panel: me,
			type: 'loading',
			text: 'Сохранение ...',
			hideDelay: null
		});

		Ext6.Ajax.request({
			url: '/?c=XmlTemplate6E&m=toggleXmlTemplateSelected',
			params: {
				XmlTemplate_id: template.get('XmlTemplate_id'),
				MedStaffFact_id: me.params.MedStaffFact_id,
				MedPersonal_id: me.params.MedPersonal_id,
				operation: (template.get('XmlTemplate_IsFavorite') == 2)?'unselect':'select'
			},
			callback: function(options, success, response) {
				if (infoMsg) infoMsg.hide();
				var responseObj = Ext6.decode(response.responseText);

				if (responseObj.success) {
					sw4.showInfoMsg({
						panel: me,
						type: 'success',
						text: 'Данные сохранены'
					});

					var isFavorite = (responseObj.operation == 'select');

					if (me.ModeToggler.getValue() == 'favorite') {
						me.grid.store.remove(template);
					} else {
						template.set('XmlTemplate_IsFavorite', isFavorite?2:1);
						template.commit();
					}
				}
			}
		});
	},

	show: function() {
		var me = this;

		me.callParent(arguments);

		me.QueryField.focus();
	},

	initComponent: function () {
		var me = this;

		me.QueryField = Ext6.create('sw.form.QueryField', {
			name: 'query',
			emptyText: 'Поиск шаблона',
			query: me.load.bind(me),
			width: 246,
			margin: '0 52 0 0',
			listeners: {
				keyup: {
					order: 'before',
					fn: function(field, e) {
						if (!Ext6.isEmpty(e.target.value)) {
							me.ModeToggler.setValue(null);
						}
					}
				}
			}
		});

		me.ModeToggler = Ext6.create('Ext6.button.Segmented', {
			userCls: 'template-search-button-without-border',
			allowDepress: true,
			margin: 0,
			items: [{
				text: 'Избранные',
				value: 'favorite',
				cls: 'button-without-border btn-grey-blue',
				iconCls: 'favTemp-btn-icon'
			} /*{
				text: 'Последние',
				tooltip: '10 последних созданных или измененных шаблонов пользователя',
				value: 'last10Templates',
				cls: 'button-without-border btn-grey-blue',
				iconCls: 'lastStand-btn-icon'
			}*/],
			listeners: {
				change: function(toggler, value) {
					if (!Ext6.isEmpty(value)) {
						me.QueryField.setValue('');
						me.QueryField.refreshTrigger();
					}
					me.load();
				}
			}
		});

		var toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			style: 'background-color: #EEE;',
			padding: '6 10',
			border: true,
			height: 59,
			items: [
				me.QueryField,
				me.ModeToggler,'->',
				{
					cls: 'sw-tool',
					iconCls: 'icon-template',
					text: 'Все шаблоны',
					handler: function() {
						me.openTemplateSearchWindow();
					}
				}
			]
		});

		var rowValueTpl = new Ext6.XTemplate(
			'<div class="template-cell">',
				'<div class="template-cell-caption" data-qtip="{author} {caption}">',
					'<span style="color: #f44336;">{subcaption1}</span><span style="color: #000">{subcaption2}</span> ',
					'<span style="color: #666">{author}</span>',
				'</div>',
				'<div class="template-cell-tools">{tools}</div>',
			'</div>'
		);

		var actionIdTpl = new Ext6.Template([
			'{wndId}-{name}-{id}'
		]);
		var toolTpl = new Ext6.Template([
			'<span id="{actionId}" class="template-cell-btn template-icon-{name} {cls}" data-qtip="{qtip}"></span>'
		]);
		var createTool = function(toolCfg) {
			if (toolCfg.hidden) return '';
			var obj = Ext6.apply({wndId: me.getId()}, toolCfg);
			obj.actionId = actionIdTpl.apply(obj);
			Ext6.defer(function() {
				var el = Ext.get(obj.actionId);
				if (el) el.on('click', function(e) {
					e.stopEvent();
					if (toolCfg.menu) {
						toolCfg.menu.showBy(e.target);
					}
					if (toolCfg.handler) {
						toolCfg.handler();
					}
				});
			}, 10);
			return toolTpl.apply(obj);
		};

		var toolsRenderer = function(value, meta, record) {
			if (!record.get('active')) return '';
			var id = record.get('XmlTemplate_id');

			var tools = [{
				id: id,
				name: 'select',
				qtip: 'Применить шаблон',
				handler: function() {
					me.select(record);
				}
			}, {
				id: id,
				name: 'edit',
				qtip: 'Открыть шаблон в редакторе',
				handler: function() {
					me.openTemplateSearchWindow(id);
				}
			}];

			return tools.map(createTool).join('');
		};

		var captionRenderer = function(value, meta, record) {
			if (Ext6.isEmpty(record.get('XmlTemplate_Caption'))) {
				return '';
			}

			var caption = record.get('XmlTemplate_Caption');
			var subcaption1 = '';
			var subcaption2 = caption;
			var author = record.get('Author_Fin');
			var query = me.QueryField.getValue();

			if (query.length > 0) {
				var l = query.length;
				var s1 = caption.slice(0, l);
				var s2 = caption.slice(l);

				if (s1.toLowerCase() == query.toLowerCase()) {
					subcaption1 = s1;
					subcaption2 = s2;
				}
			}

			return rowValueTpl.apply({
				subcaption1: subcaption1,
				subcaption2: subcaption2,
				caption: caption,
				author: author,
				tools: toolsRenderer.apply(me, arguments)
			});
		};

		me.grid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			tbar: toolbar,
			emptyText: 'Нет результатов.',
			userCls: 'template-search-grid',
			store: {
				fields: [
					{name: 'XmlTemplate_id', type: 'int'},
					{name: 'XmlTemplate_Caption', type: 'string'},
					{name: 'XmlTemplate_Descr', type: 'string'},
					{name: 'XmlTemplate_IsFavorite', type: 'int'},
					{name: 'Author_id', type: 'int'},
					{name: 'Author_Fin', type: 'string'},
					{name: 'XmlType_id', type: 'int'},
					{name: 'XmlType_Name', type: 'string'},
					{name: 'EvnClass_id', type: 'int'},
					{name: 'EvnClass_SysNick', type: 'string'},
					{name: 'EvnClass_Name', type: 'string'},
					{name: 'XmlTemplateScope_id', type: 'int'},
					{name: 'XmlTemplateScope_Name', type: 'string'},
					{name: 'active', type: 'bool', defaultValue: false}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=XmlTemplate6E&m=loadXmlTemplateList',
					reader: {type: 'json'}
				}
			},
			columns: [
				{dataIndex: 'XmlTemplate_IsFavorite', xtype: 'actioncolumn', width: 30, align: 'end',
					items: [{
						getClass: function(value) {
							return (value == 2)
								?'icon-star-active'
								:'icon-star';
						},
						getTip: function(value) {
							return (value == 2)
								?'Убрать из избранных'
								:'Добавить в избранное';
						},
						handler: function(grid, rowIndex, colIndex, item, e, record) {
							me.toggleFavorite(record);
						}
					}]
				},
				{dataIndex: 'XmlTemplate_Caption', flex: 1, renderer: captionRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function (model, record) {
						record.set('active', true);
					},
					deselect: function (model, record) {
						record.set('active', false);
					}
				}
			},
			listeners: {
				itemdblclick: function() {
					me.select();
				},
				itemmouseenter: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', true);
					}
				},
				itemmouseleave: function(grid, record) {
					if (grid.selection != record) {
						record.set('active', false);
					}
				}
			}
		});

		Ext6.apply(me, {
			panel: me.grid
		});

		me.callParent(arguments);
	}
});
Ext6.define('common.XmlTemplate.SpecMarkerBlockSelectPanel', {
	extend: 'Ext6.Panel',
	alias: 'widget.specmarkerselectpanel',
	layout: 'fit',
	border: false,

	onSelect: function(me, data){},
	EvnClass_id: null,

	setParams: function(params, reset) {
		var me = this;
		reset = !Ext6.isEmpty(reset)?reset:true;

		if (params && params.EvnClass_id) {
			me.EvnClass_id = params.EvnClass_id;
		}
		if (params && params.onSelect) {
			me.onSelect = params.onSelect;
		}

		if (reset) {
			me.queryField.setValue('');
			me.modeToggler.setValue('last');
		}

		me.load(reset);
	},

	select: function(record) {
		var me = this;
		record = record || me.grid.selection;
		if (!record) return;
		me.onSelect(record.data);
	},

	load: function(force) {
		var me = this;
		if (!me.el) return;
		var store = me.grid.getStore();
		var lastParams = store.lastOptions?store.lastOptions.params:{};

		var params = {
			EvnClass_id: me.EvnClass_id,
			query: me.queryField.getValue(),
			mode: me.modeToggler.getValue()
		};

		if (force || Ext6.encode(lastParams) != Ext6.encode(params)) {
			store.removeAll();
			store.load({params: params});
		}
	},

	initComponent: function() {
		var me = this;

		var delaySearch = function(delay) {
			if (me.delaySearchId) {
				clearTimeout(me.delaySearchId);
			}
			me.delaySearchId = setTimeout(function() {
				me.load();
				me.delaySearchId = null;
			}, delay);
		};

		me.queryField = Ext6.create('sw.form.QueryField', {
			name: 'query',
			emptyText: 'Поиск спецмаркера',
			margin: '0 0 0 12',
			width: 220,
			query: me.load.bind(me)
		});

		me.modeToggler = Ext6.create('Ext6.button.Segmented', {
			allowDepress: true,
			value: 'last',
			items: [{
				text: 'Избранные',
				value: 'favorite',
				cls: 'button-without-border btn-grey-blue',
				iconCls: 'favTemp-btn-icon'
			}, {
				text: 'Последние',
				value: 'last',
				cls: 'button-without-border btn-grey-blue',
				iconCls: 'lastStand-btn-icon'
			}],
			listeners: {
				change: function(toggler, value) {
					me.load();
				}
			}
		});

		var toolbar = Ext6.create('Ext6.toolbar.Toolbar', {
			cls: 'sw-toolbar-grey',
			border: true,
			items: [
				me.queryField,
				'->',
				me.modeToggler
			]
		});

		var rowValueTpl = new Ext6.Template(
			'<span style="color: #f44336;">{subname1}</span><span style="color: #000;">{subname2} </span>',
			'<span style="color: #999999; font-size: 12px; font-weight: 300;">{description}</span>'
		);

		var nameRenderer = function(value, meta, record) {
			if (Ext6.isEmpty(record.get('name'))) {
				return '';
			}

			var name = record.get('name');
			var subname1 = '';
			var subname2 = name;
			var description = record.get('description');
			var query = me.queryField.getValue();

			if (query.length > 0) {
				var l = query.length;
				var s1 = name.slice(0, l);
				var s2 = name.slice(l);

				if (s1.toLowerCase() == query.toLowerCase()) {
					subname1 = s1;
					subname2 = s2;
				}
			}

			meta.tdAttr = 'data-qtip="'+description+'"';

			return rowValueTpl.apply({
				subname1: subname1,
				subname2: subname2,
				description: description
			});
		};

		me.grid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			tbar: toolbar,
			emptyText: 'Нет результатов.',
			userCls: 'template-search-grid',
			store: {
				fields: [
					{name: 'id', type: 'int'},
					{name: 'name', type: 'string'},
					{name: 'description', type: 'string'},
					{name: 'isFavorite', type: 'int'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=XmlTemplate6E&m=loadSpecMarkerList',
					reader: {
						type: 'json'
					}
				},
				sorters: [
					'name'
				]
			},
			columns: [
				{dataIndex: 'isFavorite', xtype: 'actioncolumn', width: 30, align: 'end',
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
						handler: function() {
							log('favorite');	//todo: add to favorite
						}
					}]
				},
				{dataIndex: 'name', flex: 1, renderer: nameRenderer}
			],
			listeners: {
				itemdblclick: function(grid, record) {
					me.select(record);
				}
			}
		});

		Ext6.apply(me, {
			items: me.grid
		});

		me.callParent(arguments);
	}
});
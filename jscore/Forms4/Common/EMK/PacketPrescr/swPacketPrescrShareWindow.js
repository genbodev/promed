Ext6.define('common.EMK.PacketPrescr.swPacketPrescrShareWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swPacketPrescrShareWindow',
	renderTo: Ext.getCmp('main-center-panel').body.dom,
	autoShow: false,
	cls: 'arm-window-new arm-window-new-without-padding',
	title: 'Поделиться',
	width: 520,
	autoHeight: true,
	modal: true,

	apply: function() {
		var me = this;
		var records = me.pmUserGrid.store.data.items;

		var shareTo = records.map(function(record) {
			return {
				pmUser_getID: record.get('pmUser_id'),
				Lpu_gid: record.get('Lpu_id')
			};
		});

		if (shareTo.length == 0) {
			Ext6.Msg.alert(langs('Ошибка'), langs('Не выбраны пользователи для отправки пакета'));
			return;
		}

		var params = {
			PacketPrescr_id: me.PacketPrescr_id,
			shareTo: Ext6.encode(shareTo)
		};

		var infoMsg = sw4.showInfoMsg({
			type: 'loading',
			text: 'Отправка пакета ...',
			hideDelay: null
		});

		me.hide();

		Ext6.Ajax.request({
			url: '/?c=PacketPrescr&m=sharePacketPrescr',
			params: params,
			callback: function(options, success, response) {
				if (infoMsg) infoMsg.hide();
				var responseObj = Ext6.decode(response.responseText);

				if (responseObj.success) {
					sw4.showInfoMsg({
						type: 'success',
						text: 'Пакет отправлен'
					});
				} else {
					sw4.showInfoMsg({
						type: 'error',
						text: 'Ошибка при отправке пакета'
					});
				}
			}
		});
	},

	show: function() {
		var me = this;

		me.pmUserGrid.store.removeAll();

		me.callParent(arguments);

		if (!arguments[0] || !arguments[0].PacketPrescr_id) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не переданы все необходимые параметры'));
			return;
		}

		me.PacketPrescr_id = arguments[0].PacketPrescr_id;
	},

	initComponent: function() {
		var me = this;

		me.pmUserSearchField = Ext6.create('swBaseCombobox', {
			triggerAction: 'all',
			displayField: 'pmUser_Name',
			valueField: 'pmUser_id',
			queryMode: 'remote',
			name: 'query',
			minChars: 1,
			hideEmptyRow: true,
			forceSelection: true,
			enableKeyEvents: true,
			fieldLabel: 'Пользователь',

			tpl: new Ext6.XTemplate(
				'<tpl for=".">',
				'<tpl if="compareField == \'pmUser_Login\'">',
				'<div class="x6-boundlist-item" style="padding: 7px 10px 6px 20px;">',
				'<span style="line-height: 16px;">{[this.renderCaption(values.pmUser_Login)]}</span> ',
				'<span style="font-size: 11px; line-height: 17px; color: #666;">{pmUser_Name}. {Lpu_Nick}</span>',
				'</div>',
				'</tpl>',
				'<tpl if="compareField == \'pmUser_Name\'">',
				'<div class="x6-boundlist-item" style="padding: 7px 10px 6px 20px;">',
				'<span style="line-height: 16px;">{[this.renderCaption(values.pmUser_Name)]}</span> ',
				'<span style="font-size: 11px; line-height: 17px; color: #666;">{pmUser_Login}. {Lpu_Nick}</span>',
				'</div>',
				'</tpl>',
				'<tpl if="compareField == null">',
				'<div class="x6-boundlist-item" style="padding: 7px 10px 6px 20px;">',
				'<span style="line-height: 16px;">{pmUser_Name}</span> ',
				'<span style="font-size: 11px; line-height: 17px; color: #666;">{pmUser_Login}. {Lpu_Nick}</span>',
				'</div>',
				'</tpl>',
				'</tpl>',
				{
					renderCaption: function(caption) {
						var query = this.field.getRawValue();
						return '<span style="color: red;">'+caption.slice(0, query.length)+'</span>'+
							'<span style="font-weight: 500;">'+caption.slice(query.length)+'</span>';
					}
				}
			),

			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'id'},
					{name: 'pmUser_id'},
					{name: 'pmUser_Login'},
					{name: 'pmUser_Name'},
					{name: 'Lpu_id'},
					{name: 'Lpu_Nick'},
					{name: 'compareField'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=PacketPrescr&m=loadPMUserForShareList',
					reader: {type: 'json'}
				},
				listeners: {
					beforeload: function(store, operation) {
						var params = operation.getParams();
						params.PacketPrescr_id = me.PacketPrescr_id;
					},
					load: function(store) {
						var selectedRecords = me.pmUserGrid.store.data.items;
						var selectedIds = selectedRecords.map(function(record) {
							return record.get('id');
						});

						store.clearFilter();
						store.filterBy(function(record) {
							return !record.get('id').inlist(selectedIds);
						});
					}
				}
			}),

			refreshTrigger: function(value) {
				var me = this;
				var isEmpty = Ext6.isEmpty(value || me.getValue());
				me.triggers.clear.setVisible(!isEmpty);
				me.triggers.search.setVisible(isEmpty);
			},

			triggers: {
				picker: {
					hidden: true
				},
				search: {
					cls: 'x6-form-search-trigger',
					handler: function() {}
				},
				clear: {
					cls: 'sw-clear-trigger',
					hidden: true,
					handler: function() {
						me.templateSearchField.setValue(null);
						me.templateSearchField.refreshTrigger();
					}
				}
			},

			listeners: {
				afterrender: function(combo) {
					combo.refreshTrigger();
				},
				select: function(combo, record) {
					me.pmUserGrid.store.add(record);
					combo.setValue(null);
					combo.refreshTrigger(null);
				}
			}
		});

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: 20,
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: 100,
				matchFieldWidth: false
			},
			items: [
				me.pmUserSearchField
			]
		});

		var pmUserTpl = new Ext6.XTemplate([
			'<span>{pmUser_Name}</span>&nbsp;',
			'<span style="font-size: 11px; color: #999;">{pmUser_Login}. {Lpu_Nick}</span>'
		]);

		var pmUserRenderer = function(value, meta, record) {
			return pmUserTpl.apply(record.data);
		};

		me.pmUserGrid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			padding: '15 18',
			minHeight: 59,
			maxHeight: 250,
			userCls: 'template-share-user-grid',
			store: {
				fields: [
					{name: 'id'},
					{name: 'pmUser_id'},
					{name: 'pmUser_Login'},
					{name: 'pmUser_Name'},
					{name: 'Lpu_id'},
					{name: 'Lpu_Nick'}
				]
			},
			columns: [
				{dataIndex: 'pmUser_Name', flex: 1, renderer: pmUserRenderer},
				{xtype: 'actioncolumn', width: 30, align: 'end',
					items: [{
						getClass: function() { return 'sw-clear-trigger-big'; },
						getTip: function() { return 'Убрать'; },
						handler: function(grid, rowIndex, colIndex, row, e, record) {
							grid.store.remove(record);
						}
					}]
				}
			]
		});

		Ext6.apply(me, {
			items: [
				me.formPanel,
				me.pmUserGrid
			],
			buttons: [
				'->', {
					text: 'Отмена',
					cls: 'buttonCancel',
					handler: function() {
						me.hide();
					}
				}, {
					text: 'Отправить',
					cls: 'buttonAccept',
					handler: function() {
						me.apply();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});
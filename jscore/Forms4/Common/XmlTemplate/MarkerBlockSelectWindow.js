Ext6.define('common.XmlTemplate.MarkerBlockSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateMarkerBlockSelectWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new PolkaWP',
	userCls: 'template-search marker-widow-panel',
	title: 'Маркер документа',
	width: 890,
	height: 320,
	modal: true,

	apply: function() {
		var me = this;
		var record = me.grid.selection;
		var base_form = me.formPanel.form;
		var Code2011ListReg = /[AB]+[\.]?[0-9]+[\.]?[0-9]+[\.]?[0-9]+[,]*?/ig;

		if (!record) return;

		if (!base_form.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.formPanel.getFirstInvalidEl().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		var code = record.get('XmlMarkerType_Code');
		var key = 'marker'+code;
		var fieldNames = me.markerFieldsMap[key];

		var data = {};
		data.XmlMarkerType_Code = {
			value: record.get('XmlMarkerType_Code'),
			text: record.get('XmlMarkerType_Name')
		};

		fieldNames.forEach(function(fieldName) {
			var field = me.fields[fieldName];
			data[field.getName()] = {
				value: field.getValue(),
				text: field.getText()
			};
		});

		if (data.Code2011List && !Code2011ListReg.test(data.Code2011List)) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.fields.Code2011List.focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: langs('Проверьте формат списка кодов ГОСТ-2011'),
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		me.callback(data);
		me.hide();
	},

	reset: function() {
		var me = this;

		me.formPanel.hide();
		me.formPanel.removeAll(false);

		me.grid.store.removeAll();
		me.grid.store.load({
			callback: function(){
				var record = me.grid.store.getAt(0);
				me.grid.setSelection(record);
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

		me.reset();
	},

	initComponent: function() {
		var me = this;

		me.grid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			userCls: 'marker-grid-panel',
			bodyBorder: false,
			bodyPadding: '6 0 6 0',
			//flex: 1,
			width: 415,
			height: '100%',
			store: {
				fields: [
					{name: 'XmlMarkerType_id'},
					{name: 'XmlMarkerType_Code'},
					{name: 'XmlMarkerType_Name'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=EvnXml&m=loadXmlMarkerTypeList',
					reader: {type: 'json'}
				},
				sorters: [
					'XmlMarkerType_Code'
				]
			},
			columns: [
				{dataIndex: 'XmlMarkerType_Name', flex: 1}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					deselect: function() {
						me.formPanel.hide();
						me.formPanel.removeAll(false);
					},
					select: function(model, record) {
						var key = 'marker'+record.get('XmlMarkerType_Code');
						var fieldNames = me.markerFieldsMap[key];

						if (!fieldNames) {
							me.formPanel.hide();
							me.formPanel.removeAll(false);
							return;
						}

						var fields = fieldNames.filter(function(fieldName) {
							return me.fields[fieldName];
						}).map(function(fieldName) {
							return me.fields[fieldName];
						});

						me.fields.XmlDataSelectType.store.clearFilter();
						me.fields.XmlDataSelectType.store.filterBy(function(item) {
							return (
								record.get('XmlMarkerType_Code') == 3 ||
								!item.get('XmlDataSelectType_SysNick').inlist(['firstused','lastused'])
							);
						});

						me.formPanel.removeAll(false);
						me.formPanel.show();
						me.formPanel.add(fields);
						me.formPanel.form.reset();
					}
				}
			}
		});

		me.fields = {
			XmlType: Ext6.create('swCommonSprCombo', {
				comboSubject: 'XmlType',
				name: 'XmlType_id',
				fieldLabel: 'Тип документа',
				getText: function() {
					return me.fields.XmlType.getFieldValue('XmlType_Name');
				}
			}),
			UslugaComplexAttributeType: Ext6.create('swCommonSprCombo', {
				comboSubject: 'UslugaComplexAttributeType',
				name: 'UslugaComplexAttributeType_id',
				fieldLabel: 'Тип услуги',
				getText: function() {
					return me.fields.UslugaComplexAttributeType.getFieldValue('UslugaComplexAttributeType_Name');
				}
			}),
			XmlDataLevel: Ext6.create('swBaseCombobox', {
				queryMode: 'local',
				name: 'XmlDataLevel_SysNick',
				valueField: 'XmlDataLevel_SysNick',
				displayField: 'XmlDataLevel_Name',
				fieldLabel: 'Уровень',
				value: 'section',
				store: {
					fields: [
						{name: 'XmlDataLevel_id'},
						{name: 'XmlDataLevel_SysNick'},
						{name: 'XmlDataLevel_Name'}
					],
					data: [
						[1, 'section', 'Текущее движение/посещение'],
						[2, 'evn', 'Случай лечения (ТАП/КВС)'],
						[3, 'priem', 'Движение в приемном']
					]
				},
				getText: function() {
					return me.fields.XmlDataLevel.getFieldValue('XmlDataLevel_Name');
				}
			}),
			XmlDataSection: Ext6.create('swCommonSprCombo', {
				comboSubject: 'XmlDataSection',
				name: 'XmlDataSection_SysNick',
				valueField: 'XmlDataSection_SysNick',
				sortField: 'XmlDataSection_id',
				fieldLabel: 'Имя раздела',
				getText: function() {
					return me.fields.XmlDataSection.getFieldValue('XmlDataSection_Name');
				}
			}),
			SqlOrderType: Ext6.create('swBaseCombobox', {
				queryMode: 'local',
				name: 'SqlOrderType_SysNick',
				valueField: 'SqlOrderType_SysNick',
				displayField: 'SqlOrderType_Name',
				fieldLabel: 'Сортировка',
				value: 'asc',
				store: {
					fields: [
						{name: 'SqlOrderType_id'},
						{name: 'SqlOrderType_SysNick'},
						{name: 'SqlOrderType_Name'}
					],
					data: [
						[1, 'asc', 'Прямая хронологическая последовательность'],
						[2, 'desc', 'Обратная хронологическая последовательность']
					]
				},
				getText: function() {
					return me.fields.XmlDataLevel.getFieldValue('XmlDataLevel_Name');
				}
			}),
			XmlDataSelectType: Ext6.create('swBaseCombobox', {
				queryMode: 'local',
				name: 'XmlDataSelectType_SysNick',
				valueField: 'XmlDataSelectType_SysNick',
				displayField: 'XmlDataSelectType_Name',
				fieldLabel: 'Порядковый номер',
				value: 'last',
				getText: function() {
					return this.getFieldValue('XmlDataSelectType_Name');
				},
				store: {
					fields: [
						{name: 'XmlDataSelectType_id'},
						{name: 'XmlDataSelectType_SysNick'},
						{name: 'XmlDataSelectType_Name'}
					],
					data: [
						[1, 'first', langs('Первый документ')],
						[2, 'last', langs('Последний документ')],
						[3, 'firstused', 'Первый документ, в котором создан раздел'],
						[4, 'lastused', 'Последний документ, в котором создан раздел']
					]
				}
			}),
			Code2011List: Ext6.create('Ext6.form.Text', {
				name: 'code2011list',
				labelWidth: 132,
				fieldLabel: 'Коды ГОСТ-2011',
				getText: function() {
					return this.getFieldLabel+': '+this.getValue();
				}
			})
		};

		me.markerFieldsMap = {
			marker1: ['XmlType', 'XmlDataLevel', 'SqlOrderType'],
			marker2: ['XmlType', 'XmlDataLevel', 'XmlDataSection', 'XmlDataSelectType'],
			marker3: ['XmlType', 'XmlDataLevel', 'XmlDataSection', 'XmlDataSelectType'],
			marker10: ['UslugaComplexAttributeType', 'XmlDataLevel', 'SqlOrderType'],
			marker11: ['UslugaComplexAttributeType', 'XmlDataLevel', 'XmlDataSection', 'SqlOrderType'],
			marker12: ['UslugaComplexAttributeType', 'XmlDataLevel', 'SqlOrderType', 'Code2011List'],
			marker13: ['UslugaComplexAttributeType', 'XmlDataLevel', 'XmlDataSection', 'SqlOrderType', 'Code2011List']
		};

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			scrollable: true,
			userCls: 'marker-input-panel',
			bodyPadding: 20,
			flex: 1,
			height: '100%',
			bodyStyle: 'border-width: 0 0 0 1px',
			layout: 'vbox',
			defaults: {
				border: false,
				allowBlank: false,
				matchFieldWidth: false,
				width: '100%',
				labelWidth: 132
			},
			items: Object.keys(me.fields).map(function(key) {
				return me.fields[key];
			})
		});

		Ext6.apply(me, {
			layout: 'hbox',
			items: [
				me.grid,
				me.formPanel
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
					text: 'Применить',
					cls: 'buttonAccept',
					margin: '0 19 0 0',
					handler: function() {
						me.apply();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});
Ext6.define('common.XmlTemplate.ParameterBlockEditWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateParameterBlockEditWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new template-parameter-block-edit-window',
	title: 'Параметр',
	width: 640,
	height: 400,
	modal: true,
	constrain: true,
	save: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		if (me.valueListContainer.getFieldsCount() == 0) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				icon: Ext6.Msg.WARNING,
				msg: 'Не указаны значения параметра',
				title: ERR_INVFIELDS_TIT
			});
			return;
		}
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
		if (!me.valueListContainer.isValid()) {
			Ext6.Msg.show({
				buttons: Ext6.Msg.OK,
				fn: function() {
					me.valueListContainer.getFirstInvalid().focus();
				},
				icon: Ext6.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		var params = {
			ParameterValueList: me.valueListContainer.getValues({
				json: true/*, changed: true*/
			})
		};

		me.mask('Сохранение');

		base_form.submit({
			url: '/?c=XmlTemplate6E&m=saveParameterValue',
			params: params,
			success: function(form, action) {
				me.unmask();

				if (action.result.ParameterValue_id) {
					me.callback(action.result.ParameterValue_id);
					me.hide();
				}
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	show: function() {
		var me = this;
		var base_form = me.formPanel.getForm();

		me.callback = Ext6.emptyFn;
		me.ParameterValue_id = null;

		me.callParent(arguments);

		base_form.reset();
		me.valueListContainer.reset();

		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}
		if (arguments[0] && arguments[0].ParameterValue_id) {
			me.ParameterValue_id = arguments[0].ParameterValue_id;
		}

		if (!me.ParameterValue_id) {
			me.setTitle('Новый параметр');
			Ext6.getCmp(me.getId()+'-save-btn').setText('Создать');
		} else {
			me.setTitle('Параметр');
			Ext6.getCmp(me.getId()+'-save-btn').setText('Применить');
		}

		me.formPanel.form.reset();
		me.valueListContainer.reset();

		if (!me.ParameterValue_id) {
			me.valueListContainer.addValue();	//Добавление пустого поля значения
			return;
		}

		me.mask('Загрузка');

		base_form.load({
			params: {
				ParameterValue_id: me.ParameterValue_id
			},
			success: function (form, action) {
				me.unmask();

				me.setTitle(base_form.findField('ParameterValue_Name').getValue());

				if (action.result.data.ParameterValueList) {
					me.valueListContainer.setValues(action.result.data.ParameterValueList);
				}
			},
			failure: function (form, action) {
				me.unmask();
			}
		});
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 180;

		me.tmpId = 1;

		var getFieldContainerId = function(record) {
			return me.getId()+'-parameter-'+record.get('ParameterValue_id');
		};
		var getFieldId = function(record) {
			return me.getId()+'-parameter-field-'+record.get('ParameterValue_id');
		};

		var createValueField = function(record, index) {
			var number = me.valueFields.items.length + (index || 0) + 1;
			var id = record.get('ParameterValue_id');

			return {
				id: getFieldContainerId(record),
				layout: 'hbox',
				border: false,
				style: 'margin-bottom: 5px;',
				items: [{
					id: getFieldId(record),
					xtype: 'textfield',
					excludeForm: true,
					allowBlank: false,
					value: record.get('ParameterValue_Name'),
					listeners: {
						change: function(field, newValue, oldValue) {
							record.set('ParameterValue_Name', newValue);

							if (record.get('RecordStatus_Code') == 1 && field.originalValue != newValue) {
								record.set('RecordStatus_Code', 2);
							}
							if (record.get('RecordStatus_Code') == 2 && field.originalValue == newValue) {
								record.set('RecordStatus_Code', 1);
							}
						}
					},
					flex: 1
				}, {
					xtype: 'button',
					iconCls: 'sw-clear-trigger',
					width: 30,
					height: 30,
					handler: function() {
						me.valueListContainer.removeValue(id);
					}
				}]
			};
		};

		var removeValueField = function(record) {
			var comp = me.valueFields.queryById(getFieldContainerId(record));
			if (comp) me.valueFields.remove(comp, true);
		};

		me.valueLabel = Ext6.create('Ext6.form.Label', {
			text: 'Значения:',
			baseCls: 'x6-form-item-label',
			width: labelWidth + 5
		});

		me.valueFields = Ext6.create('Ext6.Panel', {
			border: false,
			flex: 1,
			getItems: function() {
				return this.items.items;
			}
		});

		me.valueLabelFields = Ext6.create('Ext6.Panel', {
			border: false,
			layout: 'hbox',
			style: 'margin-top: 10px;',
			items: [
				me.valueLabel,
				me.valueFields
			]
		});

		me.addValueFieldBtn = Ext6.create('Ext6.button.Button', {
			cls: 'parameter-add-value',
			iconCls: 'icon-add',
			text: 'Добавить значение',
			style: 'margin-left: '+(labelWidth+5)+'px; margin-top: 15px',
			handler: function() {
				me.valueListContainer.addValue();
			}
		});

		me.valueListContainer = Ext6.create('Ext6.Panel', {
			border: false,
			style: 'margin-top: 10px;',
			items: [
				me.valueLabelFields,
				me.addValueFieldBtn
			],
			setValues: function(values) {
				if (Ext6.isString(values)) {
					values = Ext6.JSON.decode(values);
				}
				if (Ext6.isArray(values)) {
					me.valueListContainer.store.loadData(values);
				}
			},
			getValues: function(options) {
				options = Ext6.apply({json: false, changed: false}, options);
				var store = me.valueListContainer.store;
				var getValue = function(item){return item.data};
				var filter = function(item){return true};

				if (options.changed) {
					filter = function(item){
						return item.get('RecordStatus_Code') != 1;
					};
				}

				var values = store.queryRecordsBy(filter).map(getValue);
				return options.json?Ext6.JSON.encode(values):values;
			},
			getFieldsCount: function() {
				return me.valueListContainer.getFields().length;
			},
			getFields: function() {
				return me.valueListContainer.store.data.items.map(function(item) {
					return me.valueFields.queryById(getFieldId(item));
				}).filter(function(field){return field});
			},
			isValid: function() {
				return me.valueListContainer.getFields().every(function(field) {
					return field.validate();
				});
			},
			getFirstInvalid: function() {
				return me.valueListContainer.getFields().find(function(field) {
					return !field.wasValid;
				});
			},
			addValue: function(obj) {
				var base_form = me.formPanel.getForm();
				var pid = base_form.findField('ParameterValue_id').getValue();

				if (!obj) {
					obj = {
						ParameterValue_id: -(++me.tmpId),
						ParameterValue_pid: pid,
						ParameterValue_Name: '',
						RecordStatus_Code: 0
					};
				}

				me.valueListContainer.store.add(obj);
			},
			removeValue: function(id) {
				var store = me.valueListContainer.store;
				var record = store.findRecord('ParameterValue_id', id);
				if (!record) return;

				if (record.get('RecordStatus_Code') == 0) {
					store.remove(record);
				} else {
					record.set('RecordStatus_Code', 3);
				}
			},
			reset: function() {
				var store = me.valueListContainer.store;
				store.removeAll(true);
				store.fireEvent('clear', store);
			},
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'ParameterValue_id'},
					{name: 'ParameterValue_pid'},
					{name: 'ParameterValue_Name'},
					{name: 'RecordStatus_Code'}
				],
				filters: [{
					property: 'RecordStatus_Code',
					value: /[^3]/
				}],
				listeners: {
					add: function(store, records) {
						var fields = records.map(createValueField);
						me.valueFields.add(fields);
					},
					remove: function(store, records) {
						records.forEach(removeValueField);
					},
					update: function(store, record, type, fieldNames) {
						if ('RecordStatus_Code'.inlist(fieldNames) && record.get('RecordStatus_Code') == 3) {
							removeValueField(record);
						}
					},
					refresh: function(store) {
						me.valueFields.removeAll();
						var fields = store.data.items.map(createValueField);
						me.valueFields.add(fields);
					},
					clear: function(store) {
						me.valueLabelFields.hide();
						me.valueFields.removeAll();
					},
					datachanged: function(store) {
						me.valueLabelFields.setVisible(store.count() > 0);
					}
				}
			})
		});

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			scrollable: true,
			bodyPadding: 20,
			url: '/?c=XmlTemplate6E&m=loadParameterValueForm',
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'ParameterValue_id'},
						{name: 'ParameterValue_Name'},
						{name: 'ParameterValue_Alias'},
						{name: 'ParameterValueListType_id'},
						{name: 'XmlTemplateScope_id'},
						{name: 'ParameterValueList'}
					]
				})
			}),
			items: [
				{
					xtype: 'hidden',
					name: 'ParameterValue_id'
				}, {
					border: false,
					padding: '0 30px 0 0',
					layout: {
						type: 'anchor',
					},
					defaults: {
						anchor: '100%',
						labelWidth: labelWidth,
						matchFieldWidth: false
					},
					items: [{
						allowBlank: false,
						xtype: 'textfield',
						name: 'ParameterValue_Alias',
						fieldLabel: 'Наименование параметра'
					}, {
						allowBlank: false,
						xtype: 'textfield',
						name: 'ParameterValue_Name',
						fieldLabel: 'Наименование для печати'
					}, {
						allowBlank: false,
						xtype: 'commonSprCombo',
						comboSubject: 'ParameterValueListType',
						name: 'ParameterValueListType_id',
						fieldLabel: 'Тип списка значений'
					}, {
						allowBlank: false,
						xtype: 'commonSprCombo',
						comboSubject: 'XmlTemplateScope',
						name: 'XmlTemplateScope_id',
						fieldLabel: 'Видимость'
					}]
				},
				me.valueListContainer
			]
		});

		Ext6.apply(me, {
			layout: 'fit',
			items: [
				me.formPanel
			],
			buttons: [
				'->',
				{
					text: 'Отмена',
					userCls: 'buttonCancel',
					handler: function() {
						me.hide();
					}
				}, {
					id: me.getId()+'-save-btn',
					cls: 'buttonAccept',
					text: 'Применить',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});
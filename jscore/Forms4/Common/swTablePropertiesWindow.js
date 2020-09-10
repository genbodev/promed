Ext6.define('common.swTablePropertiesWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swTablePropertiesWindow',
	autoShow: false,
	cls: 'arm-window-new save-template-window arm-window-new-without-padding',
	title: 'Свойства таблицы',
	renderTo: main_center_panel.body.dom,
	width: 520,
	modal: true,

	apply: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		if (!baseForm.isValid()) {
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

		me.callback(baseForm.getValues());
		me.hide();
	},

	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.callback = Ext6.emptyFn;

		me.callParent(arguments);

		baseForm.reset();

		if (arguments[0] && arguments[0].formParams) {
			baseForm.setValues(arguments[0].formParams);
		}
		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 140;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 20 20',
			trackResetOnLoad: false,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth
			},
			items: [{
				layout: 'hbox',
				border: false,
				margin: '0 0 5 0',
				defaults: {
					labelWidth: labelWidth
				},
				items: [{
					allowBlank: false,
					allowDecimals: false,
					flex: 1,
					xtype: 'numberfield',
					name: 'columns',
					fieldLabel: 'Количество столбцов',
					minValue: 1
				}, {
					allowBlank: false,
					allowDecimals: false,
					flex: 1,
					xtype: 'numberfield',
					name: 'rows',
					fieldLabel: 'Количество строк',
					labelAlign: 'right',
					minValue: 1
				}]
			}, {
				allowBlank: false,
				xtype: 'baseCombobox',
				name: 'align',
				fieldLabel: 'Выравнивание',
				editable: false,
				forceSelection: true,
				triggerAction: 'all',
				valueField: 'id',
				displayField: 'name',
				queryMode: 'local',
				store: {
					fields: [
						{name: 'id', type: 'string'},
						{name: 'name', type: 'string'}
					],
					data: [
						{id: 'left', name: 'по левому краю'},
						{id: 'center', name: 'по центру'},
						{id: 'right', name: 'по правому краю'}
					]
				}
			}]
		});

		Ext6.apply(me, {
			items: [
				me.formPanel
			],
			buttons: [
				'->',
				{
					cls: 'buttonCancel',
					text: 'Отмена',
					margin: 0,
					handler: function() {
						me.hide();
					}
				}, {
					cls: 'buttonAccept',
					text: 'Применить',
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
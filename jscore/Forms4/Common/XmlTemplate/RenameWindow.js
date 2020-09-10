Ext6.define('common.XmlTemplate.RenameWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swXmlTemplateRenameWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new save-template-window',
	title: 'Переименовать',
	width: 500,
	height: 150,
	modal: true,

	save: function() {
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

		me.mask('Сохранение...');

		baseForm.submit({
			url: '/?c=XmlTemplate6E&m=renameXmlTemplateItem',
			success: function(form, action) {
				me.unmask();

				me.callback(baseForm.getValues());
				me.hide();
			},
			failure: function(form, action) {
				me.unmask();
			}
		});
	},

	show: function() {
		var me = this;
		var baseForm = me.formPanel.getForm();

		me.formPanel.reset();
		me.callback = Ext6.emptyFn;

		me.callParent(arguments);

		if (!arguments[0] || !arguments[0].params) {
			me.hide();
			Ext6.Msg.alert(langs('Ошибка'), langs('Не переданы все необходимые параметры'));
			return;
		}

		if (arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		baseForm.setValues(arguments[0].params);
	},

	initComponent: function() {
		var me = this;
		var labelWidth = 80;

		me.formPanel = Ext6.create('Ext6.form.Panel', {
			border: false,
			bodyPadding: '20 20 0 20',
			trackResetOnLoad: false,
			enableKeyEvents: true,
			defaults: {
				anchor: '100%',
				labelWidth: labelWidth,
				matchFieldWidth: false
			},
			reader: Ext6.create('Ext6.data.reader.Json', {
				type: 'json',
				model: Ext6.create('Ext6.data.Model', {
					fields: [
						{name: 'XmlTemplate_id'},
						{name: 'XmlTemplateCat_id'},
						{name: 'name'}
					]
				})
			}),
			items: [{
				xtype: 'hidden',
				name: 'XmlTemplate_id'
			}, {
				xtype: 'hidden',
				name: 'XmlTemplateCat_id'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'name',
				fieldLabel: 'Название'
			}],
			keyMap: {
				'ENTER': function(e) {
					e.stopEvent();
					me.save();
				}
			}
		});

		Ext6.apply(me, {
			layout: 'vbox',
			defaults: {
				width: '100%'
			},
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
					text: 'Сохранить',
					handler: function() {
						me.save();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});
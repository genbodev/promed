/**
 * swPINCodeWindow - окно добавления/редактирования списка доступных локальных справочников
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      12.09.2018
 * @comment      Форма ввода пинкода
 */
Ext6.define('emd.swPINCodeWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swPINCodeWindow',
	title: langs('ПИН-код'),
	autoHeight: true,
	callback: Ext6.emptyFn,
	onHideWindow: Ext6.emptyFn,
	width: 300,
	layout: 'form',
	id: 'swPINCodeWindow',
	modal: true,
	listeners: {
		'hide': function() {
			this.onHideWindow();
		}
	},
	submit: function() {
		var me = this;
		if (!me.fieldForm.getForm().isValid()) {
			sw.swMsg.show({
				buttons: Ext6.Msg.OK,
				fn: function()
				{
					me.fieldForm.getFirstInvalidEl().focus(true);
				},
				icon: Ext6.Msg.WARNING,
				msg: langs('ПИН-код обязателен для заполнения'),
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (me.savePinObject && me.fieldForm.getForm().findField('save').checked) {
			if (!sw.savedPinCode) {
				sw.savedPinCode = {};
			}
			sw.savedPinCode[me.savePinObject] = me.fieldForm.getForm().findField('pin').getValue();
		}

		this.onHideWindow = Ext6.emptyFn; // просто скрываем форму, без вызова onHideWindow
		this.hide();
		if (typeof this.callback == 'function') {
			this.callback({
				pin: me.fieldForm.getForm().findField('pin').getValue()
			});
		}
	},
	show: function(data) {
		if (arguments[0] && arguments[0].savePinObject && sw.savedPinCode && sw.savedPinCode[arguments[0].savePinObject]) {
			if (arguments[0].callback) {
				arguments[0].callback({
					pin: sw.savedPinCode[arguments[0].savePinObject]
				});
			}

			return false;
		}

		var me = this;
		this.callParent(arguments);

		me.fieldForm.getForm().reset();
		me.callback = Ext6.emptyFn;
		me.onHideWindow = Ext6.emptyFn;
		me.savePinObject = null;

		if (arguments[0]) {
			if (arguments[0].callback) {
				me.callback = arguments[0].callback;
			}
			if (arguments[0].onHide) {
				me.onHideWindow = arguments[0].onHide;
			}
			if (arguments[0].savePinObject) {
				me.savePinObject = arguments[0].savePinObject;
			}
		}

		if (me.savePinObject) {
			this.fieldForm.getForm().findField('save').show();
		} else {
			this.fieldForm.getForm().findField('save').hide();
		}

		this.fieldForm.getForm().findField('pin').focus(100, true);
	},
	initComponent: function() {
		var me = this;

		this.fieldForm = Ext6.create('Ext6.form.FormPanel', {
			autoHeight: true,
			border: false,
			defaults: {
				labelAlign: 'top',
				listeners: {
					specialkey: function(field, e, eOpts) {
						if (e.getKey() == e.ENTER) {
							me.submit();
						}
					}
				}
			},
			items:[{
				xtype: 'textfield',
				width: 250,
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: langs('Введите ПИН-код'),
				inputType: 'password',
				name: 'pin',
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				name: 'save',
				boxLabel: 'Запомнить'
			}]
		});

		Ext6.apply(this, {
			buttons: [{
				handler: function() {
					me.hide();
				},
				text: BTN_FRMCANCEL
			}, {
				handler: function() {
					me.submit();
				},
				cls: 'flat-button-primary',
				text: langs('ОК')
			}],
			items: [
				me.fieldForm
			]
		});

		this.callParent(arguments);
	}
});
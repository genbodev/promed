/**
* swUserPasswordChangeWindow - окно смены пароля пользователя
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2016 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      03.2016
*
*/

sw.Promed.swUserPasswordChangeWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	shim: false,
	width: 500,
	autoHeight: true,
	closeAction: 'hide',
	id: 'swUserPasswordChangeWindow',
	objectName: 'swUserPasswordChangeWindow',
	title: 'Пароль',
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.saveUserData();
			},
			iconCls: 'save16',
			text: 'Согласен'
		},
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : 'Отказ',
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	show: function()
	{
		sw.Promed.swUserPasswordChangeWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		if (arguments && arguments[0] && arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		var win = this;
		var base_form = this.UserDataPanel.getForm();
		base_form.reset();

		var kolvo = 6;
		if (getGlobalOptions().password_minlength) {
			kolvo = getGlobalOptions().password_minlength;
		}

		var soder = "буквы в ";
		if (getGlobalOptions().password_hasuppercase) {
			soder += "верхнем и ";
		}
		soder += "нижнем регистре";
		if (getGlobalOptions().password_hasnumber) {
			soder += ", цифры";
		}
		if (getGlobalOptions().password_hasspec) {
			soder += ", специальные символы (@, #, $, *, % и т.п.)";
		}

		win.findById('passwordInfo').setText("Длина пароля – не менее "+kolvo+" символов. Пароль должен содержать "+soder+".");

		base_form.findField('old_password').focus(true, 100);
		base_form.clearInvalid();
		base_form.findField('new_password').removeClass('textFieldVerified');
		base_form.findField('new_password_two').removeClass('textFieldVerified');
	},

	saveUserData: function()
	{
		var win = this;
		var frm = win.UserDataPanel.getForm();
		if(!frm.isValid())
		{
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				fn: function()
				{
					win.UserDataPanel.getFirstInvalidEl().focus(false);
				},
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		if (frm.findField('new_password').getValue() != frm.findField('new_password_two').getValue()) {
			sw.swMsg.alert('Ошибка', 'Значения в полях "Новый пароль" и "Повторите пароль" не совпадают');
			return false;
		}

		if (!checkPassword(frm.findField('new_password').getValue())) {
			return false;
		}

		win.getLoadMask(LOAD_WAIT_SAVE).show();
		frm.submit({
			success: function(f, r)
			{
				win.getLoadMask().hide();
				win.hide();
			},
			failure: function()
			{
				win.getLoadMask().hide();
			}
		});
	},
	checkPasswordFields: function() {
		var base_form = this.UserDataPanel.getForm();
		// если пароль верный, то зелёная галочка
		if (checkPassword(base_form.findField('new_password').getValue(), true)) {
			base_form.findField('new_password').addClass('textFieldVerified');
			// если повторите пароль совпадает с паролем, то зелёная галочка
			if (!Ext.isEmpty(base_form.findField('new_password_two').getValue()) && base_form.findField('new_password_two').getValue() == base_form.findField('new_password').getValue()) {
				base_form.findField('new_password_two').addClass('textFieldVerified');
			} else {
				base_form.findField('new_password_two').removeClass('textFieldVerified');
			}
		} else {
			base_form.findField('new_password').removeClass('textFieldVerified');
			base_form.findField('new_password_two').removeClass('textFieldVerified');
		}
	},
	initComponent: function()
	{
		var win = this;

		this.UserDataPanel = new Ext.form.FormPanel({
			autoHeight: 'true',
			url: '/?c=User&m=changePassword',
			layout: 'form',
			frame: true,
			labelWidth: 120,
			defaults:
			{
				bodyStyle: 'padding: 5px;'
			},
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'old_password' },
				{ name: 'new_password' },
				{ name: 'new_password_two' }
			]),
			items: [{
				layout: 'column',
				defaults: {
					border: false
				},
				items: [{
					layout: 'form',
					columnWidth: .65,
					items: [{
						xtype: 'textfield',
						inputType : 'password',
						anchor: '-10',
						allowBlank: false,
						name: 'old_password',
						fieldLabel: 'Старый пароль'
					}, {
						xtype: 'textfield',
						inputType : 'password',
						anchor: '-10',
						allowBlank: false,
						listeners: {
							'change': function() {
								win.checkPasswordFields();
							}
						},
						name: 'new_password',
						fieldLabel: 'Новый пароль'
					}, {
						xtype: 'textfield',
						inputType : 'password',
						anchor: '-10',
						allowBlank: false,
						listeners: {
							'change': function() {
								win.checkPasswordFields();
							}
						},
						name: 'new_password_two',
						fieldLabel: 'Повторите пароль'
					}]
				}, {
					layout: 'form',
					columnWidth: .35,
					items: [{
						xtype: 'label',
						id: 'passwordInfo',
						html: ''
					}]
				}]
			}]
		});


		Ext.apply(this,
		{
			layout: 'form',
			defaults:
			{
				bodyStyle: 'padding: 3px;'
			},
			items: [this.UserDataPanel]
		});
		sw.Promed.swUserPasswordChangeWindow.superclass.initComponent.apply(this, arguments);
	}
});
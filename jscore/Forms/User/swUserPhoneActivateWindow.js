/**
 * swUserPhoneActivateWindow - окно активации номера мобильного телефона пользователя.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Messages
 * @access			public
 * @copyright		Copyright (c) 2013 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			23.04.2014
 */

/*NO PARSE JSON*/

sw.Promed.swUserPhoneActivateWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	width: 320,
	autoHeight: true,
	layout: 'form',
	id: 'swUserPhoneActivateWindow',
	title: lang['aktivatsiya_nomera_mobilnogo_telefona'],

	pmUser_id: null,

	sendActivationCode: function() {
		var params = new Object();
		params.pmUser_Login = this.pmUser_Login;
		params.user_phone = this.user_phone;

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет отправка СМС..." });
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();

			},
			success: function(result_form, action) {
				loadMask.hide();
				var result = Ext.util.JSON.decode(result_form.responseText);

				if (result.success) {
					if (!Ext.isEmpty(result.Error_Msg)) {
						Ext.Msg.alert(lang['preduprejdenie'], result.Error_Msg);
						this.callback({activatedPhone: this.user_phone});
						this.hide();
					}
				}
			}.createDelegate(this),
			url: '/?c=Messages&m=sendUserPhoneActivationCode'
		});
	},

	activate: function() {
		var base_form = this.FormPanel.getForm();
		var params = new Object();

		params.pmUser_Login = this.pmUser_Login;
		params.user_phone = this.user_phone;
		params.user_phone_act_code = base_form.findField('phone_act_code').getValue();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Активация телефона..." });
		loadMask.show();

		Ext.Ajax.request({
			params: params,
			failure: function(result_form, action) {
				loadMask.hide();

			},
			success: function(result_form, action) {
				loadMask.hide();
				var result = Ext.util.JSON.decode(result_form.responseText);

				if (result.success) {
					Ext.Msg.alert(lang['nomer_aktivirovan'], lang['nomer_byil_uspeshno_aktivirovan']);
					this.callback({activatedPhone: this.user_phone});
					this.hide();
				}
			}.createDelegate(this),
			url: '/?c=Messages&m=activateUserPhone'
		});
	},

	show: function() {
		sw.Promed.swUserPhoneActivateWindow.superclass.show.apply(this, arguments);

		this.pmUser_Login = null;
		this.callback = Ext.emptyFn;

		if (arguments[0] && arguments[0].user_phone) {
			this.user_phone = arguments[0].user_phone;
		} else {
			Ext.Msg.alert(lang['oshibka'], lang['doljen_byit_ukazan_nomer_mobilnogo_telefona']);
			this.hide();
		}

		if (arguments[0].pmUser_Login) {
			this.pmUser_Login = arguments[0].pmUser_Login;
		}

		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
	},

	initComponent: function() {
		var form = this;

		this.FormPanel = new Ext.form.FormPanel({
			url: '/?c=Messages&m=saveUserDataProfile',
			frame: true,
			items: [{
				xtype: 'button',
				text: lang['poluchit_sms_s_kodom_aktivatsii'],
				style: 'margin-bottom: 10px;',
				handler: function() {
					form.sendActivationCode();
				}
			}, {
				xtype: 'numberfield',
				name: 'phone_act_code',
				fieldLabel: lang['kod_aktivatsii'],
				width: 80
			}]
		});

		Ext.apply(this,{
			buttons: [{
				handler: function()
				{
					form.activate();
				},
				//iconCls: 'save16',
				text: lang['aktivirovat']
			},
			'-',
			{
				text: BTN_FRMHELP,
				iconCls: 'help16',
				handler: function(button, event)
				{
					ShowHelp(form.title);
				}
			}, {
				text      : lang['otmena'],
				tooltip   : lang['otmena'],
				iconCls   : 'cancel16',
				handler   : function() {
					form.hide();
				}
			}],
			items: [form.FormPanel]
		});

		sw.Promed.swUserPhoneActivateWindow.superclass.initComponent.apply(this, arguments);
	}
});
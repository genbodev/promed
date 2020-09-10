/**
 * swPersonPhoneVerificationWindow - окно для ввода кода подтверждения номера телефона
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      	Common
 * @access       	public
 * @copyright		Copyright (c) 2018 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			05.02.2018
 */
/*NO PARSE JSON*/

sw.Promed.swPersonPhoneVerificationCodeWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPhoneVerificationCodeWindow',
	width: 400,
	autoHeight: true,
	modal: true,
	closable: false,
	title: langs('Код подтверждения'),

	listeners: {
		'beforehide': function() {
			clearTimeout(this.refreshActionsTimeoutId);

			if (this.allowOnHide) {
				this.onHide({PersonPhoneStatus_id: this.status});
			}
		}
	},

	doSave: function(options) {
		var base_form = this.FormPanel.getForm();

		var callback = options.callback;
		if (typeof callback != 'function') {
			callback = function() {
				this.hide();
			}.createDelegate(this);
		}

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			MedStaffFact_id: base_form.findField('MedStaffFact_id').getValue(),
			PersonPhone_Phone: base_form.findField('PersonPhone_Phone').getValue(),
			PersonPhoneStatus_id: options.PersonPhoneStatus_id || null,
			PersonPhoneFailCause_id: options.PersonPhoneFailCause_id || null
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Подождите, идет сохранение..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Person&m=addPersonPhoneHist',
			params: params,
			success: function(response) {
				loadMask.hide();

				var answer = Ext.util.JSON.decode(response.responseText);

				if (answer.success) {
					this.status = params.PersonPhoneStatus_id;
					callback();
				}
			}.createDelegate(this),
			failure: function(response) {
				loadMask.hide();
			}.createDelegate(this)
		});
	},

	verify: function() {
		var base_form = this.FormPanel.getForm();

		var input_code = base_form.findField('PersonPhone_VerificationCode').getValue();

		if (Ext.isEmpty(this.code) || Ext.isEmpty(input_code)) {
			return;
		}

		if (this.code == input_code) {
			this.doSave({
				PersonPhoneStatus_id: 3,
				callback: function() {
					this.hide();
					showPopupInfoMsg('Номер успешно подтвержден!');
				}.createDelegate(this)
			});
		} else {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.WARNING,
				msg: langs('Вы ввели не правильный код. Проверьте введенные данные.'),
				title: langs('Ошибка')
			});
		}
	},

	sendVerificationCode: function(onError) {
		onError = onError || Ext.emptyFn();
		var wnd = this;
		var base_form = this.FormPanel.getForm();

		this.refreshActionsPanel();

		this.sendCount++;

		var params = {
			Person_id: base_form.findField('Person_id').getValue(),
			PersonPhone_Phone: base_form.findField('PersonPhone_Phone').getValue()
		};

		var loadMask = new Ext.LoadMask(this.getEl(), {msg: "Отправка кода подтверждения..."});
		loadMask.show();

		Ext.Ajax.request({
			url: '/?c=Person&m=sendPersonPhoneVerificationCode',
			params: params,
			success: function(response) {
				loadMask.hide();

				var answer = Ext.util.JSON.decode(response.responseText);

				if (answer.success && !Ext.isEmpty(answer.PersonPhone_VerificationCode)) {
					this.code = answer.PersonPhone_VerificationCode;

					this.refreshActionsPanel('delay');
					this.refreshActionsTimeoutId = setTimeout(function() {
						wnd.refreshActionsPanel('resend');
					}, 30 * 1000);
				} else {
					this.allowOnHide = false;
					this.hide();

					this.refreshActionsPanel('resend');

					var error_msg = langs('Ошибка при отправке смс-сообщения');
					if (!Ext.isEmpty(answer.Error_Msg)) {
						error_msg = answer.Error_Msg;
					}

					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							this.onHide();
						}.createDelegate(this),
						icon: Ext.Msg.WARNING,
						msg: error_msg,
						title: langs('Ошибка')
					});
				}
			}.createDelegate(this),
			failure: function() {
				loadMask.hide();
				this.refreshActionsPanel('resend');
				onError();
			}.createDelegate(this)
		});
	},

	refreshButtons: function() {
		var base_form = this.FormPanel.getForm();

		var code = base_form.findField('PersonPhone_VerificationCode').getValue();

		this.VerifyButton.setDisabled(String(code).length != 4);
	},

	refreshActionsPanel: function(action) {
		var params = {
			color: 'black',
			onClick: '',
			text: ''
		};

		switch(action) {
			case 'resend':
				if (this.sendCount < this.sendLimit) {
					params.color = '#e76d73';
					params.onClick = "Ext.getCmp('"+this.getId()+"').sendVerificationCode()";
					params.text = 'Запросить код подтверждения повторно';
				}
				break;
			case 'delay':
				params.color = '#4a7931';
				params.text = 'Запросить код подтверждения повторно можно будет через 30 секунд';
				break;
		}

		this.ActionsTpl.overwrite(this.ActionsPanel.body, params);
		this.ActionsPanel.setVisible(!Ext.isEmpty(params.text));
		this.syncShadow();
	},

	show: function() {
		sw.Promed.swPersonPhoneVerificationCodeWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		this.allowOnHide = true;
		this.code = null;
		this.status = null;
		this.sendLimit = 4;
		this.sendCount = 0;
		this.refreshActionsTimeoutId = null;

		if (!arguments[0] || !arguments[0].formParams) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.hide();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Не переданы необходимые параметры'),
				title: langs('Ошибка')
			});
			return;
		}

		if (arguments[0].onHide) {
			this.onHide = arguments[0].onHide;
		}

		var base_form = this.FormPanel.getForm();

		base_form.reset();
		base_form.setValues(arguments[0].formParams);

		base_form.findField('PersonPhone_VerificationCode').focus(true);

		this.TextTpl.overwrite(this.TextPanel.body, {
			phone: '+7 '+base_form.findField('PersonPhone_Phone').getValue()
		});

		this.refreshButtons();
		this.refreshActionsPanel();

		this.sendVerificationCode();
	},

	initComponent: function() {
		this.TextTpl = new Ext.XTemplate([
			'На номер телефона {phone} отправлено СМС с кодом подтверждения'
		]);

		this.TextPanel = new Ext.Panel({
			bodyStyle: 'font-size: 12px; padding:5px; margin-bottom: 5px;',
			border: false,
			html: ''
		});

		this.ActionsTpl = new Ext.XTemplate([
			'<p style="color: {color}; cursor: pointer;" onClick="{onClick}">{text}</p>'
		]);

		this.ActionsPanel = new Ext.Panel({
			bodyStyle: 'font-size: 12px; padding:5px; margin-bottom: 5px;',
			border: false,
			html: ''
		});

		this.FormPanel = new Ext.FormPanel({
			id: 'PPVCW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 126,
			keys: [{
				fn: function() {
					if (!this.VerifyButton.disabled) {
						this.verify();
					}
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}],
			items: [{
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'MedStaffFact_id'
			}, {
				xtype: 'hidden',
				name: 'PersonPhone_Phone'
			}, {
				allowBlank: false,
				xtype: 'textfield',
				name: 'PersonPhone_VerificationCode',
				fieldLabel: 'Код подтверждения',
				width: 45,
				maskRe: /\d/,
				minLength: 4,
				maxLength: 4,
				autoCreate: {tag: "input", maxLength: 4, autocomplete: "off"},
				enableKeyEvents: true,
				listeners: {
					'change': function(field, newValue, oldValue) {
						this.refreshButtons();
					}.createDelegate(this),
					'keyup': function(inp, e) {
						this.refreshButtons();
					}.createDelegate(this)
				}
			}]
		});

		this.CancelButton = new Ext.Button({
			id: 'PPVCW_CancelButton',
			text: langs('Отмена'),
			handler: function() {
				this.doSave({
					PersonPhoneStatus_id: 1,
					PersonPhoneFailCause_id: 3
				});
			}.createDelegate(this),
			minWidth: 80
		});

		this.VerifyButton = new Ext.Button({
			id: 'PPVCW_VerifyButton',
			text: langs('Подтвердить'),
			handler: function () {
				this.verify();
			}.createDelegate(this),
			minWidth: 80
		});

		Ext.apply(this, {
			buttons: [
				{
					text: '-'
				},
				this.CancelButton,
				this.VerifyButton
			],
			items: [{
				frame: true,
				items: [
					this.TextPanel,
					this.FormPanel,
					this.ActionsPanel
				]
			}]
		});

		sw.Promed.swPersonPhoneVerificationCodeWindow.superclass.initComponent.apply(this, arguments);
	}
});
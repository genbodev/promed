/**
 * swPersonPhoneVerificationWindow - окно подтверждения номера телефона
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

sw.Promed.swPersonPhoneVerificationWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swPersonPhoneVerificationWindow',
	width: 480,
	autoHeight: true,
	modal: true,
	closable: false,
	title: langs('Подтверждение номера телефона'),

	listeners: {
		'beforehide': function() {
			if (!this.openVerificationCodeWindow) {
				this.onHide();
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

		var phone = base_form.findField('PersonPhone_Phone').getValue();

		if (Ext.isEmpty(phone)) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					base_form.findField('PersonPhone_Phone').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: langs('Номер телефона должен быть заполнен'),
				title: ERR_INVFIELDS_TIT
			});
			return;
		}

		phone = phone.replace(/([-\(\)])/g,'');

		if (phone[0] != 9) {
			//Номер стационарного телефона. Подтверждаем.
			this.doSave({
				PersonPhoneStatus_id: 3,
				callback: function() {
					this.hide();
					showPopupInfoMsg('Номер успешно подтвержден!');
				}.createDelegate(this)
			});
		} else {
			//Номер мобильного телефона. Открывем окно для ввода кода подтверждения.
			var params = {};
			params.formParams = base_form.getValues();
			params.onHide = function() {
				this.onHide();
			}.createDelegate(this);

			getWnd('swPersonPhoneVerificationCodeWindow').show(params);
			this.openVerificationCodeWindow = true;
			this.hide();
		}
	},

	refreshButtons: function() {
		var base_form = this.FormPanel.getForm();

		var phone_field = base_form.findField('PersonPhone_Phone');
		var phone = phone_field.getValue();

		this.VerifyButton.setDisabled(Ext.isEmpty(phone));

		var itemsConfig = [{
			text: langs('Напомнить позже'),
			visible: true,
			status: 1,
			failCause: 1
		}, {
			text: langs('Отказ пациента'),
			visible: !Ext.isEmpty(phone),
			status: 2
		}, {
			text: langs('Телефон отсутствует'),
			visible: Ext.isEmpty(phone),
			status: 4
		}];

		this.CancelMenu.removeAll();

		itemsConfig.forEach(function(item){
			if (item.visible) this.CancelMenu.addMenuItem({
				text: item.text,
				handler: function () {
					this.doSave({
						PersonPhoneStatus_id: item.status,
						PersonPhoneFailCause_id: item.failCause
					});
				}.createDelegate(this)
			});
		}.createDelegate(this));
	},

	show: function() {
		sw.Promed.swPersonPhoneVerificationWindow.superclass.show.apply(this, arguments);

		this.onHide = Ext.emptyFn;
		this.openVerificationCodeWindow = false;

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

		var phone_field = base_form.findField('PersonPhone_Phone');
		phone_field.fireEvent('change', phone_field, phone_field.getValue());
		phone_field.focus(true);
	},

	initComponent: function() {
		var htmlText = [
			'<div class="x-window-dlg" style="margin-bottom: 7px;">',
			'<div class="ext-mb-icon ext-mb-warning"></div>',
			'<div class="ext-mb-content"><span class="ext-mb-text">',
			'Пациент не подтвердил свой номер телефона<br/>',
			'Если у пациента есть телефон, то необходимо его подтвердить',
			'</span></div>',
			'</div>'
		];

		this.FormPanel = new Ext.FormPanel({
			frame: true,
			id: 'PPVW_FormPanel',
			autoHeight: true,
			labelAlign: 'right',
			labelWidth: 61,
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
				html: htmlText
			}, {
				xtype: 'hidden',
				name: 'Person_id'
			}, {
				xtype: 'hidden',
				name: 'MedStaffFact_id'
			}, {
				allowBlank: false,
				xtype: 'swphonefield',
				name: 'PersonPhone_Phone',
				labelSeparator: '',
				fieldLabel: '+7',
				fieldWidth: 110,
				enableKeyEvents: true,
				onChange: function(field, value) {
					this.refreshButtons();
				}.createDelegate(this),
				onKeyUp: function(inp, e) {
					var phone = String(inp.getValue()).replace(/[-\(\)_]/g,'');
					this.VerifyButton.setDisabled(phone.length < 10);
				}.createDelegate(this)
			}]
		});

		this.CancelMenu = new Ext.menu.Menu({
			items: []
		});

		this.CancelButton = new Ext.Button({
			id: 'PPVW_CancelButton',
			text: langs('Отмена'),
			menu: this.CancelMenu,
			minWidth: 80
		});

		this.VerifyButton = new Ext.Button({
			id: 'PPVW_VerifyButton',
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
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swPersonPhoneVerificationWindow.superclass.initComponent.apply(this, arguments);
	}
});
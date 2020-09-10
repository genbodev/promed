/**
 * swRegistryHealDepErrorTypeSelectWindow - форма выбора ошибки МЗ
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2018 Swan Ltd.
 * @author       Dmitry Vlasenko
 * @version      18.11.2018
 */

sw.Promed.swRegistryHealDepErrorTypeSelectWindow = Ext.extend(sw.Promed.BaseForm, {
	width: 500,
	height: 100,
	modal: true,
	resizable: false,
	autoHeight: false,
	plain: false,
	title: langs('Выбор ошибки'),
	onCancel: function() {
	},
	show: function() {
		sw.Promed.swRegistryHealDepErrorTypeSelectWindow.superclass.show.apply(this, arguments);

		var win = this;

		if (arguments[0]) {
			if (arguments[0].callback && typeof arguments[0].callback == 'function') {
				win.callback = arguments[0].callback;
			}
		}

		win.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		if (base_form.findField('RegistryHealDepErrorType_id').getStore().getCount() == 0) {
			base_form.findField('RegistryHealDepErrorType_id').getStore().load();
		}
	},

	callback: Ext.emptyFn,

	onRegistryHealDepErrorTypeSelect: function() {
		var base_form = this.FormPanel.getForm();

		this.callback({
			RegistryHealDepErrorType_id: base_form.findField('RegistryHealDepErrorType_id').getValue()
		});

		this.hide();
	},

	initComponent: function() {

		var win = this;

		this.FormPanel = new Ext.form.FormPanel({
			border: false,
			frame: true,
			labelAlign: 'right',
			items: [{
				anchor: '100%',
				fieldLabel: 'Ошибка',
				hiddenName: 'RegistryHealDepErrorType_id',
				xtype: 'swregistryhealdeperrortypecombo'
			}]
		});

		Ext.apply(this, {
			buttonAlign: 'right',
			layout: 'fit',
			buttons: [{
				text: langs('Выбрать'),
				iconCls: 'ok16',
				handler: this.onRegistryHealDepErrorTypeSelect.createDelegate(this)
			}, '-', HelpButton(this), {
				text: langs('Закрыть'),
				iconCls: 'close16',
				handler: function(button, event) {
					win.onCancel();
					win.hide();
				}
			}],
			items: [
				this.FormPanel
			]

		});

		sw.Promed.swRegistryHealDepErrorTypeSelectWindow.superclass.initComponent.apply(this, arguments);
	}
});
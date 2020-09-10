/**
 * swSystemMonitorQueryEditWindow - окно редактирования запроса для мониторинга
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package			Admin
 * @access			public
 * @copyright		Copyright (c) 2014 Swan Ltd.
 * @author			Sabirov Kirill (ksabirov@swan.perm.ru)
 * @version			04.04.2014
 */

/*NO PARSE JSON*/

sw.Promed.swSystemMonitorQueryEditWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swSystemMonitorQueryEditWindow',
	width: 540,
	autoHeight: true,
	modal: true,

	formStatus: 'edit',
	action: 'view',
	callback: Ext.emptyFn,

	doSave: function() {
		if (this.formStatus == 'save') {
			return false;
		}
		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();
			}.createDelegate(this),
			success: function(result_form, action) {
				loadMask.hide();
				if (typeof this.callback == 'function') {
					this.callback();
				}
				this.formStatus = 'edit';
				this.hide();
			}.createDelegate(this)
		});
	},

	show: function() {
		sw.Promed.swSystemMonitorQueryEditWindow.superclass.show.apply(this, arguments);

		var form = this;
		var base_form = form.FormPanel.getForm();

		base_form.reset();

		if (arguments[0].action) {
			this.action = arguments[0].action;
		}
		if (arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
		if (arguments[0].callback) {
			base_form.setValues(arguments[0].formParams);
		}

		var loadMask = new Ext.LoadMask(form.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.clearInvalid();

		switch (this.action) {
			case 'add':
				form.enableEdit(true);
				form.setTitle(langs('Мониторинг системы: Добавление запроса'));
				loadMask.hide();
			break;

			case 'edit':
			case 'view':
				if (this.action == 'edit') {
					form.enableEdit(true);
					form.setTitle(langs('Мониторинг системы: Редактирование запроса'));
				} else {
					form.enableEdit(false);
					form.setTitle(lang['monitoring_sistemyi_prosmotr_zaprosa']);
				}

				base_form.load({
					failure:function () {
						sw.swMsg.alert(lang['oshibka'], lang['ne_udalos_poluchit_dannyie']);
						loadMask.hide();
						form.hide();
					},
					url: '/?c=SystemMonitor&m=loadSystemMonitorQueryForm',
					params: {SystemMonitorQuery_id: base_form.findField('SystemMonitorQuery_id').getValue()},
					success: function() {
						loadMask.hide();
					}
				});

			break;
		}
	},

	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			border: false,
			buttonAlign: 'left',
			frame: true,
			id: 'SMQEW_QueryEditForm',
			labelAlign: 'top',
			labelWidth: 150,
			url: '/?c=SystemMonitor&m=saveSystemMonitorQuery',

			items: [{
				name: 'SystemMonitorQuery_id',
				xtype: 'hidden'
			}, {
				allowBlank: false,
				name: 'SystemMonitorQuery_Name',
				fieldLabel: lang['nazvanie'],
				xtype: 'textfield',
				width: 515
			}, {
				allowBlank: false,
				name: 'SystemMonitorQuery_Query',
				fieldLabel: lang['zapros'],
				xtype: 'textarea',
				height: 180,
				width: 515
			}, {
				allowBlank: false,
				allowDecimals: false,
				allowNegative: false,
				name: 'SystemMonitorQuery_RepeatCount',
				fieldLabel: lang['kolichestvo_vyipolneniy_podryad'],
				xtype: 'numberfield',
				width: 515,
				value: 3
			}, {
				allowDecimals: true,
				allowNegative: false,
				name: 'SystemMonitorQuery_TimeLimit',
				fieldLabel: lang['prevyishenie'],
				xtype: 'numberfield',
				width: 515
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{name: 'SystemMonitorQuery_id'},
				{name: 'SystemMonitorQuery_Name'},
				{name: 'SystemMonitorQuery_Query'},
				{name: 'SystemMonitorQuery_RepeatCount'},
				{name: 'SystemMonitorQuery_TimeLimit'}
			]),
			keys: [{
				fn: function(e) {
					this.doSave();
				}.createDelegate(this),
				key: Ext.EventObject.ENTER,
				stopEvent: true
			}]
		});

		Ext.apply(this, {
			items: [
				this.FormPanel
			],
			buttons: [
				{
					text: BTN_FRMSAVE,
					id: 'SMQEW_ButtonSave',
					tooltip: lang['sohranit'],
					iconCls: 'save16',
					handler: function()
					{
						this.doSave();
					}.createDelegate(this)
				}, {
					text: '-'
				},
				HelpButton(this, 1),
				{
					handler: function () {
						this.hide();
					}.createDelegate(this),
					iconCls: 'cancel16',
					id: 'SMQEW_CancelButton',
					text: lang['otmenit']
				}]
		});

		sw.Promed.swSystemMonitorQueryEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
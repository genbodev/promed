/**
* swRegisterOutWindow - Исключение записи из регистра
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @author       Magafurov SM
* @version      05.2019
*/

sw.Promed.swRegisterOutWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: langs('Исключение записи из регистра'),
	autoHeight: true,
	callback: Ext.emptyFn,
	closeAction: 'hide',
	draggable: true,
	layout: 'form',
	modal: true,
	doSave: function() {
		var win = this,
			baseForm = win.FormPanel.getForm();
		win.showLoadMask('Сохранение..');
		baseForm.submit({
			clientValidation: true,
			success: function(form, action) {
				win.hideLoadMask();
				win.hide();
				win.callback();
			},
			failure: function(form,action) {
				win.hideLoadMask();
				switch (action.failureType) {
					case Ext.form.Action.CLIENT_INVALID:
						Ext.Msg.alert("Ошибка", "Заполните обязательные поля");
						break;
					default:
						Ext.Msg.alert("Ошибка", "Ошибка при сохранении");
				}
			}
		});
	},
	show: function()
	{
		sw.Promed.swRegisterOutWindow.superclass.show.apply(this, arguments);

		var win = this,
			base_form = win.FormPanel.getForm(),
			params = arguments[0] || {};

		if (!params || !params.Register_id || !params.Person_id || !params.Register_setDate) {
			sw.swMsg.show(
			{
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: langs('Ошибка открытия формы.<br/>Не указаны нужные входные параметры.'),
				title: langs('Ошибка'),
				fn: function() {
					this.hide();
				}.createDelegate(this)
			});
		}

		base_form.reset();

		win.callback = params.callback || Ext.emptyFn;

		base_form.setValues(params);
		base_form.findField('Register_disDate').setMinValue( params.Register_setDate );

		if(params.RegisterType_Code) {
			base_form.findField('RegisterDisCause_id').setRegisterFilter(params.RegisterType_Code);
		}

		win.InformationPanel.load({
			Person_id: params.Person_id
		});
	},

	initComponent: function() {
		var win = this;

		win.InformationPanel = new sw.Promed.PersonInformationPanelShort({
			region: 'north'
		});

		win.FormPanel = new Ext.form.FormPanel({
			frame: true,
			region: 'center',
			bodyStyle: 'padding: 5px',
			labelAlign: 'right',
			labelWidth: 200,
			url:'/?c=Register&m=out',
			items: [
				{
					name: 'Register_id',
					xtype: 'hidden'
				}, {
					name: 'Person_id',
					disabled: true,
					xtype: 'hidden'
				}, {
					name: 'Register_setDate',
					disabled: true,
					xtype: 'hidden',
				}, {
					allowBlank: false,
					fieldLabel: langs('Дата исключения из регистра'),
					name: 'Register_disDate',
					xtype: 'swdatefield',
					maxValue: new Date()
				}, {
					xtype: 'swregisterdiscausecombo',
					allowBlank: false
				}
			]
		});

		win.buttons = [
			{
				handler: function() {
					win.doSave();
				},
				iconCls: 'save16',
				text: BTN_FRMSAVE
			},
			{
				text: '-'
			},
			HelpButton(win),
			{
				handler: function()
				{
					win.hide();
				},
				iconCls: 'cancel16',
				text: BTN_FRMCANCEL
			}
		];

		Ext.apply(win, {
			items: [
				win.InformationPanel,
				win.FormPanel
			]
		});

		sw.Promed.swRegisterOutWindow.superclass.initComponent.apply(win, arguments);
	}
});
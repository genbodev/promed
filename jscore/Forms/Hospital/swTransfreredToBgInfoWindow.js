/**
 * sw.Promed.swTransfreredToBgInfoWindow Форма для отображения информации о переданных в казахстанский сервис квс / направлениях
 * Используется: sw.Promed.BaseJournal, sw.Promed.swEvnPSSearchWindow
 *
 */

sw.Promed.swTransfreredToBgInfoWindow = Ext.extend(sw.Promed.BaseForm, {
	id: 'swTransfreredToBgInfoWindow',
	title: "Информация о КВС из БГ", // Информация о направлении из БГ
	layout: 'form',
	resizable: false,
	autoHeight: true,
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	plain: true,
	xtype: 'panel',
	fields: ['id', 'code', 'insDate'],
	scenarioOptions: {
		EvnPS: {fields: ['id', 'insDate'], title: 'Информация о КВС из БГ'},
		EvnDirection: {fields: ['id', 'code', 'insDate'], title: 'Информация о направлении из БГ'}
	},

	buttons: [{
		text: '-'
	},
		{
			iconCls: 'close16',
			tabIndex: TABINDEX_RRLW + 14,
			handler: function() {
				Ext.getCmp('swTransfreredToBgInfoWindow').hide();
			}.createDelegate(),
			text: BTN_FRMCLOSE
		}],
	getMainForm: function ()
	{
		return Ext.getCmp('swTransfreredToBgInfoWindow_mainForm').getForm();
	},

	initComponent: function()
	{
		var formPanel = new Ext.Panel({
			region: 'center',
			labelAlign: 'right',
			labelWidth: 50,
			frame: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			autoHeight: true,
			items: [
				new Ext.form.FormPanel({
					id: 'swTransfreredToBgInfoWindow_mainForm',
					layout: 'form',
					region: 'center',
					autoScroll: true,
					bodyBorder: false,
					labelAlign: 'left',
					labelWidth: 170,
					border: false,
					frame: true,
					reader: new Ext.data.JsonReader({},
						[
							{name: 'id'},
							{name: 'code'},
							{name: 'insDate'}
						]),
					items: [{
						border: false,
						layout: 'column',
						items: [{
							border: false,
							labelWidth: 200,
							layout: 'form',
							items: [{
								fieldLabel: langs('Идентификатор'),
								text: '',
								hidden: true,
								name: 'id',
								width: 300,
								xtype: 'textfield',
								readOnly: true
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								border: false,
								labelWidth: 200,
								layout: 'form',
								items: [{
									fieldLabel: langs('Код'),
									text: '',
									hidden: true,
									name: 'code',
									width: 300,
									xtype: 'textfield',
									readOnly: true
								}]
							}]
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							layout: 'form',
							items: [{
								border: false,
								labelWidth: 200,
								layout: 'form',
								items: [{
									fieldLabel: langs('Дата передачи в БГ'),
									text: '',
									hidden: true,
									name: 'insDate',
									width: 300,
									xtype: 'textfield',
									readOnly: true
								}]
							}]
						}]
					}
					]
				})
			]
		});

		Ext.apply(this, {
			xtype: 'panel',
			items: [
				formPanel
			]
		});


		sw.Promed.swTransfreredToBgInfoWindow.superclass.initComponent.apply(this, arguments);
	},
	show: function(params) {

		var wnd = this,
			id = params['id'],
			scenario = params['scenario'],
			formPanel = wnd.findById('swTransfreredToBgInfoWindow_mainForm'),
			mainForm = formPanel.getForm(),
			fields = wnd.scenarioOptions[scenario]['fields'];

		wnd.setTitle(wnd.scenarioOptions[scenario]['title']);

		mainForm.reset();

		Ext.each(this.fields, function (field) {
			mainForm.findField(field).hide();
			mainForm.findField(field).getEl().up('.x-form-item').setDisplayed(false);
		});


		Ext.each(fields, function (field) {
			mainForm.findField(field).show();
			mainForm.findField(field).getEl().up('.x-form-item').setDisplayed(true);
		});

		mainForm.load({
			params: {id: id},

			url: '/?c=' + scenario + '&m=getInfo' + scenario + 'fromBg'
		});


		sw.Promed.swTransfreredToBgInfoWindow.superclass.show.apply(this, arguments);
	}
});

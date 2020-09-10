/**
* swPlanObsDispImportWindow - окно импорта данных плана КП ДН
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2019 Swan Ltd.
*/

/*NO PARSE JSON*/

sw.Promed.swPlanObsDispImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	title: 'Импорт ответа от ТФОМС',
	width: 400,
	//layout: 'form',
	resizable: false,
	plain: true,
	initComponent: function()
	{
		var win = this;

		this.ImportPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 36,
			html: ''
		});

		this.TextPanel = new Ext.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			id: 'ImportTextPanel',
			labelWidth: 50,
			url: '/?c=PlanObsDisp&m=importPlanObsDisp',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{name: 'recAll'},
				{name: 'recErr'}
			]),
			defaults: {
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: [{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: 'Выберите файл',
				fieldLabel: 'Файл',
				name: 'File'
			}, this.ImportPanel]
		});

		this.Panel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});

		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				handler: function () {
					win.doSave();
				},
				iconCls: 'refresh16',
				text: 'Загрузить'
			},
				{
					text: '-'
				},
				HelpButton(this),
				{
					handler: function () {
						win.hide();
					},
					iconCls: 'cancel16',
					text: BTN_FRMCANCEL
				}],
			items: [this.Panel]
		});
		sw.Promed.swPlanObsDispImportWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function()
	{
		var form = this.TextPanel;
		if (!form.getForm().isValid())
		{
			sw.swMsg.show(
				{
					buttons: Ext.Msg.OK,
					fn: function()
					{
						form.getFirstInvalidEl().focus(true);
					},
					icon: Ext.Msg.WARNING,
					msg: ERR_INVFIELDS_MSG,
					title: ERR_INVFIELDS_TIT
				});
			return false;
		}
		form.ownerCt.ownerCt.submit();
		return true;
	},
	callback: Ext.emptyFn,
	submit: function()
	{
		var form = this.TextPanel;
		var win = this;
		win.buttons[0].disable();
		win.getLoadMask('Загрузка и анализ файла. Подождите...').show();

		form.getForm().submit({
			failure: function (result_form, action) {
				win.buttons[0].enable();
				if (action.result) {

					if (action.result.Error_Msg) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки файла произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
				win.getLoadMask().hide();
			},
			success: function (result_form, action) {
				win.getLoadMask().hide();
				var answer = action.result;
				if (answer) {
					if (answer.success) {

						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: "Импорт успешно завершён",
							title: 'Сообщение'
						});
						win.callback();
						win.hide();
					}
					else {
						sw.swMsg.show(
							{
								buttons: Ext.Msg.OK,
								fn: function () {
									form.hide();
								},
								icon: Ext.Msg.ERROR,
								msg: 'Во время выполнения операции загрузки файла произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
								title: 'Ошибка'
							});
					}
				}
			}
		});
	},
	getLoadMask: function(MSG)
	{
		if (MSG)
		{
			delete(this.loadMask);
		}
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: MSG });
		}
		return this.loadMask;
	},
	show: function()
	{
		sw.Promed.swPlanObsDispImportWindow.superclass.show.apply(this, arguments);
		var win = this;
		win.buttons[0].enable();
		win.TextPanel.getForm().reset();

		win.getLoadMask().hide();

		this.callback = null;
		if (arguments[0] && arguments[0].callback) {
			this.callback = arguments[0].callback;
		}
	}
});
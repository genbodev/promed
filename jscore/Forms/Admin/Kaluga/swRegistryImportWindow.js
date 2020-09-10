/**
 * swRegistryImportWindow - окно загрузки реестра-ответа в формате DBF.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2011 Swan Ltd.
 * @author       Власенко Дмитрий
 * @version      11.04.2013
 * @comment      Префикс для id компонентов RIW (RegistryImportWindow)
 *
 *
 * @input data: Registry_id - ID реестра
 */

sw.Promed.swRegistryImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportWindow',
	title: 'Импорт реестра из ТФОМС',
	width: 400,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function()
	{

		this.RegistryImportTpl = new Ext.Template([
			'<div>{recAll}</div><div>{recErr}</div><div>{recErrNotIdentified}</div><div>{recErrFixed}</div><div>{recErrAlreadyFixed}</div><div>{errorlink}</div> <div>{dates}</div>'
		]);

		this.RegistryImportPanel = new Ext.Panel({
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 90,
			//maxSize: 30,
			html: ''
		});

		this.TextPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			labelWidth: 50,
			timeout: 999999999,
			url: '/?c=Registry&m=importRegistryFromTFOMS',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'Registry_id' }
			]),
			//html: 'Загрузка данных проверенного реестра'
			defaults: {
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: [{
					allowedExtensions: ['dbf'],
					xtype: 'fileuploadfield',
					anchor: '95%',
					emptyText: 'Выберите файл реестра',
					fieldLabel: 'Файл',
					name: 'RegistryFile'
				},
				this.RegistryImportPanel
			]
		});

		this.Panel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			//frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});

		Ext.apply(this, {
			autoHeight: true,
			buttons: [{
				id: 'RIW_Ok',
				handler: function()
				{
					this.ownerCt.doSave();
				},
				iconCls: 'refresh16',
				text: 'Загрузить'
			},
			{
				text: '-'
			},
			HelpButton(this),
			{
				handler: function()
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'RIW_Ok',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryImportWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners:
	{
		'hide': function()
		{
			this.onHide();
		}
	},
	doSave: function()
	{
		var form = this.TextPanel;
		if (!form.getForm().isValid())
		{
			sw.swMsg.show({
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
		this.submit();
		return true;
	},
	submit: function()
	{
		var form = this.TextPanel;
		var win = this;

		Ext.getCmp('RIW_Ok').disable();

		form.getForm().submit({
			params:
			{
				Registry_id: win.Registry_id
			},
			timeout: 3600000,
			failure: function(result_form, action)
			{
				Ext.getCmp('RIW_Ok').enable();

				if ( action.result )
				{
					if ( action.result.Error_Msg )
					{
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else
					{
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			},
			success: function(result_form, action)
			{
				var answer = action.result;
				if (answer) {
					if (answer.Registry_id && answer.path) { // Если сообщение содержит Id реестра и путь к загруженному файлу то вызовем этот же адрес повторно с этим же путем
						Ext.Ajax.request({
							url: '/?c=Registry&m=importRegistryFromTFOMS',
							params: {
								Registry_id: win.Registry_id, 
								path: answer.path
							},
							success: function(response, action) {
								log('Загрузка файла завершена...');
							}
						});
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.callback();
								win.hide();
							},
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});
					}
					else {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});

		// закрываем форму, а запрос пусть крутится в фоне TODO
		// this.hide();
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
		sw.Promed.swRegistryImportWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.Registry_id = null;
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('RIW_Ok').enable();
		form.TextPanel.getForm().reset();
		if (!arguments[0] || !arguments[0].Registry_id)
		{
			sw.swMsg.show(
					{
						buttons: Ext.Msg.OK,
						icon: Ext.Msg.ERROR,
						msg: 'Ошибка открытия формы ' + form.id + '.<br/>Не указаны необходимые входные параметры.',
						title: 'Ошибка'
					});
			this.hide();
		}
		form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body,
				{});

		if (arguments[0].Registry_id)
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (typeof arguments[0].callback == 'function')
		{
			form.callback = arguments[0].callback;
		}
		if (typeof arguments[0].onHide == 'function')
		{
			form.onHide = arguments[0].onHide;
		}
	}
});
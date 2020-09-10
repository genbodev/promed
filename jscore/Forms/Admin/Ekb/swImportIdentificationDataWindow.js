/**
* swImportIdentificationDataWindow - окно загрузки ответа от сервиса идентификации.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2014 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      23.05.2014
* @comment      Префикс для id компонентов iidw (ImportIdentificationDataWindow)
*
*/

sw.Promed.swImportIdentificationDataWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'ImportIdentificationDataWindow',
	title: 'Загрузка ответа',
	width: 400,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		
		this.IdentificationImportTpl = new Ext.Template(
		[
			'<div>{recAll}</div> <div>{dates}</div>'
		]);
		
		this.IdentificationImportPanel = new Ext.Panel(
		{
			id: 'IdentificationImportPanel',
			bodyStyle: 'padding:2px',
			layout: 'fit',
			border: true,
			frame: false,
			height: 36,
			//maxSize: 30,
			html: ''
		});
	
		this.TextPanel = new Ext.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			id: 'IdentificationImportTextPanel',
			labelWidth: 50,
			timeout: 7200000,
			url: '/?c=Registry&m=importIdentificationData',
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{ name: 'recAll' }
			]),
			defaults: 
			{
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: 
			[{
				xtype: 'fileuploadfield',
				id: 'riwuIdentificationFile',
				anchor: '95%',
				emptyText: 'Выберите файл ответа',
				fieldLabel: 'Файл',
				name: 'IdentificationFile'
				/*,
				buttonText: '...',
				buttonCfg: 
				{
					text: '',
					iconCls: 'file-upload16'
				}*/
			},
			this.IdentificationImportPanel]
		});
		
		this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			//frame: true,
			id: 'IdentificationImportPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'iidwOk',
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
				onTabElement: 'iidwOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swImportIdentificationDataWindow.superclass.initComponent.apply(this, arguments);
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
		var win = this;
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
		
		//win.getLoadMask('Загрузка файла ответа. Подождите...').show();
		sw.swMsg.show({
			buttons: Ext.Msg.OK,
			fn: function() {
				win.hide();
			},
			icon: Ext.Msg.INFO,
			msg: 'Обработка ответа производится в фоновом режиме',
			title: 'Сообщение'
		});

		var proc = form.getForm().submit(
		{
			failure: function(result_form, action) 
			{
				win.getLoadMask().hide();
				if ( action.result ) 
				{
					
					if ( action.result.Error_Msg ) 
					{
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else 
					{
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки файла произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.');
					}
				}
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				var answer = action.result;
				if (answer && answer.success) {
					if (answer.Message) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							fn: function() {
								win.hide();
							},
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});
					}
				} else {
					sw.swMsg.show({
						buttons: Ext.Msg.OK,
						fn: function() {
							win.hide();
						},
						icon: Ext.Msg.ERROR,
						msg: 'Во время выполнения операции загрузки файла произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
						title: 'Ошибка'
					});
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
		sw.Promed.swImportIdentificationDataWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('iidwOk').enable();
		form.TextPanel.getForm().reset();
		form.IdentificationImportTpl.overwrite(form.IdentificationImportPanel.body, 
		{});
						
		if (arguments[0] && arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
	}
});
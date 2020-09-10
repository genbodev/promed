/**
* swRegistryImportFLKWindow - окно загрузки реестра-ответа в формате XML.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2011 Swan Ltd.
* @author       Власенко Дмитрий
* @version      23.12.2011
* @comment      Префикс для id компонентов rifw (RegistryImportFLKWindow)
*
*
* @input data: Registry_id - ID реестра
*/

sw.Promed.swRegistryImportFLKWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportFLKWindow',
	title: 'Импорт результата ФЛК (ТФОМС/СМО)',
	width: 500,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		
		this.RegistryImportTpl = new Ext.Template(
		[
			'<div>{recAll}</div> <div>{dates}</div>'
		]);
		
		this.RegistryImportPanel = new Ext.Panel(
		{
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
			bodyStyle: 'padding: 0 5px',
			frame: true,
			labelWidth: 50,
			url: '/?c=Registry&m=importRegistryFLK',
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{ name: 'Registry_id' },
				{ name: 'recAll' }
			]),
			//html: 'Загрузка данных проверенного реестра'
			defaults: 
			{
				anchor: '95%',
				allowBlank: false,
				msgTarget: 'side'
			},
			items: 
			[{
				xtype: 'fileuploadfield',
				anchor: '95%',
				emptyText: 'Выберите файл реестра',
				fieldLabel: 'Реестр',
				name: 'RegistryFile'
				/*,
				buttonText: '...',
				buttonCfg: 
				{
					text: '',
					iconCls: 'file-upload16'
				}*/
			},
			this.RegistryImportPanel]
		});
		
		this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			//frame: true,
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rifwOk',
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
				onTabElement: 'rifwOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryImportFLKWindow.superclass.initComponent.apply(this, arguments);
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
	submit: function() 
	{
		var form = this.TextPanel;
		var win = this;
		win.getLoadMask('Загрузка и анализ реестра. Подождите...').show();
		
		form.getForm().submit(
		{
			params: 
			{
				Registry_id: win.Registry_id
			},
			timeout: 3600000,
			failure: function(result_form, action) 
			{
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
				win.getLoadMask().hide();
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				var answer = action.result;
				if (answer) 
				{
					if (answer.Registry_id)
					{
						
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});
						win.RegistryImportTpl.overwrite(win.RegistryImportPanel.body, 
						{
							recAll: "Всего записей с ошибками:  <b>"+answer.recAll+"</b>"
						});
						Ext.getCmp('rifwOk').disable();
						win.callback();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								form.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки реестра произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
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
		sw.Promed.swRegistryImportFLKWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.Registry_id = null;
		form.callback = Ext.emptyFn;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rifwOk').enable();
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
		//form.findById('rifwRegistryFile').setValue('');
		form.RegistryImportTpl.overwrite(form.RegistryImportPanel.body, 
		{});
						
		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (typeof arguments[0].callback == 'function') 
		{
			form.callback = arguments[0].callback;
		}

		form.getLoadMask('Проверка наличия оплаченных реестров').show();
		Ext.Ajax.request({
			url: '/?c=Registry&m=checkRegistryHasPaidInside',
			params: {
				Registry_id: form.Registry_id
			},
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				if (success && response.responseText)
				{
					var result = Ext.util.JSON.decode(response.responseText);
					if (result.existPaid) {
						sw.swMsg.show({
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.ERROR,
							msg: 'Перед импортом снимите отметку "оплачен" у всех реестров, входящих в объединенный реестр.',
							title: 'Ошибка'
						});
						form.hide();
					}
				}
			}
		});
	}
});
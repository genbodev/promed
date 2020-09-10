/**
* swRegistryImportWindow - окно выгрузки реестра в DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Марков Андрей
* @version      22.12.2009
* @comment      Префикс для id компонентов rdf (RegistryImportWindow)
*
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swRegistryImportWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportWindow',
	title: 'Импорт реестра DBF',
	width: 400,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		
		this.RegistryImportTpl = new Ext.Template(
		[
			'<div>{recAll} {recErr}</div> <div>{dates}</div>'
		]);
		
		this.RegistryImportPanel = new Ext.Panel(
		{
			id: 'RegistryImportPanel',
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
			id: 'RegistryImportTextPanel',
			labelWidth: 50,
			url: '/?c=RegistryUfa&m=importRegistryFromDbf',
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{ name: 'Registry_id' },
				{ name: 'recAll' },
				{ name: 'recErr' }
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
				id: 'riwuRegistryFile',
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
			id: 'RegistryImportPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'rifOk',
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
				onTabElement: 'rifOk',
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
							recAll: "Всего записей в реестре: <b>"+answer.recAll+"</b>", 
							recErr: ", с ошибками:  <b>"+answer.recErr+"</b>", 
							dates: "Период реестра: "+answer.dateBeg+" - "+answer.dateEnd
						});
						Ext.getCmp('rifOk').disable();
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
		sw.Promed.swRegistryImportWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.Registry_id = null;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rifOk').enable();
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
		//form.findById('riwuRegistryFile').setValue('');
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
	}
});
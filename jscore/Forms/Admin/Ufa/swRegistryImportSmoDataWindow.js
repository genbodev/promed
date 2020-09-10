/**
* swRegistryImportSmoDataWindow - окно выгрузки реестра в DBF.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Пшеницын Иван
* @version      26.11.2010
* @comment      Префикс для id компонентов risdw (RegistryImportSmoDataWindow)
*
*/

sw.Promed.swRegistryImportSmoDataWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'RegistryImportSmoDataWindow',
	title: 'Импорт исправленых данных DBF',
	width: 400,
	//layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		
		this.RegistryImportTpl = new Ext.Template(
		[
			'<div>{recAll} {recUpd} {recErr}</div>'
		]);
		
		this.RegistryImportSmoDataPanel = new Ext.Panel(
		{
			id: 'RegistryImportSmoDataPanel',
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
			id: 'RegistryImportSmoDataTextPanel',
			labelWidth: 50,
			url: '/?c=RegistryUfa&m=importRegistrySmoDataFromDbf',
			reader: new Ext.data.JsonReader(
			{
				success: Ext.emptyFn
			}, 
			[
				{ name: 'recAll' },
				{ name: 'recUpd' },
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
				id: 'riwuRegistrySmoDataFile',
				anchor: '95%',
				emptyText: 'Выберите файл для импорта',
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
			this.RegistryImportSmoDataPanel]
		});
		
		this.Panel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			//frame: true,
			id: 'RegistryImportSmoDataPanelPanel',
			labelAlign: 'right',
			labelWidth: 100,
			items: [this.TextPanel]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				id: 'risdwOk',
				handler: function() 
				{
					this.doSave();
				}.createDelegate(this),
				iconCls: 'refresh16',
				text: 'Загрузить'
			}, 
			{				text: '-'
			},
			HelpButton(this),
			{
				handler: function() 
				{
					this.ownerCt.hide();
				},
				iconCls: 'cancel16',
				onTabElement: 'risdwOk',
				text: BTN_FRMCANCEL
			}],
			items: [this.Panel]
		});
		sw.Promed.swRegistryImportSmoDataWindow.superclass.initComponent.apply(this, arguments);
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

		this.submit();

		return true;
	},
	submit: function() 
	{
		var form = this.TextPanel;
		var win = this;
		win.getLoadMask('Загрузка и анализ данных. Подождите...').show();

		form.getForm().submit({
			clientValidation: true,
			failure: function(result_form, action) {
				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert('Ошибка', action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert('Ошибка', 'Во время выполнения операции загрузки данных произошла ошибка.<br/>Пожалуйста, повторите попытку позже.');
					}
				}
				win.getLoadMask().hide();
			},
			params: {
				Registry_id: win.Registry_id
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				var answer = action.result;

				if (answer) 
				{
					if ( answer.success && answer.success === true )
					{
						
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							icon: Ext.Msg.INFO,
							msg: answer.Message,
							title: 'Сообщение'
						});
						win.RegistryImportTpl.overwrite(win.RegistryImportSmoDataPanel.body, 
						{
							recAll: "Всего записей: <b>"+answer.recAll+"</b>", 
							recUpd: ", измененных: <b>"+answer.recUpd+"</b>", 
							recErr: ", с ошибками:  <b>"+answer.recErr+"</b>"
						});

						if ( answer.recUpd > 0 ) {
							form.callback();
						}

						Ext.getCmp('risdwOk').disable();
					}
					else
					{
						sw.swMsg.show(
						{
							buttons: Ext.Msg.OK,
							fn: function() 
							{
								win.hide();
							},
							icon: Ext.Msg.ERROR,
							msg: 'Во время выполнения операции загрузки данных произошла ошибка.<br/>Пожалуйста, повторите попытку чуть позже.',
							title: 'Ошибка'
						});
					}
				}
			}
		});
	},
	getLoadMask: function(MSG)
	{
		if ( MSG ) {
			delete(this.loadMask);
		}

		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: MSG });
		}

		return this.loadMask;
	},
	show: function() {
		sw.Promed.swRegistryImportSmoDataWindow.superclass.show.apply(this, arguments);

		this.callback = Ext.emptyFn;
		this.onHide = Ext.emptyFn;
		this.Registry_id = null;

		this.buttons[0].enable();
		this.getLoadMask().hide();
		this.TextPanel.getForm().reset();

		this.TextPanel.getForm().findField('RegistryFile').fireEvent('change', this.TextPanel.getForm().findField('RegistryFile'), '');

		if ( !arguments[0] || !arguments[0].Registry_id ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				icon: Ext.Msg.ERROR,
				msg: 'Ошибка открытия формы ' + this.id + '.<br/>Не указаны необходимые входные параметры.',
				title: 'Ошибка'
			});

			this.hide();
			return false;
		}
		//this.findById('riwuRegistryFile').setValue('');
		this.RegistryImportTpl.overwrite(this.RegistryImportSmoDataPanel.body, {});

		if ( arguments[0].callback )  {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide )  {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].Registry_id ) {
			this.Registry_id = arguments[0].Registry_id;
		}
	}
});
/**
 * swPersonXmlWindow - окно выгрузки прикрепленного населения в XML.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
 * @author
 * @version      22.06.2011
 * @comment      Префикс для id компонентов rxw (PersonXmlWindow)
 *
 *
 * @input data: arm - из какого АРМа ведётся выгрузка
 */

sw.Promed.swPersonXmlWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'PersonXmlWindow',
	title: 'Выгрузка списка прикрепленного населения',
	width: 460,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryXmlTextPanel',
			html: 'Выгрузка списка прикрепленного населения в формате Xml'
		});

		win.radioButtonGroup = new sw.Promed.Panel({
			items: [{
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'Скачать файл с сервера',
				inputValue: 0,
				id: 'rxw_radio_useexist',
				name: 'exporttype',
				checked: true
			}, {
				xtype: 'radio',
				hideLabel: true,
				boxLabel: 'Сформировать новый файл',
				inputValue: 1,
				id: 'rxw_radio_usenew',
				name: 'exporttype'
			}]
		});

		win.Panel = new Ext.form.FormPanel(
			{
				autoHeight: true,
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: true,
				id: 'RegistryXmlPanel',
				labelAlign: 'right',
				items: [{
					border: false,
					labelWidth: 100,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: 'Дата выгрузки',
						name: 'Date_upload',
						width: 100,
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						xtype: 'swdatefield'
					}]
				}, {
					border: false,
					labelWidth: 100,
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel: 'МО',
						hiddenName: 'Lpu_id',
						width: 300,
						xtype: 'swlpucombo'
					}]
				},
					win.radioButtonGroup,
					win.TextPanel
				]
			});

		Ext.apply(this,
			{
				autoHeight: true,
				buttons: [
					{
						id: 'rxfOk',
						handler: function()
						{
							win.createXML();
						},
						iconCls: 'refresh16',
						text: 'Сформировать'
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
						onTabElement: 'rxfOk',
						text: BTN_FRMCANCEL
					}],
				items: [
					win.Panel
				]
			});

		sw.Promed.swPersonXmlWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			if ( this.refresh ) {
				this.onHide();
			}
		}
	},
	
	baseName: function(str) {
		var base = new String(str).substring(str.lastIndexOf('/') + 1);
		return base;
	},
	
	createXML: function(addParams)
	{
		var form = this;
		var Lpu_id = this.Panel.getForm().findField('Lpu_id').getValue();
		var Date_upload = Ext.util.Format.date(this.Panel.getForm().findField('Date_upload').getValue(),'d.m.Y');

		form.getLoadMask().show();

		var params = {
			AttachLpu_id: Lpu_id,
			Date_upload: Date_upload,
			OverrideExportOneMoreOrUseExist: 1
		};

		if ( form.Panel.findById('rxw_radio_usenew').getValue() ) {
			params.OverrideExportOneMoreOrUseExist = 2;
		}

		if ( !Ext.isEmpty(addParams) ) {
			for ( var par in addParams) {
				params[par] = addParams[par];
			}
		}
		else {
			addParams = [];
		}

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadAttachedList',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response)
			{
				form.getLoadMask().hide();
				if (success)
				{
					if (!response.responseText) {
						var newParams = addParams;
						newParams.OverrideExportOneMoreOrUseExist = 1;
						newParams.onlyLink = 1;
						form.createXML(newParams);
						return false;
					}
					var result = Ext.util.JSON.decode(response.responseText);

					if (result.Error_Code && result.Error_Code == '10') { // Статус реестра "Проведен контроль ФЛК"
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideControlFlkStatus = 1;
									form.createXML(newParams);
								}
							},
							msg: 'Статус реестра "Проведен контроль ФЛК". Вы уверены, что хотите повтороно отправить его в ТФОМС?',
							title: 'Подтверждение'
						});

						return false;
					}

					if (result.Error_Code && result.Error_Code == '11') { // Уже есть выгруженный XML
						sw.swMsg.show({
							buttons: Ext.Msg.YESNO,
							fn: function ( buttonId ) {
								if ( buttonId == 'yes' )
								{
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist = 2;
									form.createXML(newParams);
								} else {
									var newParams = addParams;
									newParams.OverrideExportOneMoreOrUseExist = 1;
									form.createXML(newParams);
								}
							},
							msg: 'Файл реестра существует на сервере. Если вы хотите сформировать новый файл выберете (Да), если хотите скачать файл с сервера нажмите (Нет)',
							title: 'Подтверждение'
						});

						return false;
					}

					var alt = '';
					var msg = '';
					form.refresh = true;
					if (result.usePrevXml)
					{
						alt = 'Изменений с реестром не было произведено. Используется сохраненный Xml предыдущей выгрузки.';
						msg = ' (xml предыдущей выгрузки).';
					}
					if (result.Link) {
						//form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg;
						form.setHeight(160 + result.Link.length * 15);
						form.TextPanel.getEl().dom.innerHTML += '<br>';
						for (var i=0; i < result.Link.length; i++) {
							form.TextPanel.getEl().dom.innerHTML += '<a target="_blank" href="'+result.Link[i]+'">Скачать архив: '+form.baseName(result.Link[i])+'</a><br>';
						}
						form.radioButtonGroup.hide();
						form.syncShadow();
						//Ext.getCmp('rxfOk').disable();
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.radioButtonGroup.hide();
						form.syncShadow();
						//Ext.getCmp('rxfOk').disable();
					}
					form.TextPanel.render();
				}
				else
				{
					var result = Ext.util.JSON.decode(response.responseText);
					form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
					form.TextPanel.render();
				}
			}
		});
	},
	getLoadMask: function()
	{
		if (!this.loadMask)
		{
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. Идет формирование. ' });
		}
		return this.loadMask;
	},
	show: function()
	{
		sw.Promed.swPersonXmlWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			form = this;

		if ( !isExpPop() ) {
			sw.swMsg.alert('Ошибка', 'Функционал недоступен', function() { form.hide(); });
			return false;
		}

		base_form.reset();

		if ( isSuperAdmin() ) {
			base_form.findField('Lpu_id').clearValue();
			base_form.findField('Lpu_id').enable();
		}
		else {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_id').disable();
		}

		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = 'Выгрузка данных прикрепленного населения в формате Xml';
		form.TextPanel.render();

		this.radioButtonGroup.hide();
		this.syncShadow();

		getCurrentDateTime({
			callback: function(result) {
				if (result.success) {
					base_form.findField('Date_upload').setValue(result.date, 'd.m.Y');
				}
			}
		});
	}
});
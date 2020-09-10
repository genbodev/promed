/**
* swRegistryExportToSMOWindow - окно выгрузки реестра в XML для СМО.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2011 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.04.2013
* @comment      Префикс для id компонентов RETSW (RegistryExportToSMOWindow)
*
*
* @input data: Registry_id - ID регистра
*/

sw.Promed.swRegistryExportToSMOWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'RegistryExportToSMOWindow',
	title: WND_REGISTRY_EXPORTTOSMO,
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		this.TextPanel = new Ext.Panel(
		{
			autoHeight: true,
			bodyBorder: false,
			border: false,
			style: 'margin-bottom: 5px;',
			id: 'RegistryXmlTextPanel',
			html: 'Выгрузка данных реестра в формате Xml для СМО'
		});
		
		this.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'RegistryXmlPanel',
			labelAlign: 'right',
			labelWidth: 50,
			items: [
				this.TextPanel, 
				{
					anchor: '100%',
					allowBlank: false,
					fieldLabel: 'СМО',
					xtype:'swbaselocalcombo',
					hiddenName: 'OrgSMO_id',
					store: new Ext.data.JsonStore({
						url: '/?c=Registry&m=getOrgSMOListForExportRegistry',
						editable: false,
						key: 'OrgSMO_id',
						autoLoad: false,
						fields: [
							{name: 'OrgSMO_id',    type:'int'},
							{name: 'OrgSMO_Nick',  type:'string'},
							{name: 'OrgSMO_Name',  type:'string'}
						],
						sortInfo: {
							field: 'OrgSMO_Nick'
						}
					}),
					triggerAction: 'all',
					displayField:'OrgSMO_Nick',
					tpl: '<tpl for="."><div class="x-combo-list-item">'+
								'{OrgSMO_Nick}'+
							'</div></tpl>',
					valueField: 'OrgSMO_id'
				}	
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
					this.ownerCt.createXML();
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
			items: [this.Panel]
		});
		sw.Promed.swRegistryExportToSMOWindow.superclass.initComponent.apply(this, arguments);
	},
	
	listeners: 
	{
		'hide': function() 
		{
			if (this.refresh)
				this.onHide();
		}
	},
	createXML: function(addParams) 
	{
		var Registry_id = this.Registry_id;
		var RegistryType_id = this.RegistryType_id;
		var KatNasel_id = this.KatNasel_id;
		var form = this;
		
		var base_form = form.Panel.getForm();
		
		if (!base_form.isValid()) {
			sw.swMsg.show(
				{
					buttons:Ext.Msg.OK,
					fn:function () {
						form.Panel.getFirstInvalidEl().focus(true);
					},
					icon:Ext.Msg.WARNING,
					msg:ERR_INVFIELDS_MSG,
					title:ERR_INVFIELDS_TIT
				});
			return false;
		}
		
		form.getLoadMask().show();
		
		var params = {
				Registry_id: Registry_id,
				RegistryType_id: RegistryType_id,
				KatNasel_id: KatNasel_id
			};
			
		if (addParams != undefined) {
			for(var par in addParams) {
				params[par] = addParams[par];
			}
		} else {
			addParams = [];
		}
		
		Ext.Ajax.request(
		{
			url: form.formUrl,
			params: params,
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
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить реестр</a>'+msg;
						Ext.getCmp('rxfOk').disable();
					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						Ext.getCmp('rxfOk').disable();
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
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: 'Подождите. Идет формирование ' });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swRegistryExportToSMOWindow.superclass.show.apply(this, arguments);
		var form = this;
		
		form.Registry_id = null;
		form.RegistryType_id = null;
		form.KatNasel_id = null;
		form.onHide = Ext.emptyFn;
		Ext.getCmp('rxfOk').enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = 'Выгрузка данных реестра в формате Xml';
		form.TextPanel.render();
		
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

		if (arguments[0].Registry_id) 
		{
			form.Registry_id = arguments[0].Registry_id;
		}
		if (arguments[0].RegistryType_id) 
		{
			form.RegistryType_id = arguments[0].RegistryType_id;
		}
		if (arguments[0].KatNasel_id) 
		{
			form.KatNasel_id = arguments[0].KatNasel_id;
		}
		if (arguments[0].onHide) 
		{
			form.onHide = arguments[0].onHide;
		}
		if (arguments[0].url) 
		{
			form.formUrl = arguments[0].url;
		}
		else 
		{
			form.formUrl = '/?c=Registry&m=exportRegistryToSMO';
		}
		
		var base_form = form.Panel.getForm();
		base_form.findField('OrgSMO_id').getStore().removeAll();
		base_form.findField('OrgSMO_id').getStore().load({
			params: {
				Registry_id: form.Registry_id
			}
		});
	}
});
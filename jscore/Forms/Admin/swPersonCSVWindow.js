/**
* swPersonCSVWindow - окно выгрузки информации о прикрепленном населении в формате csv
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 - 2015 Swan Ltd.
* @author		Abakhri Samir
* @version      01.07.2015
* @comment      Префикс для id компонентов MCX
*
* @input data: arm - из какого АРМа ведётся выгрузка
*/

sw.Promed.swPersonCSVWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	id: 'PersonCSVWindow',
	title: lang['vyigruzka_prikreplennogo_naseleniya_v_csv'],
	width: 400,
	layout: 'form',
	resizable: false,
	onHide: Ext.emptyFn,
	plain: true,
	period: 1,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'MCX_ExportCSVTextPanel',
			html: lang['vyigruzka_prikreplennogo_naseleniya_v_csv']
		});

		win.Panel = new Ext.form.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: true,
			id: 'ExportCSVPanel',
			labelAlign: 'right',
			labelWidth: 25,
			items: [
				{
				emptyText: lang['vse'],
				fieldLabel: lang['mo'],
				hiddenName: 'AttachLpu_id',
				width: 300,
				xtype: 'swlpucombo'
			},

				{
					xtype: 'radio',
					hideLabel: true,
					inputValue: 1,
					checked: true,
					boxLabel: langs('Все данные'),
					name: 'AttachPeriod',
					listeners: {
						check: function(radio, checked, c){
							var form = win.Panel.getForm();
							var fieldAttachPeriod_ToDate = form.findField('AttachPeriod_FromDate');
							if(checked){
								win.period = 1;
								fieldAttachPeriod_ToDate.setAllowBlank(true);
								fieldAttachPeriod_ToDate.disable();
							}
						}
					}
				},

				{
					xtype: 'radio',
					hideLabel: true,
					inputValue: 2,
					boxLabel: langs('Изменения после даты'),
					name: 'AttachPeriod',
					listeners: {
						check: function(radio, checked, c){

							var form = win.Panel.getForm();
							var fieldAttachPeriod_ToDate = form.findField('AttachPeriod_FromDate');

							fieldAttachPeriod_ToDate.setAllowBlank(true);
							fieldAttachPeriod_ToDate.disable();
							if(checked){
								win.period = 2;
								fieldAttachPeriod_ToDate.setAllowBlank(false);
								fieldAttachPeriod_ToDate.enable();
							}
						}
					}
				},

				{
					xtype: 'swdatefield',
					plugins: [new Ext.ux.InputTextMask('99.99.9999', false)],
					disabled: true,
					allowBlank: true,
					fieldLabel: 'Дата',
					maxValue: (new Date()),
					format: 'd.m.Y',
					name: 'AttachPeriod_FromDate'
				},

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
				text: lang['sformirovat']
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

		sw.Promed.swPersonCSVWindow.superclass.initComponent.apply(this, arguments);
	},
	listeners: {
		'hide': function() {
			if ( this.refresh ) {
				this.onHide();
			}
		}
	},
	createXML: function(addParams) 
	{
		var form = this;
		var base_form = form.Panel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var AttachLpu_id = base_form.findField('AttachLpu_id').getValue();
		var AttachPeriod = form.period;
		var AttachPeriod_FromDate = base_form.findField('AttachPeriod_FromDate').getRawValue();

		form.getLoadMask().show();
		
		var params = {
			AttachLpu_id: AttachLpu_id,
			AttachPeriod: AttachPeriod,
			AttachPeriod_FromDate: AttachPeriod_FromDate
		};

		if ( !Ext.isEmpty(addParams) ) {
			for ( var par in addParams) {
				params[par] = addParams[par];
			}
		}
		else {
			addParams = [];
		}

		Ext.getCmp('rxfOk').disable();

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadAttachedListCSV',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				Ext.getCmp('rxfOk').enable();
				if (success)
				{
					if (!response.responseText) {
						var newParams = addParams;
						form.createXML(newParams);
						return false;
					}
					var result = Ext.util.JSON.decode(response.responseText);

					var alt = '';
					var msg = '';
					if (result.Alert_Msg) {
						msg = '<br />' + result.Alert_Msg;
					}

					form.refresh = true;
					if (result.Link) {
						form.TextPanel.getEl().dom.innerHTML = '<a target="_blank" title="'+alt+'" href="'+result.Link+'">Скачать и сохранить список</a>'+msg;
						form.syncShadow();

					}
					if (result.success === false) {
						form.TextPanel.getEl().dom.innerHTML = result.Error_Msg;
						form.syncShadow();
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
			this.loadMask = new Ext.LoadMask(Ext.get(this.id), { msg: lang['podojdite_idet_formirovanie'] });
		}
		return this.loadMask;
	},
	show: function() 
	{
		sw.Promed.swPersonCSVWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			form = this;

		base_form.reset();

		form.onHide = Ext.emptyFn;
		form.buttons[0].enable();
		form.refresh = false;
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_prikreplennogo_naseleniya_v_csv'];
		form.TextPanel.render();

		if ( !isUserGroup('SuperAdmin') ) {
			base_form.findField('AttachLpu_id').setValue(getGlobalOptions().lpu_id);
		}
		else {
			base_form.findField('AttachLpu_id').clearValue();
		}

		base_form.findField('AttachLpu_id').setAllowBlank(isUserGroup('SuperAdmin'));
		base_form.findField('AttachLpu_id').setDisabled(!isUserGroup('SuperAdmin'));

		this.syncSize();
		this.syncShadow();
	}
});
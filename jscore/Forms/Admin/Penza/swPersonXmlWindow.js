/**
 * swPersonXmlWindow - окно выгрузки прикрепленного населения в XML.
 *
 * PromedWeb - The New Generation of Medical Statistic Software
 * http://swan.perm.ru/PromedWeb
 *
 *
 * @package      Admin
 * @access       public
 * @copyright    Copyright (c) 2009 - 2017 Swan Ltd.
 * @author
 * @version      07.10.2017
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
	title: lang['vyigruzka_spiska_prikreplennogo_naseleniya'],
	width: 450,
	layout: 'form',
	resizable: false,
	plain: true,
	initComponent: function() {
		var win = this;

		win.TextPanel = new Ext.Panel({
			autoHeight: true,
			bodyBorder: false,
			border: false,
			id: 'RegistryXmlTextPanel',
			html: lang['vyigruzka_spiska_prikreplennogo_naseleniya_v_formate_xml']
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
			//labelWidth: 75,
			items: [{
				fieldLabel: 'Дата выгрузки',
				labelSeparator: '',
				xtype: 'swdatefield',
				format: 'd.m.Y',
				allowBlank: false,
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				name: 'Date_upload'
			}, {
				allowBlank: false,
				fieldLabel: lang['mo'],
				hiddenName: 'Lpu_id',
				width: 300,
				xtype: 'swlpucombo'
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

		sw.Promed.swPersonXmlWindow.superclass.initComponent.apply(this, arguments);
	},
	createXML: function()
	{
		var form = this;
		var base_form = form.Panel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.Panel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		form.getLoadMask().show();
		
		var params = {
			AttachLpu_id: base_form.findField('Lpu_id').getValue(),
			Date_upload: base_form.findField('Date_upload').getValue().format('d.m.Y')
		};

		Ext.getCmp('rxfOk').disable();

		Ext.Ajax.request({
			url: '/?c=PersonCard&m=loadAttachedList',
			params: params,
			timeout: 1800000,
			callback: function(options, success, response) 
			{
				form.getLoadMask().hide();
				Ext.getCmp('rxfOk').enable();
				if (success)
				{
					var result = Ext.util.JSON.decode(response.responseText);

					var alt = '';
					var msg = '';
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
		sw.Promed.swPersonXmlWindow.superclass.show.apply(this, arguments);

		var
			base_form = this.Panel.getForm(),
			form = this;

		base_form.reset();

		if ( isSuperAdmin() ) {
			base_form.findField('Lpu_id').clearValue();
			base_form.findField('Lpu_id').enable();
		}
		else if ( isLpuAdmin(getGlobalOptions().lpu_id) ) {
			base_form.findField('Lpu_id').setValue(getGlobalOptions().lpu_id);
			base_form.findField('Lpu_id').disable();
		}
		else {
			sw.swMsg.alert(lang['oshibka'], lang['funktsional_nedostupen'], function() { form.hide(); });
			return false;
		}

		form.buttons[0].enable();
		form.TextPanel.getEl().dom.innerHTML = lang['vyigruzka_dannyih_prikreplennogo_naseleniya_v_formate_xml'];
		form.TextPanel.render();

		this.syncShadow();
	}
});
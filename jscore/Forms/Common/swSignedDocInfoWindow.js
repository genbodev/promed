/**
* swSignedDocInfoWindow - окно просмотра информации о версии документа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      07.11.2013
*
*/

sw.Promed.swSignedDocInfoWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'right',
	modal: true,
	layout: 'form',
	resizable: false,
	closable: true,
	shim: false,
	width: 500,
	closeAction: 'hide',
	id: 'swSignedDocInfoWindow',
	objectName: 'swSignedDocInfoWindow',
	title: lang['informatsiya_o_podpisi'],
	plain: true,
	buttons: [
		'-',
		{
			text: BTN_FRMHELP,
			iconCls: 'help16',
			handler: function(button, event)
			{
				ShowHelp(this.ownerCt.title);
			}
		}, {
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['otmena'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],	
	show: function() 
	{
		sw.Promed.swSignedDocInfoWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		var base_form = win.FormPanel.getForm();
		
		this.Doc_Version = null;
		this.Doc_DateTime = null;
		if (arguments[0].Doc_Version) {
			this.Doc_Version = arguments[0].Doc_Version;
		}
		if (arguments[0].Doc_DateTime) {
			this.Doc_DateTime = arguments[0].Doc_DateTime;
		}
		base_form.reset();
		if (arguments[0].Doc_id && arguments[0].Doc_Version) {
			win.getLoadMask(lang['poluchenie_informatsii_o_podpisi']).show();
			base_form.load({
				params: {
					Doc_id: arguments[0].Doc_id,
					Doc_Version: win.Doc_Version
				},
				success: function() {
					win.getLoadMask().hide();
					base_form.findField('Doc_Version').setValue('test');
					base_form.findField('Doc_Version').setValue(win.Doc_Version);
					base_form.findField('Doc_DateTime').setValue(win.Doc_DateTime);
				},
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_dannyih_o_podpisi']);
				}
			});
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			border: false,
			labelAlign: 'right',
			url: '/?c=ElectronicDigitalSign&m=getSignedDocInfo',
			items: [
				{
					xtype: 'textfield',
					name: 'pmUser_Name',
					anchor: '100%',
					readOnly: true,
					fieldLabel: lang['polzovatel']
				}, {
					xtype: 'textfield',
					name: 'Doc_Version',
					anchor: '100%',
					readOnly: true,
					fieldLabel: lang['versiya']
				}, {
					xtype: 'textfield',
					name: 'Doc_DateTime',
					anchor: '100%',
					readOnly: true,
					fieldLabel: lang['data']
				}, {
					xtype: 'textarea',
					name: 'xmldsig',
					anchor: '100%',
					readOnly: true,
					fieldLabel: lang['hesh']
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function() { 
				}
			}, 
			[
				{ name: 'pmUser_Name' },
				{ name: 'xmldsig' }
			])
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			defaults:
			{
				border: false,
				bodyStyle: 'padding: 3px;'
			},
			items: [this.FormPanel]
		});
		sw.Promed.swSignedDocInfoWindow.superclass.initComponent.apply(this, arguments);
	}
});
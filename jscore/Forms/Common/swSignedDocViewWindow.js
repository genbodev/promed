/**
* swSignedDocViewWindow - окно просмотра версии документа
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Messages
* @access       public
* @copyright    Copyright (c) 2009-2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      08.11.2013
*
*/

sw.Promed.swSignedDocViewWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	layout: 'border',
	resizable: false,
	closable: true,
	shim: false,
	maximized: true,
	width: 500,
	closeAction: 'hide',
	id: 'swSignedDocViewWindow',
	objectName: 'swSignedDocViewWindow',
	title: lang['podpisannyiy_dokument_prosmotr'],
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
		sw.Promed.swSignedDocViewWindow.superclass.show.apply(this, arguments);
		
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
		win.findById('sdvw_htmlarea').body.update('');
		if (arguments[0].Doc_id && arguments[0].Doc_Version) {
			win.getLoadMask(lang['poluchenie_informatsii_o_podpisi']).show();
			base_form.load({
				params: {
					Doc_id: arguments[0].Doc_id,
					Doc_Version: win.Doc_Version
				},
				success: function(result_form, action) {
					if (action.result && action.result.data && action.result.data.html) {
						win.findById('sdvw_htmlarea').body.update(action.result.data.html);
					}
					win.getLoadMask().hide();
					base_form.findField('Doc_Version').setValue('test');
					base_form.findField('Doc_Version').setValue(win.Doc_Version);
					base_form.findField('Doc_DateTime').setValue(win.Doc_DateTime);
				},
				failure: function() {
					win.getLoadMask().hide();
					sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_poluchenii_dannyih_podpisannogo_dokumenta']);
				}
			});
		}
	},
	initComponent: function() 
	{
		this.FormPanel = new Ext.form.FormPanel({
			frame: true,
			layout: 'border',
			border: false,
			region: 'center',
			labelAlign: 'right',
			url: '/?c=ElectronicDigitalSign&m=getSignedDoc',
			items: [
				{
					region: 'north',
					layout: 'form',
					height: 80,
					items: [{
						xtype: 'textfield',
						name: 'pmUser_Name',
						width: 200,
						readOnly: true,
						fieldLabel: lang['polzovatel']
					}, {
						xtype: 'textfield',
						name: 'Doc_Version',
						width: 200,
						readOnly: true,
						fieldLabel: lang['versiya']
					}, {
						xtype: 'textfield',
						name: 'Doc_DateTime',
						width: 200,
						readOnly: true,
						fieldLabel: lang['data']
					}]
				}, {
					region: 'center',
					border: true,
					id: 'sdvw_htmlarea',
					height: 200,
					bodyStyle: 'background: #FFF; border: 1px solid #555;',
					autoScroll: true,
					html: ''
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function() { 
				}
			}, 
			[
				{ name: 'pmUser_Name' },
				{ name: 'html' }
			])
		});
		
		Ext.apply(this, 
		{
			defaults:
			{
				border: false,
				bodyStyle: 'padding: 3px;'
			},
			items: [this.FormPanel]
		});
		sw.Promed.swSignedDocViewWindow.superclass.initComponent.apply(this, arguments);
	}
});
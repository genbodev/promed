/**
* swUserCertsUploadWindow - окно загрузки файла сертификата
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      User
* @access       public
* @copyright    Copyright (c) 2013 Swan Ltd.
* @author       Dmitry Vlasenko
* @version      06.11.2013
*
*/

sw.Promed.swUserCertsUploadWindow = Ext.extend(sw.Promed.BaseForm, {
	autoHeight: true,
	buttonAlign: 'left',
	modal: true,
	closable: true,
	closeAction: 'hide',
	draggable: false,
	id: 'UserCertsUploadWindow',
	title: lang['zagruzka_sertifikata'],
	width: 500,
	//layout: 'form',
	resizable: false,
	callback: Ext.emptyFn,
	plain: true,
	initComponent: function() 
	{
		this.FormPanel = new Ext.FormPanel(
		{
			autoHeight: true,
			bodyBorder: false,
			fileUpload: true,
			bodyStyle: 'padding: 5px 5px 0',
			frame: true,
			id: 'UserCertImportFormPanel',
			labelWidth: 80,
			url: '/?c=User&m=importUserCert',
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
				emptyText: lang['vyiberite_fayl_sertifikata'],
				fieldLabel: lang['sertifikat'],
				name: 'UserCertFile'
			}]
		});
		
		Ext.apply(this, 
		{
			autoHeight: true,
			buttons: [
			{
				handler: function() 
				{
					this.ownerCt.doSave();
				},
				iconCls: 'refresh16',
				text: lang['zagruzit']
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
				text: BTN_FRMCANCEL
			}],
			items: [this.FormPanel]
		});
		sw.Promed.swUserCertsUploadWindow.superclass.initComponent.apply(this, arguments);
	},
	doSave: function() 
	{
		var form = this.FormPanel;
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
		var form = this.FormPanel;
		var win = this;
		win.getLoadMask(lang['import_sertifikata_polzovatelya']).show();
		
		form.getForm().submit(
		{
			failure: function(result_form, action) 
			{
				win.getLoadMask().hide();
				if ( action.result ) 
				{
					
					if ( action.result.Error_Msg ) 
					{
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else 
					{
						sw.swMsg.alert(lang['oshibka'], lang['vo_vremya_vyipolneniya_operatsii_zagruzki_reestra_proizoshla_oshibka_pojaluysta_povtorite_popyitku_chut_pozje']);
					}
				}
			},
			success: function(result_form, action) 
			{
				win.getLoadMask().hide();
				var answer = action.result;
				if (answer) 
				{
					if (answer.newcert) {
						win.callback(answer.newcert);
						win.hide();
					} else {
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_importa_sertifikata']);
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
		sw.Promed.swUserCertsUploadWindow.superclass.show.apply(this, arguments);
		var form = this;
		form.callback = Ext.emptyFn;
		form.FormPanel.getForm().reset();

		if (arguments[0].callback) 
		{
			form.callback = arguments[0].callback;
		}
	}
});
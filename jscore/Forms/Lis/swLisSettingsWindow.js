/**
* swLisSettingsWindow - окно ввода настроек пользователя для связи с ЛИС.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Lis
* @access       public
* @copyright    Copyright (c) 2009-2010 Swan Ltd.
* @author       Марков Андрей <markov@wan.perm.ru>
* @version      дек.2011
*
*/

sw.Promed.swLisSettingsWindow = Ext.extend(sw.Promed.BaseForm, {
	buttonAlign: 'right',
	modal: true,
	closable: true,
	shim: false,
	width: 600,
	autoHeight: true,
	closeAction: 'hide',
	id: 'swLisSettingsWindow',
	objectName: 'swLisSettingsWindow',
	title: '',
	onHide: Ext.emptyFn,
	plain: true,
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.saveLisData();
			},
			iconCls: 'save16',
			text: lang['sohranit']
		},
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
	listeners:
	{
		'hide': function(win)
		{
			if(win.form_fields)
			{
				for(i=0; i<win.form_fields.length; i++)
				{
					win.form_fields[i].enable();
				}
			}
			this.buttons[0].setVisible(true);
			win.LisDataPanel.getForm().reset();
		}
	},
	show: function() 
	{
		sw.Promed.swLisSettingsWindow.superclass.show.apply(this, arguments);
		
		var win = this;
		if(!arguments[0] || !arguments[0].action) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			win.hide();
			return false;
		}
		
		if( arguments[0].onHide )
			this.onHide = arguments[0].onHide;
		// Аргументы 
		this.pmUser_id = (arguments[0].pmUser_id)?arguments[0].pmUser_id:getGlobalOptions().pmuser_id;
		this.pmUser_Login = arguments[0].pmUser_Login || null;
		this.action = arguments[0].action;
		
		var base_form = this.LisDataPanel.getForm();
		var params = {};
		if (this.pmUser_Login) {
			params = {
				pmUser_Login: this.pmUser_Login,
				lis_login: arguments[0].lis_login
			}
		}
		base_form.findField('pmUser_Login').setValue(this.pmUser_Login);
		
		base_form.findField('lis_login').setDisabled( this.action != 'add' );
		
		if(this.action != 'add') {
			var lm = this.getLoadMask(lang['zagruzka_dannyih']);
			lm.show();
			// Чтение инфы о юзере
			base_form.load({
				params: params,
				url: '/?c=User&m=getLisSettings',
				success: function(form, resp) {
					lm.hide();
					base_form.findField('lis_login').focus();
				},
				failure: function(form, resp) {
					lm.hide();
					win.hide();
				}
			});
		}
		var title = lang['nastroyki_polzovatelya_dlya_lis'];
		switch(this.action) {
			case 'add': title += lang['_dobavlenie']; break
			case 'edit': title += lang['_redaktirovanie']; break
			case 'view': title += lang['_prosmotr']; break
		}
		this.setTitle(title);
		this.center();
	},
	
	saveLisData: function()
	{
		var win = this;
		var frm = win.LisDataPanel.getForm();
		if(!frm.isValid()) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_zapolnenyi_ili_zapolnenyi_nekorrektno_obyazatelnyie_polya_polya_obyazatelnyie_k_zapolneniyu_vyidelenyi_osobo']);
			return false;
		}
		var params = {}
		params.pmUser_Login = frm.findField('pmUser_Login').getValue();
		
		if( frm.findField('lis_login').disabled )
			params.lis_login = frm.findField('lis_login').getValue();
		
		params.lis_analyzername = frm.findField('lis_analyzername').getValue();
		
		win.getLoadMask().show();
		frm.submit({
			params: params,
			success: function(f, r) {
				win.getLoadMask().hide();
				win.hide();
				win.onHide();
			},
			failure: function() {
				win.getLoadMask().hide();
				sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_nastroek_polzovatelya_lis_proizoshla_oshibka']);
			}
		});
	},
	
	genAnalyzerName: function()
	{
		var frm = this.LisDataPanel.getForm(),
			field = frm.findField('lis_analyzername'),
			lis_lab_val = frm.findField('lis_lab').getValue(),
			lis_machine_val = frm.findField('lis_machine').getValue();
		if(lis_lab_val != '' && lis_machine_val != '') {
			field.setValue(lis_lab_val + '/' + lis_machine_val);
		} else if (lis_lab_val != '') {
			field.setValue(lis_lab_val);
		} else if (lis_machine_val != '') {
			field.setValue(lis_machine_val);
		}
	},
	
	initComponent: function() 
	{
		
		this.LisDataMainPanel = new sw.Promed.Panel({
			region: 'center',
			border: false,
			labelWidth : 180,
			labelAlign : 'right',
			layout: 'form',
			items: [
				/*{
					border: true,
					frame: true,
					html: ''
				}, */{
					fieldLabel: lang['naimenovanie_analizatora'],
					maxLength: 40,
					disabled: true,
					name: 'lis_analyzername',
					allowBlank: false,
					anchor: '100%',
					xtype: 'textfield'
				}, {
					fieldLabel: lang['primechanie'],
					maxLength: 100,
					name: 'lis_note',
					anchor: '100%',
					xtype: 'textarea'
				}, {
					xtype: 'textfield',
					anchor: '100%',
					disabled: true,
					name: 'pmUser_Login',
					fieldLabel: lang['polzovatel']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_login',
					fieldLabel: lang['login_v_lis']
				}, {
					xtype: 'textfield',
					inputType : 'password',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_password',
					fieldLabel: lang['parol']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_company',
					fieldLabel: lang['naimenovanie_lpu']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_lab',
					listeners: {
						change: this.genAnalyzerName.createDelegate(this)
					},
					fieldLabel: lang['naimenovanie_laboratorii']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_machine',
					listeners: {
						change: this.genAnalyzerName.createDelegate(this)
					},
					fieldLabel: lang['nazvanie_mashinyi_v_lis']
				}, {
					xtype: 'textfield',
					anchor: '100%',
					allowBlank: false,
					name: 'lis_clientId',
					fieldLabel: lang['id_klienta']
				}
			]
		});
		this.LisDataPanel = new Ext.form.FormPanel({
			url: '/?c=User&m=setLisSettings',
			defaults:
			{
				bodyStyle: 'padding: 5px; background: #DFE8F6;'
			},
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[	
				{ name: 'pmuser_id' },
				{ name: 'lis_analyzername' },
				{ name: 'lis_note' },
				{ name: 'lis_login' },
				{ name: 'lis_password' },
				{ name: 'lis_company' },
				{ name: 'lis_company' },
				{ name: 'lis_lab' },
				{ name: 'lis_machine' },
				{ name: 'lis_clientId' }
			]),
			items: [this.LisDataMainPanel]
		});
	
		
		Ext.apply(this, 
		{
			defaults:
			{
				bodyStyle: 'padding: 3px; background: #DFE8F6;'
			},
			items: [this.LisDataPanel]
		});
		sw.Promed.swLisSettingsWindow.superclass.initComponent.apply(this, arguments);
	}
});
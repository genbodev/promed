/**
* Справочник РЛС: Форма добавления/редактирования производителей
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Dmitry Storozhev aka nekto_O
* @copyright    Copyright (c) 2011 Swan Ltd.
* @version      01.12.2011
*/

sw.Promed.swRlsFirmsEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	modal: true,
	shim: false,
	plain: true,
	height: 263,
	resizable: false,
	onHide: Ext.emptyFn,
	layout: 'fit',
	buttonAlign: "right",
	objectName: 'swRlsFirmsEditWindow',
	closeAction: 'hide',
	id: 'swRlsFirmsEditWindow',
	objectSrc: '/jscore/Forms/Rls/swRlsFirmsEditWindow.js',
	buttons: [
		{
			handler: function()
			{
				this.ownerCt.doSave();
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
		},
		{
			text      : lang['otmena'],
			tabIndex  : -1,
			tooltip   : lang['zakryit'],
			iconCls   : 'cancel16',
			handler   : function()
			{
				this.ownerCt.hide();
			}
		}
	],
	
	listeners: {
		hide: function(w){
			w.CommonForm.getForm().reset();
			w.buttons[0].setVisible(true);
			w.disableFields(false);
		}
	},
	
	show: function()
	{
		sw.Promed.swRlsFirmsEditWindow.superclass.show.apply(this, arguments);
		
		if(!arguments[0] || !arguments[0].action){
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		this.action = arguments[0].action;
		if(arguments[0].callback){
			this.args = arguments[0];
			this.callback = this.args.callback;
			this.onHide = function(){
				var owner = this.args.owner;
				this.callback(owner, 0);
			}.createDelegate(this);
		}
		
		this.center();
		var win = this;
		var b_f = this.CommonForm.getForm();
		
		var title = lang['spravochnik_medikamentov'];
		switch(this.action){
			case 'add':
				title += lang['_dobavlenie'];
				b_f.findField('FIRMS_NAME').focus(true, 100);
			break;
			case 'edit':
				title += lang['_redaktirovanie'];
			break;
			case 'view':
				title += lang['_prosmotr'];
				this.buttons[0].setVisible(false);
			break;
		}
		this.setTitle(title+lang['proizvoditelya']);
		
		if(this.action.inlist(['edit', 'view'])){
			win.getLoadMask(lang['zagruzka_dannyih']).show();
			b_f.load({
				params: {
					FIRMS_ID: arguments[0].FIRMS_ID
				},
				url: '/?c=Rls&m=getFirm',
				success: function(f, r){
					win.getLoadMask().hide();
					b_f.findField('FIRMS_NAME').focus(true, 100);
					if(win.action == 'view')
						win.disableFields(true);
				},
				failure: function(){
					win.getLoadMask().hide();
				}
			});
		}
		
	},	
	
	doSave: function()
	{
		var win = this;
		var form = this.CommonForm.getForm();
		if(!form.isValid()){
			sw.swMsg.alert(lang['oshibka'], lang['zapolnenyi_ne_vse_obyazatelnyie_polya']);
			return false;
		}
		var lm = this.getLoadMask(lang['sohranenie']);
		lm.show();
		form.submit({
			success: function(f, r){
				lm.hide();
				win.hide();
				win.onHide();
			},
			failure: function(){
				lm.hide();
			}
		});
	},
	
	disableFields: function(isView)
	{
		this.findBy(function(field){
			if(field.xtype && !field.xtype.inlist(['panel', 'fieldset'])){
				if(isView)
					field.disable();
				else
					field.enable();
			}
		});
	},
	
	initComponent: function()
	{		
		this.CommonForm = new Ext.form.FormPanel({
			bodyStyle: 'padding: 5px;',
			url: '/?c=Rls&m=saveFirm',
			items: [
				{
					layout: 'form',
					border: false,
					labelAlign: 'right',
					defaults: {
						anchor: '100%'
					},
					labelWidth: 150,
					items: [
						{
							xtype: 'hidden',
							name: 'FIRMS_ID'
						}, {
							xtype: 'hidden',
							name: 'FIRMNAMES_ID'
						}, {
							xtype: 'textfield',
							allowBlank: false,
							minLength: 3,
							name: 'FIRMS_NAME',
							fieldLabel: lang['polnoe_nazvanie']
						}, {
							xtype: 'swrlscountrycombo',
							allowBlank: false,
							hiddenName: 'FIRMS_COUNTID',
							fieldLabel: lang['strana']
						}, {
							xtype: 'textarea',
							allowBlank: false,
							name: 'FIRMS_ADRMAIN',
							fieldLabel: lang['adres_osnovnogo_ofisa']
						}, {
							xtype: 'textarea',
							name: 'FIRMS_ADRRUSSIA',
							fieldLabel: lang['adres_v'] + getCountryName('predl')
						}
					]
				}
			],
			reader: new Ext.data.JsonReader(
			{
				success: function(){}
			},
			[
				{ name: 'FIRMS_ID' },
				{ name: 'FIRMNAMES_ID' },
				{ name: 'FIRMS_NAME' },
				{ name: 'FIRMS_COUNTID' },
				{ name: 'FIRMS_ADRMAIN' },
				{ name: 'FIRMS_ADRRUSSIA' }
			])
		});
		
		Ext.apply(this,	{
			layout: 'fit',
			items: [this.CommonForm]
		});
		sw.Promed.swRlsFirmsEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
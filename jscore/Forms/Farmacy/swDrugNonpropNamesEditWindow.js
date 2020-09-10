/**
* форма добавление/редактир-я непатентованого наименования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      09.2016
*/

sw.Promed.swDrugNonpropNamesEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 640,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swDrugNonpropNamesEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},

	show: function() {
		sw.Promed.swDrugNonpropNamesEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] || !arguments[0].action ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}

		this.action = arguments[0].action;

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.setTitle(lang['nepatentovannoe_naimenovanie'] + ': ' + this.getActionName(this.action));
		
		var bf = this.Form.getForm();
		if( this.action !== 'add' ) {
			bf.setValues(arguments[0].owner.getGrid().getSelectionModel().getSelected().data);
		}
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		bf.findField('DrugNonpropNames_Code').focus(true, 100);
		this.center();
	},
	
	getActionName: function(action) {
		return {
			add: lang['dobavlenie'],
			edit: lang['redaktirovanie'],
			view: lang['prosmotr']
		}[action];
	},
	
	doSave: function() {
		var bf = this.Form.getForm();
		if( !bf.isValid() ) {
			sw.swMsg.alert(lang['oshibka'], lang['ne_vse_obyazatelnyie_polya_zapolnenyi_korrektno']);
			return false;
		}

		var params = new Object();

		bf.submit({
			scope: this,
			params: params,
			failure: function() {
			
			},
			success: function(form, act) {
				this.callback.call(this.owner, this.owner, 0);
				this.hide();
			}
		});
	},
	
	disableFields: function(s) {
		this.Form.findBy(function(f) {
			if( f.xtype && f.xtype != 'hidden' ) {
				f.setDisabled(s);
			}
		});
	},
	
	initComponent: function() {

		this.Form = new Ext.FormPanel({
			url: '/?c=DrugNonpropNames&m=saveDrugNonpropNames',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 170,
			items: [{
				xtype: 'hidden',
				name: 'DrugNonpropNames_id'
			}, {
				xtype: 'textfield',
				anchor: '100%',
				id: 'DrugNonpropNames_Code',
				allowBlank: false,
				name: 'DrugNonpropNames_Code',
				fieldLabel: lang['kod'],
				maxLength: 50,
				listeners: {
					render: function(c) {
				      Ext.QuickTips.register({
				        target: c.getEl(),
				        text: 'В качестве кода позиции могут быть указаны числа или часть наименования',
				        enabled: true,
				        showDelay: 5,
				        trackMouse: true,
				        autoShow: true
				      });
				    }
				}
			}, {
				xtype: 'textarea',
				anchor: '100%',
				allowBlank: false,
				name: 'DrugNonpropNames_Nick',
				fieldLabel: lang['kratkoe_naimenovanie'],
				maxLength: 500,
				listeners: {
					change: function (f,v) {
						var prop = this.Form.getForm().findField('DrugNonpropNames_Property').getValue();
						this.Form.getForm().findField('DrugNonpropNames_Name').setValue(v+' '+prop);
					}.createDelegate(this),
					render: function(c) {
				      Ext.QuickTips.register({
				        target: c.getEl(),
				        text: 'В поле указывается наименование медикамента, без уточнения его размера',
				        enabled: true,
				        showDelay: 5,
				        trackMouse: true,
				        autoShow: true
				      });
				    }
				}
			}, {
				xtype: 'textarea',
				anchor: '100%',
				name: 'DrugNonpropNames_Property',
				fieldLabel: lang['svoystvo'],
				maxLength: 200,
				listeners: {
					change: function (f,v) {
						var nick = this.Form.getForm().findField('DrugNonpropNames_Nick').getValue();
						this.Form.getForm().findField('DrugNonpropNames_Name').setValue(nick+' '+v);
					}.createDelegate(this),
					render: function(c) {
				      Ext.QuickTips.register({
				        target: c.getEl(),
				        text: 'В поле указываются данные о размере медикамента и другие важные потребительские свойства',
				        enabled: true,
				        showDelay: 5,
				        trackMouse: true,
				        autoShow: true
				      });
				    }
				}
			}, {
				xtype: 'textarea',
				anchor: '100%',
				allowBlank: false,
				name: 'DrugNonpropNames_Name',
				fieldLabel: lang['naimenovanie'],
				maxLength: 500
			}]
		});
		
		Ext.apply(this, {
			items: [this.Form],
			buttons: [{
				handler: this.doSave,
				scope: this,
				iconCls: 'save16',
				text: lang['sohranit']
			},
			'-',
			HelpButton(this),
			{
				text: lang['otmena'],
				tabIndex: -1,
				tooltip: lang['otmena'],
				iconCls: 'cancel16',
				handler: this.hide.createDelegate(this, [])
			}]
		});
		sw.Promed.swDrugNonpropNamesEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
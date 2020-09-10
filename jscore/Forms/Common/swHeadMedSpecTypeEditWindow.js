/**
* swHeadMedSpecTypeEditWindow - Форма редактирования данных записи номенклатуры главных внештатных специалистов
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      All
* @access       public
* @autor		Alexander Kurakin
* @copyright    Copyright (c) 2016 Swan Ltd.
* @version      2016
*/

sw.Promed.swHeadMedSpecTypeEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 640,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swHeadMedSpecTypeEditWindow',
	
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	
	show: function() {
		sw.Promed.swHeadMedSpecTypeEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( (arguments[0].action == 'edit' || arguments[0].action == 'view') && !arguments[0].HeadMedSpecType_id ) {
			sw.swMsg.alert(lang['oshibka'], 'Не передан идентификатор специальности');
			this.hide();
			return false;
		}

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.HeadMedSpecType_id = null;
		if( arguments[0].HeadMedSpecType_id ) {
			this.HeadMedSpecType_id = arguments[0].HeadMedSpecType_id;
		}

		this.action = arguments[0].action;

		this.setTitle('Запись номенклатуры главных внештатных специалистов: ' + this.getActionName(this.action));
		
		var bf = this.Form.getForm();

		bf.setValues(arguments[0]);
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		bf.findField('HeadMedSpecType_Name').focus(true, 100);

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
				this.callback(form);
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
			url: '/?c=HeadMedSpec&m=saveHeadMedSpecType',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 120,
			items: [{
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'HeadMedSpecType_id'
				}, {
					layout: 'form',
					items: [{
						allowBlank: false,
						xtype: 'textfieldpmw',
						name: 'HeadMedSpecType_Name',
						fieldLabel: 'Наименование',
						anchor: '100%',
					}]
				}, {
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel : 'Профиль',
						hiddenName: 'Post_id',
						anchor: '100%',
						xtype: 'swpersispostcombo'
					}]
				}]
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
		sw.Promed.swHeadMedSpecTypeEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
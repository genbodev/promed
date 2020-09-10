/**
* swHeadMedSpecEditWindow - Форма редактирования данных главного внештатного специалиста
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

sw.Promed.swHeadMedSpecEditWindow = Ext.extend(sw.Promed.BaseForm,
{
	title: '',
	maximized: false,
	maximizable: false,
	modal: true,
	autoHeight: true,
	resizable: false,
	width: 600,
	onHide: Ext.emptyFn,
	callback: Ext.emptyFn,
	owner: null,
	shim: false,
	buttonAlign: "right",
	closeAction: 'hide',
	id: 'swHeadMedSpecEditWindow',
	listeners: {
		hide: function() {
			this.Form.getForm().reset();
		}
	},
	show: function() {
		sw.Promed.swHeadMedSpecEditWindow.superclass.show.apply(this, arguments);
		
		if( !arguments[0] ) {
			sw.swMsg.alert(lang['oshibka'], lang['nevernyie_parametryi']);
			this.hide();
			return false;
		}
		
		if( (arguments[0].action == 'edit' || arguments[0].action == 'view') && !arguments[0].HeadMedSpec_id ) {
			sw.swMsg.alert(lang['oshibka'], 'Не передан идентификатор специалиста');
			this.hide();
			return false;
		}

		if( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}
		
		if( arguments[0].owner ) {
			this.owner = arguments[0].owner;
		}

		this.MedWorker_id = null;
		if( arguments[0].MedWorker_id ) {
			this.MedWorker_id = arguments[0].MedWorker_id;
		}

		this.HeadMedSpec_id = null;
		if( arguments[0].HeadMedSpec_id ) {
			this.HeadMedSpec_id = arguments[0].HeadMedSpec_id;
		}

		this.HeadMedSpecType_id = null;
		if( arguments[0].HeadMedSpecType_id ) {
			this.HeadMedSpecType_id = arguments[0].HeadMedSpecType_id;
		}

		this.PersonData = '';
		if( arguments[0].person ) {
			if ( arguments[0].person.MedWorker_id ) {
				this.MedWorker_id = arguments[0].person.MedWorker_id;
			}
			if ( arguments[0].person.PersonSurName_SurName ) {
				this.PersonData += arguments[0].person.PersonSurName_SurName;
			}
			if ( arguments[0].person.PersonFirName_FirName ) {
				this.PersonData += (' '+arguments[0].person.PersonFirName_FirName);
			}
			if ( arguments[0].person.PersonSecName_SecName ) {
				this.PersonData += (' '+arguments[0].person.PersonSecName_SecName);
			}
			if ( arguments[0].person.PersonBirthDay_BirthDay ) {
				this.PersonData += (' '+ Ext.util.Format.date(arguments[0].person.PersonBirthDay_BirthDay, 'd.m.Y'));
			}
		}

		this.action = arguments[0].action;

		this.setTitle('Главный внештатный специалист: ' + this.getActionName(this.action));
		
		var me = this;
		var bf = this.Form.getForm();

		if(this.action == 'add'){
			bf.findField('PersonData').setValue(this.PersonData);
			bf.findField('HeadMedSpecType_id').getStore().baseParams = {action : 'add'};
		} else {
			bf.findField('HeadMedSpecType_id').getStore().baseParams = {action : 'edit',HeadMedSpec_id : this.HeadMedSpec_id};
		}
		bf.findField('HeadMedSpecType_id').getStore().load({
			callback:function(){
				me.Form.getForm().findField('HeadMedSpecType_id').setValue(me.HeadMedSpecType_id);
			}
		});

		bf.setValues(arguments[0]);
		
		this.disableFields( this.action == 'view' );
		this.buttons[0].setDisabled( this.action == 'view' );

		//bf.findField('HeadMedSpecType_id').focus(true, 100);

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
		params.MedWorker_id = this.MedWorker_id;

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
		this.Form.getForm().findField('PersonData').disable();
	},
	
	initComponent: function() {

		this.Form = new Ext.FormPanel({
			url: '/?c=HeadMedSpec&m=saveHeadMedSpec',
			frame: true,
			defaults: {
				labelAlign: 'right'
			},
			layout: 'form',
			labelWidth: 100,
			items: [{
				layout: 'form',
				items: [{
					xtype: 'hidden',
					name: 'HeadMedSpec_id'
				}, {
					layout: 'form',
					items: [{
						disabled: true,
						name: 'PersonData',
						anchor: '100%',
						xtype: 'textfield',
						fieldLabel: 'ФИО'
				}, {
					layout: 'form',
					items: [{
						allowBlank: false,
						fieldLabel : 'Специальность',
						hiddenName: 'HeadMedSpecType_id',
						anchor: '100%',
						xtype: 'swheadmedspectypecombo'
					}]
				}]
				}, {
					layout: 'form',
					items:
					[{
						xtype:'swdatefield',
						allowBlank: false,
						format:'d.m.Y',
						plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'HeadMedSpec_begDT',
						fieldLabel: 'Дата начала'
					}]
				}, {
					layout: 'form',
					items:
					[{
						xtype:'swdatefield',
						format:'d.m.Y',
						plugins:[ new Ext.ux.InputTextMask('99.99.9999', false) ],
						name: 'HeadMedSpec_endDT',
						fieldLabel: 'Дата окончания'
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
		sw.Promed.swHeadMedSpecEditWindow.superclass.initComponent.apply(this, arguments);
	}
});
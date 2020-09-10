/**
* swProcedureEditWindow - окно добавления/редактирования процедуры 
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @version      0.001-03.04.2012
* @comment      ..
*				
*/
/*NO PARSE JSON*/

sw.Promed.swProcedureEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swProcedureEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swProcedureEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		var base_form = this.FormPanel.getForm();

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

		var evnProcedureData = new Object();		
		var Proc_Name = '';
		var proc_fas_id = base_form.findField('Usluga_id').getValue();
		var index = base_form.findField('Usluga_id').getStore().findBy(function(rec) {
			if ( rec.get('Usluga_id') == proc_fas_id ) {
				return true;
			}
			else {
				return false;
			}
		});
		var record = base_form.findField('Usluga_id').getStore().getAt(index);
		console.log(record);
		console.log('@2');
		if ( record ) {
			Proc_Name = record.get('Usluga_Name');			
		}
		evnProcedureData.Procedure_id = base_form.findField('Usluga_id').getValue();
		evnProcedureData.Usluga_id = base_form.findField('Usluga_id').getValue();
		evnProcedureData.Procedure_Name = Proc_Name;
		evnProcedureData.EvnPrescrProc_Descr = base_form.findField('EvnProcedure_Descr').getValue();

		this.callback({evnProcedureData: evnProcedureData});
		this.hide();
	},
	draggable: true,	
	formStatus: 'edit',
	id: 'ProcedureEditWindow',
	initComponent: function() {
		/*
		this.UslugaComplexPanel = new sw.Promed.UslugaComplexPanel({
			win: this,
			firstTabIndex: TABINDEX_EVNPRESCR + 130,
			baseParams: {UslugaGost_Code: 'PR', level:0},
			labelWidth: 145,
			bodyStyle: 'padding: 0'
		});
		*/

		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'ProcedureEditForm',
			labelAlign: 'right',
			labelWidth: 145,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [				
				{name: 'Usluga_id'},
				{name: 'EvnProcedure_Descr'}
			]),
			region: 'center',
			//url: '/?c=EvnPrescr&m=saveProcedure',

			items: [
			{
				allowBlank: false,
				fieldLabel: lang['usluga'],
				hiddenName: 'Usluga_id',
				listWidth: 600,				
				width: 350,
				xtype: 'swuslugacombo'
			},
			{
				fieldLabel: lang['kommentariy'],
				height: 70,
				name: 'EvnProcedure_Descr',
				width: 370,				
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EVNPRESCR + 155,
				text: BTN_FRMSAVE
			},{
				text: '-'
			},			
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onTabAction: function () {
					this.UslugaComplexPanel.getFirstCombo().focus(true, 250);
				}.createDelegate(this),
				tabIndex: TABINDEX_EVNPRESCR + 159,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swProcedureEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					this.doSave();
				break;

				case Ext.EventObject.J:
					this.hide();
				break;
			}
		}.createDelegate(this),
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		scope: this,
		stopEvent: false
	}],
	layout: 'form',
	listeners: {
		'beforehide': function(win) {
			//
		},
		'hide': function(win) {
			win.onHide();
		}
	},
	loadMask: null,
	maximizable: false,
	maximized: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swProcedureEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.parentEvnClass_SysNick = null;
		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() {this.hide();}.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}
		
		if ( arguments[0].parentEvnClass_SysNick && typeof arguments[0].parentEvnClass_SysNick == 'string' ) {
			this.parentEvnClass_SysNick = arguments[0].parentEvnClass_SysNick;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask(LOAD_WAIT).show();


		this.getLoadMask().hide();
		base_form.clearInvalid();
		this.setTitle(lang['manipulyatsii_dobavlenie']);				
	//	this.UslugaComplexPanel.setValues([null]);
		//this.UslugaComplexPanel.getFirstCombo().focus(true, 250);
			
	},
	width: 550
});
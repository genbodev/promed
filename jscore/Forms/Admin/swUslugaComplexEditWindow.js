/**
* swUslugaComplexEditWindow - окно редактирования/добавления комплексной услуги.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Admin
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.002-16.11.2009
* @comment      Префикс для id компонентов UCEF (UslugaComplexEditForm)
*               tabIndex: ???
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swUslugaComplexEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.findById('UslugaComplexEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(false);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
					}
				}
			}.createDelegate(this),
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.UslugaComplex_id > 0 ) {
					base_form.findField('UslugaComplex_id').setValue(action.result.UslugaComplex_id);

					var data = new Object();

					data.UslugaComplexData = [{
						'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
						'UslugaComplex_Code': base_form.findField('UslugaComplex_Code').getValue(),
						'UslugaComplex_Name': base_form.findField('UslugaComplex_Name').getValue()
					}];

					this.callback(data);
					this.hide();
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.findById('UslugaComplexEditForm').getForm();

		if ( enable ) {
			base_form.findField('UslugaComplex_Code').enable();
			base_form.findField('UslugaComplex_Name').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('UslugaComplex_Code').disable();
			base_form.findField('UslugaComplex_Name').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'UslugaComplexEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.findById('UslugaComplexEditForm').getForm().findField('UslugaComplex_Name').focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				// tabIndex: 3005,
				text: BTN_FRMSAVE
			}, {
				text: '-'
			},
			HelpButton(this, -1),
			{
				handler: function() {
					this.hide();
				}.createDelegate(this),
				iconCls: 'cancel16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.buttons[0].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					if ( this.action != 'view' ) {
						this.findById('UslugaComplexEditForm').getForm().findField('UslugaComplex_Code').focus(true);
					}
				}.createDelegate(this),
				// tabIndex: 3006,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'UslugaComplexEditForm',
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					name: 'UslugaComplex_id',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					enableKeyEvents: true,
					fieldLabel: lang['kod'],
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus(true);
							}
						}.createDelegate(this)
					},
					name: 'UslugaComplex_Code',
					// tabIndex: 3001,
					width: 100,
					xtype: 'textfield'
				}, {
					allowBlank: false,
					fieldLabel: lang['naimenovanie'],
					name: 'UslugaComplex_Name',
					// tabIndex: 3001,
					width: 450,
					xtype: 'textfield'
				}],
				layout: 'form',
				url: '/?c=EvnUsluga&m=saveUslugaComplex'
			})]
		});
		sw.Promed.swUslugaComplexEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					if ( current_window.action != 'view' ) {
						current_window.doSave();
					}
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		},
		key: [
			Ext.EventObject.C,
			Ext.EventObject.J
		],
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function() {
			this.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('UslugaComplexEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['usluga_v_otdelenii_dobavlenie']);
				this.enableEdit(true);

				base_form.findField('UslugaComplex_Code').focus(false, 250);

				loadMask.hide();
			break;

			case 'edit':
				this.setTitle(lang['usluga_v_otdelenii_redaktirovanie']);
				this.enableEdit(true);

				base_form.findField('UslugaComplex_Code').focus(false, 250);

				loadMask.hide();
			break;

			case 'view':
				this.setTitle(lang['usluga_v_otdelenii_prosmotr']);
				this.enableEdit(false);

				this.buttons[this.buttons.length - 1].focus();

				loadMask.hide();
			break;
		}

		base_form.clearInvalid();
	},
	width: 650
});
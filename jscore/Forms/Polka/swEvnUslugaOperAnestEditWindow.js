/**
* swEvnUslugaOperAnestEditWindow - окно редактирования/добавления вида анестезии.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-19.04.2010
* @comment      Префикс для id компонентов EUOAEF (EvnUslugaOperAnestEditForm)
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swEvnUslugaOperAnestEditWindow = Ext.extend(sw.Promed.BaseForm, {
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

		var form = this.findById('EvnUslugaOperAnestEditForm');
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

				if ( action.result && action.result.EvnUslugaOperAnest_id > 0 ) {
					base_form.findField('EvnUslugaOperAnest_id').setValue(action.result.EvnUslugaOperAnest_id);

					var data = new Object();

					var anesthesia_class_code = '';
					var anesthesia_class_id = base_form.findField('AnesthesiaClass_id').getValue();
					var anesthesia_class_name = '';
					var record = null;

					record = base_form.findField('AnesthesiaClass_id').getStore().getById(anesthesia_class_id);
					if ( record ) {
						anesthesia_class_name = record.get('AnesthesiaClass_Name');
						anesthesia_class_code = record.get('AnesthesiaClass_Code');
					}

					data.EvnUslugaOperAnestData = [{
						'accessType': 'edit',
						'EvnUslugaOperAnest_id': base_form.findField('EvnUslugaOperAnest_id').getValue(),
						'EvnUslugaOperAnest_pid': base_form.findField('EvnUslugaOperAnest_pid').getValue(),
						'AnesthesiaClass_id': anesthesia_class_id,
						'AnesthesiaClass_Code': anesthesia_class_code,
						'AnesthesiaClass_Name': anesthesia_class_name
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
		var base_form = this.findById('EvnUslugaOperAnestEditForm').getForm();

		if ( enable ) {
			base_form.findField('AnesthesiaClass_id').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('AnesthesiaClass_id').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnUslugaOperAnestEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.findById('EvnUslugaOperAnestEditForm').getForm().findField('AnesthesiaClass_id').focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUOANEST + 2,
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
						this.findById('EvnUslugaOperAnestEditForm').getForm().findField('AnesthesiaClass_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUOANEST + 3,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUOAEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				buttonAlign: 'left',
				frame: false,
				id: 'EvnUslugaOperAnestEditForm',
				labelAlign: 'right',
				labelWidth: 150,
				items: [{
					name: 'EvnUslugaOperAnest_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOperAnest_pid',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					hidddenName: 'AnesthesiaClass_id',
					tabIndex: TABINDEX_EUOANEST + 1,
					width: 450,
					xtype: 'swanesthesiaclasscombo'
				}],
				layout: 'form',
				url: '/?c=EvnUslugaOperAnest&m=saveEvnUslugaOperAnest'
			})]
		});
		sw.Promed.swEvnUslugaOperAnestEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaOperAnestEditWindow');

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
		sw.Promed.swEvnUslugaOperAnestEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.findById('EvnUslugaOperAnestEditForm').getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
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

		this.findById('EUOAEF_PersonInformationFrame').load({			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EUOANESTADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('AnesthesiaClass_id').focus(false, 250);
			break;

			case 'edit':
				this.setTitle(WND_POL_EUOANESTEDIT);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('AnesthesiaClass_id').focus(false, 250);
			break;

			case 'view':
				this.setTitle(WND_POL_EUOANESTVIEW);
				this.enableEdit(false);

				loadMask.hide();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}

		base_form.clearInvalid();
	},
	width: 650
});
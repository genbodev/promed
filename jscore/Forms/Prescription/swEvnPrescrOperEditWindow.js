/**
* swEvnPrescrOperEditWindow - окно добавления/редактирования назначения.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Prescription
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.001-03.11.2011
* @comment      Префикс для id компонентов EPROPEF (EvnPrescrOperEditForm)
*/
/*NO PARSE JSON*/

/**
*	@todo:
*		1) добавить выбор бирки
*/

sw.Promed.swEvnPrescrOperEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnPrescrOperEditWindow',
	objectSrc: '/jscore/Forms/Prescription/swEvnPrescrOperEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' ) {
			return false;
		}

		if ( typeof options != 'object' ) {
			options = new Object();
		}

		this.formStatus = 'save';

		var base_form = this.FormPanel.getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.FormPanel.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var params = new Object();

		if ( base_form.findField('EvnPrescrOper_setDate').disabled ) {
			params.EvnPrescrOper_setDate = Ext.util.Format.date(base_form.findField('EvnPrescrOper_setDate').getValue(), 'd.m.Y');
		}

		if ( base_form.findField('PrescriptionStatusType_id').disabled ) {
			params.PrescriptionStatusType_id = base_form.findField('PrescriptionStatusType_id').getValue();
		}

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_1]']);
					}
				}
			}.createDelegate(this),
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					var data = new Object();
					var evnPrescrOperData = new Object();

					evnPrescrOperData.EvnPrescrOper_id = action.result.EvnPrescrOper_id;

					data.evnPrescrOperData = evnPrescrOperData;

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
		var base_form = this.FormPanel.getForm();
		var formFields = [
			'EvnPrescrOper_Descr',
			'EvnPrescrOper_setTime',
			'Usluga_id'
		];
		var i = 0;

		for ( i = 0; i < formFields.length; i++ ) {
			if ( enable ) {
				base_form.findField(formFields[i]).enable();
			}
			else {
				base_form.findField(formFields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	// height: 550,
	id: 'EvnPrescrOperEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnPrescrOperEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnPrescrOper_Descr' },
				{ name: 'EvnPrescrOper_id' },
				{ name: 'EvnPrescrOper_pid' },
				{ name: 'EvnPrescrOper_setDate' },
				{ name: 'EvnPrescrOper_setTime' },
				{ name: 'PersonEvn_id' },
				{ name: 'PrescriptionStatusType_id' },
				{ name: 'Server_id' },
				{ name: 'Usluga_id' }
			]),
			region: 'center',
			url: '/?c=EvnPrescr&m=saveEvnPrescrOper',

			items: [{
				name: 'accessType', // Режим доступа
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrOper_id', // Идентификатор бирки
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'EvnPrescrOper_pid', // Идентификатор назначения с типом "Операция"
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id', // Идентификатор состояния человека
				value: -1,
				xtype: 'hidden'
			}, {
				name: 'Server_id', // Идентификатор сервера
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				disabled: true,
				fieldLabel: lang['data'],
				format: 'd.m.Y',
				name: 'EvnPrescrOper_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				selectOnFocus: true,
				width: 100,
				xtype: 'swdatefield'
			}, {
				allowBlank: false,
				comboSubject: 'PrescriptionStatusType',
				disabled: true,
				fieldLabel: lang['status'],
				hiddenName: 'PrescriptionStatusType_id',
				listeners: {
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				// tabIndex: TABINDEX_EPRRGMEF + 1,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['vremya'],
				listeners: {
					'keydown': function (inp, e) {
						if ( e.getKey() == Ext.EventObject.F4 ) {
							e.stopEvent();
							inp.onTriggerClick();
						}
					}
				},
				name: 'EvnPrescrOper_setTime',
				onTriggerClick: function() {
					var date = new Date();
					this.setValue(Ext.util.Format.date(date, 'H:i'));
				},
				plugins: [ new Ext.ux.InputTextMask('99:99', true) ],
				// tabIndex: TABINDEX_EPSEF + 5,
				validateOnBlur: false,
				width: 60,
				xtype: 'swtimefield'
			}, {
				allowBlank: false,
				fieldLabel: lang['usluga'],
				hiddenName: 'Usluga_id',
				listWidth: 600,
				// tabIndex: TABINDEX_EUCOMEF + 9,
				width: 400,
				xtype: 'swuslugacombo'
			}, {
				fieldLabel: lang['kommentariy'],
				height: 100,
				name: 'EvnPrescrOper_Descr',
				// tabIndex: TABINDEX_EHPEF + 14,
				width: 400,
				xtype: 'textarea'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				onTabAction: function () {
					// this.buttons[1].focus();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPRDTEF + 34,
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
					// this.buttons[1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					// var base_form = this.FormPanel.getForm();
				}.createDelegate(this),
				// tabIndex: TABINDEX_EPRDTEF + 36,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnPrescrOperEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnPrescrOperEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
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
		sw.Promed.swEvnPrescrOperEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
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

		if ( arguments[0].action && typeof arguments[0].action == 'string' ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback && typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide && typeof arguments[0].onHide == 'function' ) {
			this.onHide = arguments[0].onHide;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: lang['zagruzka'] });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(lang['naznachenie_dobavlenie']);

				loadMask.hide();

				base_form.clearInvalid();
				base_form.findField('EvnPrescrOper_setTime').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				base_form.load({
					failure: function() {
						loadMask.hide();
						sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
					}.createDelegate(this),
					params: {
						'EvnPrescrOper_id': base_form.findField('EvnPrescrOper_id').getValue()
					},
					success: function(frm, act) {
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(lang['naznachenie_redaktirovanie']);
							this.enableEdit(true);
						}
						else {
							this.setTitle(lang['naznachenie_prosmotr']);
							this.enableEdit(false);
						}

						var usluga_id = base_form.findField('Usluga_id').getValue();

						if ( usluga_id ) {
							base_form.findField('Usluga_id').getStore().load({
								callback: function() {
									base_form.findField('Usluga_id').getStore().each(function(record) {
										if ( record.get('Usluga_id') == usluga_id ) {
											base_form.findField('Usluga_id').setValue(usluga_id);
											base_form.findField('Usluga_id').fireEvent('select', base_form.findField('Usluga_id'), record, 0);
										}
									});
								},
								params: {
									where: "where UslugaType_id = 2 and Usluga_id = " + usluga_id
								}
							});
						}
						else {
							base_form.findField('Usluga_id').clearValue();
						}

						loadMask.hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('EvnPrescrOper_setTime').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnPrescr&m=loadEvnPrescrOperEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 550
});
/**
* swPersonBloodGroupEditWindow - ошибка клинической диагностики
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Common
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      25.02.2011
* @comment      Префикс для id компонентов PBGEF (PersonBloodGroupEditForm)
*/

sw.Promed.swPersonBloodGroupEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var form = this.FormPanel;
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

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();

		var blood_group_type_id = base_form.findField('BloodGroupType_id').getValue();
		var blood_group_type_name = '';
		var rh_factor_type_id = base_form.findField('RhFactorType_id').getValue();
		var rh_factor_type_name = '';
		var index;
		var params = new Object();

		index = base_form.findField('BloodGroupType_id').getStore().findBy(function(rec) {
			if ( rec.get('BloodGroupType_id') == blood_group_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			blood_group_type_name = base_form.findField('BloodGroupType_id').getStore().getAt(index).get('BloodGroupType_Name');
		}

		index = base_form.findField('RhFactorType_id').getStore().findBy(function(rec) {
			if ( rec.get('RhFactorType_id') == rh_factor_type_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			rh_factor_type_name = base_form.findField('RhFactorType_id').getStore().getAt(index).get('RhFactorType_Name');
		}

		var data = new Object();

		switch ( this.formMode ) {
			case 'local':
				data.personBloodGroupData = {
					'PersonBloodGroup_id': base_form.findField('PersonBloodGroup_id').getValue(),
					'Person_id': base_form.findField('Person_id').getValue(),
					'Server_id': base_form.findField('Server_id').getValue(),
					'BloodGroupType_id': blood_group_type_id,
					'RhFactorType_id': rh_factor_type_id,
					'PersonBloodGroup_setDate': base_form.findField('PersonBloodGroup_setDate').getValue(),
					'BloodGroupType_Name': blood_group_type_name,
					'RhFactorType_Name': rh_factor_type_name
				};

				this.callback(data);

				this.formStatus = 'edit';
				loadMask.hide();

				this.hide();
			break;

			case 'remote':
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
							if ( action.result.PersonBloodGroup_id > 0 ) {
								base_form.findField('PersonBloodGroup_id').setValue(action.result.PersonBloodGroup_id);

								data.personBloodGroupData = {
									'PersonBloodGroup_id': base_form.findField('PersonBloodGroup_id').getValue(),
									'Person_id': base_form.findField('Person_id').getValue(),
									'Server_id': base_form.findField('Server_id').getValue(),
									'BloodGroupType_id': blood_group_type_id,
									'RhFactorType_id': rh_factor_type_id,
									'PersonBloodGroup_setDate': base_form.findField('PersonBloodGroup_setDate').getValue(),
									'BloodGroupType_Name': blood_group_type_name,
									'RhFactorType_Name': rh_factor_type_name
								};

								this.callback(data);
								this.hide();
							}
							else {
								if ( action.result.Error_Msg ) {
									sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
								}
								else {
									sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
								}
							}
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;

			default:
				loadMask.hide();
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'BloodGroupType_id',
			'PersonBloodGroup_setDate',
			'RhFactorType_id'
		);
		var i = 0;

		for ( i = 0; i < form_fields.length; i++ ) {
			if ( enable ) {
				base_form.findField(form_fields[i]).enable();
			}
			else {
				base_form.findField(form_fields[i]).disable();
			}
		}

		if ( enable ) {
			this.buttons[0].show();
		}
		else {
			this.buttons[0].hide();
		}
	},
	formMode: 'remote',
	formStatus: 'edit',
	id: 'PersonBloodGroupEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'PersonBloodGroupEditForm',
			labelAlign: 'right',
			labelWidth: 250,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'BloodGroupType_id' },
				{ name: 'PersonBloodGroup_id' },
				{ name: 'PersonBloodGroup_setDate' },
				{ name: 'Person_id' },
				{ name: 'RhFactorType_id' },
				{ name: 'Server_id' }
			]),
			url: '/?c=PersonBloodGroup&m=savePersonBloodGroup',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'PersonBloodGroup_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				comboSubject: 'BloodGroupType',
				fieldLabel: lang['gruppa_krovi'],
				hiddenName: 'BloodGroupType_id',
				listeners: {
					'keydown': function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.TAB:
								if ( e.shiftKey == true ) {
									e.stopEvent();
									this.buttons[this.buttons.length - 1].focus();
								}
							break;
						}
					}.createDelegate(this)
				},
				tabIndex: TABINDEX_PBGEF + 1,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				comboSubject: 'RhFactorType',
				fieldLabel: lang['rh-faktor'],
				hiddenName: 'RhFactorType_id',
				tabIndex: TABINDEX_PBGEF + 2,
				width: 400,
				xtype: 'swcommonsprcombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_opredeleniya'],
				format: 'd.m.Y',
				name: 'PersonBloodGroup_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_PBGEF + 3,
				width: 100,
				xtype: 'swdatefield'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'PBGEF_PersonInformationFrame',
			region: 'north'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus(true);
					}
					else {
						this.FormPanel.getForm().findField('PersonBloodGroup_setDate').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_PBGEF + 4,
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
						this.FormPanel.getForm().findField('BloodGroupType_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_PBGEF + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swPersonBloodGroupEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('PersonBloodGroupEditWindow');

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
		stopEvent: true
	}],
	layout: 'form',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: false,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swPersonBloodGroupEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formMode = 'remote';
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

		if ( arguments[0].formMode && typeof arguments[0].formMode == 'string' && arguments[0].formMode.inlist([ 'local', 'remote' ]) ) {
			this.formMode = arguments[0].formMode;
		}
		
		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.PersonInfo.load({
			Person_id: base_form.findField('Person_id').getValue(),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				clearDateAfterPersonDeath('personpanelid', 'PBGEF_PersonInformationFrame', base_form.findField('PersonBloodGroup_setDate'));
			}
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PERSON_PBGEFADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.clearInvalid();

				base_form.findField('BloodGroupType_id').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				if ( this.formMode == 'local' ) {
					if ( this.action == 'edit' ) {
						this.setTitle(WND_PERSON_PBGEFEDIT);
						this.enableEdit(true);
					}
					else {
						this.setTitle(WND_PERSON_PBGEFVIEW);
						this.enableEdit(false);
					}

					loadMask.hide();

					base_form.clearInvalid();

					if ( this.action == 'view' ) {
						this.buttons[this.buttons.length - 1].focus();
					}
					else {
						base_form.findField('BloodGroupType_id').focus(true, 250);
					}
				}
				else {
					var person_blood_group_id = base_form.findField('PersonBloodGroup_id').getValue();

					if ( !person_blood_group_id ) {
						loadMask.hide();
						this.hide();
						return false;
					}

					base_form.load({
						failure: function() {
							loadMask.hide();
							sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
						}.createDelegate(this),
						params: {
							'PersonBloodGroup_id': person_blood_group_id
						},
						success: function() {
							if ( base_form.findField('accessType').getValue() == 'view' ) {
								this.action = 'view';
							}

							if ( this.action == 'edit' ) {
								this.setTitle(WND_PERSON_PBGEFEDIT);
								this.enableEdit(true);
							}
							else {
								this.setTitle(WND_PERSON_PBGEFVIEW);
								this.enableEdit(false);
							}

							loadMask.hide();

							base_form.clearInvalid();

							if ( this.action == 'view' ) {
								this.buttons[this.buttons.length - 1].focus();
							}
							else {
								base_form.findField('BloodGroupType_id').focus(true, 250);
							}
						}.createDelegate(this),
						url: '/?c=PersonBloodGroup&m=loadPersonBloodGroupEditForm'
					});
				}
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 700
});
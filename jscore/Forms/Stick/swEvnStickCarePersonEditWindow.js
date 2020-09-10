/**
* swEvnStickCarePersonEditWindow - микроскопическое описание
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Stick
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      17.08.2011
* @comment      Префикс для id компонентов ESCPEF (EvnStickCarePersonEditForm)
*
*
* Использует: -
*/
/*NO PARSE JSON*/

sw.Promed.swEvnStickCarePersonEditWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnStickCarePersonEditWindow',
	objectSrc: '/jscore/Forms/Stick/swEvnStickCarePersonEditWindow.js',

	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	doSave: function(options) {
		// options @Object

		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
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

		var data = new Object();
		var record = base_form.findField('RelatedLinkType_id').getStore().getById(base_form.findField('RelatedLinkType_id').getValue());
		var related_link_type_name = '';

		if ( record ) {
			related_link_type_name = record.get('RelatedLinkType_Name');
		}

		data.evnStickCarePersonData = {
			'accessType': 'edit',
			'Evn_id': base_form.findField('Evn_id').getValue(),
			'Person_Age': base_form.findField('Person_Age').getValue(),
			'Person_id': base_form.findField('Person_id').getValue(),
			'Person_pid': base_form.findField('Person_pid').getValue(),
			'RelatedLinkType_id': base_form.findField('RelatedLinkType_id').getValue(),
			'Person_Fio': base_form.findField('Person_Fio').getRawValue(),
			'Person_Birthday': base_form.findField('Person_Birthday').getRawValue(),
			'Person_Firname': base_form.findField('Person_Firname').getRawValue(),
			'Person_Surname': base_form.findField('Person_Surname').getRawValue(),
			'Person_Secname': base_form.findField('Person_Secname').getRawValue(),
			'RelatedLinkType_Name': related_link_type_name
		};

		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

				data.evnStickCarePersonData.EvnStickCarePerson_id = base_form.findField('EvnStickCarePerson_id').getValue();

				this.callback(data);
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
								sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_3]']);
							}
						}
					}.createDelegate(this),
					success: function(result_form, action) {
						this.formStatus = 'edit';
						loadMask.hide();

						if ( action.result && action.result.EvnStickCarePerson_id > 0 ) {
							base_form.findField('EvnStickCarePerson_id').setValue(action.result.EvnStickCarePerson_id);

							data.evnStickCarePersonData.EvnStickCarePerson_id = base_form.findField('EvnStickCarePerson_id').getValue();

							this.callback(data);
							this.hide();
						}
						else {
							sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
						}
					}.createDelegate(this)
				});
			break;
		}
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Person_Fio', // ФИО пациента
			'RelatedLinkType_id' // Родственная связь
		);
		var i;

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
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'EvnStickCarePersonEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnStickCarePersonEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'accessType' },
				{ name: 'EvnStickCarePerson_id' },
				{ name: 'Evn_id' },
				{ name: 'Person_id' },
				{ name: 'Person_pid' },
				{ name: 'RelatedLinkType_id' },
				{ name: 'Person_Fio' }
			]),
			url: '/?c=EvnStickCarePerson&m=saveEvnStickCarePerson',
			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnStickCarePerson_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Evn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_Age',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				enableKeyEvents: true,
				fieldLabel: lang['patsient'],
				listeners: {
					'keydown': function(inp, e) {
						if ( inp.disabled ) {
							return false;
						}

						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
							e.stopEvent();
							this.buttons[this.buttons.length - 1].focus();
						}
						else if ( e.getKey() == Ext.EventObject.F4 ) {
							e.stopEvent();
							this.openPersonSearchWindow();
						}
					}.createDelegate(this)
				},
				name: 'Person_Fio',
				onTriggerClick: function() {
					this.openPersonSearchWindow();
				}.createDelegate(this),
				readOnly: true,
				tabIndex: TABINDEX_ESCPEF + 1,
				triggerClass: 'x-form-search-trigger',
				width: 430,
				xtype: 'trigger'
			}, {
				allowBlank: getRegionNick()=='kz',
				comboSubject: 'RelatedLinkType',
				fieldLabel: lang['rodstvennaya_svyaz'],
				hiddenName: 'RelatedLinkType_id',
				tabIndex: TABINDEX_ESCPEF + 2,
				width: 250,
				xtype: 'swcommonsprcombo'
			}, {
				name: 'Person_Birthday',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_Firname',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_Surname',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_Secname',
				value: 0,
				xtype: 'hidden'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					if ( this.action != 'view' ) {
						this.FormPanel.getForm().findField('RelatedLinkType_id').focus(true);
					}
					else {
						this.buttons[this.buttons.length - 1].focus();
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_ESCPEF + 3,
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
						this.FormPanel.getForm().findField('Person_Fio').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_ESCPEF + 4,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swEvnStickCarePersonEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnStickCarePersonEditWindow');

			switch ( e.getKey() ) {
				case Ext.EventObject.C:
					current_window.doSave();
				break;

				case Ext.EventObject.J:
					current_window.hide();
				break;
			}
		}.createDelegate(this),
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
	openPersonSearchWindow: function() {
		var base_form = this.FormPanel.getForm();

		getWnd('swPersonSearchWindow').show({
			onSelect: function(person_data) {
				if ( base_form.findField('Person_pid').getValue() == person_data.Person_id ) {
					sw.swMsg.alert(lang['oshibka'], lang['patsient_ne_mojet_uhajivat_sam_za_soboy']);
				}
				else {
					var fio = person_data.PersonSurName_SurName + ' ' + person_data.PersonFirName_FirName + ' ' + person_data.PersonSecName_SecName;

					base_form.findField('Person_Firname').setValue(person_data.PersonFirName_FirName);
					base_form.findField('Person_Surname').setValue(person_data.PersonSurName_SurName);
					base_form.findField('Person_Secname').setValue(person_data.PersonSecName_SecName);
					if ( person_data.Person_Birthday ) {
						base_form.findField('Person_Birthday').setValue(Ext.util.Format.date(person_data.Person_Birthday,'d.m.Y'));
					} else {
						base_form.findField('Person_Birthday').setValue('');
					}
					
					// Пересчитать Person_Age
					if ( this.evnStickSetDate && person_data.Person_Birthday ) {
						base_form.findField('Person_Age').setValue(swGetPersonAge(person_data.Person_Birthday, this.evnStickSetDate));
					}

					base_form.findField('Person_Fio').setValue(fio);
					base_form.findField('Person_id').setValue(person_data.Person_id);

					getWnd('swPersonSearchWindow').hide();
				}
			}.createDelegate(this),
			searchMode: 'all'
		});
	},
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swEvnStickCarePersonEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.evnStickSetDate = null;
		this.formMode = 'local';
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

		if ( arguments[0].evnStickSetDate ) {
			this.evnStickSetDate = arguments[0].evnStickSetDate;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}
		
		this.getLoadMask().show();
							
		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_STICK_ESCPEFADD);
				this.enableEdit(true);

				this.getLoadMask().hide();

				base_form.clearInvalid();

				base_form.findField('Person_Fio').focus(true, 250);
			break;

			case 'edit':
			case 'view':
				switch ( this.formMode ) {
					case 'local':
						if ( this.action == 'edit' ) {
							this.setTitle(WND_STICK_ESCPEFEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_STICK_ESCPEFVIEW);
							this.enableEdit(false);
						}

						this.getLoadMask().hide();

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							base_form.findField('Person_Fio').focus(true, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					break;

					case 'remote':
						base_form.load({
							failure: function() {
								this.getLoadMask().hide();
								sw.swMsg.alert(lang['oshibka'], lang['oshibka_pri_zagruzke_dannyih_formyi'], function() { this.hide(); }.createDelegate(this) );
							}.createDelegate(this),
							params: {
								'EvnStickCarePerson_id': base_form.findField('EvnStickCarePerson_id').getValue()
							},
							success: function() {
								// В зависимости от accessType переопределяем this.action
								if ( base_form.findField('accessType').getValue() == 'view' ) {
									this.action = 'view';
								}

								if ( this.action == 'edit' ) {
									this.setTitle(WND_STICK_ESCPEFEDIT);
									this.enableEdit(true);
								}
								else {
									this.setTitle(WND_STICK_ESCPEFVIEW);
									this.enableEdit(false);
								}

								this.getLoadMask().hide();

								base_form.clearInvalid();

								if ( this.action == 'edit' ) {
									base_form.findField('Person_Fio').focus(true, 250);
								}
								else {
									this.buttons[this.buttons.length - 1].focus();
								}
							}.createDelegate(this),
							url: '/?c=EvnStickCarePerson&m=loadEvnStickCarePersonEditForm'
						});
					break;
				}
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
	},
	width: 650
});

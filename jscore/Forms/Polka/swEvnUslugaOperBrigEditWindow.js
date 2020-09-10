/**
* swEvnUslugaOperBrigEditWindow - окно редактирования/добавления операционной бригады.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-23.03.2010
* @comment      Префикс для id компонентов EUOBEF (EvnUslugaOperBrigEditForm)
*
*
* @input data: action - действие (add, edit, view)
*/

sw.Promed.swEvnUslugaOperBrigEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	draggable: true,
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';

		var data = new Object();
		var record;

		var form = this.findById('EvnUslugaOperBrigEditForm');
		var base_form = form.getForm();

		if ( !base_form.isValid() ) {
			Ext.MessageBox.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					form.getFirstInvalidEl().focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_personal_code = '';
		var med_personal_id = null;
		var med_personal_fio = '';
		var surg_type_code = null;
		var surg_type_id = base_form.findField('SurgType_id').getValue();
		var surg_type_name = '';

		var record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_code = record.get('MedPersonal_TabCode');
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		record = base_form.findField('SurgType_id').getStore().getById(surg_type_id);
		if ( record ) {
			surg_type_code = record.get('SurgType_Code');
			surg_type_name = record.get('SurgType_Name');
		}

		var params = new Object();

		params.MedPersonal_id = med_personal_id;
		params.EvnUslugaOper_setDate = this.EvnUslugaOper_setDate;

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
			params: params,
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnUslugaOperBrig_id > 0 ) {
					base_form.findField('EvnUslugaOperBrig_id').setValue(action.result.EvnUslugaOperBrig_id);

					data.EvnUslugaOperBrigData = [{
						'accessType': 'edit',
						'EvnUslugaOperBrig_id': base_form.findField('EvnUslugaOperBrig_id').getValue(),
						'EvnUslugaOperBrig_pid': base_form.findField('EvnUslugaOperBrig_pid').getValue(),
						'MedPersonal_id': med_personal_id,
						'MedStaffFact_id': med_staff_fact_id,
						'SurgType_Code': surg_type_code,
						'SurgType_id': surg_type_id,
						'MedPersonal_Code': med_personal_code,
						'MedPersonal_Fio': med_personal_fio,
						'SurgType_Name': surg_type_name
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
	enableEdit: function(enable) {
		var base_form = this.findById('EvnUslugaOperBrigEditForm').getForm();

		if ( enable ) {
			base_form.findField('MedStaffFact_id').enable();
			base_form.findField('SurgType_id').enable();

			this.buttons[0].show();
		}
		else {
			base_form.findField('MedStaffFact_id').disable();
			base_form.findField('SurgType_id').disable();

			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnUslugaOperBrigEditWindow',
	initComponent: function() {
		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EUOBEF + 3,
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
						this.findById('EvnUslugaOperBrigEditForm').getForm().findField('SurgType_id').focus(true, 100);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EUOBEF + 4,
				text: BTN_FRMCANCEL
			}],
			items: [ new sw.Promed.PersonInformationPanelShort({
				id: 'EUOBEF_PersonInformationFrame'
			}),
			new Ext.form.FormPanel({
				bodyBorder: false,
				bodyStyle: 'padding: 5px 5px 0',
				border: false,
				frame: false,
				id: 'EvnUslugaOperBrigEditForm',
				labelAlign: 'right',
				labelWidth: 120,
				items: [{
					name: 'EvnUslugaOperBrig_id',
					value: 0,
					xtype: 'hidden'
				}, {
					name: 'EvnUslugaOperBrig_pid',
					value: 0,
					xtype: 'hidden'
				}, {
					allowBlank: false,
					autoLoad: false,
					comboSubject: 'SurgType',
					fieldLabel: lang['vid'],
					hiddenName: 'SurgType_id',
					lastQuery: '',
					listeners: {
						'keydown': function (inp, e) {
							if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
								e.stopEvent();
								this.buttons[this.buttons.length - 1].focus();
							}
						}.createDelegate(this),
						'render': function (field) {
							field.getStore().load();
						}.createDelegate(this)
					},
					tabIndex: TABINDEX_EUOBEF + 1,
					width: 300,
					xtype: 'swcommonsprcombo'
				}, {
					allowBlank: false,
					fieldLabel: lang['vrach'],
					listWidth: 650,
					tabIndex: TABINDEX_EUOBEF + 2,
					width: 500,
					xtype: 'swmedstafffactglobalcombo'
				}],
				keys: [{
					alt: true,
					fn: function(inp, e) {
						switch ( e.getKey() ) {
							case Ext.EventObject.C:
								if ( this.action != 'view' ) {
									this.doSave();
								}
							break;

							case Ext.EventObject.J:
								this.hide();
							break;
						}
					}.createDelegate(this),
					key: [ Ext.EventObject.C, Ext.EventObject.J ],
					scope: this,
					stopEvent: true
				}],
				layout: 'form',
				reader: new Ext.data.JsonReader({
					success: Ext.emptyFn
				}, [
					{ name: 'EvnUslugaOperBrig_id' }
				]),
				url: '/?c=EvnUslugaOperBrig&m=saveEvnUslugaOperBrig'
			})]
		});
		sw.Promed.swEvnUslugaOperBrigEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			e.stopEvent();

			if ( e.browserEvent.stopPropagation ) {
				e.browserEvent.stopPropagation();
			}
			else {
				e.browserEvent.cancelBubble = true;
			}

			if ( e.browserEvent.preventDefault ) {
				e.browserEvent.preventDefault();
			}
			else {
				e.browserEvent.returnValue = false;
			}

			e.returnValue = false;

			if ( Ext.isIE ) {
				e.browserEvent.keyCode = 0;
				e.browserEvent.which = 0;
			}

			var current_window = Ext.getCmp('EvnUslugaOperBrigEditWindow');

			if ( e.getKey() == Ext.EventObject.J ) {
				current_window.hide();
			}
			else if ( e.getKey() == Ext.EventObject.C ) {
				if ( 'view' != current_window.action ) {
					current_window.doSave();
				}
			}
		},
		key: [ Ext.EventObject.C, Ext.EventObject.J ],
		scope: this,
		stopEvent: false
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
		sw.Promed.swEvnUslugaOperBrigEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.findById('EvnUslugaOperBrigEditForm').getForm();
		base_form.reset();

		base_form.findField('SurgType_id').getStore().clearFilter();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.surgTypeFilter = 0;
		this.EvnUslugaOper_setDate = null;
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi']);
			return false;
		}

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].surgTypeFilter ) {
			this.surgTypeFilter = arguments[0].surgTypeFilter;

			base_form.findField('SurgType_id').getStore().filterBy(function(rec) {
				switch ( Number(this.surgTypeFilter) ) {
					case -1:
						if ( rec.get('SurgType_Code') == 1 ) {
							return false;
						}
						else {
							return true;
						}
					break;

					case 1:
						if ( rec.get('SurgType_Code') == 1 ) {
							return true;
						}
						else {
							return false;
						}
					break;

					default:
						return true;
					break;
				}
			}.createDelegate(this));
		}

		this.findById('EUOBEF_PersonInformationFrame').load({
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : '')
		});

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		base_form.setValues(arguments[0].formParams);
		if(arguments[0].formParams.EvnUslugaOper_setDate){
			this.EvnUslugaOper_setDate = arguments[0].formParams.EvnUslugaOper_setDate;
			setMedStaffFactGlobalStoreFilter({onDate: arguments[0].formParams.EvnUslugaOper_setDate});
		}else{
			setMedStaffFactGlobalStoreFilter();
		}

		base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EUOPERBRIGADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.findField('SurgType_id').focus(true, 250);
			break;

			case 'edit':
				this.setTitle(WND_POL_EUOPERBRIGEDIT);
				this.enableEdit(true);

				var med_personal_id = base_form.findField('MedStaffFact_id').getValue();

				base_form.findField('MedStaffFact_id').clearValue();

				var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					if ( rec.get('MedPersonal_id') == med_personal_id ) {
						return true;
					}
					else {
						return false;
					}
				});

				var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

				if ( med_personal_record ) {
					base_form.findField('MedStaffFact_id').setValue(med_personal_record.get('MedStaffFact_id'));
				}

				loadMask.hide();

				base_form.findField('SurgType_id').focus(true, 250);
			break;

			case 'view':
				this.setTitle(WND_POL_EUOPERBRIGVIEW);
				this.enableEdit(false);

				var med_personal_id = base_form.findField('MedStaffFact_id').getValue();

				base_form.findField('MedStaffFact_id').clearValue();

				var index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
					if ( rec.get('MedPersonal_id') == med_personal_id ) {
						return true;
					}
					else {
						return false;
					}
				});

				var med_personal_record = base_form.findField('MedStaffFact_id').getStore().getAt(index);

				if ( med_personal_record ) {
					base_form.findField('MedStaffFact_id').setValue(med_personal_record.get('MedStaffFact_id'));
				}

				loadMask.hide();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	surgTypeFilter: 0,
	width: 700
});
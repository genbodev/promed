/**
* swEvnMorfoHistologicMemberEditWindow - протокол патоморфогистологического исследования
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      PathoMorphology
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      15.02.2011
* @comment      Префикс для id компонентов EMHMEF (EvnMorfoHistologicMemberEditForm)
*/

sw.Promed.swEvnMorfoHistologicMemberEditWindow = Ext.extend(sw.Promed.BaseForm, {
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

		var index;
		var lpu_id = base_form.findField('Lpu_id').getValue();
		var lpu_name = '';
		var med_personal_code = '';
		var med_personal_fio = '';
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var params = new Object();

		index = base_form.findField('Lpu_id').getStore().findBy(function(rec) {
			if ( rec.get('Lpu_id') == lpu_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			lpu_name = base_form.findField('Lpu_id').getStore().getAt(index).get('Lpu_Nick');
		}

		index = base_form.findField('MedStaffFact_id').getStore().findBy(function(rec) {
			if ( rec.get('MedStaffFact_id') == med_staff_fact_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		if ( index >= 0 ) {
			med_personal_code = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_TabCode');
			med_personal_fio = base_form.findField('MedStaffFact_id').getStore().getAt(index).get('MedPersonal_Fio');
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: "Подождите, идет сохранение..." });
		loadMask.show();
		
		var data = new Object();

		data.evnMorfoHistologicMemberData = {
			'EvnMorfoHistologicMember_id': base_form.findField('EvnMorfoHistologicMember_id').getValue(),
			'EvnMorfoHistologicProto_id': base_form.findField('EvnMorfoHistologicProto_id').getValue(),
			'Lpu_id': lpu_id,
			'MedStaffFact_id': med_staff_fact_id,
			'Lpu_Name': lpu_name,
			'MedPersonal_Code': med_personal_code,
			'MedPersonal_Fio': med_personal_fio
		};

		this.callback(data);

		this.formStatus = 'edit';
		loadMask.hide();

		this.hide();
	},
	draggable: true,
	enableEdit: function(enable) {
		var base_form = this.FormPanel.getForm();
		var form_fields = new Array(
			'Lpu_id',
			'MedStaffFact_id'
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
	formStatus: 'edit',
	id: 'EvnMorfoHistologicMemberEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			autoHeight: true,
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'EvnMorfoHistologicMemberEditForm',
			labelAlign: 'right',
			labelWidth: 100,
			reader: new Ext.data.JsonReader({
				success: Ext.amptyFn
			},  [
				{ name: 'accessType' },
				{ name: 'EvnMorfoHistologicMember_id' },
				{ name: 'EvnMorfoHistologicProto_id' },
				{ name: 'Lpu_id' },
				{ name: 'MedStaffFact_id' }
			]),
			url: '/?c=EvnMorfoHistologicProto&m=saveEvnMorfoHistologicMember',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnMorfoHistologicMember_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnMorfoHistologicProto_id',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				fieldLabel: lang['lpu'],
				hiddenName: 'Lpu_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();

						var med_staff_fact_combo = base_form.findField('MedStaffFact_id');

						var med_staff_fact_id = med_staff_fact_combo.getValue();

						base_form.findField('MedStaffFact_id').clearValue();
						base_form.findField('MedStaffFact_id').disable();

						if ( !newValue ) {
							return false;
						}

						if ( this.action != 'view' ) {
							base_form.findField('MedStaffFact_id').enable();
						}

						if ( newValue == getGlobalOptions().lpu_id ) {
							setMedStaffFactGlobalStoreFilter();

							base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

							if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
								base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
							}
						}
						else {
							var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
							loadMask.show();

							base_form.findField('MedStaffFact_id').getStore().load({
								failure: function() {
									loadMask.hide();
								}.createDelegate(this),
								callback: function() {
									loadMask.hide();

									if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
										base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
									}
								}.createDelegate(this),
								params: {
									Lpu_id: newValue
								}
							});
						}
					}.createDelegate(this),
					'keydown': function(inp, e) {
						if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
							e.stopEvent();
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					'render': function(combo) {
						combo.getStore().load();
					}.createDelegate(this)
				},
				listWidth: 650,
				tabIndex: TABINDEX_EMHMEF + 1,
				width: 400,
				xtype: 'swlpucombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['vrach'],
				hiddenName: 'MedStaffFact_id',
				listWidth: 650,
				tabIndex: TABINDEX_EMHMEF + 2,
				width: 400,
				xtype: 'swmedstafffactglobalcombo'
			}]
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
						this.FormPanel.getForm().findField('MedStaffFact_id').focus(true);
					}
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHMEF + 3,
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
						this.FormPanel.getForm().findField('Lpu_id').focus(true);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EMHMEF + 4,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			],
			layout: 'form'
		});

		sw.Promed.swEvnMorfoHistologicMemberEditWindow.superclass.initComponent.apply(this, arguments);
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnMorfoHistologicMemberEditWindow');

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
		sw.Promed.swEvnMorfoHistologicMemberEditWindow.superclass.show.apply(this, arguments);

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

		var index;
		var record;

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_PATHOMORPH_EMHMEFADD);
				this.enableEdit(true);

				index = base_form.findField('Lpu_id').getStore().findBy(function(rec) {
					if ( rec.get('Lpu_id') == getGlobalOptions().lpu_id ) {
						return true;
					}
					else {
						return false;
					}
				});

				if ( index >= 0 ) {
					base_form.findField('Lpu_id').setValue(base_form.findField('Lpu_id').getStore().getAt(index).get('Lpu_id'));
				}
			break;

			case 'edit':
				this.setTitle(WND_PATHOMORPH_EMHMEFEDIT);
				this.enableEdit(true);
			break;

			case 'view':
				this.setTitle(WND_PATHOMORPH_EMHMEFVIEW);
				this.enableEdit(false);
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}

		loadMask.hide();

		base_form.findField('Lpu_id').fireEvent('change', base_form.findField('Lpu_id'), base_form.findField('Lpu_id').getValue());

		base_form.clearInvalid();

		if ( this.action == 'view' ) {
			this.buttons[this.buttons.length - 1].focus();
		}
		else {
			base_form.findField('Lpu_id').focus(true, 250);
		}
	},
	width: 600
});
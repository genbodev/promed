/**
* swUslugaComplexProfileEditWindow - редактирование профиля услуги
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Usluga
* @access       public
* @copyright    Copyright (c) 2009-1014 Swan Ltd.
* @author       Stanislav Bykov (savage@swan.perm.ru)
* @version      28.10.2014
* @comment      Префикс для id компонентов UCProfEW (UslugaComplexProfileEditWindow)
*
*
* Использует: -
*/
sw.Promed.swUslugaComplexProfileEditWindow = Ext.extend(sw.Promed.BaseForm, {
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

		var data = new Object();

		data.uslugaComplexProfileData = {
			'UslugaComplexProfile_id': base_form.findField('UslugaComplexProfile_id').getValue(),
			'UslugaComplex_id': base_form.findField('UslugaComplex_id').getValue(),
			'LpuSectionProfile_id': base_form.findField('LpuSectionProfile_id').getValue(),
			'LpuSectionProfile_Name': base_form.findField('LpuSectionProfile_id').getFieldValue('LpuSectionProfile_Name'),
			'UslugaComplexProfile_begDate': base_form.findField('UslugaComplexProfile_begDate').getValue(),
			'UslugaComplexProfile_endDate': base_form.findField('UslugaComplexProfile_endDate').getValue(),
			'RecordStatus_Code': base_form.findField('RecordStatus_Code').getValue(),
			'pmUser_Name': getGlobalOptions().pmuser_name
		};

		if ( this.checkUslugaSectionProfileDates(data.uslugaComplexProfileData) == false ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('LpuSectionProfile_id').focus(true);
				}.createDelegate(this),
				icon: Ext.Msg.ERROR,
				msg: lang['obnarujeno_peresechenie_periodov_deystviya_zapisey_s_odinakovyim_profilem'],
				title: lang['oshibka']
			});
			return false;
		}

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		switch ( this.formMode ) {
			case 'local':
				this.formStatus = 'edit';
				loadMask.hide();

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

						if ( action.result && action.result.UslugaComplexProfile_id > 0 ) {
							base_form.findField('UslugaComplexProfile_id').setValue(action.result.UslugaComplexProfile_id);
							data.uslugaComplexProfileData.UslugaComplexProfile_id = base_form.findField('UslugaComplexProfile_id').getValue();

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
	formStatus: 'edit',
	getLoadMask: function() {
		if ( !this.loadMask ) {
			this.loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		}

		return this.loadMask;
	},
	id: 'UslugaComplexProfileEditWindow',
	initComponent: function() {
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			frame: false,
			id: 'UslugaComplexProfileEditForm',
			labelAlign: 'right',
			labelWidth: 160,
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: Ext.emptyFn
			}, [
				{ name: 'UslugaComplex_id' },
				{ name: 'UslugaComplexProfile_id' },
				{ name: 'LpuSectionProfile_id' },
				{ name: 'UslugaComplexProfile_begDate' },
				{ name: 'UslugaComplexProfile_endDate' }
			]),
			url: '/?c=UslugaComplex&m=saveUslugaComplexProfile',
			items: [{
				name: 'UslugaComplexProfile_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'UslugaComplex_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'RecordStatus_Code',
				value: 0,
				xtype: 'hidden'
			}, {
				allowBlank: false,
				anchor: '100%',
				autoLoad: false,
				hiddenName: 'LpuSectionProfile_id',
				id: 'ucprofLpuSectionProfile_id',
				lastQuery: '',
				tabIndex: TABINDEX_UCProfEW + 1,
				width: 400,
				xtype: 'swlpusectionprofilecombo'
			}, {
				allowBlank: false,
				fieldLabel: lang['data_nachala'],
				format: 'd.m.Y',
				id: 'ucprofUslugaComplexProfile_begDate',
				listeners: {
					'change': function (combo, newValue, oldValue) {
						var form = this.FormPanel.getForm();
						form.findField('UslugaComplexProfile_endDate').fireEvent('change', form.findField('UslugaComplexProfile_endDate'), form.findField('UslugaComplexProfile_endDate').getValue());

						if ( typeof newValue == 'object' ) {
							form.findField('UslugaComplexProfile_endDate').setMinValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							form.findField('UslugaComplexProfile_endDate').setMinValue(undefined);
						}
					}.createDelegate(this)
				},
				name: 'UslugaComplexProfile_begDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UCProfEW + 2,
				xtype: 'swdatefield'
			}, {
				fieldLabel: lang['data_okonchaniya'],
				format: 'd.m.Y',
				id: 'ucprofUslugaComplexProfile_endDate',
				listeners: {
					'change':function (field, newValue, oldValue) {
						var form = this.FormPanel.getForm();

						if ( typeof newValue == 'object' ) {
							form.findField('UslugaComplexProfile_begDate').setMaxValue(Ext.util.Format.date(newValue, 'd.m.Y'));
						}
						else {
							form.findField('UslugaComplexProfile_begDate').setMaxValue(undefined);
						}

						var
							index,
							LpuSectionProfile_id = form.findField('LpuSectionProfile_id').getValue(),
							begDate = form.findField('UslugaComplexProfile_begDate').getValue();

						// Фильтруем список профилей отделений
						form.findField('LpuSectionProfile_id').clearValue();
						form.findField('LpuSectionProfile_id').getStore().clearFilter();
						form.findField('LpuSectionProfile_id').lastQuery = '';

						if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(newValue) ) {
							form.findField('LpuSectionProfile_id').getStore().filterBy(function(rec) {
								if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
									return true;
								}

								if ( !Ext.isEmpty(begDate) && Ext.isEmpty(newValue) ) {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= begDate)
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= begDate)
									);
								}
								else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(newValue) ) {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || rec.get('LpuSectionProfile_begDT') <= newValue)
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || rec.get('LpuSectionProfile_endDT') >= newValue)
									);
								}
								else {
									return (
										(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || typeof rec.get('LpuSectionProfile_begDT') != 'object' || (rec.get('LpuSectionProfile_begDT') <= newValue && rec.get('LpuSectionProfile_begDT') <= begDate))
										&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || typeof rec.get('LpuSectionProfile_endDT') != 'object' || (rec.get('LpuSectionProfile_endDT') >= newValue && rec.get('LpuSectionProfile_endDT') >= begDate))
									);
								}
							});
						}

						index = form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
						});
						
						if ( index >= 0 ) {
							form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
							form.findField('LpuSectionProfile_id').fireEvent('select', form.findField('LpuSectionProfile_id'), form.findField('LpuSectionProfile_id').getStore().getAt(index));
						}
					}.createDelegate(this)
				},
				name: 'UslugaComplexProfile_endDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_UCProfEW + 2,
				xtype: 'swdatefield'
			}]
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				onShiftTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_UCProfEW + 4,
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
					var base_form = this.FormPanel.getForm();
					base_form.findField('LpuSectionProfile_id').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_UCProfEW + 5,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.FormPanel
			]
		});

		sw.Promed.swUslugaComplexProfileEditWindow.superclass.initComponent.apply(this, arguments);

		this.findById('ucprofLpuSectionProfile_id').setBaseFilter(function(rec) {
			var
				begDate = this.findById('ucprofUslugaComplexProfile_begDate').getValue(),
				endDate = this.findById('ucprofUslugaComplexProfile_endDate').getValue();

			if ( !Ext.isEmpty(begDate) || !Ext.isEmpty(endDate) ) {
				if ( Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) && Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) ) {
					return true;
				}

				if ( !Ext.isEmpty(begDate) && Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= begDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= begDate)
					);
				}
				else if ( Ext.isEmpty(begDate) && !Ext.isEmpty(endDate) ) {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || rec.get('LpuSectionProfile_begDT') <= endDate)
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || rec.get('LpuSectionProfile_endDT') >= endDate)
					);
				}
				else {
					return (
						(Ext.isEmpty(rec.get('LpuSectionProfile_begDT')) || (rec.get('LpuSectionProfile_begDT') <= endDate && rec.get('LpuSectionProfile_begDT') <= begDate))
						&& (Ext.isEmpty(rec.get('LpuSectionProfile_endDT')) || (rec.get('LpuSectionProfile_endDT') >= endDate && rec.get('LpuSectionProfile_endDT') >= begDate))
					);
				}
			}

			return true;
		}.createDelegate(this));
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('UslugaComplexProfileEditWindow');

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
	parentClass: null,
	plain: true,
	resizable: false,
	show: function() {
		sw.Promed.swUslugaComplexProfileEditWindow.superclass.show.apply(this, arguments);

		this.center();

		var base_form = this.FormPanel.getForm();
		base_form.reset();

		this.center();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.checkUslugaSectionProfileDates = Ext.emptyFn;
		this.formMode = 'local';
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;

		//var deniedLpuSectionProfileList = [];
		
		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		base_form.setValues(arguments[0].formParams);

		if ( arguments[0].action ) {
			this.action = arguments[0].action;
		}
		
		/*if ( arguments[0].deniedLpuSectionProfileList ) {
			deniedLpuSectionProfileList = arguments[0].deniedLpuSectionProfileList;
		}*/

		if ( typeof arguments[0].callback == 'function' ) {
			this.callback = arguments[0].callback;
		}

		if ( typeof arguments[0].checkUslugaSectionProfileDates == 'function' ) {
			this.checkUslugaSectionProfileDates = arguments[0].checkUslugaSectionProfileDates;
		}

		if ( arguments[0].formMode && arguments[0].formMode == 'remote' ) {
			this.formMode = 'remote';
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		this.getLoadMask().show();

		base_form.findField('UslugaComplexProfile_begDate').setMaxValue(undefined);
		base_form.findField('UslugaComplexProfile_endDate').setMinValue(undefined);

		if ( base_form.findField('LpuSectionProfile_id').getStore().getCount() == 0 ) {
			var LpuSectionProfile_id = base_form.findField('LpuSectionProfile_id').getValue();

			base_form.findField('LpuSectionProfile_id').getStore().load({
				callback: function() {
					/*base_form.findField('LpuSectionProfile_id').getStore().filterBy(function(record) {
						return (!record.get('LpuSectionProfile_id').inlist(deniedLpuSectionProfileList));
					});*/

					if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
						var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
							return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
						});

						if ( index >= 0 ) {
							base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
						}
						else {
							base_form.findField('LpuSectionProfile_id').clearValue();
						}
					}
				}
			});
		}
		else {
			base_form.findField('LpuSectionProfile_id').getStore().clearFilter();
			base_form.findField('LpuSectionProfile_id').lastQuery = '';

			/*base_form.findField('LpuSectionProfile_id').getStore().filterBy(function(record) {
				return (!record.get('LpuSectionProfile_id').inlist(deniedLpuSectionProfileList));
			});*/

			if ( !Ext.isEmpty(LpuSectionProfile_id) ) {
				var index = base_form.findField('LpuSectionProfile_id').getStore().findBy(function(rec) {
					return (rec.get('LpuSectionProfile_id') == LpuSectionProfile_id);
				});

				if ( index >= 0 ) {
					base_form.findField('LpuSectionProfile_id').setValue(LpuSectionProfile_id);
				}
				else {
					base_form.findField('LpuSectionProfile_id').clearValue();
				}
			}
		}

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_USLUGA_PROFILE_ADD);
				this.enableEdit(true);
				this.getLoadMask().hide();
			break;

			case 'edit':
			case 'view':
				if ( this.action == 'edit' ) {
					this.setTitle(WND_USLUGA_PROFILE_EDIT);
					this.enableEdit(true);
				}
				else {
					this.setTitle(WND_USLUGA_PROFILE_VIEW);
					this.enableEdit(false);
				}

				base_form.findField('UslugaComplexProfile_begDate').fireEvent('change', base_form.findField('UslugaComplexProfile_begDate'), base_form.findField('UslugaComplexProfile_begDate').getValue());

				this.getLoadMask().hide();
			break;

			default:
				this.getLoadMask().hide();
				this.hide();
			break;
		}
		
		if ( !base_form.findField('LpuSectionProfile_id').disabled ) {
			base_form.findField('LpuSectionProfile_id').focus(true, 250);
		}
		else {
			this.buttons[this.buttons.length - 1].focus();
		}
	},
	width: 600
});

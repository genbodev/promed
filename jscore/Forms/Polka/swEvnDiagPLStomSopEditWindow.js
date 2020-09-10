/**
* swEvnDiagPLStomSopEditWindow - окно редактирования/добавления сопутствующего стоматологического диагноза.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      13.02.2013
* @comment      Префикс для id компонентов EDPLStomSopEF (EvnDiagPLStomSopEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              EvnDiagPLStomSop_pid - ID родительского события
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/

sw.Promed.swEvnDiagPLStomSopEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function(options) {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
			return false;
		}

		this.formStatus = 'save';
		options = options||{};

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

		var data = new Object();

		var desease_type_id = base_form.findField('DeseaseType_id').getValue();
		var diag_code = '';
		var diag_id = base_form.findField('Diag_id').getValue();
		var diag_name = '';
		var evn_diag_pl_stom_sop_set_date = base_form.findField('EvnDiagPLStomSop_setDate').getValue();
		var record = null;

		record = base_form.findField('Diag_id').getStore().getById(diag_id);
		if ( record ) {
			diag_code = record.get('Diag_Code');
			diag_name = record.get('Diag_Name');

			if ( diag_code.substr(0, 1).toUpperCase() != 'Z' && !desease_type_id ) {
				sw.swMsg.show({
					buttons: Ext.Msg.OK,
					fn: function() {
						this.formStatus = 'edit';
						base_form.findField('DeseaseType_id').focus(false);
					}.createDelegate(this),
					icon: Ext.Msg.WARNING,
					msg: lang['ne_zadan_harakter_zabolevaniya'],
					title: ERR_INVFIELDS_TIT
				});
				return false;
			}
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
			params: {
				'EvnDiagPLStomSop_setDate': Ext.util.Format.date(evn_diag_pl_stom_sop_set_date, 'd.m.Y'),
                ToothSurfaceType_id_list: base_form.findField('ToothSurfaceType_id_list').getValue(),
				ignoreCheckMorbusOnko: (options && !Ext.isEmpty(options.ignoreCheckMorbusOnko) && options.ignoreCheckMorbusOnko === 1) ? 1 : 0
			},
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnDiagPLStomSop_id > 0 ) {
					base_form.findField('EvnDiagPLStomSop_id').setValue(action.result.EvnDiagPLStomSop_id);

					var data = new Object();

					var desease_type_name = '';
					var lpu_section_name = '';

					record = base_form.findField('DeseaseType_id').getStore().getById(desease_type_id);
					if ( record ) {
						desease_type_name = record.get('DeseaseType_Name');
					}

					record = base_form.findField('Diag_id').getStore().getById(diag_id);
					if ( record ) {
						diag_code = record.get('Diag_Code');
						diag_name = record.get('Diag_Name');
					}

					data.evnDiagPLStomSopData = [{
						'accessType': 'edit',
						'EvnDiagPLStomSop_id': base_form.findField('EvnDiagPLStomSop_id').getValue(),
						'EvnDiagPLStomSop_pid': base_form.findField('EvnDiagPLStomSop_pid').getValue(),
						'Person_id': base_form.findField('Person_id').getValue(),
						'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
						'Server_id': base_form.findField('Server_id').getValue(),
						'DeseaseType_Name': desease_type_name,
						'EvnDiagPLStomSop_setDate': evn_diag_pl_stom_sop_set_date,
						'Diag_id': diag_id,
						'Diag_Code': diag_code,
						'Diag_Name': diag_name
					}];

					this.callback(data);
					this.hide();
				} 
				else if ( action.result.Alert_Msg && action.result.Error_Code > 0 ) {
						var msg = action.result.Alert_Msg;

                        sw.swMsg.show({
                            buttons: Ext.Msg.YESNO,
                            fn: function(buttonId, text, obj) {
                                if ( buttonId == 'yes' ) {
									switch (true) {
										case (289 == action.result.Error_Code):
											options.ignoreCheckMorbusOnko = 1;
											break;
									}
                                    this.doSave(options);
                                }
                            }.createDelegate(this),
                            icon: Ext.MessageBox.QUESTION,
                            msg: msg,
                            title: langs(' Продолжить сохранение?')
                        });
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

		if ( enable ) {
			base_form.findField('DeseaseType_id').enable();
			base_form.findField('Diag_id').enable();
            base_form.findField('Tooth_Code').enable();
            base_form.findField('ToothSurfaceType_id_list').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('DeseaseType_id').disable();
			base_form.findField('Diag_id').disable();
            base_form.findField('Tooth_Code').disable();
            base_form.findField('ToothSurfaceType_id_list').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnDiagPLStomSopEditWindow',
	initComponent: function() {
		var _this = this;
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'EvnDiagPLStomSopEditForm',
			labelAlign: 'right',
			labelWidth: 120,
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
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			layout: 'form',
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'accessType' },
				{ name: 'EvnDiagPLStomSop_id' },
				{ name: 'EvnDiagPLStomSop_pid' },
				{ name: 'LpuSection_id' },
				{ name: 'MedPersonal_id' },
				{ name: 'Person_id' },
				{ name: 'PersonEvn_id' },
				{ name: 'Server_id' },
				{ name: 'Diag_id' },
				{ name: 'DeseaseType_id' },
				{ name: 'EvnDiagPLStomSop_setDate' },
                { name: 'Tooth_Code' },
                { name: 'Tooth_id' },
                { name: 'ToothSurfaceType_id_list' }
			]),
			url: '/?c=EvnDiag&m=saveEvnDiagPLStomSop',

			items: [{
				name: 'accessType',
				value: '',
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStomSop_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'EvnDiagPLStomSop_pid',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'LpuSection_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'MedPersonal_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Person_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'PersonEvn_id',
				value: 0,
				xtype: 'hidden'
			}, {
				name: 'Server_id',
				value: -1,
				xtype: 'hidden'
			}, {
				disabled: true,
				fieldLabel: lang['data_ustanovki'],
				format: 'd.m.Y',
				name: 'EvnDiagPLStomSop_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_EDPLSTOMSOPEF + 1,
				width: 100,
				xtype: 'swdatefield',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanelid', 'EDPLStomSopEF_PersonInformationFrame', field, newValue, oldValue);
						_this.FormPanel().getForm().findField('Diag_id').setFilterByDate(base_form.findField('EvnDiagPLStomSop_setDate').getValue());
					}
				}
			}, {
				allowBlank: false,
				hiddenName: 'Diag_id',
				// Добавить listeners
				listWidth: 580,
				tabIndex: TABINDEX_EDPLSTOMSOPEF + 2,
				width: 480,
				xtype: 'swdiagcombo'
			}, {
				comboSubject: 'DeseaseType',
				fieldLabel: lang['harakter'],
				hiddenName: 'DeseaseType_id',
				lastQuery: '',
				tabIndex: TABINDEX_EDPLSTOMSOPEF + 3,
				width: 480,
				xtype: 'swcommonsprcombo'
			}, {
                name: 'Tooth_id',
                xtype: 'hidden'
            }, {
                tabIndex: TABINDEX_EDPLSTOMSOPEF + 4,
                listeners: {
                    change: function(field) {
                        var base_form = this.FormPanel.getForm();
                        field.applyChangeTo(this,
                            base_form.findField('Tooth_id'),
                            base_form.findField('ToothSurfaceType_id_list')
                        );
                    }.createDelegate(this)
                },
                name: 'Tooth_Code',
                xtype: 'swtoothfield'
            }, {
                name: 'ToothSurfaceType_id_list',
                xtype: 'swtoothsurfacetypecheckboxgroup'
			}]
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDPLStomSopEF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					if ( this.action != 'view' ) {
						this.doSave();
					}
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EDPLSTOMSOPEF + 5,
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
						this.FormPanel.getForm().findField('Diag_id').focus(true, 100);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EDPLSTOMSOPEF + 6,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEvnDiagPLStomSopEditWindow.superclass.initComponent.apply(this, arguments);
	},
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
		sw.Promed.swEvnDiagPLStomSopEditWindow.superclass.show.apply(this, arguments);

		var win = this;

		var base_form = this.FormPanel.getForm();

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

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnDiagPLStomSop_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EDPLStomSopEF_PersonInformationFrame', field);
			}
		});

		var diag_combo = base_form.findField('Diag_id'),
            tooth_field = base_form.findField('Tooth_Code'),
            ToothSurface_group = base_form.findField('ToothSurfaceType_id_list');

		diag_combo.filterDate = null;
		base_form.findField('Diag_id').setFilterByDate(base_form.findField('EvnDiagPLStomSop_setDate').getValue());
		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EDPLADD);
				this.enableEdit(true);

				loadMask.hide();

				base_form.clearInvalid();
                tooth_field.setValue(null);
                tooth_field.fireEvent('change', tooth_field, null);

				diag_combo.focus(false, 250);
			break;

			case 'edit':
			case 'view':
				var evn_diag_pl_stom_sop_id = base_form.findField('EvnDiagPLStomSop_id').getValue();

				if ( !evn_diag_pl_stom_sop_id ) {
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
						EvnDiagPLStomSop_id: evn_diag_pl_stom_sop_id,
						archiveRecord: win.archiveRecord
					},
					success: function() {
						// В зависимости от accessType переопределяем this.action
						if ( base_form.findField('accessType').getValue() == 'view' ) {
							this.action = 'view';
						}

						if ( this.action == 'edit' ) {
							this.setTitle(WND_POL_EDPLEDIT);
							this.enableEdit(true);
						}
						else {
							this.setTitle(WND_POL_EDPLVIEW);
							this.enableEdit(false);
						}

						var diag_id = diag_combo.getValue();

						if ( diag_id != null && diag_id.toString().length > 0 ) {
							diag_combo.getStore().load({
								callback: function() {
									diag_combo.getStore().each(function(record) {
										if ( record.get('Diag_id') == diag_id ) {
											diag_combo.fireEvent('select', diag_combo, record, 0);
										}
									});
									log(base_form.findField('EvnDiagPLStomSop_setDate').getValue());
									diag_combo.setFilterByDate(base_form.findField('EvnDiagPLStomSop_setDate').getValue());
								},
								params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
							});
						}

						loadMask.hide();

                        var tooth_code = tooth_field.getValue(),
                            ToothSurfaceType_id_list = ToothSurface_group.getValue();
                        if (!tooth_field.hasCode(tooth_code)) {
                            tooth_code = null;
                            tooth_field.setValue(tooth_code);
                        }
                        tooth_field.fireEvent('change', tooth_field, tooth_code);
                        ToothSurface_group.setValue(ToothSurfaceType_id_list);

						base_form.clearInvalid();

						if ( this.action == 'edit' ) {
							diag_combo.focus(false, 250);
						}
						else {
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this),
					url: '/?c=EvnDiag&m=loadEvnDiagPLStomSopEditForm'
				});
			break;

			default:
				loadMask.hide();
				this.hide();
			break;
		}
	},
	width: 650
});
/**
* swEvnDiagPLEditWindow - окно редактирования/добавления диагноза.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage@swan.perm.ru)
* @version      0.002-17.12.2009
* @comment      Префикс для id компонентов EDPLEF (EvnDiagPLEditForm)
*
*
* @input data: action - действие (add, edit, view)
*              Person_id - ID человека
*              PersonEvn_id - ID состояния человека
*              Server_id - ID сервера
*/

sw.Promed.swEvnDiagPLEditWindow = Ext.extend(sw.Promed.BaseForm, {
	action: null,
	autoHeight: true,
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	doSave: function() {
		if ( this.formStatus == 'save' || this.action == 'view' ) {
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

		var data = new Object();

		var desease_type_id = base_form.findField('DeseaseType_id').getValue();
		var diag_code = '';
		var diag_id = base_form.findField('Diag_id').getValue();
		var evn_diag_pl_set_date = base_form.findField('EvnDiagPL_setDate').getValue();
		var lpu_section_id = base_form.findField('LpuSection_id').getValue();
		var med_personal_fio = '';
		var med_personal_id = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var record = null;

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_fio = record.get('MedPersonal_Fio');
			med_personal_id = record.get('MedPersonal_id');
		}

		record = base_form.findField('Diag_id').getStore().getById(diag_id);
		if ( record ) {
			diag_code = record.get('Diag_Code');

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
				'EvnDiagPL_setDate': Ext.util.Format.date(evn_diag_pl_set_date, 'd.m.Y'),
				'LpuSection_id': lpu_section_id,
				'MedPersonal_id': med_personal_id
			},
			success: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result && action.result.EvnDiagPL_id > 0 ) {
					base_form.findField('EvnDiagPL_id').setValue(action.result.EvnDiagPL_id);

					var data = new Object();

					var desease_type_id = base_form.findField('DeseaseType_id').getValue();
					var desease_type_name = '';
					var diag_id = base_form.findField('Diag_id').getValue();
					var diag_code = '';
					var diag_name = '';
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

					record = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id);
					if ( record ) {
						lpu_section_name = record.get('LpuSection_Name');
					}

					data.evnDiagPLData = [{
						'accessType': 'edit',
						'EvnDiagPL_id': base_form.findField('EvnDiagPL_id').getValue(),
						'EvnVizitPL_id': base_form.findField('EvnVizitPL_id').getValue(),
						'Person_id': base_form.findField('Person_id').getValue(),
						'PersonEvn_id': base_form.findField('PersonEvn_id').getValue(),
						'Server_id': base_form.findField('Server_id').getValue(),
						'DeseaseType_id': desease_type_id,
						'DeseaseType_Name': desease_type_name,
						'Diag_id': diag_id,
						'LpuSection_id': lpu_section_id,
						'MedPersonal_id': med_personal_id,
						'EvnDiagPL_setDate': evn_diag_pl_set_date,
						'LpuSection_Name': lpu_section_name,
						'MedPersonal_Fio': med_personal_fio,
						'Diag_Code': diag_code,
						'Diag_Name': diag_name
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
		var base_form = this.FormPanel.getForm();

		if ( enable ) {
			base_form.findField('EvnVizitPL_id').enable();
			base_form.findField('Diag_id').enable();
			base_form.findField('DeseaseType_id').enable();
			this.buttons[0].show();
		}
		else {
			base_form.findField('EvnVizitPL_id').disable();
			base_form.findField('Diag_id').disable();
			base_form.findField('DeseaseType_id').disable();
			this.buttons[0].hide();
		}
	},
	formStatus: 'edit',
	id: 'EvnDiagPLEditWindow',
	initComponent: function() {
		var _this = this,
			win = this;
		this.FormPanel = new Ext.form.FormPanel({
			bodyBorder: false,
			bodyStyle: 'padding: 5px 5px 0',
			border: false,
			buttonAlign: 'left',
			frame: false,
			id: 'EvnDiagPLEditForm',
			labelAlign: 'right',
			labelWidth: 120,
			items: [{
				name: 'EvnDiagPL_id',
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
				allowBlank: false,
				displayField: 'EvnVizitPL_Name',
				editable: false,
				enableKeyEvents: true,
				fieldLabel: lang['poseschenie'],
				hiddenName: 'EvnVizitPL_id',
				listeners: {
					'change': function(combo, newValue, oldValue) {
						var base_form = this.FormPanel.getForm();
						var record = combo.getStore().getById(newValue);

						if ( record ) {
							var evn_vizit_pl_set_date = record.get('EvnVizitPL_setDate');
							var lpu_section_id = record.get('LpuSection_id');
							var lpu_section_pid;
							var med_personal_id = record.get('MedPersonal_id');

							setLpuSectionGlobalStoreFilter({
								allowLowLevel: 'yes',
								isPolka: true,
								onDate: Ext.util.Format.date(evn_vizit_pl_set_date, 'd.m.Y')
							});

							setMedStaffFactGlobalStoreFilter({
								allowLowLevel: 'yes',
								isPolka: true,
								onDate: Ext.util.Format.date(evn_vizit_pl_set_date, 'd.m.Y')
							});

							base_form.findField('EvnDiagPL_setDate').setValue(evn_vizit_pl_set_date);
							base_form.findField('DeseaseType_id').getStore().clearFilter();
							base_form.findField('DeseaseType_id').getStore().filterBy(function(rec) {
								return (
									(Ext.isEmpty(rec.get('DeseaseType_begDT'))  || rec.get('DeseaseType_begDT') <= evn_vizit_pl_set_date)
									&& (Ext.isEmpty(rec.get('DeseaseType_endDT')) || rec.get('DeseaseType_endDT') >= evn_vizit_pl_set_date)
								);
							});

							if ( base_form.findField('LpuSection_id').getStore().getById(lpu_section_id) ) {
								base_form.findField('LpuSection_id').setValue(lpu_section_id);
								lpu_section_pid = base_form.findField('LpuSection_id').getStore().getById(lpu_section_id).get('LpuSection_pid');
							}

							base_form.findField('MedStaffFact_id').getStore().each(function(rec) {
								if ( !Ext.isEmpty(rec.get('LpuSection_id')) && rec.get('LpuSection_id').inlist([ lpu_section_id, lpu_section_pid ]) && rec.get('MedPersonal_id') == med_personal_id ) {
									base_form.findField('MedStaffFact_id').setValue(rec.get('MedStaffFact_id'));
								}
							});
						}
					}.createDelegate(this),
					'keydown': function (inp, e) {
						if ( e.shiftKey == true && e.getKey() == Ext.EventObject.TAB ) {
							e.stopEvent();
							this.buttons[this.buttons.length - 1].focus();
						}
					}.createDelegate(this)
				},
				listWidth: 580,
				mode: 'local',
				store: new Ext.data.JsonStore({
					autoLoad: false,
					fields: [
						{ name: 'EvnVizitPL_id', type: 'int' },
						{ name: 'LpuSection_id', type: 'int' },
						{ name: 'MedPersonal_id', type: 'int' },
						{ name: 'EvnVizitPL_Name', type: 'string' },
						{ name: 'EvnVizitPL_setDate', type: 'date', format: 'd.m.Y' }
					],
					id: 'EvnVizitPL_id'
				}),
				tabIndex: TABINDEX_EDPLEF + 1,
				tpl: new Ext.XTemplate(
					'<tpl for="."><div class="x-combo-list-item">',
					'{EvnVizitPL_Name}',
					'</div></tpl>'
				),
				triggerAction: 'all',
				valueField: 'EvnVizitPL_id',
				width: 480,
				xtype: 'combo'
			}, {
				disabled: true,
				fieldLabel: lang['data_ustanovki'],
				format: 'd.m.Y',
				name: 'EvnDiagPL_setDate',
				plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
				tabIndex: TABINDEX_EDPLEF + 2,
				width: 100,
				xtype: 'swdatefield',
				listeners: {
					'change': function(field, newValue, oldValue) {
						blockedDateAfterPersonDeath('personpanelid', 'EDPLEF_PersonInformationFrame', field, newValue, oldValue);
						_this.FormPanel.getForm().findField('Diag_id').setFilterByDate(newValue);
					}
				}
			}, {
				disabled: true,
				hiddenName: 'LpuSection_id',
				id: 'EDPLEF_LpuSectionCombo',
				lastQuery: '',
				linkedElements: [
					'EDPLEF_MedStaffFactCombo'
				],
				tabIndex: TABINDEX_EDPLEF + 3,
				width: 480,
				xtype: 'swlpusectionglobalcombo'
			}, {
				disabled: true,
				hiddenName: 'MedStaffFact_id',
				id: 'EDPLEF_MedStaffFactCombo',
				lastQuery: '',
				parentElementId: 'EDPLEF_LpuSectionCombo',
				tabIndex: TABINDEX_EDPLEF + 4,
				width: 480,
				xtype: 'swmedstafffactglobalcombo'
			}, {
				allowBlank: false,
				checkAccessRights: true,
				hiddenName: 'Diag_id',
				listWidth: 580,
				tabIndex: TABINDEX_EDPLEF + 5,
				width: 480,
				xtype: 'swdiagcombo'
			}, {
				comboSubject: 'DeseaseType',
				fieldLabel: lang['harakter'],
				hiddenName: 'DeseaseType_id',
				lastQuery: '',
				tabIndex: TABINDEX_EDPLEF + 6,
				width: 480,
				xtype: 'swcommonsprcombo',
				moreFields: [
					{name: 'DeseaseType_begDT', type: 'date', format: 'd.m.Y'},
					{name: 'DeseaseType_endDT', type: 'date', format: 'd.m.Y'}
				],
				baseFilterFn: function(rec) {
					
					// var vizitData = form.getObjectData('EvnVizitPL', el_data.object_id);
					// var vizitDate = new Date(vizitData.EvnVizitPL_setDate120);
					// return (
					// 	(!rec.get('DeseaseType_begDT')  || rec.get('DeseaseType_begDT') <= vizitDate)
					// 	&& (!rec.get('DeseaseType_endDT') || rec.get('DeseaseType_endDT') >= vizitDate)
					// );
				}
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
				},
				key: [ Ext.EventObject.C, Ext.EventObject.J ],
				scope: this,
				stopEvent: true
			}],
			reader: new Ext.data.JsonReader({
				success: function() { }
			}, [
				{ name: 'accessType' },
				{ name: 'EvnDiagPL_id' }
			]),
			url: '/?c=EvnDiag&m=saveEvnDiagPL'
		});

		this.PersonInfo = new sw.Promed.PersonInformationPanelShort({
			id: 'EDPLEF_PersonInformationFrame'
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				tabIndex: TABINDEX_EDPLEF + 7,
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
						this.FormPanel.getForm().findField('EvnVizitPL_id').focus(true, 100);
					}
				}.createDelegate(this),
				tabIndex: TABINDEX_EDPLEF + 8,
				text: BTN_FRMCANCEL
			}],
			items: [
				this.PersonInfo,
				this.FormPanel
			]
		});

		sw.Promed.swEvnDiagPLEditWindow.superclass.initComponent.apply(this, arguments);
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
	loadEvnVizitPLCombo: function() {
		var win = this;
		var base_form = this.FormPanel.getForm();
		var evn_vizit_pl_combo = base_form.findField('EvnVizitPL_id');
		if (this.EvnPL_id && evn_vizit_pl_combo.getStore().getCount() == 0) {
			win.getLoadMask('Загрузка списка посещений').show();
			Ext.Ajax.request({
				url: '/?c=EvnPL&m=loadEvnVizitPLCombo',
				params: {
					EvnPL_id: win.EvnPL_id
				},
				callback: function(options, success, response) {
					win.getLoadMask().hide();

					if (response && response.responseText) {
						var response_obj = Ext.util.JSON.decode(response.responseText);
						if (response_obj.success && response_obj.vizitComboData) {
							evn_vizit_pl_combo.getStore().loadData(response_obj.vizitComboData);
							if ( evn_vizit_pl_combo.getStore().getCount() == 1 ) {
								evn_vizit_pl_combo.setValue(evn_vizit_pl_combo.getStore().getAt(0).get('EvnVizitPL_id'));
								evn_vizit_pl_combo.fireEvent('change', evn_vizit_pl_combo, evn_vizit_pl_combo.getStore().getAt(0).get('EvnVizitPL_id'), 0);
							}
						}
					}
				}
			});
		}
	},
	show: function() {
		sw.Promed.swEvnDiagPLEditWindow.superclass.show.apply(this, arguments);

		var base_form = this.FormPanel.getForm();

		this.center();
		base_form.reset();

		this.action = null;
		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.onHide = Ext.emptyFn;
		this.EvnPL_id = null;

		base_form.findField('EvnVizitPL_id').getStore().removeAll();

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

		if ( arguments[0].EvnPL_id ) {
			this.EvnPL_id = arguments[0].EvnPL_id;
		}

		if ( arguments[0].vizitComboData ) {
			base_form.findField('EvnVizitPL_id').getStore().loadData(arguments[0].vizitComboData);
		}

		var diag_combo = base_form.findField('Diag_id');
		diag_combo.filterDate = null;
		var evn_vizit_pl_combo = base_form.findField('EvnVizitPL_id');
		var lpu_section_combo = base_form.findField('LpuSection_id');
		var med_personal_combo = base_form.findField('MedStaffFact_id');

		this.PersonInfo.load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnDiagPL_setDate');
				diag_combo.setFilterByDate(field.getValue());
				clearDateAfterPersonDeath('personpanelid', 'EDPLEF_PersonInformationFrame', field);
			}
		});


		swLpuSectionGlobalStore.clearFilter();

		swMedStaffFactGlobalStore.clearFilter();
		swMedStaffFactGlobalStore.filterBy(function(record) {
			if ( true )
				return true;
			else
				return false;
		});

		lpu_section_combo.getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
		med_personal_combo.getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT });
		loadMask.show();

		evn_vizit_pl_combo.clearValue();

		switch ( this.action ) {
			case 'add':
				this.setTitle(WND_POL_EDPLSOPADD);
				this.enableEdit(true);

				if ( evn_vizit_pl_combo.getStore().getCount() == 1 ) {
					evn_vizit_pl_combo.setValue(evn_vizit_pl_combo.getStore().getAt(0).get('EvnVizitPL_id'));
					evn_vizit_pl_combo.fireEvent('change', evn_vizit_pl_combo, evn_vizit_pl_combo.getStore().getAt(0).get('EvnVizitPL_id'), 0);
				}

				diag_combo.focus(false, 250);

				loadMask.hide();

				this.loadEvnVizitPLCombo();
			break;

			case 'edit':
				this.setTitle(WND_POL_EDPLSOPEDIT);
				this.enableEdit(true);

				var evn_vizit_pl_id = arguments[0].formParams.EvnVizitPL_id;
				if ( evn_vizit_pl_id != null && evn_vizit_pl_id.toString().length > 0 ) {
					evn_vizit_pl_combo.setValue(evn_vizit_pl_id);
					evn_vizit_pl_combo.fireEvent('change', evn_vizit_pl_combo, evn_vizit_pl_id, evn_vizit_pl_id + 1);
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
							diag_combo.setFilterByDate(base_form.findField('EvnDiagPL_setDate').getValue());
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}

				diag_combo.focus(false, 250);

				loadMask.hide();

				this.loadEvnVizitPLCombo();
			break;

			case 'view':
				this.setTitle(WND_POL_EDPLSOPVIEW);
				this.enableEdit(false);

				var evn_vizit_pl_id = arguments[0].formParams.EvnVizitPL_id;
				if ( evn_vizit_pl_id != null && evn_vizit_pl_id.toString().length > 0 ) {
					evn_vizit_pl_combo.setValue(evn_vizit_pl_id);
					evn_vizit_pl_combo.fireEvent('change', evn_vizit_pl_combo, evn_vizit_pl_id, evn_vizit_pl_id + 1);
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
						},
						params: { where: "where DiagLevel_id = 4 and Diag_id = " + diag_id }
					});
				}

				loadMask.hide();

				this.loadEvnVizitPLCombo();

				this.buttons[this.buttons.length - 1].focus();
			break;
		}
	},
	width: 650
});
/**
* swEvnUslugaStomFastInputWindow - окно быстрого ввода стоматологических услуг.
*
* PromedWeb - The New Generation of Medical Statistic Software
* http://swan.perm.ru/PromedWeb
*
*
* @package      Polka
* @access       public
* @copyright    Copyright (c) 2009 Swan Ltd.
* @author       Stas Bykov aka Savage (savage1981@gmail.com)
* @version      0.001-02.02.2010
* @comment      Префикс для id компонентов EUStomFIF (EvnUslugaStomFastInputForm)
*/
/*NO PARSE JSON*/

sw.Promed.swEvnUslugaStomFastInputWindow = Ext.extend(sw.Promed.BaseForm, {
	codeRefresh: true,
	objectName: 'swEvnUslugaStomFastInputWindow',
	objectSrc: '/jscore/Forms/Polka/swEvnUslugaStomFastInputWindow.js',

	addUslugaToList: function() {
		var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

		var grid = this.UslugaListPanel.getGrid();
		var usluga_complex_combo = base_form.findField('UslugaComplex_id');

		var usluga_complex_id = usluga_complex_combo.getValue();

		var index = usluga_complex_combo.getStore().findBy(function(rec) {
			if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		var usluga_complex_record = usluga_complex_combo.getStore().getAt(index);

		if ( !usluga_complex_record ) {
			return false;
		}

		index = grid.getStore().findBy(function(rec) {
			if ( rec.get('UslugaComplex_id') == usluga_complex_id ) {
				return true;
			}
			else {
				return false;
			}
		});

		var grid_record = grid.getStore().getAt(index);

		if ( !grid_record ) {
			if ( grid.getStore().getCount() == 1 && !grid.getStore().getAt(0).get('UslugaComplex_id') ) {
				grid.getStore().removeAll();
			}

			grid.getStore().loadData([{
				'UslugaComplex_id': usluga_complex_record.get('UslugaComplex_id'),
				'UslugaAddFlag': 'on',
				'Usluga_Code': usluga_complex_record.get('UslugaComplex_Code'),
				'Usluga_Name': usluga_complex_record.get('UslugaComplex_Name'),
				'PayType_id': this.payTypeId,
				'UslugaComplex_UET': usluga_complex_record.get('UslugaComplex_UET'),
				'EvnUslugaStom_Kolvo': 1,
				'EvnUslugaStom_Summa': usluga_complex_record.get('UslugaComplex_UET')
			}], true);
		}
		else {
			grid_record.set('EvnUslugaStom_Kolvo', grid_record.get('EvnUslugaStom_Kolvo') + 1);
			grid_record.set('EvnUslugaStom_Summa', grid_record.get('EvnUslugaStom_Summa') + (grid_record.get('UslugaComplex_UET') ? grid_record.get('UslugaComplex_UET') : 0));

			grid_record.commit();
		}

		usluga_complex_combo.focus(true);
	},
	buttonAlign: 'left',
	callback: Ext.emptyFn,
	closable: true,
	closeAction: 'hide',
	collapsible: false,
	deleteUslugaComplex: function() {
		var grid = this.UslugaListPanel.getGrid();

		if ( !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
			return false;
		}

		var record = grid.getSelectionModel().getSelected();

		grid.getStore().remove(record);

		if ( grid.getStore().getCount() == 0 ) {
			this.UslugaListPanel.addEmptyRecord(grid.getStore());
		}

		this.UslugaListPanel.focus();
	},
	doSave: function() {
		if ( this.formStatus == 'save' ) {
			return false;
		}

		this.formStatus = 'save';

		var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

		if ( !base_form.isValid() ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					base_form.findField('LpuSection_uid').focus(true, 100);
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: ERR_INVFIELDS_MSG,
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var uslugaComplexData = new Array();
		var uslugaComplexRecords = getStoreRecords(this.UslugaListPanel.getGrid().getStore(), {
			exceptionFields: [ 'Usluga_Code', 'Usluga_Name', 'PayType_Name' ]
		});

		// Получаем записи из грида
		for ( var i = 0; i < uslugaComplexRecords.length; i++ ) {
			if ( uslugaComplexRecords[i]['EvnUslugaStom_Summa'] > 0 ) {
				uslugaComplexData.push(uslugaComplexRecords[i]);
			}
		}

		// Если количество записей с ненулевой суммой равно нулю, то выдать сообщение об ошибке и прервать выполнение процедуры
		if ( uslugaComplexData.length == 0 ) {
			sw.swMsg.show({
				buttons: Ext.Msg.OK,
				fn: function() {
					this.formStatus = 'edit';
					this.UslugaListPanel.focus();
				}.createDelegate(this),
				icon: Ext.Msg.WARNING,
				msg: lang['ne_zadana_ni_odna_usluga_s_ukazannyim_kolichestvom_i_tsenoy'],
				title: ERR_INVFIELDS_TIT
			});
			return false;
		}

		var params = new Object();
		var med_personal_id = null;
		var med_personal_sid = null;
		var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
		var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();
		var record = null;

		record = base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id);
		if ( record ) {
			med_personal_id = record.get('MedPersonal_id');
		}

		record = base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid);
		if ( record ) {
			med_personal_sid = record.get('MedPersonal_id');
		}

		params.EvnUslugaStom_setDate = Ext.util.Format.date(base_form.findField('EvnUslugaStom_setDate').getValue(), 'd.m.Y');
		params.MedPersonal_id = med_personal_id;
		params.MedPersonal_sid = med_personal_sid;
		params.UslugaPlace_id = base_form.findField('UslugaPlace_id').getValue();
		params.uslugaComplexData = Ext.util.JSON.encode(uslugaComplexData);

		var loadMask = new Ext.LoadMask(this.getEl(), { msg: LOAD_WAIT_SAVE });
		loadMask.show();

		base_form.submit({
			failure: function(result_form, action) {
				this.formStatus = 'edit';
				loadMask.hide();

				if ( action.result ) {
					if ( action.result.Error_Msg ) {
						this.callback();
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
					if ( action.result.Error_Msg.length > 0 ) {
						sw.swMsg.alert(lang['oshibka'], action.result.Error_Msg);
					}
					else {
						// sw.swMsg.alert('Успех', 'Услуги были успешно сохранены', function() {
							this.callback();
							this.hide();
						// }.createDelegate(this) );
					}
				}
				else {
					sw.swMsg.alert(lang['oshibka'], lang['pri_sohranenii_proizoshli_oshibki_[tip_oshibki_2]']);
				}
			}.createDelegate(this)
		});
	},
	draggable: true,
	formStatus: 'edit',
	height: 550,
	id: 'EvnUslugaStomFastInputWindow',
	initComponent: function() {
		this.UslugaListPanel = new sw.Promed.ViewFrame({
			actions: [
				{ name: 'action_add', disabled: true },
				{ name: 'action_edit', disabled: true },
				{ name: 'action_view', disabled: true },
				{ name: 'action_delete', handler: function() { this.deleteUslugaComplex(); }.createDelegate(this) },
				{ name: 'action_refresh', disabled: true },
				{ name: 'action_save', disabled: true }
			],
			autoLoadData: false,
			dataUrl: '/?c=EvnUsluga&m=loadUslugaComplexList',
			editformclassname: '',
			height: 200,
			id: 'EUStomFIF_UslugaViewPanel',
			maxSize: 300,
			minSize: 200,
			object: 'EvnUslugaStom',
			onAfterEditSelf: function(o) {
				var field = o.grid.getColumnModel().getCellEditor(o.column, o.row).field;

				o.grid.stopEditing(true);

				if ( o.column == 5 ) {
					var index = field.getStore().findBy(function(rec) { return rec.get(field.valueField) == o.value; });

					if ( index >= 0 ) {
						o.record.set('PayType_id', o.value);
						o.record.set('PayType_Name', field.getStore().getAt(index).get(field.displayField));
					}
				}
				else {
					o.record.set('EvnUslugaStom_Summa', Number(o.record.get('UslugaComplex_UET') * o.record.get('EvnUslugaStom_Kolvo')).toFixed(2));
				}

				o.record.commit();
			},
			onLoadData: function (result) {
				var grid = this.UslugaListPanel.getGrid();

				grid.getStore().each(function(rec) {
					if ( rec.get('UslugaComplex_id') ) {
						var field = grid.getColumnModel().getCellEditor(5, grid.getStore().indexOf(rec)).field;

						var index = field.getStore().findBy(function(r) {
							if ( r.get(field.valueField) == this.payTypeId ) {
								return true;
							}
							else {
								return false;
							}
						}.createDelegate(this));

						if ( index >= 0 ) {
							rec.set('PayType_id', this.payTypeId);
							rec.set('PayType_Name', field.getStore().getAt(index).get(field.displayField));
							rec.commit();
						}
					}
				}.createDelegate(this));
			}.createDelegate(this),
			onRowSelect: function (sm, index, record) {
				//
			},
			region: 'center',
			saveAllParams: true,
			saveAtOnce: false,
			stringfields: [
				{ name: 'UslugaComplex_id', type: 'int', header: 'ID', key: true },
				{ name: 'PayType_id', hidden: true, isparams: true },
				{ name: 'UslugaAddFlag', header: lang['dobavit'], width: 80, align: 'center', editor: new Ext.form.Checkbox({
					listeners: {
						'check': function(checkbox, value) {
							var grid = this.UslugaListPanel.getGrid();

							if ( !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
								return false;
							}

							var selected_record = grid.getSelectionModel().getSelected();

							if ( value == true ) {
								selected_record.set('EvnUslugaStom_Kolvo', 1);
							}
							else {
								selected_record.set('EvnUslugaStom_Kolvo', 0);
							}

							selected_record.commit();
						}.createDelegate(this)
					}
				}), type: 'checkcolumn' },
				{ name: 'Usluga_Code', header: lang['kod_uslugi'], width: 80},
				{ name: 'Usluga_Name', header: lang['usluga'], id: 'autoexpand' },
				{ name: 'PayType_Name', header: lang['vid_oplatyi'], width: 250, type: 'string', editor: new sw.Promed.SwPayTypeCombo({
					allowBlank: false,
					displayField: 'PayType_Name',
					useCommonFilter: true
				}) },
				{ name: 'UslugaComplex_UET', type: 'money', header: lang['tsena_uet'], width: 80, align: 'right', editor: new Ext.form.NumberField({
					allowBlank: false,
					allowNegative: false,
					listeners: {
						'change': function(field, newValue, oldValue) {
							var grid = this.UslugaListPanel.getGrid();

							if ( !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
								return false;
							}

							var selected_record = grid.getSelectionModel().getSelected();

							if ( newValue && Number(newValue) > 0 ) {
								selected_record.set('UslugaAddFlag', true);
								selected_record.set('EvnUslugaStom_Summa', (selected_record.get('EvnUslugaStom_Kolvo') ? selected_record.get('EvnUslugaStom_Kolvo') : 0) * newValue);
							}
							else {
								selected_record.set('UslugaAddFlag', false);
								selected_record.set('EvnUslugaStom_Summa', 0);
							}

							selected_record.commit();
						}.createDelegate(this)
					},
					minValue: 0.01
				}) },
					{ name: 'EvnUslugaStom_Kolvo', type: 'int', header: lang['kolichestvo'], width: 80, align: 'right', editor: new Ext.form.NumberField({
					allowBlank: false,
					allowNegative: false,
					listeners: {
						'change': function(field, newValue, oldValue) {
							var grid = this.UslugaListPanel.getGrid();

							if ( !grid.getSelectionModel() || !grid.getSelectionModel().getSelected() || !grid.getSelectionModel().getSelected().get('UslugaComplex_id') ) {
								return false;
							}

							var selected_record = grid.getSelectionModel().getSelected();

							if ( newValue && Number(newValue) > 0 ) {
								selected_record.set('UslugaAddFlag', 'true');
								selected_record.set('EvnUslugaStom_Summa', newValue * (selected_record.get('UslugaComplex_UET') ? selected_record.get('UslugaComplex_UET') : 0));
							}
							else {
								selected_record.set('UslugaAddFlag', false);
								selected_record.set('EvnUslugaStom_Summa', 0);
							}

							selected_record.commit();
						}.createDelegate(this)
					},
					minValue: 1
				}) },
				{ name: 'EvnUslugaStom_Summa', type: 'money', header: lang['summa_uet'], width: 80, align: 'right' }
			],
			title: lang['uslugi'],
			toolbar: true
		});

		Ext.apply(this, {
			buttons: [{
				handler: function() {
					this.doSave();
				}.createDelegate(this),
				iconCls: 'save16',
				id: 'EUStomFIF_SaveButton',
				onShiftTabAction: function () {
					this.findById('EvnUslugaStomFastInputForm').getForm().findField('UslugaComplex_id').focus(true);
				}.createDelegate(this),
				onTabAction: function () {
					this.buttons[this.buttons.length - 1].focus();
				}.createDelegate(this),
				tabIndex: TABINDEX_EUStomFIF + 10,
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
					this.buttons[0].focus();
				}.createDelegate(this),
				onTabAction: function () {
					this.findById('EvnUslugaStomFastInputForm').getForm().findField('LpuSection_uid').focus(true);
				}.createDelegate(this),
				tabIndex: TABINDEX_EUStomFIF + 11,
				text: BTN_FRMCANCEL
			}],
			items: [ new Ext.Panel({
				height: 280,
				items: [ new sw.Promed.PersonInformationPanelShort({
					id: 'EUStomFIF_PersonInformationFrame'
				}),
				new Ext.form.FormPanel({
					autoScroll: true,
					bodyBorder: false,
					bodyStyle: 'padding: 5px 5px 0',
					border: false,
					frame: false,
					id: 'EvnUslugaStomFastInputForm',
					labelAlign: 'right',
					labelWidth: 130,
					layout: 'form',
					reader: new Ext.data.JsonReader({
						success: Ext.emptyFn
					}, [
						{ name: 'EvnUslugaStom_id' }
					]),
					region: 'center',
					url: '/?c=EvnUsluga&m=saveEvnUslugaStomFast',
					items: [{
						name: 'EvnUslugaStom_pid',
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
						disabled: true,
						fieldLabel: lang['data_vyipolneniya'],
						format: 'd.m.Y',
						listeners: {
							'change': function(field, newValue, oldValue) {
								if (blockedDateAfterPersonDeath('personpanelid', 'EUStomFIF_PersonInformationFrame', field, newValue, oldValue)) return;
							
								var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

								var lpu_section_id = base_form.findField('LpuSection_uid').getValue();
								var med_staff_fact_id = base_form.findField('MedStaffFact_id').getValue();
								var med_staff_fact_sid = base_form.findField('MedStaffFact_sid').getValue();

								base_form.findField('LpuSection_uid').clearValue();
								base_form.findField('MedStaffFact_id').clearValue();
								base_form.findField('MedStaffFact_sid').clearValue();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
								base_form.findField('UslugaComplex_id').getStore().baseParams.UslugaComplex_Date = (typeof newValue == 'object' ? Ext.util.Format.date(newValue, 'd.m.Y') : newValue);
								base_form.findField('UslugaComplex_id').getStore().removeAll();

								if ( !newValue ) {
									setLpuSectionGlobalStoreFilter({
										isStom: true,
										regionCode: getGlobalOptions().region.number
									});

									setMedStaffFactGlobalStoreFilter({
										isStom: true,
										regionCode: getGlobalOptions().region.number
									});
								}
								else {
									setLpuSectionGlobalStoreFilter({
										isStom: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										regionCode: getGlobalOptions().region.number
									});

									setMedStaffFactGlobalStoreFilter({
										isStom: true,
										onDate: Ext.util.Format.date(newValue, 'd.m.Y'),
										regionCode: getGlobalOptions().region.number
									});
								}

								base_form.findField('LpuSection_uid').getStore().loadData(getStoreRecords(swLpuSectionGlobalStore));
								base_form.findField('MedStaffFact_id').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));
								base_form.findField('MedStaffFact_sid').getStore().loadData(getStoreRecords(swMedStaffFactGlobalStore));

								if ( base_form.findField('LpuSection_uid').getStore().getById(lpu_section_id) ) {
									base_form.findField('LpuSection_uid').setValue(lpu_section_id);
									base_form.findField('LpuSection_uid').fireEvent('change', base_form.findField('LpuSection_uid'), lpu_section_id, 0);
								}
								else {
									base_form.findField('LpuSection_uid').focus();
								}

								if ( base_form.findField('MedStaffFact_id').getStore().getById(med_staff_fact_id) ) {
									base_form.findField('MedStaffFact_id').setValue(med_staff_fact_id);
								}

								if ( base_form.findField('MedStaffFact_sid').getStore().getById(med_staff_fact_sid) ) {
									base_form.findField('MedStaffFact_sid').setValue(med_staff_fact_sid);
								}
							}.createDelegate(this)
						},
						name: 'EvnUslugaStom_setDate',
						plugins: [ new Ext.ux.InputTextMask('99.99.9999', false) ],
						tabIndex: TABINDEX_EUStomFIF + 1,
						width: 100,
						xtype: 'swdatefield'
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						xtype: 'fieldset',
						items: [{
							allowBlank: false,
							disabled: true,
							hiddenName: 'UslugaPlace_id',
							tabIndex: TABINDEX_EUStomFIF + 2,
							value: 1,
							width: 500,
							xtype: 'swuslugaplacecombo'
						}, {
							allowBlank: false,
							hiddenName: 'LpuSection_uid',
							id: 'EUStomFIF_LpuSectionCombo',
							lastQuery: '',
							linkedElements: [
								'EUStomFIF_MedPersonalCombo',
								'EUStomFIF_MidMedPersonalCombo'
							],
							tabIndex: TABINDEX_EUStomFIF + 3,
							width: 500,
							xtype: 'swlpusectionglobalcombo'
						}]
					}, {
						autoHeight: true,
						style: 'padding: 2px 0px 0px 0px;',
						title: lang['vrach_vyipolnivshiy_uslugu'],
						xtype: 'fieldset',
						items: [{
							allowBlank: false,
							fieldLabel: lang['kod_i_fio_vracha'],
							hiddenName: 'MedStaffFact_id',
							id: 'EUStomFIF_MedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUStomFIF_LpuSectionCombo',
							tabIndex: TABINDEX_EUStomFIF + 4,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}, {
							allowBlank: true,
							fieldLabel: lang['sred_m_personal'],
							hiddenName: 'MedStaffFact_sid',
							id: 'EUStomFIF_MidMedPersonalCombo',
							lastQuery: '',
							listWidth: 750,
							parentElementId: 'EUStomFIF_LpuSectionCombo',
							tabIndex: TABINDEX_EUStomFIF + 5,
							width: 500,
							xtype: 'swmedstafffactglobalcombo'
						}]
					}, {
						allowBlank: false,
						fieldLabel: lang['kategoriya_uslugi'],
						hiddenName: 'UslugaCategory_id',
						isStom: true,
						listeners: {
							'change': function (combo, newValue, oldValue) {
								var index = combo.getStore().findBy(function(rec) {
									return (rec.get('UslugaCategory_id') == newValue);
								});

								combo.fireEvent('select', combo, combo.getStore().getAt(index));

								return true;
							}.createDelegate(this),
							'select': function (combo, record) {
								var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

								base_form.findField('UslugaComplex_id').clearValue();
								base_form.findField('UslugaComplex_id').lastQuery = '';
								base_form.findField('UslugaComplex_id').getStore().removeAll();
								base_form.findField('UslugaComplex_id').getStore().baseParams.query = '';

								base_form.findField('UslugaComplex_id').setLpuLevelCode(0);

								if ( !record ) {
									base_form.findField('UslugaComplex_id').setUslugaCategoryList();
									return false;
								}

								base_form.findField('UslugaComplex_id').setUslugaCategoryList([ record.get('UslugaCategory_SysNick') ]);

								return true;
							}.createDelegate(this)
						},
						listWidth: 400,
						tabIndex: TABINDEX_EUStomFIF + 6,
						width: 250,
						xtype: 'swuslugacategorycombo'
					}, {
						border: false,
						layout: 'form',
						hidden: !(getGlobalOptions().region && getGlobalOptions().region.nick == 'perm'),
						items: [{
							fieldLabel: lang['uslugi_po_mes'],
							hiddenName: 'EvnUslugaStom_IsMes',
							listeners: {
								'change': function (combo, newValue, oldValue) {
									var index = combo.getStore().findBy(function(rec) {
										return (rec.get('YesNo_id') == newValue);
									});

									combo.fireEvent('select', combo, combo.getStore().getAt(index));

									return true;
								}.createDelegate(this),
								'select': function (combo, record) {
									var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

									if ( record && record.get('YesNo_Code') == 1 && !Ext.isEmpty(this.Mes_id) ) {
										if ( this.action == 'add' || this.action == 'edit' ) {
											base_form.findField('UslugaComplex_id').clearValue();
											base_form.findField('UslugaComplex_id').lastQuery = 'This query sample that is not will never appear';
											base_form.findField('UslugaComplex_id').getStore().removeAll();
										}

										base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = this.Mes_id;
									}
									else {
										base_form.findField('UslugaComplex_id').getStore().baseParams.Mes_id = null;
									}

									return true;
								}.createDelegate(this)
							},
							tabIndex: TABINDEX_EUStomFIF + 7,
							width: 100,
							xtype: 'swyesnocombo'
						}]
					}, {
						border: false,
						layout: 'column',
						items: [{
							border: false,
							width: 500,
							layout: 'form',
							items: [{
								fieldLabel: lang['usluga'],
								hiddenName: 'UslugaComplex_id',
								id: 'EUStomFIF_UslugaComplexCombo',
								listWidth: 600,
								tabIndex: TABINDEX_EUStomFIF + 8,
								width: 350,
								xtype: 'swuslugacomplexnewcombo'
							}]
						}, {
							border: false,
							layout: 'form',
							width: 100,
							items: [{
								handler: function()  {
									this.addUslugaToList();
								}.createDelegate(this),
								iconCls: 'add16',
								id: 'EUStomFIF_UslugaAddButton',
								tabIndex: TABINDEX_EUStomFIF + 9,
								text: lang['dobavit'],
								tooltip: lang['dobavlenie_uslugi_v_tablitsu'],
								xtype: 'button'
							}]
						}]
					}]
				})],
				region: 'north'
			}),
				this.UslugaListPanel
			]
		})
		sw.Promed.swEvnUslugaStomFastInputWindow.superclass.initComponent.apply(this, arguments);

		this.findById('EUStomFIF_LpuSectionCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

			if ( getGlobalOptions().region ) {
				switch ( getGlobalOptions().region.nick ) {
					case 'perm':
						base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = newValue;
					break;

					case 'ufa':
						base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
					break;
				}
			}
		}.createDelegate(this));

		this.findById('EUStomFIF_MedPersonalCombo').addListener('change', function(combo, newValue, oldValue) {
			var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();

			if ( getGlobalOptions().region ) {
				switch ( getGlobalOptions().region.nick ) {
					case 'perm':
						var index = combo.getStore().findBy(function(rec) { return (rec.get('MedStaffFact_id') == newValue); });

						if ( index >= 0 ) {
							base_form.findField('UslugaComplex_id').getStore().baseParams.LpuSection_id = combo.getStore().getAt(index).get('LpuSection_id');
						}
					break;

					case 'ufa':
						base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());
					break;
				}
			}
		}.createDelegate(this));

		this.findById('EUStomFIF_LpuSectionCombo').addListener('keydown', function(inp, e) {
			if ( e.getKey() == Ext.EventObject.TAB && e.shiftKey == true ) {
				e.stopEvent();
				this.buttons[this.buttons.length - 1].focus();
			}
		}.createDelegate(this));

		this.UslugaListPanel.addListenersFocusOnFields();
	},
	keys: [{
		alt: true,
		fn: function(inp, e) {
			var current_window = Ext.getCmp('EvnUslugaStomFastInputWindow');

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
	layout: 'border',
	listeners: {
		'hide': function(win) {
			win.onHide();
		}
	},
	maximizable: true,
	minHeight: 450,
	minWidth: 700,
	modal: true,
	onHide: Ext.emptyFn,
	plain: true,
	resizable: true,
	show: function() {
		sw.Promed.swEvnUslugaStomFastInputWindow.superclass.show.apply(this, arguments);

		this.restore();
		this.center();

		var base_form = this.findById('EvnUslugaStomFastInputForm').getForm();
		base_form.reset();

		base_form.findField('UslugaComplex_id').clearBaseParams();
		base_form.findField('UslugaComplex_id').setAllowedUslugaComplexAttributeList([ 'stom' ]);

		if ( getGlobalOptions().region && getGlobalOptions().region.nick.inlist([ 'pskov' ]) ) {
			base_form.findField('UslugaComplex_id').setDisallowedUslugaComplexAttributeList([ 'vizit' ]);
		}

		this.callback = Ext.emptyFn;
		this.formStatus = 'edit';
		this.Mes_id = null;
		this.onHide = Ext.emptyFn;
		this.payTypeId = null;

		if ( !arguments[0] || !arguments[0].formParams ) {
			sw.swMsg.alert(lang['soobschenie'], lang['nevernyie_parametryi'], function() { this.hide(); }.createDelegate(this) );
			return false;
		}

		if ( arguments[0].callback ) {
			this.callback = arguments[0].callback;
		}

		if ( arguments[0].Mes_id ) {
			this.Mes_id = arguments[0].Mes_id;
		}

		if ( arguments[0].onHide ) {
			this.onHide = arguments[0].onHide;
		}

		if ( arguments[0].PayType_id ) {
			this.payTypeId = arguments[0].PayType_id;
		}

		this.findById('EUStomFIF_PersonInformationFrame').load({
			Person_id: (arguments[0].Person_id ? arguments[0].Person_id : ''),
			Person_Birthday: (arguments[0].Person_Birthday ? arguments[0].Person_Birthday : ''),
			Person_Firname: (arguments[0].Person_Firname ? arguments[0].Person_Firname : ''),
			Person_Secname: (arguments[0].Person_Secname ? arguments[0].Person_Secname : ''),
			Person_Surname: (arguments[0].Person_Surname ? arguments[0].Person_Surname : ''),
			callback: function() {
				var field = base_form.findField('EvnUslugaStom_setDate');
				clearDateAfterPersonDeath('personpanelid', 'EUStomFIF_PersonInformationFrame', field);
			}
		});

		this.UslugaListPanel.removeAll({
			addEmptyRecord: true
		});

		// Для Перми устанавливаем параметр "Услуги по МЭС"
		if ( getGlobalOptions().region && getGlobalOptions().region.nick == 'perm' ) {
			if ( !Ext.isEmpty(this.Mes_id) ) {
				base_form.findField('EvnUslugaStom_IsMes').enable();
				base_form.findField('EvnUslugaStom_IsMes').setValue(2);
			}
			else {
				base_form.findField('EvnUslugaStom_IsMes').disable();
				base_form.findField('EvnUslugaStom_IsMes').setValue(1);
			}

			base_form.findField('EvnUslugaStom_IsMes').fireEvent('change', base_form.findField('EvnUslugaStom_IsMes'), base_form.findField('EvnUslugaStom_IsMes').getValue());
		}

		base_form.setValues(arguments[0].formParams);

		if ( base_form.findField('EvnUslugaStom_setDate').getValue() ) {
			base_form.findField('EvnUslugaStom_setDate').fireEvent('change', base_form.findField('EvnUslugaStom_setDate'), base_form.findField('EvnUslugaStom_setDate').getValue());
		}

		if ( base_form.findField('UslugaCategory_id').getStore().getCount() == 1 ) {
			base_form.findField('UslugaCategory_id').disable();
			base_form.findField('UslugaCategory_id').setValue(base_form.findField('UslugaCategory_id').getStore().getAt(0).get('UslugaCategory_id'));
		}

		base_form.findField('UslugaCategory_id').fireEvent('change', base_form.findField('UslugaCategory_id'), base_form.findField('UslugaCategory_id').getValue());

		base_form.findField('LpuSection_uid').focus(true, 250);

		base_form.clearInvalid();
	},
	title: lang['stomatologicheskie_uslugi_byistryiy_vvod'],
	width: 800
});
